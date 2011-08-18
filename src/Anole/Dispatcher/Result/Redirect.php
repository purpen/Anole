<?php
/**
 * redirect other url result class
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Dispatcher_Result_Redirect extends Anole_Dispatcher_Result_Abstract {
	
	protected function render(){
		$url = $this->_invocation->getInvocationContext()->getResult('url');
		$code = $this->_invocation->getInvocationContext()->getResult('code');
		
		$this->_invocation->getInvocationContext()->getResponse()->setRedirect($url,$code);
	}
	
}
/**vim:sw=4 et ts=4 **/
?>