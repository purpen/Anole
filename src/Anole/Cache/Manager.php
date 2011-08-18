<?php
/**
 * Cache Manager Interface Class
 * 
 * @author purpen
 * @version $Id$
 */
abstract class Anole_Cache_Manager {
	/**
     * Cache Provider instance
     *
     * @var Anole_Cache_Provider
     */
    private static $cachers = array();
    
    /**
     * Factory Manager Instance
     *
     * @return Anole_Cache_Provider
     */
    public static function getCache($id='default'){
        if(!isset(self::$cachers[$id])){
            $config = Anole_Config::get("cache.$id");
            $provider = $config['provider'];
            $options =  $config['options'];
            $cacher = new $provider($options);
            if(!$cacher instanceof Anole_Cache_Provider){
                throw new Anole_Cache_Exception("Invalid cacher[id:$id,provider:$provider] class");
            }
            self::$cachers[$id] = $cacher;
        }
        
        return self::$cachers[$id];
    }
    
    /**
     * 将数据存到Cache中
     *
     * @param string $key 缓存数据的key
     * @param mixed $value 需要缓存的数据
     * @param string $group 缓存数据所属的组,默认为'default'
     * @param int $ttl 需要缓存的时间,单位为秒
     * 
     * @return Anole_Cache_Provider
     */
    public static function set($key,$value,$group='default',$ttl=null){
        return self::getCache()->set($key,$value,$group,$ttl);
    }
    /**
     * 从cache中获取指定key的数据
     *
     * @param string $key 缓存数据的key
     * @param string $group 缓存数据所属的组,默认为'default'
     * 
     * @return mixed
     */
    public static function get($key,$group='default'){
        return self::getCache()->get($key,$group);
    }
    /**
     * 清除cache中缓存的数据(全部或指定分组)
     *
     * @param string $group 是否限定某个组,若为空则清除全部的缓存
     * 
     * @return Anole_Cache_Provider
     */
    public static function clear($group=null){
        return self::getCache()->clear($group);
    }
    /**
     * 从cache中删除已缓存的数据
     * 
     * @param string $key 缓存数据的key
     * @param string $group 缓存数据所属的组,默认为'default'
     * 
     * @return Anole_Cache_Provider
     */
    public static function remove($key,$group='default'){
        return self::getCache()->remove($key,$group);
    }
}
/**vim:sw=4 et ts=4 **/
?>