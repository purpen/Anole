<?php
/**
 * support jQuery taconite plugin result
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Dispatcher_Result_JQuery extends Anole_Dispatcher_Result_Smarty {
	
	public function init(){
		parent::init();
	}
	
	public function render(){
		$response = $this->_invocation->getInvocationContext()->getResponse();
		
		$context = $this->_invocation->getInvocationContext();
		$cross_domain = $context->getResult('cross_domain');
		$callback = $context->getResult('callback');
		
		$response->setContentCharset('utf-8');
		$response->setHeadersRaw('Cache-Control:no-store,no-cache, must-revalidate,private,pre-check=0, post-check=0, max-age=0,max-stale=0')
		->setHeadersRaw('Expires:Mon, 23 Jan 1978 12:52:30 GMT')
		->setHeadersRaw('Pragma:no-cache');
		
		parent::render();
		
		$content = "<taconite>\n".$this->_buffer."</taconite>";
		if($cross_domain){
			if($callback){
				$content = $callback.'("'.$content.'");';
			}else{
				$content = '$.taconite("'.$content.'");';
			}
			$response->setContentType('text/javascript');
		}else{
			$response->setContentType('text/xml');
		}
		
		
		$this->_buffer = $content;
	}
}
/**vim:sw=4 et ts=4 **/
?>