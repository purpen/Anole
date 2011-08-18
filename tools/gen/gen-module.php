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
if(count($params) !== 2){
    echo "Usage:gen-module <module's name>\n";
    echo "gen-module <module name>\n";
    echo "   gen-module Blog\n";
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
    $building->setDataRoot($configs['Anole.data_dir']);
    $building->setModule($params[1]);
    $building->genModule();
}catch(Exception $e){
	echo "Gen Model[$params[1]] Failed:".$e->getMessage()."\n";
	exit();
}
/**vim:sw=4 et ts=4 **/
?>