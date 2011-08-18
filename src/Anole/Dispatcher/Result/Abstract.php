<?php
/**
 * all result base class interface
 *
 * @version $Id$
 * @author purpen
 */
abstract class Anole_Dispatcher_Result_Abstract extends Anole_Object implements Anole_Dispatcher_Result {
	/**
	 * action invocation
	 *
	 * @var Anole_Dispatcher_ActionInvocation
	 */
	protected $_invocation;
	
	protected $_executed;
	
	protected $_buffer;
	
	public function __construct(){
		$this->init();
	}
	protected function init(){
		//parent class do nothing
	}
	/**
	 * run result
	 *
	 * @param Anole_Dispatcher_ActionInvocation $invocation
	 */
	public function execute(Anole_Dispatcher_ActionInvocation $invocation){
		self::debug("start to run result.",__METHOD__);
		$this->_invocation = $invocation;
		$this->render();
		if($this->_executed){
			$this->_invocation->getInvocationContext()->getResponse()->setBuffer($this->getBuffer());
		}
	}
	
	abstract protected function render();
	
	public function setBuffer($content){
		$this->_buffer = $content;
		return $this;
	}
	
	public function getBuffer(){
		return $this->_buffer;
	}
}
/**vim:sw=4 et ts=4 **/
?>