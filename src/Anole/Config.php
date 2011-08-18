<?php
/**
 * 从配置文件中加载配置参数
 *
 * @author purpen
 * @version $id$
 */
class Anole_Config {
	/**
	 * all configs var
	 *
	 * @var array
	 */
    protected static $_configs = array();
    /**
     * load all config file
     *
     * @param string $filename
     */
    public static function load($filename){
        $config_file = self::$_configs['Anole.config_dir'].'/'.$filename.'.yml';
        if(!file_exists($config_file)){
            return;
        }
        $yml = new Anole_Yaml_Spyc();
        $settings = $yml->load($config_file);
        if(empty($settings)){
        	return;
        }
        $parent = basename($config_file,'.yml');
        $env_settings = array();
        if(isset($settings['all'])){
        	$env_settings = $settings['all'];
        }
        foreach($env_settings as $key=>$value){
        	self::$_configs["$parent.$key"] = $value;
        }
    }
    /**
     * return config array
     *
     * @return array
     */
    public static function all(){
        return self::$_configs;
    }
    /**
     * get the value of config name
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public static function get($name, $default=null){
        return isset(self::$_configs[$name]) ? self::$_configs[$name] : $default;
    }
    /**
     * set the value into config
     *
     * @param string $name
     * @param mixed $value
     */
    public static function set($name,$value){
    	self::$_configs[$name] = $value;
    }
    /**
     * add config value array into $_configs
     *
     * @param array $options
     */
    public static function add($options=array()){
        self::$_configs = array_merge(self::$_configs,$options);
    }
}
/**vim:sw=4 et ts=4 **/
?>