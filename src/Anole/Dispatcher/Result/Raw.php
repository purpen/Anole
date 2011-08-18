<?php
/**
 * raw data result class
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Dispatcher_Result_Raw extends Anole_Dispatcher_Result_Abstract {
	public function render(){
		$context = $this->_invocation->getInvocationContext();
		$data = $context->getResult('data');
		$content_type = $context->getResult('content_type');
		$charset = $context->getResult('charset');
		if(empty($charset)){
			$charset = 'utf-8';
		}
		if(!empty($content_type)){
			$context->getResponse()->setContentType($content_type)->setContentCharset($charset);
		}
		$this->setBuffer($data);
		$this->_executed = true;
	}
}
/**vim:sw=4 et ts=4 **/
?>