<?php
/**
 * Interceptor apple parameters
 * 
 * @version $Id$
 * @author purpen
 */
class Anole_Dispatcher_Interceptor_Parameters extends Anole_Dispatcher_Interceptor_Abstract {
	/**
	 * Interceptor method
	 *
	 * @param Anole_Dispatcher_ActionInvocation $invocation
	 */
    public function intercept(Anole_Dispatcher_ActionInvocation $invocation){
        $action = $invocation->getAction();
        //apply params by action/s set/get method
        Anole_Util_Dispatcher::applyDispatcherContextParams($invocation->getInvocationContext(),$action);
        
        return $invocation->invoke();
    }
}
/**vim:sw=4 et ts=4 **/
?>