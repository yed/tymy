<?php
/**
 * TEST: Test Supplier class
 * 
 */

namespace Test;

use Nette;
use Tester\Assert;
use Tester\TestCase;
use Environment;

$container = require __DIR__ . '/../bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Environment::skip('Test skipped as set in config file.');
}

class SupplierTest extends TestCase {
    
    /** @var \App\Model\Supplier */
    private $supplier;
    
    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function getTeams(){
        return[
            ["flrbl"],["hb"],["monkeys"],["3sb"],["uhblrptzdf"],["jirka"],["novyhcpuk"],["CZ"],["fbk-atom-praha-mb"],["CZ"],["mamy"],["hudebni"],["rektalka"],["lc"],["alles"],["hawks"],["dudaci"],["gjk"],["fuj"],["sos"],["open"],["luzinka"],["peaceegg"],["EN"],["gymjbc"],["kypr"],["dreamteam"],["chlupatazaba"],["CZ"],["atruc"],["petactyricitka"],["miniboleslav"],["beach"],["dandi"],["ybw"],["desitka"],["volejbal"],["zlatnicka"],["kusplastu"],["102ppr"],["rats"],["CZ"],["skv"],["CZ"],["fbk-atom-praha-m"],["florbalpraha"],["fs"],["member"],["zz"],["past"],["pajda"],["zkolitomerice"],["CZ"],["vb"],["fbk-atom-trenink-w"],["fbk-atom-praha-j"],["fbk-atom-praha-s"],["lk-atom-praha"],["beachz"],["kvetnak"],["ls"],["juniori"],["askcbjintes"],["CZ"],["tonda"],["chaos"],["11-pp"],["volejbalctvrtek"],["mamapap"],["invalidovna"],["verbindungsproblem"],["pondeli"],["kalamajka"],["bksluneta"],["uk"],["kotelnabohumin"],["fbcletohrad"],["bycizlazy"],["CZ"],["brn"],["hairyjet"],["rambo"],["vyborkkvfu"],["CZ"],["dorost"],["vm"],["freestylefrisbee"],["arman"],["loukic"],["t-a-c-p"],["beachuk"],["zalymada"],["hcdallmayr"],["CZ"],["hrajeme"],["watches"],["zkodc"],["keyon"],["vrsovice"],["uhrineves"],["vesan"],["CZ"],["accessories"],["zizkac"],["CZ"],["chrustenice"],["floraflorbal"],["skpcb"],["wm"],["ulita"],["achg"],["frisbee-nachod"],["jilove"],["perhotam"],["rcr"],["omega"],["milgauss"],["cvut"],["180"],["projekt"],["slaviaztp"],["yb"],["ubadminton"],["kkvfu"],["kafky"],["strejdamike"],["gebaek"],["kladska"],["ycexbetszv"],["tomashr70"],["4brn-cb"],["4brncb"],["imran"],["CZ"],["CZ"],["CZ"],["rodinka"],["dg"],["lady-datejust"],["cheeky"],["bisaci"],["hrusicemix"],["oreldd"],["CZ"],["agilitykkk"],["fbk-atom-k"],["fnfa"],["CZ"],["inchabove"],["breguet"],["day-date"],["sk-lesany"],["fotbalhradistko"],["demo"],["ddc"],["CZ"],["blbak"],["CZ"],["volejstreda"],["fujtt"],["CZ"],["xcyv"],["CZ"],["florbalove"],["chrustenice-zaci"],["drevaci"],["cmelaci"],["beachstrahov"],["marianka"],["matrix"],["ples2012"],["zizalyzabori"],["zichlinek"],["loginohec"],["vocem"],["tomes010"],["jurasz"],["mix"],["spacir"],["baskethrado"],["michacky"],["bckladno"],["tios"],["jelita"],["agilitysemily"],["kounicka"],["rolex"],["jenec"],["blbl"],["e-floorball"],["araceli"],["hrajemevolejbal"],["CZ"],["malina"],["dogfrisbee"],["dnd"],["CZ"],["mu"],["pokus"],["CZ"],["utery"],["treemonkeys"],["moraviandragons"],["agilitylipnik"],["treninky-agility"],["fn"],["florbaldd"],["badminton"],["speedmaster"],["rcplzen"],["yoyo"],["resslovka"],["CZ"],["pdf"],["liberec-wanderers"],["frkot"],["juliska"],["fotbalek"],["rychlost"],["krkslovan"],["vbw"],["kp-vedeni"],["spad"],["kurzy"],["ks"],["preview"],["taborovy"],["hudek"],["bisaci2014"],["pd"],["nacapa"],["p7"],["ruagswqvmf"],["kurz"],["runkanice"],["rb"],["morinka"],["datejust"],["tiffany"],["vajecan"],["CZ"],["CZ"],["znqqbwdqoy"],["vprztludlk"],["diwidgkujn"],["longines"],["bhryrtjvsa"],["bppgousadp"],["eegmubpcsk"],["oxgpqtfddx"],["hzbeach"],["frnda"],["medik"],["kunava"],["wcbuwomen"],["hahu"],["chopard"],["women"],["nohejbal"],["kp-ftb-1"],["kp-ik-1"],["lehovec"],["kp-ftb-3"],["kp-cck-gmh"],["princezny"],["vocemavl"],["gaudeamus"],["jarosovci"],["ctvrtecni-fotbal"],["CZ"],["agility"],["panerai"],["askcb2015"],["vodnikuba"],["3262"],["aksokolici"],["breitling"],["bvlgari"],["honney"],["brno"],["badmintonchrast"],["fkharrachov"],["t-roller"],["wcbumixed"]
        ];
    }
    
    /**
     * @dataProvider getTeams
     */
    function testSupplier($team){
        $this->supplier = $this->container->getByType('App\Model\Supplier');
        $tapi_config = $this->supplier->getTapi_config();
        Assert::truthy($tapi_config);
        $tapi_config["tym"] = $team;
        $root = $tapi_config["root"];
        $this->supplier->setTapi_config($tapi_config);

        Assert::equal($team, $this->supplier->getTym());
        Assert::equal("http://$team.$root", $this->supplier->getTymyRoot());
        Assert::equal("http://$team.$root/api", $this->supplier->getApiRoot());
        Assert::equal("btn-outline-success", $this->supplier->getRoleClass("SUPER"));
        Assert::equal("btn-outline-info", $this->supplier->getRoleClass("USR"));
        Assert::equal("btn-outline-warning", $this->supplier->getRoleClass("ATT"));
        Assert::equal("warning", $this->supplier->getStatusClass("LAT"));
        Assert::equal("danger", $this->supplier->getStatusClass("NO"));
        Assert::equal("success", $this->supplier->getStatusClass("YES"));
        Assert::equal("warning", $this->supplier->getStatusClass("DKY"));
        Assert::type("array", $this->supplier->getEventColors());
    }
    
    function testSupplierVersion(){
        $version = $this->supplier->getVersion(0);
        Assert::type("object", $version);
        Assert::type("int", $version->year);
        Assert::type("int", $version->month);
        Assert::type("int", $version->day);
        Assert::type("int", $version->hour);
        Assert::type("int", $version->minute);
        Assert::type("int", $version->second);
        Assert::type("int", $version->major);
        Assert::type("int", $version->minor);
        Assert::type("int", $version->patch);
        Assert::type("string", $version->version);
        Assert::equal($version->version, $version->major . "." . $version->minor . "." . $version->patch);
        Assert::type("string", $version->date);
        
    }
}

$test = new SupplierTest($container);
$test->run();
