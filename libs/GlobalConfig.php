<?php
/**
 * Singleton для глобальных переменных конфигурации
 */
class GlobalConfig
{
/**
 * @var array - массив с параметрами конфигурации 
 * array( имя параметра => значение, ... )
 */
public static $ConfigArray = array();
protected static $inst;


protected function __construct($configfilepath)
{
    if(!is_file($configfilepath)) 
    {
        throw new Exception("Файл конфигурации не найден!");
    }
    GlobalConfig::$ConfigArray = include $configfilepath;
    if( empty(GlobalConfig::$ConfigArray) )
    {
        throw new Exception( "Не удалось загрузить конфигурацию![{$configfilepath}]" );
    }
}

/**
 * @param type $configfilepath - должен передаваться файл с содержимым типа:   return array("var1" => "value1", ...);
 * @return GlobalConfig
 */
public static function instance($configfilepath)
{
    if(!isset(GlobalConfig::$inst)) { GlobalConfig::$inst = new GlobalConfig($configfilepath); }
    return GlobalConfig::$inst;
}


} // GlobalConfig
