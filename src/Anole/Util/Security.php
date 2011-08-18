<?php
/**
 * 代码安全防范，防止SQL注入
 * 
 * 获取的变量一般都是通过GET或者POST方式提交过来的，那么我们只要对GET和POST过来的变量进行
 * 过滤，那么就能够达到防止注入的效果。filterGetVar(),filterPostVar().
 * 
 * @author purpen
 * @version $Id$
 */
class Anole_Util_Security extends Anole_Object {
	/**
	 * 检查输入的参数是否为数字
	 * 
	 * @param $val
	 * @return bool 
	 */
	public static function isNumber($val){
		if(preg_match("/^[0-9]+$/",$val)){
			return true;
		}
		return false;
	}
	/**
	 * 检查字符串长度是否符合要求
	 * 
	 * @param $val
	 * @param $min
	 * @param $max
	 * @return boolean
	 */
	public static function isNumLength($val,$min,$max){
		$theelement = trim($val);
		if(preg_match("/^[0-9]{".$min.",".$max."}$/",$val)){
			return true;
		}
		return false;
	}
	/**
	 * 检查字符串长度是否符合要求
	 * 
	 * @param $val
	 * @param $min
	 * @param $max
	 * @return boolean
	 */
	public static function isEngLength($val,$min,$max){
		$theelement = trim($val);
		if(preg_match("/^[a-zA-Z]{".$min.",".$max."}$/",$val)){
			return true;
		}
		return false;
	}
	/**
	 * 检查输入的参数是否为电话
	 * 
	 * @param $val
	 * @return bool
	 */
	public static function isPhone($val){
		if(preg_match("/^((0\d{2,3})-)(\d{7,8})(-(\d{3,}))?$/",$val)){
			return true;
		}
		return false;
	}
	/**
	 * 检查输入与的参数是否为手机号
	 * 
	 * @param $val
	 * @return bool
	 */
	public static function isMobile($val){
		if(preg_match("/(^(\d{2,4}[-_－—]?)?\d{3,8}([-_－—]?\d{3,8})? ([-_－—]?\d{1,7})?$)|(^0?1[35]\d{9}$)/", $val)){
			return true;
		}
		return false;
	}
	/**
	 * 检查输入的参数是否为邮编
	 * 
	 * @param $val
	 * @return boolean
	 */
	public static function isPostcode($val){
		if(preg_match("/[0-9]{4,6}$/",$val)){
			return true;
		}
		return false;
	}
	/**
	 * 检查邮箱地址是否合法性
	 * 
	 * @param $val
	 * @param $domain
	 * @return boolean
	 */
	public static function isEmail($val,$domain=null){
		if(is_null($domain)){
			if(preg_match("/^[a-z0-9-_.]+@[\da-z][\.\w-]+\.[a-z]{2,4}$/i", $val)){
				return true;
			}else{
				return false;
			}
		}else{
			if(preg_match("/^[a-z0-9-_.]+@".$domain."$/i", $val)){
				return true;
			}else{
				return false;
			}
		}
	}
	/**
	 * 检查输入的IP是否符合要求
	 * 
	 * @param $val
	 * @return boolean
	 */
	public static function isIp($val){
		return (bool) ip2long($val);
	}
	/**
	 * 检查输入的参数是否为合法人民币格式
	 * 
	 * @param $val
	 * @return boolean
	 */
	public static function isMoney($val){
		if(preg_match("/^[0-9]{1,}$/",$val)){
			return true;
		}
		if(preg_match("/^[0-9]{1,}\.[0-9]{1,2}$/",$val)){
			return true;
		}
		return false;
	}
	/**
	 * 检查日期是否符合0000-00-00
	 * 
	 * @param $val
	 * @return boolean
	 */
	public static function isDate($val){
		if(preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/", $val)){
			return true;
		}
		return false;
	}
	/**
	 * 检查日期是否符合0000-00-00 00:00:00
	 * 
	 * @param $val
	 * @return boolean
	 */
	public static function isTime($val){
		if(preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/",$val)){
			return true;
		}
		return false;
	}
	/**
	 * 检查是否输入为汉字
	 * 
	 * @param $val
	 * @return boolean
	 */
	public static function isChinese($val){
		$ilen = strlen($val);
		for($i=0;$i<$ilen;$i++){
			if(ord($val{$i})>=0x80){
				if((ord($val{$i})>=0x81 && ord($val{$i})<=0xFE) && ((ord($val{$i+1})>=0x40 && ord($val{$i+1}) < 0x7E) || (ord($val{$i+1}) > 0x7E && ord($val{$i+1})<=0xFE))){
					if(ord($val{$i})>0xA0 && ord($val{$i})<0xAA){
						//有中文标点
						return false;
					}
				}else{
					//有日文或其它文字
					return false;
				}
				$i++;
			}else{
				return false;
			}
		}
		
		return true;
	}
	/**
	 * 检查输入的参数是否为英文
	 * 
	 * @param $theelement
	 * @return boolean
	 */
	public static function isEnglish($theelement){
		if(preg_match("/[\x80-\xff]./",$theelement)){
			return false;
		}
		return true;
	}
	/**
	 * 代码清理
	 * 
	 * @param $string
	 * @return string
	 */
	public static function clean($string){
		
		//删除由 addslashes() 函数添加的反斜杠
		$string = stripslashes($string);
		
		//将把字符转换为 HTML 实体
		$string = htmlentities($string);
		
		//剥去 HTML、XML 以及 PHP 的标签,
		//strip_tags(string,allow),allow 可选。规定允许的标签。这些标签不会被删除。
		$string = strip_tags($string);
		
		return $string;
	}
	/**
	 * 过滤$_GET变量
	 * 
	 * @return array
	 */
	public static function filterGetVar(){
		if(empty($_GET)){
			return;
		}
		foreach($_GET as $get_key=>$get_value){
			if(is_numeric($get_value)){
				$get[strtolower($get_key)] = self::forceInt($get_value);
			}else{
				$get[strtolower($get_key)] = self::forceZF($get_value);
			}
		}
		return $get;
	}
	
	public static function filterPostVar(){
		if(empty($_POST)){
			return;
		}
		foreach($_POST as $post_key=>$post_value){
			if(is_numeric($post_value)){
				$post[strtolower($post_key)] = self::forceInt($post_value);
			}else{
				$post[strtolower($post_key)] = self::forceZF($post_value);
			}
		}
		return $post;
	}
	/**
	 * 字符串类型过滤
	 * 
	 * @param $string
	 * @return string
	 */
	public static function forceZF($string){
		//get_magic_quotes_gpc取得 PHP 环境配置的变量 magic_quotes_gpc的值
		//当 magic_quotes_gpc 打开时，所有的 ' (单引号), " (双引号), \ (反斜线)和空字符
		//会自动转为含有反斜线的溢出字符。
		if(!get_magic_quotes_gpc()){
			return addslashes($string);
		}
		return $string;
	}
	/**
	 * 将变量转换成整型变量
	 * 
	 * @param $v
	 * @return int
	 */
	public static function forceInt($number){
		return intval($number);
	}
	
	
}
/**vim:sw=4 et ts=4 **/
?>