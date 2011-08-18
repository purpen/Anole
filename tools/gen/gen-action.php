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
    echo "Model class name is null!\n";
    echo "Usage:$0 <action classname> <model classname>\n";
    echo "example:\n";
    echo "   gen-action Forum_Action_Topic Forum_Model_Topic\n";
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
	$action_tpl = $configs['Anole.data_dir'].'/gen/action.class.php';
	$building->setTpl($action_tpl);
	$building->genAction($params[1], $params[2]);
	echo "Gen-Action [$params[1]] is done.\n";
}catch(Exception $e){
    echo "Gen-Action Create Failed:".$e->getMessage()."\n";
    exit();
}
/**vim:sw=4 et ts=4 **/
?>