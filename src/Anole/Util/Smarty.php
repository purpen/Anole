<?php
/**
 * samrty instance factory
 * 
 * @version $Id$
 * @author purpen
 */
abstract class Anole_Util_Smarty {
	/**
	 * smarty instance
	 *
	 * @var Anole_Util_Smarty_Base
	 */
	protected static $_smarty;
	/**
	 * factory a smarty instance
	 *
	 * @param bool $force_new
	 * @return Anole_Util_Smarty_Base
	 */
	public static function factory($force_new=false){
		if($force_new){
			return new Anole_Util_Smarty_Base();
		}
		if(is_null(self::$_smarty)){
			self::$_smarty = new Anole_Util_Smarty_Base();
		}
		return self::$_smarty;
	}
}
/**vim:sw=4 et ts=4 **/
?>