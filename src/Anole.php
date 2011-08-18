<?php
/**
 * Anole框架入口类
 * 
 * @author purpen
 * @version $Id$
 * 
 */
class Anole{
	
	private function __construct(){}
	
	public static function initConfig(){
	    $config_dir = ANOLE_DATA_ROOT.'/config';
	    
	    $cfgs = array('Anole.lib_dir'=>ANOLE_LIB_ROOT,
	        'Anole.data_dir'=>ANOLE_DATA_ROOT,
	        'Anole.config_dir'=>$config_dir);
	    Anole_Config::add($cfgs);
	    
	    Anole_Config::load('anole');
	    $config_files = Anole_Config::get('anole.load_all_config');
	    if(!empty($config_files)){
	        for($i=0;$i<count($config_files);$i++){
	            Anole_Config::load($config_files[$i]);
	        }
	    }
	}
	
	public static function boot(){
		self::initConfig();
		//print_r(Anole_Config::all());
		Anole_ClassLoader::addIncludePath(ANOLE_LIB_ROOT);
		Anole_ClassLoader::addIncludePath(Anole_Config::get('classes.3rd_path'));
		Anole_ClassLoader::addIncludePath(Anole_Config::get('classes.class_path'));
		Anole_ClassLoader::applyIncludePath();
		Anole_ClassLoader::initAutoload();
		
	}
}
//config params
if(!defined('ANOLE_LIB_ROOT') || !defined('ANOLE_DATA_ROOT')){
	die('You must defined ANOLE_LIB_ROOT,ANOLE_DATA_ROOT'."\n");
}
if(!defined('Anole_APP')){
    define('Anole_APP','anole');
}

//include file before _autoload
require_once ANOLE_LIB_ROOT.'/Anole/Config.php';
require_once ANOLE_LIB_ROOT.'/Anole/Object.php';
require_once ANOLE_LIB_ROOT.'/Anole/Exception.php';
require_once ANOLE_LIB_ROOT.'/Anole/ClassLoader.php';
//yuml support
require_once ANOLE_LIB_ROOT.'/Anole/Yaml/Spyc.php';

?>