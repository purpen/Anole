<?php
/**
 * 依session机制的Auth Provider
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Auth_Authentication_Provider_Session extends Anole_Object implements Anole_Auth_Authentication_Provider {
	const AUTH_SESSION_KEY='__auth_session_key';
	
	/**
     * 撤消和失效指定的凭据信息
     *
     * @param Anole_Auth_Authentication $authen
     */
    public function revoke(Anole_Auth_Authentication $authen=null){
    	if(is_null($authen)){
    		$authen = $this->createAuthentication();
    	}
    	$authen->setAuthenticated(false);
    	$context = Anole_Session_Context::getContext();
    	//empty session
    	$context->set(self::AUTH_SESSION_KEY,null);
    }
    /**
     * 创建一个可供当前用户使用的授权凭证信息
     *
     * @return Doggy_Auth_Authentication
     */
    function createAuthentication(){
    	$context = Anole_Session_Context::getContext();
    	$authen = $context->get(self::AUTH_SESSION_KEY);
    	if(!is_null($authen)){
    		return $authen;
    	}
    	$authen = new Anole_Auth_Authentication();
    	$authen->setAcl(new Anole_Auth_Acl());
    	$authen->setAuthenticated(false);
    	$context->set(self::AUTH_SESSION_KEY,$authen);
    	return $authen;
    }
}
/**vim:sw=4 et ts=4 **/
?>