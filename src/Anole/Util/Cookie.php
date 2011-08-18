<?php
/**
 * cookie 操作工具类
 * 
 * @author purpen
 * @versiion $Id$
 */
class Anole_Util_Cookie extends Anole_Object {
    
    const LIFE_TIME = 864000; //10day
    
    /**
     * 清空cookie
     * 
     * @return void
     */
    public static function clearCookie($key){
        @setcookie($key,'',time()-self::LIFE_TIME,'/');
    }
    /**
     * 设置cookie
     * 
     * @param string $key
     * @param string $value
     * @param $ttl
     * @return void
     */
    public static function setCookie($key,$value,$ttl=864000){
        if($ttl <= 0){
            $ttl = 0;
        }else{
            $ttl = time()+$ttl;
        }
        setcookie($key,$value,$ttl,'/');
    }
    /**
     * 获取cookie的值
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getCookie($key,$default=null){
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }
    
    /**
     * 获取后台管理用户的cookie
     * 
     * @return mixed
     */
    public static function getAdminID(){
        return self::getCookie('__XE_AMID_');   
    }
    /**
     * 设置后台管理用户的cookie
     * 
     */
    public static function setAdminID($value,$ttl=null){
        self::setCookie('__XE_AMID_',$value,$ttl);
    }
    /**
     * 获取后台管理用户的cookie
     * 
     * @return mixed
     */
    public static function getAdminName(){
        return self::getCookie('__XE_AMNAME_');
    }
    /**
     * 设置后台管理用户的cookie
     */
    public static function setAdminName($value,$ttl=null){
        self::setCookie('__XE_AMNAME_',$value,$ttl);
    }
    /**
     * 清空管理用户的登录凭证<UID、Username>
     * 
     * @return void
     */
    public static function clearAdminer(){
        self::clearCookie('__XE_AMNAME_');
        self::clearCookie('__XE_AMID_');
    }
    /**
     * 验证管理用户是否登录
     * 
     * @return bool
     */
    public static function hasAdminLogged(){
        $AMID = self::getAdminID();
        return !is_null($AMID) ? TRUE : FALSE;
    }
    /**
     * 未验证成功跳转
     * 
     * @param $url
     * @param $urlencode
     */
    public static function redirect($url=null){
        header("Location:$url");
    }
    
    /**
     * 获取用户的cookie
     * 
     * @return mixed
     */
    public static function getUserID(){
        return self::getCookie('__XE_UVID_');   
    }
    /**
     * 设置用户的cookie
     * 
     */
    public static function setUserID($value,$ttl=null){
        self::setCookie('__XE_UVID_',$value,$ttl);
    }
    
    /**
     * 获取用户的cookie
     * 
     * @return mixed
     */
    public static function getUserName(){
        return self::getCookie('__XE_UVNAME_');
    }
    /**
     * 设置用户的cookie
     */
    public static function setUserName($value,$ttl=null){
        self::setCookie('__XE_UVNAME_',$value,$ttl);
    }
    /**
     * 清空用户的登录凭证<UID、Username>
     * 
     * @return void
     */
    public static function clearUser(){
        self::clearCookie('__XE_UVNAME_');
        self::clearCookie('__XE_UVID_');
    }
    /**
     * 验证用户是否登录
     * 
     * @return bool
     */
    public static function hasUserLogged(){
        $UVID = self::getUserID();
        return !is_null($UVID) ? TRUE : FALSE;
    }
}
/**vim:sw=4 et ts=4 **/
?>