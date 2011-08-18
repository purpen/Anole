<?php
/**
 * 支持HTTP特性的Response类
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Dispatcher_Response_Http extends Anole_Dispatcher_Response_Abstract {
	/**
	 * 回应代码
	 *
	 * @var int
	 */
	protected $_http_response_code = 200;
	/**
	 * 返回的文本类型
	 *
	 * @var string
	 */
	protected $_content_type = null;
	/**
	 * 返回的文本编码
	 *
	 * @var string
	 */
	protected $_content_charset = null;
	/**
	 * 设置最后的修改时间
	 *
	 * @var string
	 */
	protected $_last_modified;
	
	/**
	 * set http response code
	 *
	 * @param int $code
	 * @return Anole_Dispatcher_Response_Http
	 */
	public function setHttpResponseCode($code){
		if(!is_int($code) || ($code < 100) || ($code > 599)){
			throw new Anole_Dispatcher_Response_Exception('Invalid Http Response Code!');
		}
		$this->_http_response_code = $code;
		return $this;
	}
	/**
	 * get response code
	 *
	 * @return int
	 */
	public function getHttpResponseCode(){
		return $this->_http_response_code;
	}
	
	/**
	 * redirect target url
	 *
	 * @param string $url
	 * @param int $code
	 * @return Anole_Dispatcher_Response_Http
	 */
	public function setRedirect($url,$code=302){
		self::debug('redirect url: '.$url, __METHOD__);
		$this->setHeader('Location',$url,true)->setHttpResponseCode($code);
		return $this;
	}
	/**
	 * send all response and headers
	 */
	public function flushResponse(){
		self::debug('send response.', __METHOD__);
		$this->sendHeaders();
		$this->sendBuffer();
	}
	/**
	 * send all headers
	 *
	 * @return Anole_Dispatcher_Response_Http
	 */
	public function sendHeaders(){
        if(!headers_sent()){
        	$http_code_sent = false;
        	$content_type = $this->getContentType();
        	if(!empty($content_type)){
        		$this->setHeader('Content-Type',$content_type,true);
        	}
        	//send raw header
        	foreach($this->_headers_raw as $header){
        		if(!$http_code_sent && $this->_http_response_code){
        			header($header,true,$this->_http_response_code);
        			$http_code_sent = true;
        		}else{
        			header($header);
        		}
        	}
        	//send header value
        	foreach($this->_headers as $header){
        		if(!$http_code_sent && $this->_http_response_code){
        			header($header['name'].': '.$header['value'],false,$this->_http_response_code);
        			$http_code_sent = true;
        		}else{
        			header($header['name'].': '.$header['value'],false);
        		}
        	}
        }
        return $this;
	}
	/**
	 * output buffer content
	 */
	public function sendBuffer(){
		echo @implode('',$this->_buffer);
	}
	
    public function __toString(){
        ob_start();
        $this->flushResponse();
        return ob_get_clean();
    }
    /**
     * set response content type
     *
     * @param string $type
     * @return Anole_Dispatcher_Response_Http
     */
    public function setContentType($type){
    	$this->_content_type = $type;
    	return $this;
    }
    /**
     * get response content type
     *
     * @return string
     */
    public function getContentType(){
    	$content_type = $this->_content_type;
    	if(!is_null($this->getContentCharset())){
    		$content_type .= '; charset='. $this->getContentCharset();
    	}
    	return $content_type;
    }
    /**
     * set response content charset
     *
     * @param string $charset
     * @return Anole_Dispatcher_Response_Http
     */
    public function setContentCharset($charset){
    	$this->_content_charset = $charset;
    	return $this;
    }
    /**
     * get response content charset
     *
     * @return string
     */
    public function getContentCharset(){
    	return $this->_content_charset;
    }
    /**
     * set response last modified datetime
     *
     * @param datetime $time
     * @return Anole_Dispatcher_Response_Http
     */
    public function setLastModified($time){
    	$this->_last_modified = $time;
    	return $this;
    }
    /**
     * get response last modified datetime
     *
     * @return datetime
     */
    public function getLastModified(){
    	return $this->_last_modified;
    }
}
/**vim:sw=4 et ts=4 **/
?>