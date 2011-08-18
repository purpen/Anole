<?php
/**
 * request abstract base class
 *
 * @version $Id$
 * @author purpen
 */
abstract class Anole_Dispatcher_Request_Abstract extends Anole_Object {
	/**
	 * params boxer
	 *
	 * @var array
	 */
	protected $_params = array();
	/**
	 * set current param into the boxer
	 *
	 * @param string $name
	 * @param string $value
	 * @return Anole_Dispatcher_Request_Abstract
	 */
	public function setParam($name,$value){
		$this->_params[$name] = $value;
		return $this;
	}
	/**
	 * get the param value of the name
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getParam($name,$default=null){
		return isset($this->_params[$name]) ? $this->_params[$name] : $default;
	}
	/**
	 * set params array into the boxer
	 *
	 * @param array $params
	 * @return Anole_Dispatcher_Request_Abstract
	 */
	public function setParams($params=array()){
		if(!empty($params)){
			$_params = array_merge($this->_params,$params);
			$this->_params = $_params;
		}
		return $this;
	}
	/**
	 * get all params value
	 *
	 * @return array
	 */
	public function getParams(){
		return $this->_params;
	}
}
/**vim:sw=4 et ts=4 **/
?>