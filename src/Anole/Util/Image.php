<?php
/**
 * 图像缩放处理函数
 * 
 * @author purpen
 * @version $Id$
 */
class Anole_Util_Image extends Anole_Object {
    /**
     * 水印文字
     * 
     * @var string
     */
    public static  $mStr = "InStyles.Com.Cn";
    /**
     * 字体
     * 
     * @var string
     */
    public  static $mFont = "simsun.ttc";
    /**
     * 字体大小
     * 
     * @var string
     */
    public  static $mFontSize = "30";
    /**
     * 字体颜色
     * 
     * @var string
     */
    public static $mFontColor = "#000000" ;
    /**
     * 字间距
     * 
     * @var int
     */
    public  static $mFontWidth = 125;
    /**
     * 字体透明度
     * 
     * @var float
     */
    public static $mFontAlpha = 0.3;
    /**
     * 字体对齐方式
     * 
     * @var string
     */
    public static $mFontAlign = MW_SouthEastGravity;
    /**
     * 水印图片
     * 
     * @var string
     */
    public static $mMarkImage = "/opt/project/greare/tools/instyles_logo.png";
    
    public static $mImg ;

    /**
     * 创建magicwand对象
     *
     * @return Magickwand Object
     */
    public static function CreateMagick(){
        try{
            if(!is_resource(self::$mImg)){
                self::$mImg =  NewMagickWand();
            }
        }catch (Exception $e){
            self::warn(" create object........1 ".$e->getMessage(), __METHOD__);
        }
        return self::$mImg;
    }
    
    /**
     * 切图 缩略图函数
     *
     * @param string $pSrcFile 原始图片
     * @param string $pWidth   目标宽度
     * @param string $pHeight  目标高度
     * @param string $pThumpFile 目标文件
     * @param 切图类型 $pType  1:无切图缩图   2:切图缩图   3:固定宽度缩图   4：固定高度缩图  5：切图
     * 
     * @return Magickwand object
     */
    public static function ReduceImage($pSrcFile, $pWidth, $pHeight, $pType=1){
        
        $img = self::CreateMagick();
        
        if(!MagickReadImage($img,$pSrcFile)){
            return false;
        }
        $oriHeight = MagickGetImageHeight($img);
        $oriWidth = MagickGetImageWidth($img);
        if($oriWidth < $pWidth && $oriHeight < $pHeight){
        	self::warn("原图尺寸小于缩略图尺寸!", __METHOD__);
            return false;
        }
        try{
            if($pType == 1){//直接按照原图比例缩小
                $xratio = $pWidth/$oriWidth;
                $yratio = $pHeight/$oriHeight;
                if($xratio < $yratio) {
                    $pHeight = floor($oriHeight*$xratio);
                }
                else{
                    $pWidth = floor($oriWidth*$yratio);
                }
            }elseif($pType == 3){//按照宽度来缩图
                $Ratio   = $pWidth/$oriWidth;
                $pHeight = floor($oriHeight*$Ratio);
            }elseif($pType == 4){//按照高度来缩图
                $Ratio  = $pHeight/$oriHeight;
                $pWidth = floor($oriWidth*$Ratio);
            }elseif($pType == 2){//按照目标比例切图后再缩小
                if(($oriHeight/$pHeight) > ($oriWidth/$pWidth)){
                    $t = $oriWidth/$pWidth;
                }else{
                    $t = $oriHeight/$pHeight;
                }
                $_heght = $pHeight*$t;
                $_width = $pWidth*$t;
                $srX    = ceil($oriWidth/2-$_width/2); //copy开始的x坐标，单位是像素 (pixel)
                if($srX < 0){
                	$srX = 0;
                }
                $srY    = ceil($oriHeight/2-$_heght/2); //copy开始的y坐标，单位是像素 (pixel)
                if($srY < 0){
                	$srY = 0;
                }
                MagickCropImage($img,$_width, $_heght, $srX,$srY);
                
                $nimg = NewDrawingWand();
                DrawComposite($nimg, MW_AddCompositeOp, 0, 0, $pWidth, $pHeight, $img);
                
                $res  = NewMagickWand();
                MagickNewImage($res, $pWidth, $pHeight) ;
                MagickDrawImage($res, $nimg);
                MagickSetImageFormat($res, MagickGetImageFormat($img));
                
                self::$mImg = $res;
                
                return self::$mImg;
            }elseif($pType == 5) { //直接切图
                $srX    = ceil($oriWidth/2-$pHeight/2); //copy开始的x坐标，单位是像素 (pixel)
                if($srX < 0) {
                	$srX = 0;
                }
                $srY    = ceil($oriHeight/2-$pWidth/2); //copy开始的y坐标，单位是像素 (pixel)
                if($srY < 0) {
                	$srY = 0;
                }
                
                MagickCropImage($img,$pWidth, $pHeight, $srX,$srY);
                
                $nimg = NewDrawingWand();
                DrawComposite($nimg, MW_AddCompositeOp, 0, 0, $pWidth, $pHeight, $img);
                
                $res = NewMagickWand();
                MagickNewImage($res, $pWidth, $pHeight) ;
                MagickDrawImage($res, $nimg);
                MagickSetImageFormat($res, MagickGetImageFormat($img));
                
                self::$mImg = $res;
                
                return self::$mImg;
            }else{
                return false;
            }

            //去掉颜色配置等注释信息
            MagickRemoveImageProfiles($img);
            if($pType != 5){
                MagickResizeImage($img,$pWidth,$pHeight,MW_SincFilter ,1);
            }

            self::$mImg = $img;
            
            return self::$mImg;
        }catch (Exception $e){
            self::warn("reduce image failed".$e->getMessage(), __METHOD__);
        }
    }

