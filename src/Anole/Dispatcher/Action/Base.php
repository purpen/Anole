<?php
/**
 * Action Interface Base Class
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Dispatcher_Action_Base extends Anole_Object implements Anole_Dispatcher_Action {
	/**
	 * default action method
	 */
	public function execute(){
		return Anole_Dispatcher_Constant_Action::NONE;
	}
	/**
	 * 实现smarty result
	 *
	 * @param string $tpl
	 * @param string $content_type
	 * @param string $charset
	 * @return string
	 */
	protected function smartyResult($tpl,$content_type='text/html',$charset='utf-8'){
		$this->putResult('template',$tpl);
		$this->putResult('content_type',$content_type);
		$this->putResult('charset',$charset);
		self::debug("result template: ".$tpl, __METHOD__);
		return Anole_Dispatcher_Constant_Action::SMARTY;
	}
	/**
	 * 重定向到指定url的result
	 *
	 * @param string $url
	 * @param int $code
	 * @return string
	 */
	protected function redirectResult($url,$code=302){
		$this->putResult('url', $url);
		$this->putResult('code', $code);
		
		return Anole_Dispatcher_Constant_Action::REDIRECT;
	}
	/**
	 * 直接返回裸数据的result
	 *
	 * @param mixed $data
	 * @param string $content_type
	 * @param string $charset
	 * @return string
	 */
	protected function rawResult($data,$content_type=null,$charset=null){
		$this->putResult('data', $data);
		$this->putResult('content_type',$content_type);
		$this->putResult('charset', $charset);
		return Anole_Dispatcher_Constant_Action::RAW;
	}
	/**
	 * Return json result object
	 *
	 * @param string $error_code
	 * @param string $error_msg
	 * @return string
	 */
	protected function jsonResult($error_code=null,$error_msg=null){
		if(!is_null($error_code)){
			$this->getContext()->put('error_code',$error_code);
		}
		if(!is_null($error_msg)){
			$this->getContext()->put('error_msg',$error_msg);
		}
		return Anole_Dispatcher_Constant_Action::JSON;
	}
	/**
	 * 返回JQuery (taconite xml document)Result
	 *
	 * @param string $tpl 模板(Smarty格式)ID
	 * @param bool $cross_domain 是否是跨域回调
	 * @param string $callback 回调函数名称,空则回调客户端$.taconite
	 * @return string
	 */
	protected function jqueryResult($tpl,$cross_domain=false,$callback=null){
		$this->putResult('template',$tpl);
		$this->putResult('cross_domain',$cross_domain);
		$this->putResult('callback',$callback);
		return Anole_Dispatcher_Constant_Action::JQUERY;
	}
	
	/**
	 * get context object
	 *
	 * @return Anole_Dispatcher_Context
	 */
	protected function getContext(){
		return Anole_Dispatcher_Context::getContext();
	}
	/**
	 * put value into action context
	 *
	 * @param string $name
	 * @param string $value
	 * @return Anole_Dispatcher_Context
	 */
	protected function putContext($name,$value){
		return $this->getContext()->put($name,$value);
	}
	/**
	 * put value into result context
	 *
	 * @param string $name
	 * @param string $value
	 * @return Anole_Dispatcher_Context
	 */
	protected function putResult($name,$value){
		return $this->getContext()->putResult($name,$value);
	}
}
/**vim:sw=4 et ts=4 **/
?>