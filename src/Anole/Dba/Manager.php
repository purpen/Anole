<?php
/**
 * 数据库连接池抽象接口
 * 
 * @author purpen
 * @version $Id$
 */
abstract class Anole_Dba_Manager {
	
	protected static $_instances = array();
	
	private function __construct(){}
	
	public static function getDefaultConnection(){
		$dev = Anole_Config::get('database.dev');
		$dsn = isset($dev['default']) ? $dev['default'] : 'adodb';
		return self::getConnection($dsn);
	}
	
	public static function getConnection($dsn){
		if(isset(self::$_instances[$dsn])){
			return self::$_instances[$dsn];
		}
		$i = strpos($dsn,':');
		if($i !== false){
			$driver = substr($dsn,0,$i);
		}else{
			$driver = $dsn;
		}
		$class = Anole_Util_Inflector::classify('Anole_Dba_Adapter_'.$driver);
		if(!class_exists($class)){
			throw new Anole_Dba_Exception("Unknow database adapter:$class");
		}
		return self::$_instances[$dsn] = new $class($dsn);
	}
}
/**vim:sw=4 et ts=4 **/
?>