<?php
/**
 * 认证接口类
 * 
 * @author purpen
 * @version $Id$
 */
interface Anole_Auth_Authentication_Provider {
	/**
	 * 撤消和失效指定的凭据信息
	 *
	 * @param Anole_Auth_Authentication $authen
	 */
	function revoke(Anole_Auth_Authentication $authen=null);
	/**
	 * 创建一个可供当前用户使用的授权凭证信息
	 *
	 * @return Anole_Auth_Authentication
	 */
	function createAuthentication();
}
/**vim:sw=4 et ts=4 **/
?>