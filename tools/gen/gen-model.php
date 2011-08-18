<?php
require_once '../init_config.php';
//get config vars
$configs = Anole_Config::all();
if(is_null($configs)){
	echo "Init Config is Null!\n";
	exit();
}
//get argc array
$params = $argv;
if(count($params) !== 3){
    echo "Usage:gen-model.php  <table name> <module name>\n";
    echo "example:\n";
    echo "   gen-model.php topic Forum\n";
    echo "this will generate Forum_Model_Topic model class file\n";
    exit();
}
try{
	$building = new Anole_Util_Gen_Building();
	$class_path = !empty($configs['classes.class_path']) ? $configs['classes.class_path'][0] : null;
    if(empty($class_path)){
        echo "Module[$params[1]] Class path  is NULL!\n";
        exit();
    }
    $building->setRoot($class_path);
	$table_tpl = $configs['Anole.data_dir'].'/gen/table.class.php';
	$model_tpl = $configs['Anole.data_dir'].'/gen/model.class.php';
	$building->setTpl($table_tpl);
	$building->setModelTpl($model_tpl);
	$building->setTable($params[1]);
	$building->setModule($params[2]);
	$building->genModel();
	echo "Gen-Model[$params[2]_Model_$params[1]] is done.\n";
}catch(Exception $e){
	echo "Gen-Model Create Failed:".$e->getMessage()."\n";
	exit();
}
/**vim:sw=4 et ts=4 **/
?>