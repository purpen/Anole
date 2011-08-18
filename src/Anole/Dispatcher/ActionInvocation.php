<?php
/**
 * default action interface
 *
 * @version $Id$
 * @author purpen
 */
class Anole_Dispatcher_ActionInvocation extends Anole_Object{
	
	protected $_invocation_context;
	/**
     * Interceptors inteceptor
     *
     * @var ArrayInterceptor
     */
    protected $_interceptors;
	
	protected $_execute_result = true;
    protected $_executed = false;
    /**
     *
     * @var Doggy_Dispatcher_Result
     */
    protected $_result;
	protected $_result_code;
	private $_action_class;
	private $_action;
	private $_method;
	
	public function __construct(Anole_Dispatcher_Context $context,$action_class,$method){
		$this->_invocation_context = $context;
		$this->_action_class = $action_class;
		$this->_method = $method;
		
		$this->init();
	}
	/**
	 * initize
	 */
	private function init(){
		//interceptors
		$list = Anole_Config::get('interceptors.run');
	    if(is_null($list)){
            $list = array();
        }
		if(!is_array($list)){
			throw new Anole_Dispatcher_Exception("Invalid interceptors.run should is array.");
		}
		$this->buildInteceptors($list);
	}
	/**
	 * build all intecepators into ArrayIterator
	 *
	 * @param unknown_type $class
	 */
	protected function buildInteceptors($class){
		if(!is_array($class)){
			$class = (Array) $class;
		}
		$result = array();
		for($i=0;$i<count($class);$i++){
			$cls = $class[$i];
			if(!class_exists($cls,true)){
				throw new Anole_Dispatcher_Exception("Class[$cls] not found!");
			}
			$itx = new $cls();
			if(!$itx instanceof Anole_Dispatcher_Interceptor){
				throw new Anole_Dispatcher_Exception("Class is not an interceptor:".$cls);
			}
			$itx->init();
			$result[] = $itx;
		}
		$itxs = new ArrayObject($result);
		$this->_interceptors = $itxs->getIterator();
		$this->_interceptors->rewind();
	}
	
	/**
	 * ActionInvocation execute
	 *
	 * @return string
	 */
	public function invoke(){
		if($this->_executed){
			throw new Anole_Dispatcher_Exception("Illegal state,Action has been executed!");
		}
		self::debug("inteceptor is validate!\n", __METHOD__);
		//first run all inteceptors
		if($this->_interceptors->valid()){
			$itx = $this->_interceptors->current();
			//next interceptor
			$this->_interceptors->next();
			//execute interceptor
			$this->_result_code = $itx->intercept($this);
			return null;
		}else{
			self::debug("inteceptor ok,start to execute action...", __METHOD__);
			//已经执行完全部的inteceptor,准备运行action本身
			$this->_result_code = $this->invokeActionOnly();
			self::debug("action ok,result code:".$this->_result_code, __METHOD__);
		}
		//inteceptor递归的出口点
		if(!$this->isExecuted()){
			if($this->getExecuteResult()){
				$this->executeResult();
			}
			$this->setExecuted(true);
		}
		
		return $this->_result_code;
	}
	
	public function invokeActionOnly(){
		$action = $this->getAction();
		$method = $this->getMethod();
		return $action->{$method}();
	}
	/**
	 * create an action to execute
	 */
	protected function _createAction(){
		if(class_exists($this->_action_class)){
		    $this->_action = new $this->_action_class();
		}else{
		    throw new Anole_Dispatcher_Exception('Action class:'.$this->_action_class.' not found!');
		}
		if(!method_exists($this->_action,$this->_method)){
		    $this->_method = 'execute';
		}
	}
	
	private function executeResult(){
		$result = $this->getResult();
		if(is_null($result)){
			$code = $this->getResultCode();
			if($code == Anole_Dispatcher_Constant_Action::NONE){
				return;
			}
			$result_class = Anole_Config::get('results.'.$code);
			self::debug('result class is '.$result_class, __METHOD__);
			if(is_null($result_class) || !class_exists($result_class)){
				self::warn('result class:'.$result_class.' not found!',__METHOD__);
				throw new Anole_Dispatcher_Exception('result class:'.$result_class.' not found!');
			}
			
			$result = new $result_class();
			if(!$result instanceof Anole_Dispatcher_Result){
				self::warn($result_class.' not match Anole_Dispatcher_Result!',__METHOD__);
				throw new Anole_Dispatcher_Exception($result_class.' not match Anole_Dispatcher_Result!');
			}
			$this->setResult($result);
		}
		$this->_result->execute($this);
	}
	
	public function setAction(Anole_Dispatcher_Action $action){
		$this->_action = $action;
		return $this;
	}
	/**
	 * current execute action
	 *
	 * @return Anole_Dispatcher_Action
	 */
	public function getAction(){
		if(is_null($this->_action)){
			$this->_createAction();
		}
		return $this->_action;
	}
	
	public function setMethod($method){
		$this->_method = $method;
		return $this;
	}
	/**
	 * get action class name
	 *
	 * @return string
	 */
	public function getActionClass(){
		return $this->_action_class;
	}
	
	public function setActionClass($class){
		$this->_action_class = $class;
		return $this;
	}
	
	public function getMethod(){
		return $this->_method;
	}
	
	public function setResult($v){
		$this->_result = $v;
		return $this;
	}
	/**
	 * get result object
	 *
	 * @return Doggy_Dispatcher_Result
	 */
	public function getResult(){
		return $this->_result;
	}
	
	/**
	 * return action result code
	 *
	 * @return string
	 */
    public function getResultCode(){
        return $this->_result_code;
    }
	public function setResultCode($c){
		$this->_result_code = $c;
		return $this;
	}
	/**
	 * return action context
	 *
	 * @return Anole_Dispatcher_Context
	 */
	public function getInvocationContext(){
		return $this->_invocation_context;
	}
	
	public function getExecuteResult(){
		return $this->_execute_result;
	}
	public function setExecuteResult($value){
		$this->_execute_result = $value;
		return $this;
	}
	/**
	 * return status of the action has been executed
	 *
	 * @return bool
	 */
	public function isExecuted(){
		return $this->_executed;
	}
	
	public function setExecuted($v){
		$this->_executed = $v;
		return $this;
	}
	
	
	
}
/**vim:sw=4 et ts=4 **/
?>