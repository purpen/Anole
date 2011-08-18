<?php
/**
 * Action加载规则解析
 * 
 * @author purpen
 * @version $Id$
 * 
 */
class Anole_Dispatcher_ActionMapper extends Anole_Object {
	/**
	 * parse request uri
	 *
	 * @param Anole_Dispatcher_Request_Http $request
	 * @return Anole_Dispatcher_ActionMapping
	 */
	public static function parse($request){
		$uri = $request->getPathInfo();
		$uri= trim($uri,'/');
		
		$mapping = new Anole_Dispatcher_ActionMapping();
		
		$tokens = explode('/', $uri);
		//get module namespace
		if(empty($tokens)){
			$module_namespace = '';
		}else{
			//get module namespace
			$alias = urldecode(array_shift($tokens));
			//lookup module's namespace
			$module_namespace = Anole_Config::get('mapping.'.$alias);
		}
		
		if(empty($module_namespace)){
			$module_namespace = Anole_Config::get('modules.default');
		}
		if(empty($module_namespace)){
			throw new Anole_Dispatcher_Exception('module namespace not match!');
		}
		$module = Anole_Config::get('modules.'.$module_namespace);
		if(empty($module)){
			throw new Anole_Dispatcher_Exception('unknown module id:'.$module_namespace);
		}
		if($module['actived'] == 0){
			throw new Anole_Dispatcher_Exception('module '.$module.' has been disabed!');
		}
		$mapping->setNamespace($module_namespace);
		
		//get action name
		if(empty($tokens)){
			$action = '';
		}else{
			$action = urldecode(array_shift($tokens));
		}
		if(empty($action)){
			$action = $module['default_action'];
		}
		if(empty($action)){
			throw new Anole_Dispatcher_Exception('action not match!');
		}
		$mapping->setAction($action);
		
		//get action method
		if(empty($tokens)){
			$method = 'execute';
		}else{
			$method = urldecode(array_shift($tokens));
			$method = Anole_Util_Inflector::methodlize($method);
		}
		$mapping->setMethod($method);
		
		//set the params from the uri
		$params = array();
		$key_ok = true;
		//key=>value not pairing
		if(count($tokens)%2 !== 0){
			$default_key = 'id';
			$key_ok = false;
		}
		for($i=0;$i<count($tokens);$i++){
			if($key_ok){
				//get the key
				$params_key = urldecode($tokens[$i]);
				$key_ok = false;
			}else{
				//get the value
				$params_value = urldecode($tokens[$i]);
				//set key=>value into params
				if(!empty($params_key)){
					$params[$params_key] = $params_value;
				}
				$key_ok = true;
			}
		}
		$mapping->setParams($params);
		
		return $mapping;
	}
}
/**vim:sw=4 et ts=4 **/
?>