    /**
     * 给图片添加水印效果
     *
     * @param string $pSrcFile 原始图片
     * @param int    $pMark
     * 
     * @return Magickwand object
     */
    public static function WriteMark($pSrcFile,$pMark=1){
        $img = self::CreateMagick();
        
        if($pMark == 1){ //添加图片水印效果
            $pMarkImage = self::$mMarkImage;
            $img_temp =  NewMagickWand();
            if(!MagickReadImage($img_temp,$pMarkImage)){
            	self::warn("Mark image isn't readable!", __METHOD__);
                return false;
            }
            
            $oriHeight = MagickGetImageHeight($img_temp);
            $oriWidth = MagickGetImageWidth($img_temp);

            $h = MagickGetImageHeight($img);
            $w = MagickGetImageWidth($img);

            $x =  $w-$oriWidth;
            $y =  $h-$oriHeight;

            MagickCompositeImage($img, $img_temp, MW_AtopCompositeOp, $x, $y);
            
        }else{ //添加文字水印
            $ndw = NewDrawingWand();
            $fontColor = NewPixelWand();

            $textEn = iconv("gb2312", "utf-8", self::$mStr);        //如果你传入的是非UTF8中文，这里要转换
            DrawSetTextEncoding($ndw, "UTF-8");        //设定图像上文字的编码
            DrawSetFont($ndw, self::$mFont);
            DrawSetFontWeight($ndw, self::$mFontWidth);        //设定字宽
            DrawSetFillColor($ndw, self::$mFontColor);      //设定字体颜色
            DrawSetFontSize($ndw, self::$mFontSize);        //设定字体大小
            DrawSetGravity($ndw, self::$mFontAlign);        //设定对齐方式
            DrawSetFillAlpha($ndw, self::$mFontAlpha);      //设置文字透明度
            
            MagickAnnotateImage($img, $ndw, 0, 0, 0, $textEn);
            
            ClearPixelWand($fontColor);
            ClearDrawingWand($ndw);
            DestroyPixelWand($fontColor);
            DestroyDrawingWand($ndw);
        }
        self::$mImg = $img;
        return self::$mImg;
    }

