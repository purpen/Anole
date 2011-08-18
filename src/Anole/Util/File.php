<?php
/**
 * 文件日常处理类
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Util_File extends Anole_Object {
	/**
	 * 创建目录或文件
	 *
	 * @param string $path
	 * @param int $mode
	 * 
	 * @return bool
	 */
	public static function mk($path,$mode=0777){
		if(file_exists($path)){
			return true;
		}
		$dirs = split('/',$path);
		$p = '';
		for($i=0;$i<count($dirs);$i++){
			$p .= $dirs[$i].'/';
			if(is_dir($p)){
				continue;
			}
			mkdir($p);
			chmod($p,$mode);
		}
		return true;
	}
	/**
	 * read the content of the file
	 *
	 * @param string $file_path
	 * @return string
	 */
	public static function readFile($file_path){
		return file_get_contents($file_path);
	}
	
    /**
     * read all dir of target dir
     */
    public static function getDir($dir){
        $dirs = array();
        if($handle = opendir($dir)){
            while(false !== ($file = readdir($handle))){
                if($file != "." && $file != ".." && $file != ".svn") {
                    if(is_dir($dir. "/" .$file)){
                        $dirs[] = $file;
                    }
                }
            }
            closedir($handle);
            return $dirs;
        }
        return $dirs;
    }
    /**
     * read all files of target dir
     */
    public static function getDirFiles($dir){
        $files = array();
        if($handle = opendir($dir)){
            while(false !== ($file = readdir($handle))){
                if($file != "." && $file != ".." && $file != ".svn"){
                    if(is_file($dir . "/" . $file) && preg_match('/(.+)\.([a-zA-Z]+)/', $file)){
                        $files[$file] = $dir."/".$file;
                    }
                    if(is_dir($dir . "/" . $file)){
                    	$files = array_merge(self::getDirFiles($dir . "/" . $file), $files);
                    }
                }
            }
            closedir($handle);
            return $files;
        }
        return $files;
    }
	/**
	 * write the content into the file
	 *
	 * @param string $file_path
	 * @param string $content
	 * @param int $is_chmod
	 * @return bool
	 */
	public static function writeFile($file_path,$content,$is_chmod=false){
		//mkdir
		$dirs = dirname($file_path);
		self::mk($dirs);
		//put content
		$ok = file_put_contents($file_path,$content,LOCK_EX);
		if($ok === false){
			return false;
		}
		if(!$is_chmod){
			@chmod($file_path,0666);
		}
		return true;
	}
	
    /**
     * Get file extension
     *
     * @param string $filename
     * @return string $ext return file extension
     */
    public static function getFileExtension($filename){
        return strtolower(substr(strrchr($filename,'.'),1));
    }
    /**
     * 返回文件的mime类型
     * 
     * @param string $file
     * @return string
     */
    public static function getMimeContentType($file){
        static $mimeTypes = array (
         'ez'=>'application/andrew-inset',
         'hqx'=>'application/mac-binhex40',
         'cpt'=>'application/mac-compactpro',
         'doc'=>'application/msword',
         'bin'=>'application/octet-stream',
         'dms'=>'application/octet-stream',
         'lha'=>'application/octet-stream',
         'lzh'=>'application/octet-stream',
         'exe'=>'application/octet-stream',
         'class'=>'application/octet-stream',
         'oda'=>'application/oda',
         'pdf'=>'application/pdf',
         'ai'=>'application/postscript',
         'eps'=>'application/postscript',
         'ps'=>'application/postscript',
         'smi'=>'application/smil',
         'smil'=>'application/smil',
         'mif'=>'application/vnd.mif',
         'xls'=>'application/vnd.ms-excel',
         'ppt'=>'application/vnd.ms-powerpoint',
         'wbxml'=>'application/vnd.wap.wbxml',
         'wmlc'=>'application/vnd.wap.wmlc',
         'wmlsc'=>'application/vnd.wap.wmlscriptc',
         'bcpio'=>'application/x-bcpio',
         'vcd'=>'application/x-cdlink',
         'pgn'=>'application/x-chess-pgn',
         'cpio'=>'application/x-cpio',
         'csh'=>'application/x-csh',
         'dcr'=>'application/x-director',
         'dir'=>'application/x-director',
         'dxr'=>'application/x-director',
         'dvi'=>'application/x-dvi',
         'spl'=>'application/x-futuresplash',
         'gtar'=>'application/x-gtar',
         'hdf'=>'application/x-hdf',
         'js'=>'application/x-javascript',
         'skp'=>'application/x-koan',
         'skd'=>'application/x-koan',
         'skt'=>'application/x-koan',
         'skm'=>'application/x-koan',
         'latex'=>'application/x-latex',
         'nc'=>'application/x-netcdf',
         'cdf'=>'application/x-netcdf',
         'sh'=>'application/x-sh',
         'shar'=>'application/x-shar',
         'swf'=>'application/x-shockwave-flash',
         'sit'=>'application/x-stuffit',
         'sv4cpio'=>'application/x-sv4cpio',
         'sv4crc'=>'application/x-sv4crc',
         'tar'=>'application/x-tar',
         'tcl'=>'application/x-tcl',
         'tex'=>'application/x-tex',
         'texinfo'=>'application/x-texinfo',
         'texi'=>'application/x-texinfo',
         't'=>'application/x-troff',
         'tr'=>'application/x-troff',
         'roff'=>'application/x-troff',
         'man'=>'application/x-troff-man',
         'me'=>'application/x-troff-me',
         'ms'=>'application/x-troff-ms',
         'ustar'=>'application/x-ustar',
         'src'=>'application/x-wais-source',
         'zip'=>'application/zip',
         'au'=>'audio/basic',
         'snd'=>'audio/basic',
         'mid'=>'audio/midi',
         'midi'=>'audio/midi',
         'kar'=>'audio/midi',
         'mpga'=>'audio/mpeg',
         'mp2'=>'audio/mpeg',
         'mp3'=>'audio/mpeg',
         'aif'=>'audio/x-aiff',
         'aiff'=>'audio/x-aiff',
         'aifc'=>'audio/x-aiff',
         'ram'=>'audio/x-pn-realaudio',
         'rm'=>'audio/x-pn-realaudio',
         'rpm'=>'audio/x-pn-realaudio-plugin',
         'ra'=>'audio/x-realaudio',
         'wav'=>'audio/x-wav',
         'pdb'=>'chemical/x-pdb',
         'xyz'=>'chemical/x-xyz',
         'bmp'=>'image/bmp',
         'gif'=>'image/gif',
         'ief'=>'image/ief',
         'jpeg'=>'image/jpeg',
         'jpg'=>'image/jpeg',
         'jpe'=>'image/jpeg',
         'png'=>'image/png',
         'tiff'=>'image/tiff',
         'tif'=>'image/tiff',
         'wbmp'=>'image/vnd.wap.wbmp',
         'ras'=>'image/x-cmu-raster',
         'pnm'=>'image/x-portable-anymap',
         'pbm'=>'image/x-portable-bitmap',
         'pgm'=>'image/x-portable-graymap',
         'ppm'=>'image/x-portable-pixmap',
         'rgb'=>'image/x-rgb',
         'xbm'=>'image/x-xbitmap',
         'xpm'=>'image/x-xpixmap',
         'xwd'=>'image/x-xwindowdump',
         'igs'=>'model/iges',
         'iges'=>'model/iges',
         'msh'=>'model/mesh',
         'mesh'=>'model/mesh',
         'silo'=>'model/mesh',
         'wrl'=>'model/vrml',
         'vrml'=>'model/vrml',
         'css'=>'text/css',
         'html'=>'text/html',
         'htm'=>'text/html',
         'asc'=>'text/plain',
         'txt'=>'text/plain',
         'rtx'=>'text/richtext',
         'rtf'=>'text/rtf',
         'sgml'=>'text/sgml',
         'sgm'=>'text/sgml',
         'tsv'=>'text/tab-separated-values',
         'wml'=>'text/vnd.wap.wml',
         'wmls'=>'text/vnd.wap.wmlscript',
         'etx'=>'text/x-setext',
         'xml'=>'text/xml',
         'mpeg'=>'video/mpeg',
         'mpg'=>'video/mpeg',
         'mpe'=>'video/mpeg',
         'qt'=>'video/quicktime',
         'mov'=>'video/quicktime',
         'avi'=>'video/x-msvideo',
         'movie'=>'video/x-sgi-movie',
         'ice'=>'x-conference/x-cooltalk',
        );
        $ext = self::getFileExtension($file);
        if (isset( $mimeTypes[$ext])){
            return  $mimeTypes[$ext];
        }
        return 'application/octet-stream';
    }
}
/**vim:sw=4 et ts=4 **/
?>