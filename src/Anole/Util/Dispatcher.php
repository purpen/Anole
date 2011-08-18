<?php
/**
 * Dispatcher 工具类
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Util_Dispatcher extends Anole_Object {
	
	/**
	 * 向Action里装载request参数
	 *
	 * @param object $obj
	 * @param array $parameters
	 */
	public static function applyParams($obj,$parameters){
		foreach($parameters as $name=>$value){
			$method = 'set'.Anole_Util_Inflector::camelize($name);
			if(!method_exists($obj,$method)){
				self::debug("Skip parameter:$name",__METHOD__);
                continue;
			}else{
				$ok = self::_acceptableName($name);
				if($ok){
					self::debug("Set Parameter:$name [$value] accessor method[$method].",__METHOD__);
					$obj->$method($value);
				}
			}
		}
	}
	
     /**
     * 检测是否存在某方法
     *
     * @param string $name
     * @return boolean
     */
    protected static function _acceptableName($name){
        return !(substr($name,0,1) == '_');
    }
	/**
	 * 重新将invocationcontext的参数装配到object中(model或者action)
	 *
	 * @param Anole_Dispatcher_Context $context
	 * @param object $obj
	 */
    public static function applyDispatcherContextParams($context,$obj){
    	$parameters = $context->getRequest()->getParams();
    	if(empty($parameters)){
    		self::debug("This context has not any parameters, skip.",__METHOD__);
    		return;
    	}
    	self::applyParams($obj,$parameters);
    }
}
/**vim:sw=4 et ts=4 **/
?>