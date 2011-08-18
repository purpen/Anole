<?php
/**
 * 设置参数
 * 
 * @author purpen
 * @version $Id$
 */
class Anole_Dispatcher_Interceptor_ModelDriven extends Anole_Dispatcher_Interceptor_Abstract {
	/**
	 * set parameters by model's set/get method
	 *
	 * @param Anole_Dispatcher_ActionInvocation $invocation
	 * @return void
	 */
	public function intercept(Anole_Dispatcher_ActionInvocation $invocation){
		$action = $invocation->getAction();
		if(!$action instanceof Anole_Dispatcher_Action_Interface_ModelDriven){
			self::warn('Action hasnot method:wiredModel,no anything to do,skip.', __METHOD__);
			return $invocation->invoke();
		}
		$model = $action->wiredModel();
		if($model === false || is_null($model)){
			self::warn('Action disabled autowired Or returns a NULL composed model,skip.', __METHOD__);
			return $invocation->invoke();
		}
		//set parameters by model's set/get method
		Anole_Util_Dispatcher::applyDispatcherContextParams($invocation->getInvocationContext(),$model);
		$invocation->invoke();
	}
	
}
/**vim:sw=4 et ts=4 **/
?>