<?php
/**
 * Support File Upload Class
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Dispatcher_Interceptor_UploadSupport extends Anole_Dispatcher_Interceptor_Abstract {
	private $_uploads = array();
	/**
	 * interceptor entry
	 *
	 * @param Anole_Dispatcher_ActionInvocation $invocation
	 * 
	 * @return string
	 */
	public function intercept(Anole_Dispatcher_ActionInvocation $invocation){
		$this->checkUpload($invocation);
		return $invocation->invoke();
	}
	/**
	 * check upload files info
	 *
	 * @param Anole_Dispatcher_ActionInvocation $invocation
	 */
	protected function checkUpload(Anole_Dispatcher_ActionInvocation $invocation){
		$action = $invocation->getAction();
		if(!$action instanceof Anole_Dispatcher_Action_Interface_UploadSupport){
			self::debug('this action no upload support,skip', __METHOD__);
			return;
		}
		$request = $invocation->getInvocationContext()->getRequest();
		if(!$request instanceof Anole_Dispatcher_Request_Http){
			self::debug('Current request is invalid,skip',__METHOD__);
			return;
		}
		$files = $request->getUploadFiles();
		foreach($files as $k=>$f){
			if(!is_array($f['name'])){
				$f['id'] = $k;
				$_files = array($f);
			}else{
				self::debug("multi file array,revert it...", __CLASS__);
			    $_files = $this->_revertMultiFiles($f,$k);	
			}
			$this->mergeUploads($_files,$request);
		}
		//send to action
		$action->setUploadFiles($this->_uploads);
		//empty
		$this->_uploads = array();
	}
	/**
	 * Multi files revert only file
	 * 
	 * @example 
	 *  one file
	 *  [f1] => Array
     *   (
     *       [name] => bd-002.jpg
     *       [type] => image/jpeg
     *       [tmp_name] => /private/tmp/php3ws3Dw
     *       [error] => 0
     *       [size] => 472607
     *   )
	 *  
	 *  multi files
	 *  [f2] => Array
     *   (
     *       [name] => Array
     *           (
     *               [0] => bd-003-sm.jpg
     *               [1] => bd-004-sm.jpg
     *           )
     *       [type] => Array
     *           (
     *               [0] => image/jpeg
     *               [1] => image/jpeg
     *           )
     *       [tmp_name] => Array
     *           (
     *               [0] => /private/tmp/phpTB9D0C
     *               [1] => /private/tmp/phpFjVUnR
     *           )
     *       [error] => Array
     *           (
     *               [0] => 0
     *               [1] => 0
     *           )
     *       [size] => Array
     *           (
     *               [0] => 19252
     *               [1] => 19982
     *           )
     *   )
	 *
	 * @param array $file_post
	 * @param string $id
	 * 
	 * @return array
	 */
	private function _revertMultiFiles($file_post,$id){
		$cnt = count($file_post['name']);
		$file_keys = array_keys($file_post);
		$file_ary = array();
		
		for($i=0;$i<$cnt;$i++){
			foreach($file_keys as $k){
				$file_ary[$i][$k] = $file_post[$k][$i];
			}
			$file_ary[$i]['id'] = $id;
		}
		
		return $file_ary;
	}
	/**
	 * merge uploads file info
	 *
	 * @param array $files
	 * @param Anole_Dispatcher_Request_Http $request
	 * 
	 * @return void
	 */
	private function mergeUploads($files,$request){
		foreach($files as $f){
			switch($f['error']){
				case UPLOAD_ERR_OK:
					$path = $f['tmp_name'];
					$size = $f['size'];
					$name = $f['name'];
					$type = $f['type'];
					$id = $f['id'];
					if(!$request->isUploadedFile($path)){
						self::debug("$id is not an upload file,skip.", __CLASS__);
						continue;
					}
					$this->_uploads[] = array(
					   'path'=>$path,
					   'size'=>$size,
					   'name'=>$name,
					   'type'=>$type,
					   'id'=>$id
					);
					break;
				case UPLOAD_ERR_INI_SIZE:
					self::warn("The uploaded file exceeds the upload_max_filesize directive (".ini_get("upload_max_filesize").") in php.ini.",__CLASS__);
					continue;
                case UPLOAD_ERR_FORM_SIZE:
                    self::warn("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",__CLASS__);
                    continue;
                case UPLOAD_ERR_PARTIAL:
                    self::warn("The uploaded file was only partially uploaded.",__CLASS__);
                    continue;
                case UPLOAD_ERR_NO_FILE:
                    self::warn("No file was uploaded.",__CLASS__);
                    continue;
                case UPLOAD_ERR_NO_TMP_DIR:
                    self::warn("Missing a temporary folder.",__CLASS__);
                    continue;
                case UPLOAD_ERR_CANT_WRITE:
                    self::warn("Failed to write file to disk",__CLASS__);
                    continue;
                default:
                    self::warn("Unknown File Error",__CLASS__);
			}//endswitch
		}//endfor
	}
	
}
/**vim:sw=4 et ts=4 **/
?>