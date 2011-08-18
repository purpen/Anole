<?php
include3rd('phpmailer/class.phpmailer.php');
/**
 * 系统邮件发送系统工具
 * 
 * @author purpen
 * @version $Id$
 */
class Anole_Util_Mail extends Anole_Object {
    /**
     * 发送邮件
     * 
     * @param mixed $to array|string 收件人地址
     * @param string $subject 主题
     * @param string $body 邮件正文
     * @param string $from 发信人地址
     * @param string $fromName 发信人名称
     * @param string $charset 字符集
     */
    public static function send($to,$subject,$body,$from=null,$fromName=null,$charset='utf-8'){
        $mailer = new PHPMailer();
        $mailer->Host = Anole_Config::get('mail.host');
        //$mailer->Mailer = 'smtp';
        $mailer->SMTPDebug = 2;
        $mailer->IsHTML(true);
        $smtp_auth = Anole_Config::get('mail.smtp_auth');
        if($smtp_auth){
            $mailer->SMTPAuth = true;
            $mailer->Username = Anole_Config::get('mail.user');
            $mailer->Password = Anole_Config::get('mail.password');
        }else{
            $mailer->SMTPAuth = false;
        }
        $mailer->CharSet = $charset;
        if(is_null($from)){
            $from = Anole_Config::get('mail.from');
        }
        if(is_null($fromName)){
            $fromName = Anole_Config::get('mail.fromName');
        }
        $mailer->From = $from;
        $mailer->FromName = $fromName;
        
        $mailer->Subject = $subject;
        
        if(!is_array($to)){
            $to = array($to);
        }
        
        foreach($to as $address){
            $mailer->AddAddress($address);
        }
        $mailer->Body = $body;
        self::debug("host:".$mailer->Host.' username:'.$mailer->Username, __CLASS__);
        
        $ok = $mailer->Send();
        if(!$ok){
            self::error("send mail error:".$mailer->ErrorInfo,__CLASS__);
        }
        
        unset($mailer);
        
        return $ok;
    }
}
/**vim:sw=4 et ts=4 **/
?>