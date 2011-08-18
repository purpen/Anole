<?php
/**
 * 所有的Adapter应该继承本类并实现其中的抽象方法
 *
 * @version $Id$
 * @author purpen
 */
abstract class Anole_Dba_Adapter_Abstract extends Anole_Object implements Anole_Dba_Adapter {
	
	protected $_dsn;
	protected $_uri;
	protected $_args;
	
	protected $_connected = false;
	
	public function __construct($dsn){
		$uri = parse_url($dsn);
		if(isset($uri['query'])){
			parse_str($uri['query'],$args);
		}else{
			$args = null;
		}
		$this->_dsn = $dsn;
		$this->_uri = $uri;
		$this->_args = $args;
	}
	
	public function __destruct(){
	    $this->close();	
	}
	
	public function connect(){
		if(!$this->_connected){
			$this->_connected = $this->doConnect();
		}
		return $this->_connected;
	}
	
	public function close(){
		if($this->_connected){
			$this->doClose();
		}
	}
	/**
	 * Do Real Connection
	 *
	 * @return bool
	 */
	abstract protected function doConnect();
	/**
	 * Do Real Close
	 *
	 * @return bool
	 */
	abstract protected function doClose();
}

?>