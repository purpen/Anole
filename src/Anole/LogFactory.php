<?php
/**
 * LogFactory dispatcher
 *
 * @version $Id$
 * @author purpen
 */
class Anole_LogFactory{
	protected static $_instances = array();
	/**
	 * get logger from the log object factory
	 *
	 * @param sting $log_name
	 * @return Anole_Log_FileLog
	 */
	public static function getLog($log_name='default'){
		if(!isset(self::$_instances[$log_name])){
			$logs = Anole_Config::get('logs.'.$log_name);
			if(empty($logs)){
				$logs = Anole_Config::get('logs.default');
			}
			$class = $logs['class'];
			$options = $logs['options'];
			if(empty($class)){
				throw new Anole_Exception('logs.'.$log_name.':class is null!');
			}
			if(class_exists($class)){
				self::$_instances[$log_name] = new $class($options);
			}else{
				throw new Anole_Exception('logs class:'.$class."isn't exist!");
			}
			
		}
		return self::$_instances[$log_name];
	}
}
/**vim:sw=4 et ts=4 **/
?>