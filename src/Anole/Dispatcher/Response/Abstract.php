<?php
/**
 * Response abstract class
 *
 * @version $Id$
 * @author purpen
 */
abstract class Anole_Dispatcher_Response_Abstract extends Anole_Object {
	/**
     * buffer content
     *
     * @var array
     */
    protected $_buffer = array();
    /**
     * header
     *
     * @var array
     */
    protected $_headers = array();
    
    protected $_headers_raw = array();
    
    /**
     * set headers
     *
     * @param string $name
     * @param string $value
     * @param bool $replace
     * @return Anole_Dispatcher_Response_Http
     */
    public function setHeader($name,$value,$replace=false){
        $name = (string) $name;
        $value = (string) $value;
        if($replace){
            foreach($this->_headers as $key=>$header){
                if($name == $header['name']){
                    unset($this->_headers[$key]);
                }
            }
        }
        $this->_headers[] = array(
          'name'=>$name,
          'value'=>$value
        );
        return $this;
    }
    
    /**
     * get headers
     *
     * @return array
     */
    public function getHeaders(){
        return $this->_headers;
    }
    /**
     * clear headers
     *
     * @return Anole_Dispatcher_Response_Abstract
     */
    public function clearHeaders(){
    	$this->_headers = array();
    	return $this;
    }
    /**
     * set raw messsage header
     *
     * @param string $value
     * @return Anole_Dispatcher_Response_Http
     */
    public function setHeadersRaw($value){
        $this->_headers_raw[] = (string) $value;
        return $this;
    }
    /**
     * get raw header message
     *
     * @return array
     */
    public function getHeadersRaw(){
        return $this->_headers_raw;
    }
    /**
     * clear raw headers
     *
     * @return Anole_Dispatcher_Response_Abstract
     */
    public function clearRawHeaders(){
    	$this->_headers_raw = array();
    	return $this;
    }
    /**
     * clear all headers
     *
     * @return Anole_Dispatcher_Response_Abstract
     */
    public function clearAllHeaders(){
    	return $this->clearHeaders()->clearRawHeaders();
    }
    /**
     * set output buffer content
     *
     * @param string $content
     * @return Anole_Dispatcher_Response_Abstract
     */
    public function setBuffer($content){
        $this->_buffer = array((string) $content);
        return $this;
    }
    /**
     * append content to output buffer
     *
     * @param string $content
     * @return Anole_Dispatcher_Response_Abstract
     */
    public function appendBuffer($content){
       $this->_buffer[] = (string) $content;
       return $this;    
    }
    /**
     * get output content
     *
     * @param bool $isArray
     * @return mixed
     */
    public function getBuffer($isArray=false){
        return $isArray?$this->_buffer:implode('',$this->_buffer);
    }
    
    abstract public function flushResponse();
    abstract public function sendBuffer();
    abstract public function sendHeaders();
}
/**vim:sw=4 et ts=4 **/
?>