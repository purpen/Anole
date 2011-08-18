<?php
/**
 * Anole框架实现的运行环境支持方盒
 * 
 * @version $Id$
 * @author purpen
 */
class Anole_Dispatcher_Context extends Anole_Object {
	
	const SESSION='anole.session';
    const REQUEST='anole.request';
    const RESPONSE='anole.response';

    const ACTION_SCOPE='__app__';
    const SERVER_SCOPE='__server__';
    const RESULT_SCOPE='__result__';
	
	protected static $_context;
	
	protected $_data = array();
	
	public function __construct(){
		$this->_data[self::ACTION_SCOPE] = array();
		$this->_data[self::SERVER_SCOPE] = array();
		$this->_data[self::RESULT_SCOPE] = array();
	}
	/**
	 * get context object
	 *
	 * @param string $class
	 * @return Anole_Dispatcher_Context
	 */
	public static function getContext($class=null){
		if(is_null(self::$_context)){
			if(is_null($class)){
				$class = __CLASS__;
			}
			self::$_context = new $class();
		}
		
		return self::$_context;
	}
	/**
	 * Set Request Object
	 *
	 * @param Anole_Dispatcher_Request_Http $request
	 * @return Anole_Dispatcher_Context
	 */
	public function setRequest($request){
		$this->_data[self::SERVER_SCOPE][self::REQUEST] = $request;
		return self::$_context;
	}
	/**
	 * Return Request Object
	 *
	 * @return Anole_Dispatcher_Request_Http
	 */
	public function getRequest(){
		if(isset($this->_data[self::SERVER_SCOPE][self::REQUEST])){
			return $this->_data[self::SERVER_SCOPE][self::REQUEST];
		}
		return null;
	}
	/**
	 * Set Response Object
	 *
	 * @param Anole_Dispatcher_Response_Http $response
	 * @return Anole_Dispatcher_Context
	 */
	public function setResponse($response){
		$this->_data[self::SERVER_SCOPE][self::RESPONSE] = $response;
		return self::$_context;
	}
	/**
	 * Return Response Object
	 *
	 * @return Anole_Dispatcher_Response_Http
	 */
	public function getResponse(){
		if(isset($this->_data[self::SERVER_SCOPE][self::RESPONSE])){
			return $this->_data[self::SERVER_SCOPE][self::RESPONSE];
		}
		return null;
	}
	/**
	 * set name=>value into the result container
	 *
	 * @param string $name
	 * @param string $value
	 * @return Anole_Dispatcher_Context
	 */
	public function setResult($name,$value){
		$this->_data[self::RESULT_SCOPE][$name] = $value;
		return self::$_context;
	}
	/**
	 * get the name's value of the result container
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getResult($name,$default=null){
		if(isset($this->_data[self::RESULT_SCOPE][$name])){
			return $this->_data[self::RESULT_SCOPE][$name];
		}
		return $default;
	}
	/**
	 * alisa setResult
	 *
	 * @param string $name
	 * @param string $value
	 * @return Anole_Dispatcher_Context
	 */
	public function putResult($name,$value){
		return $this->setResult($name,$value);
	}
	/**
	 * set action var
	 *
	 * @param string $name
	 * @param string $value
	 * @return Anole_Dispatcher_Context
	 */
	public function set($name,$value){
		$this->_data[self::ACTION_SCOPE][$name] = $value;
		return self::$_context;
	}
	/**
	 * get action var
	 *
	 * @param string $name
	 * @param string $default
	 * @return mixed
	 */
	public function get($name,$default=null){
		if(isset($this->_data[self::ACTION_SCOPE][$name])){
			return $this->_data[self::ACTION_SCOPE][$name];
		}
		return $default;
	}
	/**
	 * alisa set
	 *
	 * @param string $name
	 * @param string $value
	 * @return Anole_Dispatcher_Context
	 */
	public function put($name,$value){
		return $this->set($name,$value);
	}
	/**
	 * get action scope params
	 *
	 * @return array
	 */
	public function getAll(){
		return $this->_data[self::ACTION_SCOPE];
	}
	/**
	 * 返回当前的SessionContext
     * 注意，这将自动初始化session
	 *
	 * @return Anole_Session_Context
	 */
	public function getSessionContext(){
		return Anole_Session_Context::getContext();
	}
	/**
	 * 设置Session 数据
	 * 实际调用Anole_Session_Context::set方法
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return Anole_Dispatcher_Context
	 */
	public function setSession($key, $value){
		$this->getSessionContext()->set($key,$value);
		return $this;
	}
	/**
	 * 返回Session数据
	 * 实际调用Anole_Session_Context::get方法
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getSession($key){
		return $this->getSessionContext()->get($key);
	}
	/**
	 * session中是否有指定的数据
	 * 实际调用Anole_Session_Context::has方法
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function hasSession($key){
		return $this->getSessionContext()->has($key);
	}
}
/**vim:sw=4 et ts=4 **/
?>