    /**
     * 输出图片
     *
     * @param string $pThumpFile
     * @param int $pOut  1:直接输出图片  2:输出图片内容
     * 
     * @return bool or Blob
     */
    public static function ShowPic($pOut=1, $pThumpFile){
        try{
            if($pOut == 1){
                MagickSetImageFormat(self::$mImg,'image/png');
                $ok = MagickWriteImage(self::$mImg, $pThumpFile);
                
                DestroyMagickWand(self::$mImg);
                
                return $ok;
            }else{
                MagickSetImageFormat(self::$mImg,'image/png');
                $ok = MagickGetImageBlob(self::$mImg);
                
                DestroyMagickWand(self::$mImg);
                
                self::$mImg = null;
                
                return $ok;
            }
        }catch (Exception $e){
            self::warn("show picture failed:".$e->getMessage(), __METHOD__);
        }
    }
    /**
     * 缩略图并判断是否添加水印
     *
     * @param  string $pSrcFile 原文件
     * @param  int $pWidth     目标宽度
     * @param  int $pHeight    目标高度
     * @param  string $pThumpFile 缩略图文件
     * @param  int $pType      1:无切图缩图   2:切图缩图   3:   切图   
     * @param  int $pOut       1:生成缩略图   2:返回缩略图数据
     * @param  int $pMark      0:不加水印 1:图片水印  2:文字水印
     * 
     * @return bool or Blob
     */
    public static function makeThumb($pSrcFile, $pWidth, $pHeight, $pThumpFile, $pType=1, $pOut=1, $pMark=0){
        if(!function_exists('NewMagickWand')){
            self::warn('NewMagickWand not undefined!', __METHOD__);
            return null;
        }
    	self::ReduceImage($pSrcFile,$pWidth,$pHeight, $pType, $pOut);
        if($pMark != 0){
            self::WriteMark($pSrcFile, $pMark);
        }       
        return self::ShowPic($pOut, $pThumpFile);
    }
    
    /**
     * 给图片加水印
     *
     * @param string  原图片  
     * @param int $pMark 1:图片水印 2:文字水印
     * @param int $pOut  1:直接输出图片  2:输出图片内容
     * 
     * @return bool or Blob
     */
    public static function makeMark($pSrcFile, $pMark=1, $pThumpFile='', $pOut=1){
        self::WriteMark($pSrcFile, $pMark);

        if(empty($pThumpFile)) {
        	$pThumpFile = $pSrcFile;
        }
        
        return self::ShowPic($pOut, $pThumpFile);
    }
    
    /**
     * 添加图片水印
     * 
     * @return bool
     */
    public static function drawPicture($pSrcFile,$pThumpFile='',$pOut=1,$water_image=null){
    	$magick_wand = self::CreateMagick();
        
        if(!MagickReadImage($magick_wand,$pSrcFile)){
            return false;
        }
        
    	if(empty($water_image)){
    		self::warn("Mark image is null!", __METHOD__);
    		return false;
    	}
    	
    	$img_temp =  NewMagickWand();
    	
        if(!MagickReadImage($img_temp,$water_image)){
            self::warn("Mark image isn't readable!", __METHOD__);
            return false;
        }
            
        $oriHeight = MagickGetImageHeight($img_temp);
        $oriWidth = MagickGetImageWidth($img_temp);

        $h = MagickGetImageHeight($magick_wand);
        $w = MagickGetImageWidth($magick_wand);
            
        $x =  $w-$oriWidth-30;
        $y =  $h-$oriHeight-30;

        MagickCompositeImage($magick_wand, $img_temp, MW_AtopCompositeOp, $x, $y);
        
        self::$mImg = $magick_wand;
        
        if(empty($pThumpFile)){
            $pThumpFile = $pSrcFile;
        }
        
        return self::ShowPic($pOut,$pThumpFile);
    }
    /**
     * 添加文字水印
     * 
     * @param $pSrcFile
     * @param $pThumpFile
     * @param $pOut
     * 
     * @return bool
     */
    public static function drawText($pSrcFile,$pThumpFile='',$pOut=1){
        $magick_wand = self::CreateMagick();
        
        if(!MagickReadImage($magick_wand,$pSrcFile)){
            return false;
        }
        
        $drawing_wand = NewDrawingWand();
        
        DrawSetTextEncoding($drawing_wand, "UTF-8");        //设定图像上文字的编码
        DrawSetFont($drawing_wand, self::$mFont);
        DrawSetFontWeight($drawing_wand, self::$mFontWidth);        //设定字宽
        DrawSetFillColor($drawing_wand, self::$mFontColor);      //设定字体颜色
        DrawSetFontSize($drawing_wand, self::$mFontSize);        //设定字体大小
        DrawSetGravity($drawing_wand, self::$mFontAlign);        //设定对齐方式
        DrawSetFillAlpha($drawing_wand, self::$mFontAlpha);      //设置文字透明度
        
        $h = MagickGetImageHeight($magick_wand);
        $w = MagickGetImageWidth($magick_wand);
            
        $x =  $w/2;
        $y =  $h/2;
        
        MagickAnnotateImage($magick_wand,$drawing_wand,$x,$y,0,self::$mStr);
        
        self::$mImg = $magick_wand;
        
        return self::ShowPic($pOut,$pThumpFile);
    }
}
/**vim:sw=4 et ts=4 **/
?>