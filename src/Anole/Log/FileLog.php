<?php
/**
 * 输出日志接口类,根据不同的运行环境，设定不同的级别。
 * 
 * @version $Id$
 * @author purpen
 */
class Anole_Log_FileLog {
	
	const OFF_LEVEL = -1;
	const ERROR_LEVEL = 100;
	const WARN_LEVEL = 200;
	const DEBUG_LEVEL = 300;
	const ALL_LEVEL=1000;
	
	private $_log_level;
    
    private $_log_file;
    /**
     * 日志输出级别
     * 
     * @var array
     */
    private static $_level_names = array(
      'off'=>self::OFF_LEVEL,
      'error'=>self::ERROR_LEVEL,
      'warn'=>self::WARN_LEVEL,
      'debug'=>self::DEBUG_LEVEL,
      'all'=>self::ALL_LEVEL
    );
	
	public function __construct($options){
		$this->setOptions($options);
	}
	
	public function setOptions($options){
		if(isset($options['level'])){
			$level = strtolower($options['level']);
			$this->_log_level = isset(self::$_level_names[$level])?self::$_level_names[$level]:self::ALL_LEVEL;
		}
		if(isset($options['output'])){
			$this->_log_file = $options['output'];
		}
		return $this;
	}
	/**
	 * output message into log file
	 *
	 * @param string $type
	 * @param string $msg
	 * @param string $sendor
	 */
	protected function _output($type,$msg,$sendor){
		$bad = array("\n", "\r", "\t");
        $good = ' ';
        $content = str_replace($bad,$good,$msg);
        if(!is_null($this->_log_file)){
        	error_log(date('y-m-d H:i:s').' '.$type." $sendor - $content\n",3,$this->_log_file);
        }else{
        	error_log(date('y-m-d H:i:s').' '.$type." $sendor - $content\n");
        }
	}
	/**
	 * output debug message
	 *
	 * @param string $msg
	 * @param string $sendor
	 * @return Anole_Log_FileLog
	 */
	public function debug($msg,$sendor){
	    if(!$this->isDebugEnabled()){
	    	return;	
	    }
	    $this->_output('Debug',$msg,$sendor);
	    return $this;
	}
	/**
	 * output warn message
	 *
     * @param string $msg
     * @param string $sendor
     * @return Anole_Log_FileLog
	 */
	public function warn($msg,$sendor){
		if(!$this->isWarnEnabled()){
			return;
		}
		$this->_output('Warn',$msg,$sendor);
		return $this;
	}
	/**
     * output error message
     *
     * @param string $msg
     * @param string $sendor
     * @return Anole_Log_FileLog
     */
	public function error($msg,$sendor){
		if(!$this->isErrorEnabled()){
			return;
		}
		$this->_output('Error',$msg,$sendor);
		return $this;
	}
	/**
	 * check if can output error level message
	 *
	 * @return bool
	 */
	public function isErrorEnabled(){
	    return $this->_log_level >= self::ERROR_LEVEL;	
	}
	/**
	 * check if can output warn level message
	 *
	 * @return bool
	 */
	public function isWarnEnabled(){
		return $this->_log_level >= self::WARN_LEVEL; 
	}
	/**
	 * check if can output debug level message
	 *
	 * @return bool
	 */
    public function isDebugEnabled(){
        return $this->_log_level >= self::DEBUG_LEVEL;
    }
}
/**vim:sw=4 et ts=4 **/
?>