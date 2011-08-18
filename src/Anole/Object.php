<?php
/**
 * Anole框架基础类
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Object {
	/**
	 * output debug message
	 *
	 * @param string $msg
	 * @param string $sendor
	 */
    public static function debug($msg,$sendor=__CLASS__){
        Anole_LogFactory::getLog()->debug($msg,$sendor);
    }
    /**
     * output warn message
     *
     * @param string $msg
     * @param string $sendor
     */
	public static function warn($msg,$sendor=__CLASS__){
		Anole_LogFactory::getLog()->warn($msg,$sendor);
	}
	/**
     * output error message
     *
     * @param string $msg
     * @param string $sendor
     */
	public static function error($msg,$sendor=__CLASS__){
		Anole_LogFactory::getLog()->error($msg,$sendor);
	}
}
/**vim:sw=4 et ts=4 **/
?>