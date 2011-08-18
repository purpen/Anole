<?php
require_once './Object.php';
class struct implements Object{
	private $_name = null;
	
	public function __construct(){
		$this->_name = 'hello';
	}
	
	public function change($name=null){
		$this->_name = $name;
	}
	
	public function getName(){
		return $this->_name;
	}
	/**
	 * 终止调用此类的时候
	 * 调用其析构函数
	 */
	public function __destruct(){
		print "bye,".$this->_name."\n";
	}
	
	public function error(){
		
	}
	
	public function info(){
		
	}
	
	public function __autoload($class_name){
		require_once ($class_name.".php");
	}
}

$st = new struct();
$st->change('ok');
//unset($st);
$st2 = $st;
$st2->change('no');
$st3 = clone $st2;
$st3->change('yes');
echo "st3: ".$st3->getName()."\n";
echo "st2: ".$st2->getName()."\n";
echo "st: ".$st->getName()."\n";
?>