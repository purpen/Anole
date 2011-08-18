<?php
/**
 * smarty result render
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Dispatcher_Result_Smarty extends Anole_Dispatcher_Result_Abstract {
	
	private $_smarty;
	
	public function setSmarty($smarty){
		$this->_smarty = $smarty;
		return $this;
	}
	
	/**
	 * get smarty instance
	 *
	 * @return Anole_Util_Smarty_Base
	 */
	protected function getSmarty(){
		if(is_null($this->_smarty)){
			$this->_smarty = Anole_Util_Smarty::factory();
			self::debug("get smarty instance is ok.", __METHOD__);
		}
		return $this->_smarty;
	}
	/**
	 * init smarty var
	 */
	public function init(){
		$smarty = $this->getSmarty();
		self::debug("result smarty init is done.",__METHOD__);
		if(!$smarty->isInitialized()){
			$smarty->initRuntimeDirectory();
		}
		
	}
	
	public function render(){
		$context = $this->_invocation->getInvocationContext();
		
		$content_type = $context->getResult('content_type');
		$charset = $context->getResult('charset');
		$tpl = $context->getResult('template');
		if(!is_null($content_type)){
			$response = $context->getResponse();
			if(is_null($charset)){
				$charset = 'utf-8';
			}
			$response->setContentType($content_type)->setContentCharset($charset);
		}
		
		//set smarty vars
		$smarty = $this->getSmarty();
		$params = $context->getAll();
		foreach($params as $key=>$value){
			$smarty->assign($key,$value);
		}
		if(!$smarty->isResourceReadable($tpl)){
			self::debug('template: '.$tpl.' cant readable.',__METHOD__);
			throw new Anole_Dispatcher_Exception('template: '.$tpl.' cant readable.');
		}
		$content = $smarty->fetch($tpl);
		$this->setBuffer($content);
		
		$this->_executed = true;
	}
}
/**vim:sw=4 et ts=4 **/
?>