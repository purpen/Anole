<?php
class Anole_Util_Inflector {
	
    /**
     * 将英文单词转换为camel case形式(LikeThis)
     *
     * @param string $lower_case_and_underscored_word
     * @return string
     */
    public static function camelize($lower_case_and_underscored_word) {
        return str_replace(" ","",ucwords(str_replace("_"," ",$lower_case_and_underscored_word)));
    }
    /**
     * format class style
     *
     * @param string $class
     * @return string
     */
    public static function classify($class){
         return str_replace(" ","_",ucwords(str_replace("_"," ",$class)));
    }
    
    /**
      * convert to Java style camlize method,like:
      * get_method
      * to
      * getMethod
      *
      * @param string $method
      * @return string
      */
     public static function methodlize($method){
         $method = self::camelize($method);
         $method[0] = strtolower($method[0]);
         return $method;
     }
	
}
?>