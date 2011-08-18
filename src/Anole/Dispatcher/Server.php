<?php
/**
 * Action执行工具类
 * 
 * @author purpen
 * @version $Id$
 */
class Anole_Dispatcher_Server extends Anole_Object {
	
	private static $_instance;
	
	public static function run(){
        if(is_null(self::$_instance)){
        	self::$_instance = new Anole_Dispatcher_Server();
        }
		
        self::$_instance->service();
	}
	
	protected function service(){
	   	$request = new Anole_Dispatcher_Request_Http();
	   	$response = new Anole_Dispatcher_Response_Http();
	   	$this->serviceAction($request,$response);
	}
	/**
	 * action interface
	 *
	 * @param Anole_Dispatcher_Request_Http $request
	 * @param Anole_Dispatcher_Response_Http $response
	 */
	protected function serviceAction($request,$response){
		try{
			$context = Anole_Dispatcher_Context::getContext();
			$context->setRequest($request);
			$context->setResponse($response);
			
			$mapping = Anole_Dispatcher_ActionMapper::parse($request);
			$request->setParams($mapping->getParams());
			$module = $mapping->getNamespace();
			$action = Anole_Util_Inflector::camelize($mapping->getAction());
			$method = $mapping->getMethod();
			$action_class = Anole_Util_Inflector::classify($module.'_Action_'.$action);
			self::debug("Action_Class:$action_class and Action_Method:$method.", __METHOD__);
			
			$invocation = new Anole_Dispatcher_ActionInvocation($context,$action_class,$method);
			$invocation->invoke();
			self::debug("Execute action is ok!", __METHOD__);
			
			$response->flushResponse();
			
		}catch(Anole_Dispatcher_Exception $e){
			self::warn("action dispatcher exception:".$e->getMessage(),__METHOD__);
		}catch(Anole_Exception $e){
			self::warn("action exception:".$e->getMessage(),__METHOD__);
		}catch(Exception $e){
			self::warn("exception:".$e->getMessage(),__METHOD__);
		}
	}
	
}
/**vim:sw=4 et ts=4 **/
?>