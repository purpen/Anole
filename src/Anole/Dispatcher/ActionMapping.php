<?php
/**
 * Action辅助方法
 * 
 * @author purpen
 * @version $Id$
 * 
 */
class Anole_Dispatcher_ActionMapping{
	private $_namespace = null;
	private $_method = null;
	private $_action = null;
	private $_params = null;
	
	public function setNamespace($value){
		$this->_namespace = $value;
		return $this;
	}
	
	public function getNamespace(){
		return $this->_namespace;
	}
	
	public function setMethod($value){
		$this->_method = $value;
		return $this;
	}
	
	public function getMethod(){
		return $this->_method;
	}
	
	public function setAction($value){
		$this->_action = $value;
		return $this;
	}
	
	public function getAction(){
		return $this->_action;
	}
	
	public function setParams($value){
		$this->_params = $value;
		return $this;
	}
	
	public function getParams(){
		return $this->_params;
	}
}
/**vim:sw=4 et ts=4 **/
?>