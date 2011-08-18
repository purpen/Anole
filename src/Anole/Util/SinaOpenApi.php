<?php
/**
 * @copyright http://lvyaozu.sinaapp.com/
 */
class Anole_Util_SinaOpenApi extends Anole_Object {
	public $curl;
	protected $_user = array();
	protected $_appKey;
	
	public function __construct($appKey, $username=null, $password=null) {
		$this->_appKey = $appKey;
		$this->curl = curl_init();
		if(!empty($username) && !empty($password)) {
			$this->setUser($username, $password);
		}
	}
	
	public function setUser($username, $password) {
		$this->_user['username'] = $username;
		$this->_user['password'] = $password;
		curl_setopt($this->curl , CURLOPT_USERPWD , "$username:$password");
	}
	
	public function request($url, $params=array(), $method='GET') {
		$apiurl = "http://api.t.sina.com.cn/";
		$apiurl .= trim($url, '/');
		$apiurl .= '.json';
		$params['source'] = $this->_appKey;
		if($url == 'statuses/upload') {
			$content = $this->_upload($apiurl, $params);
		} else {
			$content = $this->_request($apiurl, $params, $method);
		}
		return json_decode($content, true);
	}
	
	public function get($url, $params=array()) {
		return $this->request($url, $params, 'GET');
	}
	
	public function post($url, $params=array()) {
		return $this->request($url, $params, 'POST');
	}
	
	protected function _request($url, $params=array(), $method='GET') {
		curl_setopt($this->curl, CURLOPT_URL, $url);
		$method = strtoupper($method);
		switch ($method) {
			case 'POST':
				curl_setopt($this->curl, CURLOPT_POST, true);
				break;
			case 'DELETE':
				curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
				break;
			case 'PUT':
				curl_setopt($this->curl, CURLOPT_PUT, true);
				break;
			default:
				break;
		}
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($params));
		curl_setopt($this->curl, CURLOPT_HEADER, false);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($this->curl);
		return $content;
	}
	
	protected function _upload($url, $params=array()) {
		$file = file_get_contents($params['pic']);
		$mime_type = $params['mime_type'];
		$filename = $params['filename'];
		if(empty($mime_type)) {
			$mime_type = 'image/jpg';
		}
		$boundary = uniqid('------------------');
		$MPboundary = '--'.$boundary;
		$endMPboundary = $MPboundary. '--';
		
		$multipartbody .= $MPboundary . "\r\n";
		$multipartbody .= 'Content-Disposition: form-data; name="pic"; filename="'. $filename .'"'. "\r\n";
		$multipartbody .= "Content-Type: {$mime_type}\r\n\r\n";
		$multipartbody .= $file. "\r\n";

		$multipartbody .= $MPboundary . "\r\n";
		$multipartbody .= 'content-disposition: form-data; name="source"'."\r\n\r\n";
		$multipartbody .= $params['source']."\r\n";
		
		$multipartbody .= $MPboundary . "\r\n";
		$multipartbody .= 'content-disposition: form-data; name="status"'."\r\n\r\n";
		$multipartbody .= $params['status']."\r\n";
		$multipartbody .= "\r\n". $endMPboundary;
		
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_POST, true);	
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $multipartbody);
		curl_setopt($this->curl, CURLOPT_HEADER, false);
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data; boundary=$boundary"));
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($this->curl);
		return $content;
	}
	
	public function __destruct() {
		curl_close($this->curl);
	}
}
/**vim:sw=4 et ts=4 **/
?>
