<?php
/**
 * TEST: Test Attendance on TYMY api
 * 
 */

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';
Tester\Environment::skip('Temporary skipping');
if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class APIAttendanceTest extends ITapiTest {

    /** @var \Tymy\Attendance */
    private $attendance;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->attendance;
    }
    
    protected function setUp() {
        $this->attendance = $this->container->getByType('Tymy\Attendance');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */
    
    /**
     * @throws Tymy\Exception\APIException
     */
    function testPlanFailsNoEventId(){
        $attendanceObj = new \Tymy\Attendance();
        $attendance = $attendanceObj
                ->setSupplier($this->supplier)
                ->plan();
    }
    
    /**
     * @throws Tymy\Exception\APIException
     */
    function testPlanFailsNoPreStatus(){
        $attendanceObj = new \Tymy\Attendance();
        $attendance = $attendanceObj
                ->setSupplier($this->supplier)
                ->recId($GLOBALS["testedTeam"]["testEventId"])
                ->plan();
    }

    
    /**
     * @throws Tymy\Exception\APIException
     */
    function test404() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->testAuthenticator->setId(38);
        $this->testAuthenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->testAuthenticator->setArr(["tym" => "testteam", "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->testAuthenticator);
        $mockPresenter->getUser()->login("test", "test");

        $attendanceObj = new \Tymy\Attendance();
        $attendanceObj
                ->setPresenter($mockPresenter)
                ->recId($GLOBALS["testedTeam"]["testEventId"])
                ->preStatus("YES")
                ->plan();
    }
    
    /**
     * @throws Tymy\Exception\APIAuthenticationException
     */
    function test401() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->testAuthenticator->setId(38);
        $this->testAuthenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->testAuthenticator->setArr(["sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->testAuthenticator);
        $mockPresenter->getUser()->login("test", "test");


        $attendanceObj = new \Tymy\Attendance();
        $attendanceObj
                ->setPresenter($mockPresenter)
                ->recId($GLOBALS["testedTeam"]["testEventId"])
                ->preStatus("YES")
                ->plan();
    }
    
    
    function test401relogin() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;
        
        $mockPresenter->getUser()->setAuthenticator($this->tapiAuthenticator);
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        
        $attendanceObj = new \Tymy\Attendance($this->tapiAuthenticator, $mockPresenter);
        $attendanceObj
                ->setPresenter($mockPresenter)
                ->recId($GLOBALS["testedTeam"]["testEventId"])
                ->preStatus("YES")
                ->plan();
        
        $logoutObj = new \Tymy\Logout($this->tapiAuthenticator, $mockPresenter);
        $logoutObj ->logout();
        
        $attendanceObj2 = new \Tymy\Attendance($this->tapiAuthenticator, $mockPresenter);
        $attendanceObj2
                ->setPresenter($mockPresenter)
                ->recId($GLOBALS["testedTeam"]["testEventId"])
                ->preStatus("YES")
                ->plan();
    }
    
    function testPlanSuccess() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Event');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->testAuthenticator->setId($this->login->id);
        $this->testAuthenticator->setArr(["sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->testAuthenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        
        $allEvents = new \Tymy\Events($this->tapiAuthenticator, $mockPresenter);
        $allEventsObj = $allEvents
                ->setFrom(date("Ymd"))
                ->fetch();
        $idActionToUpdateOn = $allEventsObj[0]->id;

        $attendanceObj = new \Tymy\Attendance($this->tapiAuthenticator, $mockPresenter);
        $attendanceObj->recId($idActionToUpdateOn)
                ->preStatus("YES")
                ->preDescription("Tymyv2-AutoTest-yes")
                ->plan();
        Assert::type("array",$attendanceObj->postParams[0]);
        Assert::same(4,count($attendanceObj->postParams[0]));
        
        Assert::type("int",$attendanceObj->postParams[0]["userId"]);
        Assert::same($this->login->id,$attendanceObj->postParams[0]["userId"]);
        Assert::type("int",$attendanceObj->postParams[0]["eventId"]);
        Assert::same($idActionToUpdateOn,$attendanceObj->postParams[0]["eventId"]);
        Assert::type("string",$attendanceObj->postParams[0]["preStatus"]);
        Assert::same("YES",$attendanceObj->postParams[0]["preStatus"]);
        Assert::type("string",$attendanceObj->postParams[0]["preDescription"]);
        Assert::same("Tymyv2-AutoTest-yes",$attendanceObj->postParams[0]["preDescription"]); //Tested if POST params can be added as an array
        
        Assert::true(is_object($attendanceObj));
        Assert::true(is_object($attendanceObj->result));
        Assert::type("string",$attendanceObj->result->status);
        Assert::same("OK",$attendanceObj->result->status);

        //now check if the event is correctly filled
        $updatedEventObj = new \Tymy\Event($mockPresenter->tapiAuthenticator, $mockPresenter);
        $updatedEventObj
                ->recId($idActionToUpdateOn)
                ->fetch();
        
        Assert::true(is_object($updatedEventObj));
        Assert::true(is_object($updatedEventObj->result));
        Assert::type("string",$updatedEventObj->result->status);
        Assert::same("OK",$updatedEventObj->result->status);
        
        Assert::true(is_object($updatedEventObj->result->data));
        $found = FALSE;
        foreach ($updatedEventObj->result->data->attendance as $att) {
            if($att->userId == $this->login->id){
                $found = TRUE;
                Assert::type("int",$att->eventId);
                Assert::same($idActionToUpdateOn,$att->eventId);
                Assert::type("string",$att->preStatus);
                Assert::same("YES",$att->preStatus);
                Assert::type("string",$att->preDescription);
                Assert::same("Tymyv2-AutoTest-yes",$att->preDescription);
            }
        }
        Assert::true($found);
    }

}

$test = new APIAttendanceTest($container);
$test->run();