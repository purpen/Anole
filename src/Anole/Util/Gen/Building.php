<?php
/**
 * 自动创建Model/Action
 * 
 * @author purpen
 * @version $Id$
 */
class Anole_Util_Gen_Building extends Anole_Object {
	
	protected $_table;
	protected $_tpl;
	protected $_model_tpl;
	protected $_root;
	protected $_data_root;
	
	protected $_module;
	protected $_base_class = 'Anole_ActiveRecord_Base';
	public function __construct(){}
	
	/**
	 * create new module and init relation directory
	 */
	public function genModule(){
		
		$name = $this->getModule();
		$src_root = $this->getRoot();
		$data_root = $this->getDataRoot();
		if(is_null($name) || is_null($src_root) || is_null($data_root)){
		    echo "Gen Module name or root dir is NULL!\n";
		    exit();
		}
		
		$module_name = Anole_Util_Inflector::classify(strtolower($name));
		$module_path = str_replace('_','/',$module_name);
		$module_mapping = strtolower($name);
		
		echo "build module code layout...\n";
		//mkdir dir
		Anole_Util_File::mk("$src_root/$module_path/Action");
		Anole_Util_File::mk("$src_root/$module_path/Model");
		Anole_Util_File::mk("$src_root/$module_path/Result");
	    Anole_Util_File::mk("$src_root/$module_path/Interceptor");
	    
	    echo "build module template directory...\n";
	    Anole_Util_File::mk("$data_root/templates/$module_mapping");
	    
	    
	    echo "Add module meta..\n";
	    $module_file = $data_root.'/config/modules.yml';
	    if(file_exists($module_file)){
	        $meta = Anole_Yaml_Spyc::YAMLLoad($module_file);	
	    }else{
	    	$meta = array();
	    }
	    $meta['all'][$module_name] = array('default_action'=>$module_name,'actived'=>1);
	    $content = Anole_Yaml_Spyc::YAMLDump($meta);
	    Anole_Util_File::writeFile($module_file,$content);
	    
	    echo "Add module mapping...\n";
	    $mapping_file = $data_root.'/config/mapping.yml';
	    if(file_exists($mapping_file)){
	    	$mapping = Anole_Yaml_Spyc::YAMLLoad($mapping_file);
	    }else{
	        $mapping = array();	
	    }
	    $mapping['all'][$module_mapping] = $module_name;
	    $content = Anole_Yaml_Spyc::YAMLDump($mapping);
	    Anole_Util_File::writeFile($mapping_file,$content);
	    
	    echo "Module:[$module_name] genreated!\n";
	}
	/**
	 * create action class
	 *
	 * @param string $action_class
	 * @param string $model_class
	 * @param string $base_class
	 */
	public function genAction($action_class,$model_class,$base_class='Anole_Dispatcher_Action_ModelDriven'){
	    $action_file =  str_replace(" ","/",ucwords(str_replace("_"," ",$action_class)));
        $action_file = $this->getRoot().'/'.$action_file.'.php';
        if(file_exists($action_file)){
            echo "Action Exists,and skip";  
        }
		$content = Anole_Util_File::readFile($this->getTpl());
	    if(!empty($content)){
	        $content = str_replace('@class_name@', $action_class,$content);
	        $content = str_replace('@base_class@', $base_class,$content);
	        $content = str_replace('@model_class@', $model_class,$content);
	        
	        Anole_Util_File::writeFile($action_file,$content);
	    }else{
	        echo "Action Class: $action_class,Create failed!";	
	    }
	}
	/**
	 * create model and model_table class
	 */
	public function genModel(){
		$model_class = Anole_Util_Inflector::camelize($this->getTable());
		$module = Anole_Util_Inflector::classify($this->getModule());
		
		$model_file = $this->getRoot().'/'.$module.'/Model/'.$model_class.'.php';
		$model_class_name = "${module}_Model_$model_class";
		
		$model_table_file = $this->getRoot().'/'.$module.'/Model/Table/'.$model_class.'.php';
		$model_table_class = "${module}_Model_Table_$model_class";
        //connect database
		$dba = Anole_Dba_Manager::getDefaultConnection();
		$fields = $dba->getFieldMetaList($this->_table);
		$fields_attributes = var_export($fields,true);
		foreach($fields as $field){
            $info['name'] = $field['name'];
            $type = strtoupper($field['type']);
            switch ($type){
                case 'T':
                case 'D':
                    $info['type'] = 'date';
                    break;
                case 'N':
                    $info['type'] = 'integer';
                    break;
                case 'S':
                    $info['type'] = 'string';
                    break;
                default:
                    $info['type'] = 'mixed';
            }
            $info['methodname'] = Anole_Util_Inflector::camelize($field['name']);
            $fieldsInfo[] = $info;
        }
        $smarty = Anole_Util_Smarty::factory();
        $smarty->initRuntimeDirectory();
        //create model_table class
        $smarty->assign('fields',$fieldsInfo);
        $smarty->assign('fields_dump',$fields_attributes);
        $smarty->assign('class_name',$model_table_class);
        $smarty->assign('model_class',$model_class);
        $smarty->assign('base_class',$this->_base_class);
        $smarty->assign('table',$this->_table);
        
        $content = $smarty->fetch($this->_tpl);
        $this->saveClassSource($model_table_file,$content);
        
		//create model class
        if(file_exists($model_file)){
            return;	
        }
        $smarty->assign('class_name',$model_class_name);
        $smarty->assign('base_class',$model_table_class);
        
        $content = $smarty->fetch($this->_model_tpl);
        $this->saveClassSource($model_file,$content);
	}
	/**
	 * save class content
	 *
	 * @param string $file
	 * @param string $content
	 */
	private function saveClassSource($file,$content){
		$content= str_replace('<<','{',$content);
        $content= str_replace('>>','}',$content);
        
        Anole_Util_File::writeFile($file,$content);
	}
	
	public function setTable($table){
		$this->_table = $table;
		return $this;
	}
	public function getTable(){
		return $this->_table;
	}
	
	public function setTpl($tpl){
		$this->_tpl = $tpl;
		return $this;
	}
	public function getTpl(){
		return $this->_tpl;
	}
	public function setRoot($root){
		$this->_root = $root;
		return $this;
	}
	public function getRoot(){
		return $this->_root;
	}
	public function setModule($module){
		$this->_module = $module;
		return $this;
	}
	public function getModule(){
		return $this->_module;
	}
	public function setModelTpl($tpl){
	   $this->_model_tpl = $tpl;
	   return $this;
	}
	public function getModelTpl(){
	   return $this->_model_tpl;	
	}
	public function setDataRoot($root){
	    $this->_data_root = $root;
	    return $this;	
	}
	public function getDataRoot(){
	    return $this->_data_root;	
	}
}
/**vim:sw=4 et ts=4 **/
?>