<?php
/**
 * Session 数据容器类
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Session_Context extends Anole_Object {
	/**
	 * @var array
	 */
	private $_data = array();
    private static $_session_id;
    /**
     * @var Anole_Session_Context
     */
    protected static $_instance_context;
    /**
     * Session存储层
     *
     * @var Anole_Session_Storage
     */
    private $_storage;
    
    public function __construct($session_id=null){
    	$class = Anole_Config::get('session.storage.class');
    	self::debug("session class:$class", __CLASS__);
    	if(!class_exists($class)){
    		self::warn("Storage session;Unkown class:$class", __CLASS__);
    		throw new Anole_Exception("Storage session;Unkown class:$class");
    	}
    	
    	$inst = new $class();
    	if(!$inst instanceof Anole_Session_Storage){
    		self::warn("Class[$class] isnot implement Anole_Session_Storage interface", __CLASS__);
    		throw new Anole_Exception("Class[$class] isnot implement Anole_Session_Storage interface"); 
    	}
    	self::debug("Init object[$class].", __CLASS__);
    	
    	$this->_storage = $inst;
    	
    	if(!is_null($session_id)){
    		self::setSessionId($session_id);
    	}
    	
    	self::debug("Start to set session options.", __CLASS__);
    	//session options
    	$session_domain = Anole_Config::get('session.cookie_domain');
        $session_path = Anole_Config::get('session.cookie_path');
        $session_ttl = Anole_Config::get('session.ttl');
        $session_name = Anole_Config::get('session.cookie_name');
        
        if(empty($session_ttl)) {
        	$session_ttl = 0;
        }
        if(empty($session_path)) {
        	$session_path = '/';
        }
        if(empty($session_name)) {
        	$session_name = 'DAPPSID';
        }
        
        session_name($session_name);
        
        session_set_cookie_params($session_ttl,$session_path,$session_domain);
        
        @session_start();
        self::$_session_id = session_id();
        
        $this->_data = $this->_storage->init();
        self::debug("Initialize session storage class:$class", __CLASS__);
    }
    /**
     * 将session数据回写到后端存储层
     */
    public function __destruct(){
        $this->flush();
    }
    /**
     * 返回SessionContext实例
     * 
     * @return Anole_Session_Context
     */
    public static function getContext($session_id=null){
        if(is_null(self::$_instance_context)){
            self::$_instance_context = new Anole_Session_Context($session_id);
        }
        return self::$_instance_context;
    }
    /**
     * 重新初始化当前的session_context
     */
    public static function restart($session_id=null){
       if(!is_null(self::$_instance_context)){
       	   //set back to session
           self::$_instance_context->flush();
           self::$_instance_context = null;
       }
       self::$_instance_context = new Anole_Session_Context($session_id);
    }
    /**
     * store by provider
     */
    public function flush(){
    	self::debug('invoke storage to flush data',__CLASS__);
    	$this->_storage->store($this->_data);
    }
    
    /**
     * 返回session中指定key的数据
     * 
     * @param string $key
     * 
     * @return mixed
     */
    public function get($key){
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }
    /**
     * 设置session中的数据
     *
     * @param string $key
     * @param mixed $value
     * 
     * @return Anole_Session_Context
     */
    public function set($key,$value){
        $this->_data[$key] = $value;
        return $this;
    }
    /**
     * Session中是否有指定key的数据
     * 
     * @return boolean
     */
    public function has($key){
        return isset($this->_data[$key]);
    }
    /**
     * 删除session中指定key的数据=>set null
     * 
     * @param string $key
     * 
     * @return Anole_Session_Context
     */
    public function remove($key){
        return $this->set($key,null);
    }
    /**
     * 清除Session数据
     * 默认为清除当前Session的数据,如id不为空则清除指定id的session的全部数据
     *
     * @param string $key
     * 
     * @return Anole_Session_Context
     */
    public function destory($id=null){
        if(!is_null($id)){
            $_id = self::$_session_id;
            self::setSessionId($id);
        }else{
            //当前session
            $this->_data = array();
        }
        self::debug("destory session::".self::$_session_id,__CLASS__);
        $this->_storage->store(array());
        if(!is_null($id)){
            Anole_Session_Context::setSessionId($_id);
        }
        return $this;
    }
    /**
     * 返回当前的SessionId
     */
    public function getSessionId(){
        return self::$_session_id;
    }
    /**
     * 设置SessionId
     */
    public function setSessionId($id){
        self::$_session_id = $id;
        @session_id($id);
    }
}
/** vim:sw=4 et ts=4 **/
?>