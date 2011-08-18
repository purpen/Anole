<?php
/**
 * 依据一定的规则，自动加载类
 * 
 * @author purpen
 * @version $Id$
 */
class Anole_ClassLoader {
	private static $_include_path = array();
	private static $_3rd_path = array();
	public static $_classes_cache = array();
	
	/**
	 * 将指定的路径添加到类搜索路径中
	 *
	 * @param mixed $path
	 */
	public static function addIncludePath($path){
		if(empty($path)){
			return;
		}
		$paths = array();
		if(!is_array($path)){
			$paths[] = $path; 
		}else{
			$paths = $path;
		}
		$lib_dir = Anole_Config::get('anole.lib_dir');
		for($i=0;$i<count($paths);$i++){
			$c_p = $paths[$i];
			if(!in_array($c_p,self::$_include_path)){
				self::$_include_path[] = $lib_dir.'/'.$c_p;
			}
		}
	}
	/**
	 * get current class path
	 *
	 * @return array
	 */
	public static function getIncludePath(){
		return self::$_include_path;
	}
	/**
	 * 将搜索路径设置到PHP的INCLUDE_PATH中
	 */
	public static function applyIncludePath(){
		if(empty(self::$_include_path)){
			return;
		}
		//get exist include_path
		$paths = explode(PATH_SEPARATOR,get_include_path());
		foreach(self::$_include_path as $c_p){
			if(!file_exists($c_p) || (file_exists($c_p) && filetype($c_p) !== 'dir')){
				throw new Anole_Exception('include path: '.$c_p.' not invalid!');
				continue;
			}
			//add path into include_path
			if(array_search($c_p,$paths) === false){
				array_push($paths,$c_p);
			}
		}
		//apply new include_path
		set_include_path(implode(PATH_SEPARATOR,$paths));
	}
	/**
	 * load common class file
	 *
	 * @param string $file_path
	 * @return bool
	 */
	public static function includeFile($file_path){
		if(isset(self::$_3rd_path[$file_path])){
			return true;
		}
		@require_once($file_path);
		self::$_3rd_path[$file_path] = true;
		return true;
	}
	/**
	 * 自载入类
	 * 
	 * @return string
	 */
	public static function initAutoload(){
        if(function_exists('spl_autoload_register')){
            //compatiable with other lib,if there has an __autoload already
            if(function_exists('__autoload')){
                spl_autoload_register('__autoload');
            }
            ini_set('unserialize_callback_func', 'spl_autoload_call');
            spl_autoload_register(array(__CLASS__, 'loadClass'));
        }elseif(!function_exists('__autoload')){
            function __autoload($class){
                return Anole_ClassLoader::loadClass($class);
            }
            ini_set('unserialize_callback_func', '__autoload');
        }else{
            //halt
            die("SPL not installed,there has been an __autoload function,we cannot continue!\n".
                'Fatal exception,System is halt!');
        }
	}
	
    /**
     * load target class
     *
     * @param string $class_name
     * @return bool
     */
    public static function loadClass($class_name){
        if(empty($class_name)){
            return;
        }
        //检测类或接口是否已定义,如果类/接口已经存在则返回
        if(class_exists($class_name,false) || interface_exists($class_name,false)){
            return true;
        }
        //load from class cache
        if(isset(self::$_classes_cache[$class_name])){
            $full_path = self::$_classes_cache[$class_name];
            @require_once $full_path;
            if(class_exists($class_name,false) || interface_exists($class_name, false)){
                return true;
            }else{
                unset(self::$_classes_cache[$class_name]);
            }
        }
        //尝试使用绝对路径方式加载
        $full_path = str_replace('_','/',$class_name).'.php';
        @require_once $full_path;
        if(class_exists($class_name,false) || interface_exists($class_name,false)){
            self::$_classes_cache[$class_name] = $full_path;
            return true;
        }
        return false;
    }
}
/**
 * import 3rd class file
 *
 * @param string $class_path
 * @return bool
 */
function include3rd($class_path){
    if(!empty($class_path)){
    	return Anole_ClassLoader::includeFile($class_path);
    }
    return false;
}
/**vim:sw=4 et ts=4 **/
?>