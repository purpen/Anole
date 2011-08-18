<?php
/**
 * 生产验证码类
 *
 * @author purpen
 * @version $Id$
 */
class Anole_Util_ValidateCode extends Anole_Object {
	private $width=80;
	private $height=20;
	private $codenum=null;
	//产生的验证码
	public $check_code;
	//验证码图片
	private $check_image;
	//干扰像素
	private $disturb_color;
	
	public function __construct($width=80,$height=20,$codenum=4){
		$this->width   = $width;
		$this->height  = $height;
		$this->codenum = $codenum;
	}
	/**
	 * 输出文件头
	 */
	private function outputFileHeader(){
		header("Content-type:image/png");
	}
	/**
	 * 产生验证码
	 */
	private function createCode(){
		$this->check_code = strtoupper(substr(md5(rand()),0,$this->codenum));
	}
	/**
	 * 生成图片
	 */
	public function createImage(){
		$this->check_image = @imagecreate($this->width,$this->height);
		$back = imagecolorallocate($this->check_image,255,255,255);
		$border = imagecolorallocate($this->check_image,100,100,100);
		
		imagefilledrectangle($this->check_image,0,0,$this->width-1,$this->height-1,$back);
		imagerectangle($this->check_image,0,0,$this->width-1,$this->height-1,$border);
	}
	/**
	 * 设置干扰像素
	 */
	private function setDisturbColor(){
		for($i=0;$i<=200;$i++){
			$this->disturb_color = imagecolorallocate($this->check_image,rand(0,255),rand(0,255),rand(0,255));
			imagesetpixel($this->check_image,rand(2,128),rand(2,128),$this->disturb_color);
		}
	}
	/**
	 * 图片上写验证码
	 */
	private function writeCheckCodeToImage(){
		for($i=0;$i<=$this->codenum;$i++){
			$bg_color = imagecolorallocate($this->check_image,rand(0,255), rand(0,128), rand(0,255));
			$x = floor($this->width/$this->codenum)*$i + 5; //5px偏移
			$y = rand(0,$this->height-15);
			imagechar($this->check_image,rand(5,8),$x,$y,$this->check_code[$i],$bg_color);
		}
	}
	/**
	 * 输出图片
	 */
	public function outputImage(){
		$this->outputFileHeader();
		$this->createCode();
		
		$this->createImage();
		$this->setDisturbColor();
		
		//往图片上写验证码
		$this->writeCheckCodeToImage();
		imagepng($this->check_image);
		imagedestroy($this->check_image);
	}
	
	public function __destruct(){
		unset($this->width,$this->height,$this->codenum);
	}
}

?>