<?php
/**
 * 支持HTTP特性的Request
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Dispatcher_Request_Http extends Anole_Dispatcher_Request_Abstract {
	private $_uri_info = null;
	protected $_is_ajax=null;
	
	/**
	 * Return PATH_INFO
	 *
	 * @return string
	 */
	public function getPathInfo(){
		if(empty($this->_uri_info)){
			$this->setPathInfo();
		}
	    return $this->_uri_info;	
	}
	/**
	 * set PATH_INFO
	 *
	 * @param string $path_info
	 * 
	 * @return Anole_Dispatcher_Request_Abstract
	 */
	public function setPathInfo($path_info=null){
		if(is_null($path_info)){
			$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : null;
		}
		$this->_uri_info = $path_info;
		
		return $this;
	}
	
	/**
	 * magic method 
	 * get request params value
	 *
	 * @param string $name
	 * @return string
	 */
	public function __get($name){
		switch (true){
			case isset($this->_params[$name]):
				return $this->_params[$name];
			case isset($_GET[$name]):
				return $_GET[$name];
			case isset($_POST[$name]):
				return $_POST[$name];
			case isset($_COOKIE[$name]):
				return $_COOKIE[$name];
			case ($name == 'REQUEST_URI'):
				return $this->getRequestUri();
			case ($name == 'PATH_INFO'):
				return $this->getPathInfo();
			case isset($_SERVER[$name]):
				return $_SERVER[$name];
			case isset($_ENV[$name]):
				return $_ENV[$name];
			default:
				return null;
		}
	}
	/**
     * 检验某个变量是否被设置,依次检查:
     * _params,_GET,_POST,_COOKIE,_SERVER,_ENV
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name){
        switch (true) {
            case isset($this->_params[$name]):
                return true;
            case isset($_GET[$name]):
                return true;
            case isset($_POST[$name]):
                return true;
            case isset($_COOKIE[$name]):
                return true;
            case isset($_SERVER[$name]):
                return true;
            case isset($_ENV[$name]):
                return true;
            default:
                return false;
        }
    }
	/**
	 * alisa of __get
	 *
	 * @param string $name
	 * @return string
	 */
	public function get($name){
		return $this->__get($name);
	}
	
	/**
	 * return REQUEST_URI
	 *
	 * @return string
	 */
	public function getRequestUri(){
		return $_SERVER['REQUEST_URI'];
	}
	/**
	 * 获取前一链接
	 * 
	 * @return string
	 */
	public function getReferer(){
        return $_SERVER['HTTP_REFERER'];
	}
    /**
     * 当前Request的Method:PUT/GET/DELETE/POST
     *
     * @return string
     */
    public function getMethod(){
        return $_SERVER['REQUEST_METHOD'];
    }
    
    /**
     * merge params,_GET,_POST,_params will override _GET,_POST
     *
     * @return unknown
     */
    public function getParams(){
        $params = $this->_params;
        if(isset($_GET) && is_array($_GET)){
        	$params += $_GET;
        }
        if(isset($_POST) && is_array($_POST)){
        	$params += $_POST;
        }
        return $params;
    }
    
    public function setParams($params=array()){
    	foreach($params as $key=>$value){
    		$this->setParam($key,$value);
    	}
    	return $this;
    }
    
    public function getParam($name,$default=null){
    	if(isset($this->_params[$name])){
    		return $this->_params[$name];
    	}elseif(isset($_GET[$name])){
    		return $_GET[$name];
    	}elseif(isset($_POST[$name])){
    		return $_POST[$name];
    	}else{
    		return $default;
    	}
    }
    
    /**
     * 返回_FILES数组
     */
    public function getUploadFiles(){
        return $_FILES;
    }
    
    /**
     * 确认文件是否是通过PHP上传的文件
     * 
     * @param string $file
     * @return boolean
     */
    public function isUploadedFile($file){
        return is_uploaded_file($file);
    }
	/**
	 * 获得Cookie中指定key的变量值
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
    public function getCookie($key,$default=null){
    	return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }
    /**
     * 获得Server中指定key的变量值
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getServer($key,$default=null){
    	return isset($_SERVER[$key]) ? $_SERVER[$key] : $default;
    }
    /**
     * 获取_POST变量中指定key的变量值
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getPost($key,$default=null){
    	return isset($_POST[$key]) ? $_POST[$key] : $default;
    }
    /**
     * 获得GET变量中指定key的变量值
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getGet($key,$default=null){
    	return isset($_GET[$key]) ? $_GET[$key] : $default;
    }
    
    /**
     * 获得客户端的所有发送的HTTP Header
     *
     * 注：不能使用Apache的getallheader,我们需要支持FCGI
     *
     * @return array
     */
    static public function getHeaders(){
    	foreach($_SERVER as $name => $value){
    		if(substr($name,0,5) == 'HTTP_'){
    			$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
    		}
    	}
    	return $headers;
    }
    /**
     * 获取HTTP名称的Header值
     *
     * <p>
     * $name符合标准的Http名称如Last_Modified,Accept-Encoding
     * </p>
     *
     * @param string $name
     * @return mixed
     */
    public function getHeader($name){
        $headers = self::getHeaders();
        $temp = ucwords($name);
        return isset($headers[$temp]) ? $headers[$temp] : null;
    }
    
    /**
     * 返回客户端的ip地址
     */
    public function getClientIp(){
        //try find proxy ip instead of fuzzy remote_addr
        if(getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")){
        	$ip = getenv("HTTP_CLIENT_IP");
        }elseif(getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")){
            $ip = getenv("HTTP_X_FORWARDED_FOR");	
        }elseif(getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")){
        	$ip = getenv("REMOTE_ADDR");
        }elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")){
        	$ip = $_SERVER['REMOTE_ADDR'];
        }else{
            $ip = "unknown";	
        }
        
        return $ip;
    }
    /**
     * get remote ip(REMOTE_ADDR)
     *
     * @return string
     */
    public function getRemoteIp(){
        if(getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")){
        	$ip = getenv("REMOTE_ADDR");
        }elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")){
        	$ip = $_SERVER['REMOTE_ADDR'];
        }else{
            $ip = "unknown";	
        }
        
        return $ip;
    }
}
/**vim:sw=4 et ts=4 **/
?>