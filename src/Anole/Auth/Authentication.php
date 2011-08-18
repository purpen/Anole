<?php
/**
 * Authentication用于标识和确定某个用户的身份极其对应的授权状态和附加信息
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Auth_Authentication extends Anole_Object {
	private $_acl;
    private $_identity;
    private $_credentials;
    private $_authenticated = false;
    
    /**
     * 返回当前信息是否已经过认证
     *
     * @return boolean
     */
    public function isAuthenticated(){
        return $this->_authenticated;
    }
    /**
     * 标志当前信息为已认证状态
     *
     * @param bool $isAuthenticated
     * 
     * @return Anole_Auth_Authentication
     *
     */
    public function setAuthenticated($isAuthenticated){
        $this->_authenticated = $isAuthenticated;
        return $this;
    }
    
    /**
     * 设置当前对象的ACL(访问权限列表)
     *
     * @param  Anole_Auth_Acl $value
     * 
     * @return Anole_Auth_Authentication
     */
    public function setAcl($value){
        $this->acl = $value;
        return $this;
    }
    /**
     * 返回ACL
     *
     * @return Anole_Auth_Acl
     */
    public function getAcl(){
        return $this->acl;
    }
    /**
     * 设置身份标识
     * 
     * 身份标识用于唯一确定当前授权对象的身份，一般为用户名,账号等。
     *
     * @param string $value
     * @return Anole_Auth_Authentication
     */
    public function setIdentity($value){
        $this->_identity = $value;
        return $this;
    }
    /**
     * 返回身份标识
     *
     * @return string
     */
    public function getIdentity(){
        return $this->_identity;;
    }
    /**
     * 
     * 返回当前的身份凭据
     * 
     * 身份凭据是确保身份正确和不被他人冒用，一般是身份对应的密码，口令，也可以是
     * 加密令牌。
     *
     * @return string
     */
    public function getCredentials(){
        return $this->_credentials;
    }
    /**
     * 设置身份凭据
     *
     * @param string $credentials
     * 
     * @return Anole_Auth_Authentication
     */
    public function setCredentials($credentials){
        $this->_credentials = $credentials;
        return $this;
    }
    
    public function __toString(){
        return 'Authentication:id=>['.$this->getIdentity().']';
    }
}
/**vim:sw=4 et ts=4 **/
?>