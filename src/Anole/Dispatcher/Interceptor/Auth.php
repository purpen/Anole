<?php
/**
 * Validate Auth Interceptor 
 * 完成用户的身份验证和对受保护的资源的访问
 * 
 * 本拦截器需要和Anole_Auth包的类和接口进行协同工作.
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Dispatcher_Interceptor_Auth extends Anole_Dispatcher_Interceptor_Abstract {
	/**
	 * Authentication Provider
	 *
	 * @var Anole_Auth_Authentication_Provider
	 */
	protected $_auth;
	/**
     * 授权失败需要重定向的Anole_Auth_Authentication_Action
     * 
     * @var Anole_Auth_Authentication_Action
     */
	private $_eraction;
	
	public function init(){
		self::debug("start auth validate...", __METHOD__);
		$this->_auth = Anole_Auth_Authentication_Manager::getProvider();
		$action_class = Anole_Config::get('auth.authen_action');
		if(empty($action_class) || !class_exists($action_class)){
			self::warn("Not Found auth.authen_action class!", __CLASS__);
			throw new Anole_Dispatcher_Exception("Not Found auth.authen_action class!");
		}
		self::debug("Default action class:$action_class.", __METHOD__);
		
		$eraction = new $action_class();
		if(!$eraction instanceof Anole_Auth_Authentication_Action){
			self::warn("Class:$action_class isnot a valid Doggy_Auth_Authentication_Action", __CLASS__);
			throw new Anole_Dispatcher_Exception("Class:$action_class isnot a valid Doggy_Auth_Authentication_Action");
		}
		$this->_eraction = $eraction;
	}
	/**
	 * Execute Intercept Handle
	 *
	 * @param Anole_Dispatcher_ActionInvocation $invocation
	 * @return string
	 */
	public function intercept(Anole_Dispatcher_ActionInvocation $invocation){
		self::debug("start auth intercept...", __METHOD__);
		$this->checkAuth($invocation);
		return $invocation->invoke();
	}
	
	private function checkAuth(Anole_Dispatcher_ActionInvocation $invocation){
		$action = $invocation->getAction();
		if(!$action instanceof Anole_Auth_Resource){
			self::debug("Action not extends Anole_Auth_Resource;Skip.", __CLASS__);
			return;
		}
	    if(!$action instanceof Anole_Auth_AuthenticationResource){
            self::warn("Action not extends Anole_Auth_AuthenticationResource;Skip.", __CLASS__);
            return;
        }
        self::debug("Set Authentication to action is ok!", __METHOD__);
        
		$authentication = $this->_auth->createAuthentication();
		self::debug("Create Authentication is ok!", __METHOD__);
		$action->_setAuthentication($authentication);
		
		$method = strtolower($invocation->getMethod());
		$resource_id = $action->getResourceId();
		$privilege_map = $action->getPrivilegeMap();
		self::debug("Get method[$method] and resource_id[$resource_id].", __METHOD__);
		if(isset($privilege_map[$method])){
			$privilege_info = $privilege_map[$method];
		}else{
			if(isset($privilege_map['*'])){
				$privilege_info = $privilege_map['*'];
			}else{
				self::debug("$method not defined in PrivilegeMap,skip..");
				return;
			}
		}
		//检查特殊的权限名称
		$privilege_type = isset($privilege_info['privilege']) ? $privilege_info['privilege'] : $method;
		$uri = $invocation->getInvocationContext()->getRequest()->getRequestUri();
		
		switch($privilege_type){
			case Anole_Auth_AuthenticationResource::PRIV_AUTHORIZED:
				if(!$authentication->isAuthenticated()){
					self::warn("authentication not authorized,redirct to authorize action.", __CLASS__);
					$this->_eraction->_setNextUrl($uri);
					$invocation->setAction($this->_eraction);
					//redirect to login
					$invocation->setMethod('login');
					return;
				}
				self::debug("Authorize by PRIV_AUTHORIZED,APPROVED.", __CLASS__);
				return;
				break;
			case Anole_Auth_AuthenticationResource::PRIV_CUSTOM:
				$custom_method = $privilege_info['custom'];
				if(!is_array($custom_method)){
					$callback = array($action, $custom_method);
				}else{
					$callback = $custom_method;
				}
				$ok = call_user_func_array($callback, array($authentication,$method));
				if($ok){
					self::debug("Authorize by custom authorize,APPROVED.", __CLASS__);
					return;
				}
				$this->_eraction->_setNextUrl($uri);
				self::warn("Authorize by custom authorize,DENNIED.", __CLASS__);
				break;
			case Anole_Auth_AuthenticationResource::PRIV_NONE:
				self::debug("Privilege is PRIV_NONE,SKIP.", __CLASS__);
				return;
				break;
			default:
				//it isnot logined
				if(!$authentication->isAuthenticated()){
                    self::warn("authentication not authorized,redirct to authorize action.", __CLASS__);
                    $this->_eraction->_setNextUrl($uri);
                    $invocation->setAction($this->_eraction);
                    //redirect to login
                    $invocation->setMethod('login');
                    return;
                }
                //validate acl
                if($authentication->getAcl()->isAllowed($resource_id,$privilege_type)){
                	self::debug("Resource:[$resource_id] Privilege[$privilege_type] APPROVED.",__CLASS__);
                	return;
                }
                self::warn("Resource:[$resource_id] Privilege[$privilege_type] DENNIED.");
				break;
		}
		//or denny
		$invocation->setAction($this->_eraction);
		$invocation->setMethod('deny');
		return;
	}
}
/**vim:sw=4 et ts=4 **/
?>