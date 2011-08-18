<?php
/**
 * Authentication Manager
 *
 * @version $Id$
 * @author purpen
 */
abstract class Anole_Auth_Authentication_Manager extends Anole_Object {
	private static $_instance = array();
	/**
	 * 获得一个Auth_Provider的实例
	 *
	 * @param string $provider
	 * 
	 * @return Anole_Auth_Authentication_Provider
	 */
	public static function getProvider($provider=null){
		if(is_null($provider)){
			$provider = Anole_Config::get('auth.default');
		}
		self::debug("Auth provider: $provider", __METHOD__);
		if(!isset(self::$_instance[$provider])){
			$options = Anole_Config::get('auth.'.$provider);
			
			$instance = new $provider($options);
			if(!$instance instanceof Anole_Auth_Authentication_Provider){
				self::warn('Invalid authentication provider class:'.$provider, __METHOD__);
				throw new Anole_Auth_Exception('Invalid authentication provider class:'.$provider);
			}
			self::debug("Auth instance object is ok!", __METHOD__);
			
			self::$_instance[$provider] = $instance;
		}
		return self::$_instance[$provider];
	}
	/**
	 * 取消当前用户的authentication
	 */
	public static function revokeCurrent(){
		self::getProvider()->revoke();
	}
}
/**vim:sw=4 et ts=4 **/
?>