<?php
/**
 * TEST: Test Discussion on TYMY api
 * 
 */

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

class APIDiscussionTest extends Tester\TestCase {

    private $container;
    private $login;
    private $loginObj;
    private $authenticator;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function setUp() {
        parent::setUp();
        $this->authenticator = new \App\Model\TestAuthenticator();
    }
    
    function tearDown() {
        parent::tearDown();
    }
    
    function login(){
        $this->loginObj = new \Tymy\Login();
        $this->login = $this->loginObj->team("dev")
                ->setUsername($GLOBALS["username"])
                ->setPassword($GLOBALS["password"])
                ->fetch();
    }
    
    /**
     * @throws Tymy\Exception\APIException
     */
    function testFetchFailsNoRecId(){
        $discussionObj = new \Tymy\Discussion(NULL, TRUE, 1);
        $discussion = $discussionObj
                ->team("dev")
                ->fetch();
    }

    /**
     * @throws Tymy\Exception\APIException
     */
    function testFetchFailsPageDoNotExist(){
        $discussionObj = new \Tymy\Discussion(NULL, TRUE, -1);
        $discussion = $discussionObj
                ->team("dev")
                ->recId(1)
                ->fetch();
    }
    
    /**
     * @throws Tymy\Exception\APIException
     */
    function testFetchNotLoggedInFails404() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["tym" => "testteam", "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");


        $discussionObj = new \Tymy\Discussion(NULL, TRUE, 1);
        $discussionObj
                ->presenter($mockPresenter)
                ->recId(1)
                ->fetch();
    }
    
    /**
     * @throws Nette\Application\AbortException
     */
    function testFetchNotLoggedInRedirects() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["tym" => "dev", "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");


        $discussionObj = new \Tymy\Discussion(NULL, TRUE, 1);
        $discussionObj
                ->presenter($mockPresenter)
                ->recId(1)
                ->fetch();
    }
    
    function testFetchSuccess() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Discussion');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => "dev", "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["username"], $GLOBALS["password"]);

        $discussionObj = new \Tymy\Discussion($mockPresenter, TRUE, 1);
        $discussionObj->recId(1)
                ->fetch();
        Assert::true(is_object($discussionObj));
        Assert::true(is_object($discussionObj->result));
        Assert::type("string",$discussionObj->result->status);
        Assert::same("OK",$discussionObj->result->status);
        Assert::true(is_object($discussionObj->result->data->discussion));//returned discussion object
        Assert::type("int",$discussionObj->result->data->discussion->id);
    }
    
    /**
     * @throws Tymy\Exception\APIException
     */
    function testPostFailsNoRecId() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Discussion');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => "dev", "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["username"], $GLOBALS["password"]);

        $insertText = "AUTOTEST automatic discussion post";
        
        $discussionObj = new \Tymy\Discussion($mockPresenter, TRUE, 1);
        $discussionObj
                ->insert($insertText);
        Assert::true(is_object($discussionObj));
        Assert::true(is_object($discussionObj->result));
        Assert::type("string",$discussionObj->result->status);
        Assert::same("OK",$discussionObj->result->status);
        
        Assert::true(FALSE);
    }
    
    function testPost() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Discussion');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => "dev", "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["username"], $GLOBALS["password"]);

        $insertText = "AUTOTEST automatic discussion post";
        $discussionId = 2; //ID of Testovaci Diskuze
        $discussionObj = new \Tymy\Discussion($mockPresenter, TRUE, 1);
        $discussionObj
                ->recId($discussionId)
                ->insert($insertText);
        
        Assert::true(is_object($discussionObj));
        Assert::true(is_object($discussionObj->result));
        Assert::type("string",$discussionObj->result->status);
        Assert::same("OK",$discussionObj->result->status);

        Assert::true(is_object($discussionObj->result->data));

        Assert::type("int",$discussionObj->result->data->discussionId);
        Assert::same($discussionId,$discussionObj->result->data->discussionId);
        Assert::type("string",$discussionObj->result->data->post);
        Assert::same($insertText,$discussionObj->result->data->post);
        Assert::type("int",$discussionObj->result->data->createdById);
        Assert::same($this->login->id,$discussionObj->result->data->createdById);
        Assert::type("string",$discussionObj->result->data->createdAt);
        
        Assert::type("bool",$discussionObj->result->data->sticky);
        Assert::same(FALSE,$discussionObj->result->data->sticky);
        Assert::type("bool",$discussionObj->result->data->newPost);
        Assert::same(FALSE,$discussionObj->result->data->newPost);
        Assert::type("string",$discussionObj->result->data->createdAtStr);
        
        Assert::true(is_object($discussionObj->result->data->createdBy));
        Assert::type("int",$discussionObj->result->data->createdBy->id);
        Assert::same($this->login->id,$discussionObj->result->data->createdBy->id);
        Assert::type("string",$discussionObj->result->data->createdBy->login);
        Assert::type("string",$discussionObj->result->data->createdBy->callName);
        Assert::type("string",$discussionObj->result->data->createdBy->pictureUrl);
    }
    
    function testSearch() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Discussion');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => "dev", "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["username"], $GLOBALS["password"]);

        $discussionId = 2; //ID of Testovaci Diskuze
        $searchHash = "d658281299b4b756f8f6385407eaa442";
        $discussionObj = new \Tymy\Discussion($mockPresenter, TRUE, 1);
        $discussionObj
                ->recId($discussionId)
                ->search($searchHash)
                ->fetch();
        
        Assert::true(is_object($discussionObj));
        Assert::true(is_object($discussionObj->result));
        Assert::type("string",$discussionObj->result->status);
        Assert::same("OK",$discussionObj->result->status);
        Assert::true(is_object($discussionObj->result->data->discussion));//returned discussion object
        Assert::type("int",$discussionObj->result->data->discussion->id);
        Assert::same($discussionId,$discussionObj->result->data->discussion->id);
        
        Assert::type("array",$discussionObj->result->data->posts);
        Assert::same(1,count($discussionObj->result->data->posts));
        
        Assert::true(is_object($discussionObj->result->data->posts[0]));
        
        Assert::type("int",$discussionObj->result->data->posts[0]->id);
        Assert::type("int",$discussionObj->result->data->posts[0]->discussionId);
        Assert::same($discussionId,$discussionObj->result->data->posts[0]->discussionId);
        Assert::type("string",$discussionObj->result->data->posts[0]->post);
        Assert::contains($searchHash, $discussionObj->result->data->posts[0]->post);
        Assert::type("int",$discussionObj->result->data->posts[0]->createdById);
        Assert::same(11,$discussionObj->result->data->posts[0]->createdById);
        
        Assert::type("string",$discussionObj->result->data->posts[0]->createdAt);
        Assert::type("int",$discussionObj->result->data->posts[0]->updatedById);
        Assert::type("bool",$discussionObj->result->data->posts[0]->sticky);
        Assert::type("bool",$discussionObj->result->data->posts[0]->newPost);
        Assert::type("string",$discussionObj->result->data->posts[0]->createdAtStr);
        Assert::true(is_object($discussionObj->result->data->posts[0]->createdBy));
    }

}

$test = new APIDiscussionTest($container);
$test->run();