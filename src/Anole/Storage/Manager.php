<?php
/**
 * 存储区管理
 * 
 * 存储分区的域概念(domain)：
 * 出于对于实际存储需求的不同,我们将数据存储从逻辑上划分不同的域，不同的domain的数据是相互分隔
 * 每个domain可以指定不同的provider和或者相同的provider但是具有不同的配置参数，
 * 这样通过对存储分区的域划分，可以对不同类型的数据进行不同级别的管理和维护。
 * 
 * 例如：
 * 对于小量的数据，我们可以使用小型文件系统进行存储;
 * 对于某些数据则使用数据库存储;
 * 对于经常使用用共享内存方式存储;
 * 对于海量的数据则使用分布式存储等等。
 * 
 * 通过域，应用系统可以对数据的存储方式和策略进行依据粒度的不同区比额对待。
 * 
 * @version $Id$
 * @author purpen
 */
abstract class Anole_Storage_Manager extends Anole_Object {
	
	private static $_domains;
	
	/**
	 * 获得指定存储域的provider
	 *
	 * @param string $domain
	 * @param string $provider_class
	 * @param array $provider_options
	 * 
	 * @return Anole_Storage_Provider
	 */
	public static function getDomain($domain='default',$provider_class=null,$provider_options=null){
		if(!isset(self::$_domains[$domain]) || is_null(self::$_domains[$domain])){
			self::debug("factory domain[$domain] and provider class[$provider_class].", __CLASS__);
			if(is_null($provider_class)){
				//get default provider class
				$provider_class = Anole_Config::get('storage.default');
			}
			if(is_null($provider_options)){
				//get default provider_class 's options
				$provider_options = Anole_Config::get('storage.'.$provider_class);
				if(!is_array($provider_options)){
					$provider_options = array();
				}
			}
			if(!class_exists($provider_class,true)){
				self::warn("Invalid provider class.",__CLASS__);
				throw new Anole_Storage_Exception("Invalid provider class.");
			}
			$provider = new $provider_class($provider_options);
			if(!$provider instanceof Anole_Storage_Provider){
				self::warn("Invalid provider class by instance.", __CLASS__);
				throw new Anole_Storage_Exception("Invalid provider class by instance.");
			}
			self::debug("instance domain[$domain] and provider class[$provider_class] ok!", __CLASS__);
			self::$_domains[$domain] = $provider;
		}
		return self::$_domains[$domain];
	}
	/**
     * 通过storage.yml中的domain_key来获得存储区域的provider
     * 
     * 你需要在storage.yml中定义类似的配置:
     * 
     * domain:
     *     prodiver:
     *     options:
     * 
     * @param string $domain
     * 
     * @return Anole_Storage_Provider
     */
    public static function getDomainByKey($domain){
        $domain_options = Anole_Config::get('storage.'.$domain);
        return self::getDomain($domain,$domain_options['provider'],$domain_options['options']);
    }
}
/**vim:sw=4 et ts=4 **/
?>