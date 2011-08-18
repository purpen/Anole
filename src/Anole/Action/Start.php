<?php
class Anole_Action_Start extends Anole_Dispatcher_Action_ModelDriven {
	
	protected $_id = null;
	
	public function execute(){
		return $this->index();
	}
	
	public function index(){
		
		$people = array(
		  'name'=>'xiao san',
		  'age'=>20,
		  'sex'=>'m'
		);
		$id = $this->getId();
		$this->putContext('title', 'This is a title.');
		$this->putContext('people',$people);
		$this->putContext('id',$id);
		return $this->smartyResult('anole.test');
	}
	/**
	 * test redirect
	 *
	 * @return string
	 */
	public function go(){
		$url = 'http://www.jaever.com';
		return $this->redirectResult($url);
	}
	
	public function raw(){
		$data = implode(' : ',array('hello','world','raw','anole'));
		return $this->rawResult($data);
	}
	
	public function getId(){
		return $this->_id;
	}
	public function setId($id){
		$this->_id = $id;
		return $this;
	}
	
}
/**vim:sw=4 et ts=4 **/
?>