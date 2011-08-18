<?php
/**
 * 常用的标准库函数
 *
 * @version $Id$
 * @author purpen
 */
abstract class Anole_Util_StdLib {
    /**
     * 递归创建目录
     * 
     * @param string $path
     * @param int $mode 创建目录的权限掩码,默认是0755(属主读写执行,其他读执行)
     * 
     * @return bool
     */
    public static function buildDir($path,$mode=0755){
        if(file_exists($path)){
        	return true;
        }
        $dirs = split('/',$path);
        $p = '';
        for($i=0;$i<count($dirs);$i++){
            $p.= $dirs[$i].'/';
            if(is_dir($p)){
            	continue;
            }
            mkdir($p);
            chmod($p,$mode);
        }
        return true;
    }
    
    /**
     * Returns the extension part of a filename
     * 
     * @param   string $file
     * 
     * @return  string
     */
    public static function fileExtension($file) {
        $info = pathinfo($file);
        return isset($info['extension']) ? $info['extension'] : null;
    }
}
?>