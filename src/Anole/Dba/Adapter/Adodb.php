<?php
include3rd('adodb/adodb.inc.php');
/**
 * ADODb Database Adapter
 *
 * @package ActiveRecord
 */
class Anole_Dba_Adapter_Adodb extends Anole_Dba_Adapter_Abstract {
	/**
	 * AdoConnection
	 *
	 * @var AdoConnection
	 */
	protected $_adodb = null;
	
	protected function doConnect(){
		//remove adodb://
		$dsn = substr($this->_dsn,8);
		
		$this->_adodb = &NewADOConnection($dsn);
		$this->_adodb->SetFetchMode(ADODB_FETCH_ASSOC);
		$this->_adodb->autoRollback = true;
		
		$ok = $this->_adodb->Connect();
		//set charset
		if($ok && ($this->_adodb->databaseType=='mysql' || $this->_adodb->databaseType=='mysqli')){
			$ok_c = $this->_adodb->Execute('SET NAMES '.$this->_adodb->charSet);
			if(!$ok_c){
				throw new Anole_Dba_Exception("Cant SET NAMES to ".$this->_adodb->charSet);
			}
		}
		return $ok;
		
	}
	protected function doClose(){
		$this->_adodb->Close();
		$this->_connected = false;
	}
	/**
	 * 返回有结果集的查询
	 *
	 * @param string $sql
	 * @param int $size
	 * @param int $page
	 * @param array $vars
	 * @return array
	 */
	public function query($sql,$size=-1,$page=1,$vars=array()){
	    if(!$this->connect()){
            throw new Anole_Dba_Exception('Database connect failed');
        }
        if($size > 0){
        	if($page >= 1){
        		$offset = ($page-1)*$size;
        	}else{
        		$offset = -1;
        	}
        }else{
        	$page = -1;
        	$size = -1;
        }
        if($size > 0){
        	$sql .= ' LIMIT '.$size;
        	if($offset > 0){
        		$sql .= ' OFFSET '.$offset;
        	}
        }
        $rs = $this->_adodb->Query($sql,$vars);
	    if($rs === false){
            throw new Anole_Dba_Exception('Database Error:['.$this->_adodb->ErrorMsg().'] Cause SQL:'.$sql);
        }
        return $rs->GetRows();
	}
	/**
	 * 返回无结果集的查询
	 *
	 * @param string $sql
	 * @param array $vars
	 * @return bool
	 */
	public function execute($sql,$vars=array()){
		if(!$this->connect()){
			throw new Anole_Dba_Exception('Database connect failed');
		}
		$ok = $this->_adodb->Execute($sql,$vars);
		if($ok === false){
			throw new Anole_Dba_Exception('Database Error:['.$this->_adodb->ErrorMsg().'] Cause SQL:'.$sql);
		}
		return true;
	}
	/**
	 * get all tables of the database
	 *
	 * @return array
	 */
	public function getTableList(){
		if(!$this->connect()){
			return array();
		}
		return $this->_adodb->MetaTables('TABLES');
	}
	/**
	 * backwords compatibillity
	 *
	 * @return array
	 */
	public function tables(){
		return $this->getTableList();
	}
	/**
	 * get fields of the table
	 *
	 * @param string $table
	 * @return array
	 */
	public function getFieldMetaList($table){
		if(!$this->connect()){
			return array();
		}
		$fields_obj = $this->_adodb->MetaColumns($table);
		if(!$fields_obj){
			throw new Anole_Dba_Exception("Table:$table no fields.Error:".$this->_adodb->ErrorMsg());
		}
		$fields = array();
		foreach($fields_obj as $f){
			$fields[$f->name] = array('name'=>$f->name,'type'=>self::convertFieldType($f->type),'length'=>$f->max_length);
		}
		return $fields;
	}
	/**
	 * backwords compatibillity
	 *
	 * @param string $table
	 */
	public function fields($table){
		return $this->getFieldMetaList($table);
	}
	private static function convertFieldType($type){
	   switch($type){
            case 'varchar':
            case 'char':
            case 'text':
                return 'S';
            case 'date':
                return 'D';
            case 'datetime':
                return 'T';
            case 'time':
            case 'int':
            case 'float':
            case 'long':
                return 'N';
            default:
                return 'S';
        }
	}
	/**
	 * get primary id value by the hand
	 *
	 * @param string $name
	 * @return int
	 */
	public function genSeq($name){
		$name = 'SEQ_'.strtoupper($name);
		if(!$this->connect()){
			throw new Anole_Dba_Exception('Connection failed.Error:'.$this->_adodb->ErrorMsg());
		}
		$v_id = $this->_adodb->GenID($name,1);
		if($v_id === false){
			throw new Anole_Dba_Exception('Get gen id error:'.$this->_adodb->ErrorMsg());
		}
		return $v_id;
	}
	/**
	 * drop seq table
	 *
	 * @param string $name
	 * @return bool
	 */
	public function dropSeq($name){
		$name = 'SEQ_'.strtoupper($name);
		return $this->execute('DROP TABLE IF EXISTS '.$name);
	}
}
/**vim:sw=4 et ts=4 **/
?>