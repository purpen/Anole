<?php 
/**
 * Cache Provider interface
 * 
 * @author purpen
 * @version $Id$
 */
interface Anole_Cache_Provider {
	/**
	 * 将数据临时存到Cache中
	 * 
	 * @param string $key
	 * @param mixed $value
	 * @param string $group 缓存数据所属的组,默认为'default'
	 * @param int $ttl 需要缓存的时间
	 * @return Anole_Cache_Provider
	 */
	function set($key,$value,$group='default',$ttl=NULL);
	/**
	 * 从cache中获取指定key的数据
	 * 
	 * @param string $key
	 * @param string $group 缓存数据所属的组,默认为'default'
	 * @return mixed
	 */
	function get($key,$group='default');
	/**
	 * 清除cache中缓存的数据(全部或指定分组)
	 * 
	 * @param $group
	 * @return Anole_Cache_Provider
	 */
	function clear($group=null);
	/**
	 * 从cache中删除已缓存的数据
	 * 
	 * @param string $key 缓存数据的key
     * @param string $group 缓存数据所属的组,默认为'default'
     * 
	 * @return Anole_Cache_Provider
	 */
	function remove($key,$group='default');
}
/**vim:sw=4 et ts=4 **/
?>