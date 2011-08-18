<?php
/**
 * All Interceptors Interface
 *
 * @version $Id$
 * @author purpen
 */
interface Anole_Dispatcher_Interceptor {
	function init();
	function intercept(Anole_Dispatcher_ActionInvocation $invocation);
	function destory();
}
/**vim:sw=4 et ts=4 **/
?>