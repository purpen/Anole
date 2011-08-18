<?php
/**
 * Json Result Class
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Dispatcher_Result_Json extends Anole_Dispatcher_Result_Abstract {
	
	public function render(){
		$context = $this->_invocation->getInvocationContext();
		$json_data = array();
		$json_data['error_code'] = $context->get('error_code');
		$json_data['error_msg']  = $context->get('error_msg');
		$json_data['has_error']  = !is_null($json_data['error_code']);
		foreach($context->getAll() as $k=>$v){
			$json_data['data'][$k] = $v;
		}
		$output = json_encode($json_data);
		$context->getResponse()->setBuffer($output)->setContentCharset('utf-8');
	}
	
}
/**vim:sw=4 et ts=4 **/
?>