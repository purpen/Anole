<?php
/**
 * 实现本接口的Action可以自动获得当前用户的Authentication信息
 * 
 * @version $Id$
 * @author purpen
 */
interface Anole_Auth_Resource {
	/**
     * 设置当前用户的Authentication
     *
     * Authentication
     * 将由Anole_Dispatcher_Intercpetor_Auth通过调用此方法注入
     * 
     * @param Anole_Auth_Authentication $authentication
     */
	function _setAuthentication(Anole_Auth_Authentication $authentication);
}
/**vim:sw=4 et ts=4 **/
?>