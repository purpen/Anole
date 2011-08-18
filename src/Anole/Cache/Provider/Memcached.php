<?php
/**
 * Memcache Cache Interface Class
 * 
 * @author purpen
 * @version $Id$
 */
class Anole_Cache_Provider_Memcached extends Anole_Object implements Anole_Cache_Provider {
	/**
	 * Memcache object
	 * 
	 * @var Memcache
	 */
	private $memcache;
	
	public function __construct($options=array()){
		$ttl  = 3600;
        $host = null;
        $compress = true;
        
        extract($options,EXTR_IF_EXISTS);
        
        if(empty($host)){
            throw new Anole_Cache_Exception('memcache server host not specified!');
        }
        $this->memcache = new Memcache();
        $this->_parseServer($host);
        $this->ttl = $ttl;
        $this->compress = $compress;
	}
	
	private function _parseServer($s){
	    $servers = split(',',$s);
        foreach ($servers as $server) {
            list($host,$port) = split(':',$server);
            self::debug('add server:'.$host.' port:'.$port, __CLASS__);
            $this->memcache->addServer($host,$port);
        }
	}
	
    private function _parseGroupDataKey($group, $key){
        $g_v = $this->memcache->get('__g::'.$group);
        if(!$g_v){
            $g_v = 1;
            $this->memcache->set('__g::'.$group,$g_v,0,0);
        }
        return '_g:'.$group.':'.$g_v.':'.$key;
    }
    /**
     * 从cache中获取指定key的数据
     *
     * @param string $key 缓存数据的key
     * @param string $group 缓存数据所属的组,默认为'default'
     */
    public function get($key,$group='default'){
        if(!is_null($group)){
            $group = Anole_APP.'::'.$group;
            $key = $this->_parseGroupDataKey($group,$key);
            self::debug('get key:'.$key, __CLASS__);
        }
        $v = $this->memcache->get($key);
        return ($v===false) ? null : $v;
    }
    
    /**
     * 将数据存到Cache中
     *
     * @param string $key 缓存数据的key
     * @param mixed $value 需要缓存的数据
     * @param string $group 缓存数据所属的组,默认为'default'
     * @param int $ttl 需要缓存的时间,单位为秒
     * @return Anole_Cache_Provider_Memcached
     */
    public function set($key,$value,$group='default',$ttl=NULL){
        if(is_null($ttl)){
        	$ttl = $this->ttl;
        }
        if($ttl > 2592000){
        	$ttl = 2592000;
        }
        if(!is_null($group)){
            $group = Anole_APP.'::'.$group;
            $key = $this->_parseGroupDataKey($group,$key);
            self::debug('set key:'.$key, __CLASS__);
        }
        if($this->compress){
            $this->memcache->set($key,$value,MEMCACHE_COMPRESSED,$ttl);
        }else{
            $this->memcache->set($key,$value,0,$ttl);
        }
        
        return $this;
    }
    
    /**
     * 清除cache中缓存的数据(全部或指定分组)
     *
     * @param string $group 是否限定某个组,若为空则清除全部的缓存
     * 
     * @return Anole_Cache_Provider_Memcached
     */
    public function clear($group=null){
        if(is_null($group)){
            $this->memcache->flush();
        }else{
            $group = Anole_APP.'::'.$group;
            $this->_clearGroup($group);
        }
        return $this;
    }
    /**
     * 清除指定分组cache中缓存的数据
     * 
     * @param string $group
     * 
     * @return void
     */
    private function _clearGroup($group){
        $value = $this->memcache->increment('__g::'.$group,1);
        if(!$value && $value > 99999){
            $this->memcache->set('__g::'.$group,1,0,0);
        }
    }
    /**
     * 从cache中删除已缓存的数据
     * 
     * @param string $key 缓存数据的key
     * @param string $group 缓存数据所属的组,默认为'default'
     * @return Anole_Cache_Provider_Memcached
     */
    public function remove($key,$group='default'){
        if (!is_null($group)) {
            $group = Anole_APP.'::'.$group;
            $key = $this->_parseGroupDataKey($group,$key);
        }
        
        $this->memcache->delete($key);
        
        return $this;
    }
}
/**vim:sw=4 et ts=4 **/
?>