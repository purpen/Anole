<?php
/**
 * ACL用于控制和保护
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Auth_Acl extends Anole_Object {
	
	private $_resources = array();
	
	private $_denny_all;
	private $_allow_all;
	/**
	 * default allow flag
	 *
	 * @var boolean
	 */
	protected $_default_allowed = false;
	
    /**
     * 在ACL中添加一条允许规则
     *
     * 
     * @param string $resource 资源对象的id
     * @param string $privilege 权限名称
     * @param mixed $rule_data 附加的规则数据
     * 
     * @return Anole_Auth_Acl
     */
    public function allow($resource,$privilege,$rule_data=null){
        $this->_resources[$resource][$privilege]['rule'] = true;
        $this->_resources[$resource][$privilege]['data'] = $rule_data;
        return $this;
    }
    /**
     * 在ACL中添加一条禁止规则
     *
     * 如果重复添加，那么遵循的覆盖继承原则同allow方法
     * 
     * @param string $resource 资源对象的id
     * @param string $privilege 权限名称
     * @param mixed $rule_data 附加的规则数据
     * 
     * @return Anole_Auth_Acl
     */
    public function deny($resource,$privilege,$rule_data=null){
        $this->_resources[$resource][$privilege]['rule'] = false;
        $this->_resources[$resource][$privilege]['data'] = $rule_data;
        return $this;
    }
    
    /**
     * 根据ACL中的allow和deny规则匹配检查，确定是否具有对指定resource和privilege的权限
     *
     * @param string $resource Resource Id 用于标识Resource接口的Id
     * @param string $privilege 权限名称
     * 
     * @return boolean
     */
    public function isAllowed($resource,$privilege){
        if($this->_denny_all){
        	return false;
        }
        if($this->_allow_all){
        	return true;
        }
        if(!isset($this->_resources[$resource][$privilege])){
        	return $this->_default_allowed;
        }
        return $this->_resources[$resource][$privilege]['rule'];
    }
    /**
     * 获得指定权限的规则数据
     * 
     * @param string $resource
     * @param string $privilege
     * 
     * @return mixed
     */
    public function getRuleData($resource,$privilege){
        return isset($this->_resources[$resource][$privilege]['data']) ? $this->_resources[$resource][$privilege]['data'] : null;
    }
    /**
     * 从Acl列表中删除指定权限
     *
     * @param string $resource
     * @param string $privilege
     * 
     * @return Anole_Auth_Acl
     */
    public function removeRule($resource,$privilege){
    	unset($this->_resources[$resource][$privilege]);
    	return $this;
    }
    /**
     * Reset Acl List
     *
     * @return Anole_Auth_Acl
     */
    public function reset(){
    	$this->_denny_all = false;
    	$this->_allow_all = false;
    	$this->_resources = array();
    	return $this;
    }
    /**
     * 设置默认规则为允许
     *
     * @return Anole_Auth_Acl
     */
    public function setDefaultAllowed(){
    	$this->_default_allowed = true;
    	return $this;
    }
    /**
     * 设置默认规则为拒绝
     *
     * @return Anole_Auth_Acl
     */
    public function setDefaultDennied(){
    	$this->_default_allowed = false;
    	return $this;
    }
    /**
     * 设置为全部允许
     * 
     * @return Anole_Auth_Acl
     */
    public function allowAll(){
        $this->_deny_all = false;
        $this->_allow_all = true;
        return $this;
    }
    /**
     * 设置为全部禁止
     * 
     * @return Anole_Auth_Acl
     */
    public function denyAll(){
        $this->_deny_all = true;
        $this->_allow_all = false;
        return $this;
    }
}
/**vim:sw=4 et ts=4 **/
?>