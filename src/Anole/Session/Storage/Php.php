<?php
/**
 * Session机制来实现Session的存储
 * 
 * 1.目前使用的是PHP内置session
 * 2.将实现session库，运用与多个系统域之间
 * 
 * @version $Id$
 * @author purpen
 */
class Anole_Session_Storage_Php extends Anole_Object implements Anole_Session_Storage {
	
	private $_session_key;
    
    public function __construct(){
    	$key = Anole_Config::get('session.id');
    	if(is_null($key)){
    		$key = 'anole_app';
    	}
    	$this->_session_key = $key;
    }
    /**
     * 初始化storage,读取session数据
     *
     * @return array
     */
    public function init(){
    	self::debug("Fetch Session_key:".$this->_session_key." session_id: ".session_id(), __CLASS__);
    	$data = isset($_SESSION[$this->_session_key]) ? $_SESSION[$this->_session_key] : array();
    	self::debug('data:'.@implode('',$data), __CLASS__);
    	session_write_close();
    	return $data;
    }
    /**
     * 刷新session数据并持久化到后端容器中
     *
     * @param array $data
     */
    public function store($data){
    	@session_start();
    	self::debug('store data:'.@implode('',$data).' key:'.$this->_session_key.' session_id:'.session_id(), __CLASS__);
    	$_SESSION[$this->_session_key] = $data;
    	session_write_close();
    }
}
/** vim:sw=4:expandtab:ts=4 **/
?>