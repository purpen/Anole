<?php
/**
 * 支付宝付款通知验证类
 * 
 * @author purpen
 * @version $Id$
 */
class Anole_Util_AlipayNotify extends Anole_Object {
    /**
     * 支付接口
     * 
     * @var string
     */
    public $gateway;
    /**
     * 合作伙伴ID
     * 
     * @var string
     */
    public $partner="2088101846726713";
    /**
     * 安全校验码
     * 
     * @var string
     */
    public $security_code="lb992frqum0th7il4yx4lp561jz5xayv";
    /**
     * 签名类型
     * 
     * @var string
     */
    public $sign_type="MD5";
    /**
     * 签名
     * 
     * @var string
     */
    public $mysign;
    
    public $_input_charset="utf-8";
    
    public $tansport="https";
    
    public function __construct($partner,$security_code,$sign_type="MD5",$_input_charset="GBK",$transport="https"){
        $this->partner = $partner;
        $this->security_code = $security_code;
        $this->sign_type = $sign_type;
        $this->mysign = "";
        $this->_input_charset = $_input_charset;
        $this->tansport = $transport;
        if($this->tansport == "https"){
            $this->gateway = "https://www.alipay.com/cooperate/gateway.do?";
        }else{
            $this->gateway = "http://notify.alipay.com/trade/notify_query.do?";
        }
    }
    /**
     * 对notify_url的认证
     * 
     * @return bool
     */
    public function notify_verify(){
        if($this->tansport == "https"){
            $veryfy_url = $this->gateway."service=notify_verify"."&partner=".$this->partner."&notify_id=".$_POST['notify_id'];
        }else{
            $veryfy_url = $this->gateway. "partner=".$this->partner."&notify_id=".$_POST["notify_id"];
        }
        $veryfy_result = $this->get_verify($veryfy_url);
        $post = $this->para_filter($_POST);
        $sort_post = $this->arg_sort($post);
        while(list($key, $val) = each($sort_post)){
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&号
        $prestr = substr($arg,0,count($arg)-2);
        $this->mysign = $this->sign($prestr.$this->security_code);
        if(eregi("true$",$veryfy_result) && $this->mysign == $_POST["sign"]){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 对return_url的认证
     * 
     * @return bool
     */
    public function return_verify(){
        $get_sign = $_GET['sign'];
        $params = $_GET;
        /*
        while(list($k,$v) = each($params)){
            self::debug("Get pair param[$k]=>[$v].",__METHOD__);
            $arg_get .= $k."=".$v."&";
        }*/
        $sort_get = $this->arg_sort($params);
        while(list($key, $val) = each($sort_get)) {
            self::debug("sort pair param[$key]=>[$val].",__METHOD__);
            if($key != "sign" && $key != "sign_type"){
                $arg .= $key."=".$val."&";
            }
        }
        self::debug("build arg[$arg].", __METHOD__);
        //去掉最后一个&号
        $prestr = substr($arg,0,count($arg)-2);
        $this->mysign = $this->sign($prestr.$this->security_code);
            
        self::debug("return_url_log=".$get_sign."&".$this->mysign."&".$this->charset_decode(implode(",",$params),$this->_input_charset));
            
        if($this->mysign == $get_sign){
            return true;
        }else{
            return false;
        }
    }
    
    public function get_verify($url,$time_out="60"){
        $urlarr = parse_url($url);
        
        $errno = "";
        $errstr = "";
        $transports = "";

        if($urlarr["scheme"] == "https"){
            $transports = "ssl://";
            $urlarr["port"] = "443";
        }else{
            $transports = "tcp://";
            $urlarr["port"] = "80";
        }
        $fp = @fsockopen($transports.$urlarr['host'],$urlarr['port'],$errno,$errstr,$time_out);
        if(!$fp){
            die("ERROR: $errno - $errstr<br />\n");
        }else{
            fputs($fp,"POST ".$urlarr["path"]." HTTP/1.1\r\n");
            fputs($fp,"Host: ".$urlarr["host"]."\r\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-length: ".strlen($urlarr["query"])."\r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $urlarr["query"] . "\r\n\r\n");
            while(!feof($fp)){
                $info[] = @fgets($fp,1024);
            }
            fclose($fp);
            $info = implode(',',$info);
            while(list($key,$val) = each($_POST)){
                $arg .= $key."=".$val."&";
            }
            
            self::debug("notify_url_log=".$url.$this->charset_decode($info,$this->_input_charset));
            self::debug("notify_url_log=".$this->charset_decode($arg,$this->_input_charset));
            
            return $info;
        }
    }
    /**
     * 除去数组中的空值和签名模式
     * 
     * @param array $parameter
     * @return array
     */
    public function para_filter($parameter){
        $para = array();
        while(list($key,$val) = each($parameter)){
            if($key == 'sign' || $key == 'sign_type' || $val == ''){
                continue;
            }else{
                $para[$key] = $parameter[$key];
            }
        }
        return $para;
    }
    /**
     * 数组排序
     * 
     * @param $array
     * @return array
     */
    public function arg_sort($array){
        ksort($array);
        reset($array);
        
        return $array;
    }
    /**
     * 获取加密文
     * 
     * @param string $prestr
     * @return string
     */
    public function sign($prestr){
        $mysign = "";
        if($this->sign_type == 'MD5'){
            $mysign = md5($prestr);
        }elseif($this->sign_type == 'DSA'){
            die("DSA 签名方法待后续开发，请先使用MD5签名方式");
        }else{
            die("支付宝暂不支持".$this->sign_type."类型的签名方式");
        }
        return $mysign;
    }
    /**
     * 实现多种字符编码方式
     * 
     * @param $input
     * @param $_output_charset
     * @param $_input_charset
     * @return string
     */

    public function charset_encode($input,$_output_charset,$_input_charset="GBK"){
        $output = "";
        if(!isset($_output_charset) ){
            $_output_charset  = $this->parameter['_input_charset '];
        }
        if($_input_charset == $_output_charset || $input ==null){
            $output = $input;
        }elseif(function_exists("mb_convert_encoding")){
            $output = mb_convert_encoding($input,$_output_charset,$_input_charset);
        }elseif(function_exists("iconv")){
            $output = iconv($_input_charset,$_output_charset,$input);
        }else{
            die("sorry, you have no libs support for charset change.");
        }

        return $output;
    }

    /**
     * 实现多种字符解码方式
     * 
     * @param $input
     * @param $_input_charset
     * @param $_output_charset
     * 
     * @return string
     */
    public function charset_decode($input,$_input_charset,$_output_charset="GBK"){
        $output = "";
        if(!isset($_input_charset)){
            $_input_charset = $this->_input_charset ;
        }
        if($_input_charset == $_output_charset || $input == null){ 
            $output = $input;
        }elseif(function_exists("mb_convert_encoding")){
            $output = mb_convert_encoding($input,$_output_charset,$_input_charset);
        }elseif(function_exists("iconv")){
            $output = iconv($_input_charset,$_output_charset,$input);
        }else{
            die("sorry, you have no libs support for charset changes.");
        }

        return $output;
    }
}
/**vim:sw=4 et ts=4 **/
?>