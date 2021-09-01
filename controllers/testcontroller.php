<?php

namespace NewCMS\Controllers;

use NewCMS\Repositories\NewComplectPartRepository;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataMapper\TRMSafetyFields;
use TRMEngine\Helpers\TRMLib;

class TestController extends BaseController
{

/**
 *  выводим информацию о сервере и переменные PHP
 */
public function actionInfo()
{
    TRMLib::sp(ceil(9.0));
    TRMLib::sp("ROOT: " . ROOT);
    TRMLib::sp("WEB: " . WEB);
    //TRMLib::sp(TRMENGINE);
    TRMLib::sp("CONFIG: " . CONFIG);
    TRMLib::sp("__DIR__: " . __DIR__);
    TRMLib::sp("getcwd(): " . getcwd());
    phpinfo();
    exit();
}

public function actionAddon($DigitsCount)
{
    //$DigitsCount = 8;
    $Addon = sprintf( "%'.0{$DigitsCount}d", rand(0, pow(10, $DigitsCount)-1 ) );
    echo $Addon;
    echo PHP_EOL . "<br>" . PHP_EOL;
    $Addon = random_bytes($DigitsCount);
    echo $Addon;
    echo PHP_EOL . "<br>" . PHP_EOL;
    
    $Addon = substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, $DigitsCount);
    echo $Addon;
}


public function actionTest002()
{
    $XMLStartStr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><yml_catalog date=\""
            . date("Y-m-d H:i", time() ) // "2017-02-05 17:22"
            . "\" />";
    $XML = new \SimpleXMLElement($XMLStartStr);
    $Str = "<&>'\\";
    $XML->addChild("newnode", htmlspecialchars(addslashes($Str)) );
    //$XML->addChild("newnode", $Str );
    echo "<pre>";
    echo $Str;
    echo "</pre>";
    echo "<pre>";
    echo htmlspecialchars(addslashes($Str));
    echo "</pre>";
    echo "<pre>";
    echo $XML->asXML();
    echo "</pre>";
    
}


public function actionTest1()
{
TRMLib::ip($this->Request->server->get("HTTP_HOST") );
TRMLib::ip($this->Request->server->get("REQUEST_SCHEME") );
    $test = new TestClass;
    
    $ar = $test->getArray(); // clone
    
    $ar[] = "One";
    $ar[] = "Two";
    
    $test->printArray(); // empty array

    TRMLib::ip($ar);
    
    $test2 = $test; // reference
    
    $test->changeArray();
    
    $test2->printArray(); // print "Some text..."
    
    $TestContainer = new TestContainerClass;
    $test3 = $TestContainer->getTestObject();
    
    $test3->changeArray();
    
    $TestContainer->printTest();
    
    $russtr = "  <div>Новая  строка с русскими буквами для конвертации, размер - (500х700х12) мм!!! </div>";
    echo TRMLib::translit( $russtr, true, GlobalConfig::$ConfigArray["Charset"] );
    
    $arr3 = array();
    $arr3["one_key"] = "Hello!!!";
    $arr3["two_key"] = "Hello!!!";
    $newkey = "";
    
    $arr3[$newkey] = "Some text...";
    
TRMLib::ip($arr3);
    $ao = new ArrayObject($arr3);
    $iterator = $ao->getIterator();
    
    foreach ($iterator as $key => $val )
    {
        echo "<pre>{$key} => {$val}</pre>";
    }
    

    $arr5 = array();
    $arr5["i1"]["i2"]["i3"] = 1345;
    $arr5["t5"]["t7"] = "retywert";
TRMLib::ap($arr5);

    $TestCollection = new NewComplectPartRepository();

    TRMLib::ip( $TestCollection->getBy("ID_Complect", 13354) );

}

public function actionTest2()
{
    TRMLib::sp( sprintf("%'.010d", 128) );
    TRMLib::sp( $this->Request->server->get("DOCUMENT_ROOT") );
    TRMLib::sp( FULLWEB );
    
    $arr = array();
    
    $arr["object1"]["Item2"] = 55;
    
    TRMLib::ap($arr);
    
    TRMLib::ip( isset($arr["object5"]["Item7"]) );
}

public function actionTestV8()
{
//    $vue_source = file_get_contents(ROOT . '/web/js/chunk-vendors.71471957.js');
//    $app_source = file_get_contents(ROOT . '/web/js/app.8781b8d6.js');
    TRMLib::ap(get_loaded_extensions());
//    $v8 = new \V8Js();
//
//    $v8->executeString('var process = { env: { VUE_ENV: "server", NODE_ENV: "production" }}; this.global = { process: process };');
//    $v8->executeString($vue_source);
//    $v8->executeString($app_source);
}


} // class TestController


function TTest(array &$arr1 )
{
    TRMLib::ap($arr1);
}

class TestClass
{
protected $arr = array();

public function getArray()
{
    return $this->arr;
}


public function printArray()
{
    TRMLib::ip($this->arr);
}

public function changeArray()
{
    $this->arr[] = "Some text...";
}

} // TestClass


class TestContainerClass
{
protected $test;

public function __construct()
{
    $this->test = new TestClass;
}

public function getTestObject()
{
    return $this->test;
}

public function printTest()
{
    $this->test->printArray();
}

} // TestContainerClass