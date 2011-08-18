<?php
/**
 * File Storage System
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Storage_Provider_FileSystem extends Anole_Object implements Anole_Storage_Provider {
	/**
	 * storage root dir
	 *
	 * @var string
	 */
	private $_root;
	/**
	 * http request url
	 *
	 * @var string
	 */
	private $_root_url;
	/**
	 * dir isn't hashed
	 *
	 * @var bool
	 */
	protected $_hash_dir = true;
	
	public function __construct($options=array()){
		$root = null;
		$root_url = null;
		$hash_dir = true;
		
		extract($options,EXTR_IF_EXISTS);
		if(empty($root)){
			throw new Anole_Storage_Exception("Storage root directory is NULL");
		}
		if(!file_exists($root)){
			self::warn("Storage root dir is not exist;Create it:$root", __CLASS__);
			Anole_Util_StdLib::buildDir($root);
		}
		$this->_root = $root;
		$this->_root_url = $root_url;
		$this->_hash_dir = $hash_dir;
	}
	/**
	 * build storage path
	 *
	 * @param string $id
	 * @param bool $is_build
	 */
	private function _getHashPath($id,$is_build=true){
		if(!$this->_hash_dir){
			$path = ltrim($id, '/');
		}else{
			$hash = hash('md5', $id);
            $ext = strtolower(Anole_Util_StdLib::fileExtension($id));
            if(!empty($ext)){
                $hash .= '.'.$ext;
            }
            $path = substr($hash,1,2).'/'.substr($hash,2,2).'/'.$hash;
		}
		if($is_build){
			$dir = dirname($this->_root.'/'.$path);
			if(!file_exists($dir)){
				Anole_Util_StdLib::buildDir($dir);
			}
		}
		return $path;
	}
    /**
     * Write data into disk
     * 
     * @param string $path
     * @param string $data
     * 
     * @return void
     */
    private function _write($path,$data){
        self::debug('start to write backend file:'.$path, __METHOD__);
        $ok = @file_put_contents($path,$data,LOCK_EX);
        if($ok === false){
            self::warn('cannot create file:'.$path, __METHOD__);
            throw new Anole_Storage_Exception('cannot store file into filesystem:'.$path);
        }
        @chmod($path,0666);
    }
	/**
     * 将以指定的Key保存数据
     *
     * @param string $id
     * @param string $data
     * 
     * @return Anole_Storage_Provider_FileSystem
     */
    public function store($id,$data){
    	$local_path = $this->_root.'/'.$this->_getHashPath($id);
    	$this->_write($local_path,$data);
    	return $this;
    }
    /**
     * 将本地文件保存到后端
     *
     * @param string $id
     * @param string $file
     * 
     * @return Anole_Storage_Provider_FileSystem
     */
    public function storeFile($id,$file){
    	if(!is_readable($file)){
    		self::warn("File[$file] is not readable.",__CLASS__);
    		throw new Anole_Storage_Exception("File[$file] is not readable.");
    	}
    	$data = @file_get_contents($file);
    	return $this->store($id,$data);
    }
    /**
     * 删除指定id的数据
     *
     * @param string $id
     * @return Anole_Storage_Provider_FileSystem
     */
    public function delete($id){
    	$local_path = $this->_root.'/'.$this->_getHashPath($id);
    	if(file_exists($local_path)){
    		@unlink($local_path);
    	}
    	return $this;
    }
    /**
     * 以字符串形式返回指定id的数据内容
     *
     * @param string $id
     * 
     * @return string
     */
    public function get($id){
    	$local_path = $this->_root.'/'.$this->_getHashPath($id);
    	if(file_exists($local_path)){
    		return file_get_contents($local_path);
    	}
    	return null;
    }
    /**
     * 返回后端指定id的数据的Path以便客户端可以用fopen进行后续操作
     *
     * @param string $id
     * @return string
     */
    public function getPath($id){
        $local_path = $this->_root.'/'.$this->_getHashPath($id);
        if(file_exists($local_path)){
            return $local_path;
        }
        return null;
    }
    /**
     * 返回指定id的uri访问地址（如果可能)
     * 
     * 如果资源不存在或不支持uri访问则返回null
     *
     * @param string $id
     * @return string
     */
    public function getUri($id){
    	$path = $this->_getHashPath($id);
    	$full_path = $this->_root.'/'.$path;
    	if(file_exists($full_path)){
    		return $this->_root_url.'/'.$path;
    	}
    	return null;
    }
    /**
     * 检测是否已经存在指定id的数据
     *
     * @param string $id
     * @return boolean
     */
    public function exists($id){
    	$local_path = $this->_root.'/'.$this->_getHashPath($id);
    	return file_exists($local_path);
    }
    /**
     * 复制指定id的数据到新的id,若新的id已经存在则将被覆盖
     * 
     * 注意：复制是无条件复制,因此客户端应自行检查是否存在目标id
     *
     * @param string $id
     * @param string $copy_id
     * 
     * @return Anole_Storage_Provider_FileSystem
     */
    public function copy($id,$copy_id){
    	if($id == $copy_id){
    		self::warn("Found self copy operation!",__CLASS__);
    		return $this;
    	}
    	$data = $this->get($id);
    	if(!empty($data)){
    		$this->store($copy_id,$data);
    	}
    	return $this;
    }
    /**
     * 将旧id修改为新的id
     *
     * 如旧id不存在则抛出一个异常。
     * 
     * @param string $old_id
     * @param string $new_id
     * 
     * @return Anole_Storage_Provider_FileSystem
     */
    public function rename($old_id,$new_id){
    	if($old_id == $new_id){
    		self::warn("Found self copy operation!",__CLASS__);
            return $this;
    	}
    	$old_path = $this->_root.'/'.$this->_getHashPath($old_id);
    	if(!file_exists($old_path)){
    		self::warn("Old file[$old_path] is Exists!", __CLASS__);
    		throw new Anole_Storage_Exception("Old file[$old_path] is Exists!");
    	}
    	$new_path = $this->_root.'/'.$this->_getHashPath($new_id);
    	$ok = rename($old_path, $new_path);
    	if(!$ok){
    		self::warn("Rename from [$old_path] to [$new_path] failed.", __CLASS__);
    		throw new Anole_Storage_Exception("Rename file from [$old_id] to [$new_id] failed.");
    	}
    	return $this;
    }
}
/**vim:sw=4 et ts=4 **/
?>