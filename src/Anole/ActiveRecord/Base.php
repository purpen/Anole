<?php
/**
 *  ActiveRecord 数据对象
 *  
 *  @author purpen
 *  @version $Id$
 */
class Anole_ActiveRecord_Base extends Anole_Object implements ArrayAccess,Iterator,Countable {
	
    /**
     * 关联类型定义
     */
    const HAS_ONE = 'has_one';
    const HAS_MANY = 'has_many';
    const BELONGS_TO = 'belongs_to';
    const HAS_AND_BELONGS_TO_MANY = 'has_and_belongs_to_many';
    /**
     * 定义需要自动删除关系类型
     * @var array
     */
    protected $AutoDeleteRelationType = array(
        self::HAS_MANY,
        self::HAS_ONE,
        self::HAS_AND_BELONGS_TO_MANY
    );
    
    /**
     * 定义需要自动保存的关系类型
     * @var array
     */
    protected $AutoSaveRelationType = array(
        self::HAS_MANY,
        self::HAS_ONE,
        self::HAS_AND_BELONGS_TO_MANY,
        self::BELONGS_TO
    );
    /**
     * Model关系映射表
     * 
     * 本数组用于定义对象的关系,继承类通过定义此数组来实现各个model之间的关系映射(ORM)。
     * 
     * 映射表的格式如下:
     * protected $RelationMap= array(
     *  //key为关系的名称
     *  'relation_key' =>array(
     * 
     *      //关系类型,分别是:HAS_MANY,HAS_ONE,BELONGS_TO,HAS_AND_BELONGS_TO_MANY
     *      'type'=>self::HAS_MANY,
     * 
     *      //要关联的其他model的类名
     *      'class'=>'', 
     *      //外键名,如果省略，则按照如下规则设置外键名:
     *      //  hashOne/hasMany: 当前model的表名.'_id'
     *      //  belongsTo: 关联的Model的表名_id
     *      'foreign_key'=>null,
     * 
     *      //多对多表关联时,中间关联表的表名,
     *      //默认是调用$this->getJoinTable方法来获得(按照2个表的字母顺序获得)                  
     *      join_table'=>null, 
     * 
     *      //多对多关系时使用，当前model在中间关联表中的外键名
     *      'this_foreign_key'=>null, 
     * 
     *      //多对多关系时使用,关联model在中间关联表中的外键名
     *      'other_foreign_key'=>null,
     *      //多对多关系时使用，关联表中需要附加的字段名列表
     *      //通常关联表中只有2个外键字段，如果存在其他字段可在此指定
     *      'habm_other_fields'=>array(),
     * 
     *      //是否和当前model存在依赖关系，如果是则当前model删除后级联删除这个关联
     *      'depend'=>true,
     * 
     *      //关系映射选项
     *      ///////////////////////////////////////
     *          //下面的选项和find函数的相同,如果你设置了这些参数，那么将应用
     *          //这些参数作为默认值来查找关联model的数据
     *          //////////////////////////////////////
     *      'options' => array(
     *          
     *          //附加的SQL查询的Where条件语句(不包括WHERE关键字)
     *          //在查找关联数据时，除了基本的关联条件外,还将附加这个条件
     *          'condition'=>null,
     *          //关联数据的排序,SQL的order语句，如 created_time DESC,name ASC,age ASC
     *          'order'=>null,
     *          //一个整数，表示要分页时每页的记录数,-1表示不分页，返回全部关联数据
     *          'size'=>-1,
     *          //一个整数，表示返回页的索引号，如果设置了limit，则此参数默认为1
     *          'page'=>1,
     *          //默认情况下,select * FROM table,如果你希望用具体的字段限定来替换*,那么可以指定字段列表,如 'name,age'
     *          'select'=>null,
     *          //SQL查询时需要附加的JOINS语句，比如"LEFT JOIN comments ON comments.post_id = id"
     *          'joins'=>null,
     *          //SQL GROUPBY条件
     *          'groupby'=null,
     *          //要传递的预编译参数数组，如果sql中使用了?这些占位符     
     *          vars=>array()           
     *      )   
     *  )
     * )
     * 
     * @var array
     */
    protected $_relation_map = array();

    /**
     * 是否启用延迟加载$RelationMap中的关联数据
     * 如果启用，那么当访问find result数据时，会自动加载关联的数据
     */
    protected $LazyLoadRelation = true;
    
    /**
     * 需要保存的关联model的数组
     * 
     * @var array
     */
    protected $_relation_models = array();
    
    /**
     * 是否启用magick字段
     * 如果启用，那么当访问find result数据时，会自动调用
     * _magic_field方法来
     */
    protected $LazyLoadMagicField = true;
    
    /**
     * 默认的magic field
     * 格式:
     * 
     * protected $MagicField = array(
     *  'field_name'=>'method'
     * );
     */
    protected $_magic_field = array();
    
    /**
     * 需要回滚的models
     */
    private $_rollback_models = array();
    
    /**
     * validate错误数组
     */
    private $_validate_errors = array();
    
    /**
     * Model使用的DBA对象
     *
     * @var Anole_Dba_Adapter
     */
    protected static $_dba = null;
    
    /**
     * Model表的主键名
     * @var string
     */
    protected $_primary_key = 'id';
    
    /**
     * 用于生成ID的Sequence Name
     *
     * @var string
     */
    protected $_sequence_name;
    /**
     * Model class name
     *
     * @var string
     */
    protected $_class_name;
    /**
     * Model class Table name
     */
    protected $_table_name = 'User';
    protected $_table_name_prefix='';
    protected $_table_name_suffix='';
    
    /**
     * ActiveRecord的属性数组
     *
     * @var array
     */
    protected $_attributes;
    /**
      * 当前是否为新记录，从未被保存到数据库中
      * 
      * @var boolean
      * @access private
      */
    private $_new = true;

     /**
      * 不论Model的primaryKey是否是Id，都可以使用id属性来获得这个值
      * 
      * @var mixed
      */
    private $id = null;  
    /**
     * 存放model当前属性的数据集
     *
     * @var array
     */
    protected $_data = array();
    /**
     * 存放find* 方法的裸数据
     *
     * @var array
     */
    public $_result_data = array();
    /**
     * 存放find*方法的结果数据集
     * 
     * @var ArrayObject
     */
    protected $_result;
    
    protected $_row_mode=false;
    
    /**
     * construct Anole_ActiveRecord_Base Object
     *
     * @param mixed $data
     */
    public function __construct($data=null){
        if(is_null($this->_class_name)){
            $this->_class_name = get_class($this);
        }
        if(is_null($this->_table_name)){
            throw new Anole_ActiveRecord_Exception('table_name is NULL.');
        }
        if(!empty($data)){
            $this->setRawData($data);
        }
        if(is_null($this->_sequence_name)){
            $this->_sequence_name = $this->_table_name;
        }
        //self::debug("activerecord init.", __METHOD__);
        if(is_null($this->_attributes)){
            $this->_initializeAttributes();
        }
    }
    /**
     * 设置ActiveRecord使用的DBA实例
     *
     * @param Anole_Dba_Adapter $dba
     * @return Anole_ActiveRecord_Base
     */
    final protected static function setDba($dba){
        self::$_dba = $dba;
        return $this;
    }
    /**
     * 构建model的实例
     *
     * @param mixed $data
     * @param string $model_class
     * 
     * @return Anole_ActiveRecord_Base
     */
    public static function getModel($data=null,$model_class=__CLASS__){
        if(empty($model_class) || !class_exists($model_class)){
        	self::warn("model class[$model_class] is null or not exist.", __METHOD__);
            throw new Anole_Dba_Exception("model class[$model_class] is null or not exist.");
        }
        $instances = new $model_class($data);
        
        return $instances;
    }
    /**
     * 返回ActiveRecord使用的DBA实例
     *
     * @return Anole_Dba_Adapter
     */
    final public static function getDba(){
        if(is_null(self::$_dba)){
            self::$_dba = Anole_Dba_Manager::getDefaultConnection();
        }
        return self::$_dba;
    }
    /**
     * 创建一个空白的记录集
     */
    public function insert(){        
        $this->_data = array();
        $this->_relation_models = array();
        $this->_rollback_models = array();
        $this->setIsNew(true);
        $this->_buildFindResult();
    }
    /**
     * 根据model的属性值在数据库表中添加相应的记录
     * 
     * @example
     *  sql='INSERT INTO table_name (field1,field2,...) VALUES (value1,value2,...)'
     * 
     * @access private
     */
    private function _createRecord(){
        self::debug("Create Record Start.",__METHOD__);
        $sql = 'INSERT INTO `'.$this->tablelize().'`';
        $vars = array();
        foreach($this->_attributes as $k=>$v){
            if(!isset($this->_data[$k])){
                continue;
            }
            $columns[] = $k;
            $vars[] = $this->_data[$k];
            $holders[] = '?';
        }
        $sql .= ' ('.implode(', ',$columns).') VALUES ('.implode(', ',$holders).')';
        try{
        	self::debug("Insert sql:$sql", __METHOD__);
            self::getDba()->execute($sql,$vars);
            $this->_new = false;
            self::debug("Create Record OK.",__METHOD__);
        }catch(Anole_Dba_Exception $e){
            self::warn("Create Record Error:".$e->getMessage(), __METHOD__);
            throw new Anole_ActiveRecord_Exception('Create Record Error:'.$e->getMessage());
        }
    }
    /**
     * 根据model的属性值更新数据库表中相应的记录
     *
     * @example 
     * sql='UPDATE table_name SET field1=value1,field2=value2,... WHERE id=?'
     * 
     * @access private
     */
    private function _updateRecord(){
        self::debug("Update Record Start.",__METHOD__);
        $sql = 'UPDATE `'.$this->tablelize().'`';
        $vars = array();
        foreach($this->_attributes as $k=>$v){
            if(!isset($this->_data[$k])){
                continue;
            }
            
            $pairs[] = " $k = ? ";
            $vars[] = $this->_data[$k];
        }
        $sql .= ' SET '.implode(', ',$pairs);
        $sql .= ' WHERE '.$this->_primary_key.' = ?';
        $vars[] = $this->getId();
        try{
        	self::debug("Update Sql:".$sql, __METHOD__);
            self::getDba()->execute($sql,$vars);
            self::debug("Update Record OK.",__METHOD__);
        }catch(Anole_Dba_Exception $e){
            self::warn("Update Record Failed,DBA Error:".$e->getMessage(),__METHOD__);
            throw new Anole_ActiveRecord_Exception('Update Record Failed,DBA Error:'.$e->getMessage());
        }
    }
    /**
     * 保存当前model
     * 
     * 保存包括插入和更新，根据$this->isNew()来确定是插入还是更新。
     * 
     * 保存的过程如下:
     * - beforeValidation
     * - validate
     * - afterValidation
     * - beforeSave
     * 如果是新记录，则
     *  - beforeCreate
     *      - 保存BelongsTo类型的relation model
     *      - 新建记录到数据库
     *  － afterCreate
     * 否则：
     *  - beforeUpdate
     *  - 更新记录到数据库
     *  - afterUpdate
     * - afterSave
     * - 保存关联model的数据，如果有
     *
     * @param array $data optional
     * @param bool $validate optional whether validate
     * 
     * @return  Anole_ActiveRecord_Base
     * 
     * @throws Anole_ActiveRecord_Exception
     */
    public function save($data=null,$validate=true){
    	//如果是新建记录则生成主键的sequence值
    	if($this->isNew() && is_null($this->getId())){
    		$this->setId($this->genId());
    	}
    	//validate data
    	$this->beforeValidation();
    	if($validate){
    		if(!$this->validate()){
    			throw new Anole_ActiveRecord_Exception($this->popValidateError());
    		}
    	}
    	$this->afterValidation();
    	
    	//保存belongsTo类型的关系数据
    	if(in_array(self::BELONGS_TO,$this->AutoSaveRelationType)){
    		self::debug("Save belgonsTo relation models...", __METHOD__);
    		$this->saveBelongstoRelationModels();
    	}
    	
    	//save data
    	$this->beforeSave();
        if($this->isNew()){
        	$this->beforeCreate();
        	try{
        		$this->_createRecord();
        	}catch(Anole_ActiveRecord_Exception $e){
        		$this->_rollbackCreatedRelationModel();
        		throw $e;
        	}
        	$this->afterCreate();
        }else{
        	$this->beforeUpdate();
        	try{
        		$this->_updateRecord();
        	}catch(Anole_ActiveRecord_Exception $e){
        		$this->_rollbackCreatedRelationModel();
        		throw $e;
        	}
        	$this->afterUpdate();
        }
        $this->afterSave();
        
        //save relation models
        $this->saveRelationModels();
        
        //empty on the success
        $this->_relation_models = array();
        $this->_rollback_models = array();
        
        return $this;
    }
    /**
     * 立即从数据库中删除符合条件的记录而不先创建ActiveRecord对象
     *
     * @param string $condition
     * @param array $vars
     * @return Anole_ActiveRecord_Base
     */
    public function deleteAll($condition=null,$vars=array()){
    	$sql = 'DELETE FROM `'.$this->tablelize().'`';
    	if(!empty($condition)){
    		$sql .= " WHERE $condition";
    	}
    	self::getDba()->execute($sql,$vars);
    	return $this;
    }
    /**
     * 立即从数据库中删除指定id的记录而不先创建对象
     *
     * @param mixed $id
     * @return Anole_ActiveRecord_Base
     */
    public function delete($id){
    	if(!empty($id)){
    		$condition = $this->_primary_key."= $id";
    	}
    	return $this->deleteAll($condition);
    }
    /**
     * 创建指定id的ActiveRecord对象，并调用其destroy方法(对象的callback将被触发)
     *
     * @param mixed $id id数组或者单个id,空则删除自身
     * 
     * @return boolean
     */
     final public function destroy($id=null){
    	//批量delete
    	if(is_array($id)){
    		return $this->destroyAll($this->_primary_key.' IN ('.implode(',',$id).') ');
    	}
    	//单个delete
    	$last_id = $this->getId();
    	if(!is_null($id)){
    		$this->setId($id);
    	}else{
    		$id = $this->getId();
    	}
    	if(is_null($this->getId())){
    		$this->setId($last_id);
    		throw new Anole_ActiveRecord_Exception('destroy id is NULL');
    	}
    	$last_result = $this->_result;
    	$last_data = $this->_data;
    	
    	$this->findById($id);
    	$this->_apply($this->_result);
    	
    	try{
    		$this->beforeDestroy();
    		$this->delete($id);
    		$this->afterDestroy();
    	}catch(Anole_ActiveRecord_Exception $e){
    		$this->setId($last_id);
    		$this->_apply($last_data);
    		$this->_result = $last_result;
    		throw $e;
    	}
    	//restore context
    	$this->setId($last_id);
        $this->_apply($last_data);
        $this->_result = $last_result;
    	
        return $this;
    }
    /**
     * 查找出符合条件的对象,并调用其destroy方法删除(同时触发callback)
     * 
     * @param string $condition
     * @param array $vars bind array
     * 
     * @return Anole_ActiveRecord_Base
     */
    public function destroyAll($condition,$vars=array()){
    	$this->find(array('condition'=>$condition,'vars'=>$vars));
    	$success = 0;
    	if($this->count()){
    		for($i=0;$i<$this->count();$i++){
    			self::debug("Get Model Object:".$this->_class_name, __METHOD__);
    			$model = self::getModel($this[$i],$this->_class_name);
    			if($model->destroy()){
    				$success += 1;
    			}
    			unset($model);
    		}
    		self::warn("destroy $success objects!", __METHOD__);
    	}
    	return $this;
    }
    /**
     * 查找记录，返回全部符合匹配条件的记录
     *
     * 本方法是支持各种find的核心操作。
     * <p>
     * 可以传递一个关联数组$options，用来说明查询的条件，options支持的选项key有:
     * 
     * condition: string,SQL查询的Where条件语句(不包括WHERE关键字)
     * order: string,SQL的order语句，如 created_time DESC,name ASC,age ASC
     * size: 一个整数，表示要分页时每页的记录数,-1表示不分页，返回全部
     * page: 一个整数，表示返回页的索引号，如果设置了limit，则此参数默认为1
     * select: string,默认情况下,select * FROM table,如果你希望用具体的字段限定来替换*,那么可以指定字段列表,如 'name,age'
     * joins: string,SQL查询时需要附加的JOINS语句，比如"LEFT JOIN comments ON comments.post_id = id"
     * groupby:string SQL GROUPBY条件
     * vars: array,要传递的预编译参数数组，如果sql中使用了?这些占位符
     * 
     * @param array $options
     * 
     * @return Anole_ActiveRecord_Base
     */
    public function find($options=array()){
        $sql = $this->_buildSqlByOptions($options);
        return $this->findBySql($sql,$options);
    }
    /**
     * 查找并返回匹配的第一条记录
     *
     * @param array $options
     * 
     * @return Anole_ActiveRecord_Base
     */
    public function findFirst($options=array()){
    	$options['size'] = 1;
    	$options['page'] = 1;
    	$options['_first'] = true;
    	return $this->find($options);
    }
    /**
     * 查找匹配指定ID的记录
     *
     * @param mixed $id
     * @param array $options
     * 
     * @return Anole_ActiveRecord_Base
     */
    public function findById($id=null,$options=array()){
        if(is_null($id)){
        	$id = $this->getId();	
        }
        if(is_array($id)){
        	$options['condition'] = $this->_primary_key.' IN ('.$this->_createBindSqlHolders(count($id)).') ';
        	$options['vars'] = $id;
        	return $this->find($options);
        }else{
        	$options['condition'] = $this->_primary_key.'=? ';
        	$options['vars'] = $id;
        	$this->findFirst($options);
        }
        return $this;
    }
    /**
     *
     * 通过直接指定SQL查找匹配的记录
     *
     * 这是一个底层的SQL查找方法,需要指定一个完整的sql语句，同时可以使用bindingVars。
     *
     * @param string $sql 执行查询的完整的SQL语句
     * @param boolean $readonly 对象是否只读
     * @param int $limit 每页记录数量，-1表示全部
     * @param int $page 分页的页索引(1-based)
     * @param array $vars 要传递的预编译参数数组，如果sql中使用了?这些占位符
     * @param boolean $raw 是否直接返回表记录数据，如果true,则不为这些数据创建对象
     * 
     * @return Anole_ActiveRecord_Base
     */
    public function findBySql($sql,$options=array()){
    	$size = -1;
    	$page = 1;
    	$vars = null;
    	$_first = false;
    	
    	extract($options,EXTR_IF_EXISTS);
    	
    	try{
			//print $sql;
    		$result = self::getDba()->query($sql,$size,$page,$vars);

    		if($_first){
    			$result = !empty($result) ? $result[0] : array();
    			$this->_row_mode = true;
    		}else{
    			$this->_row_mode = false;
    		}
    	}catch(Anole_Dba_Exception $e){
    		self::warn("Find data error:".$e->getMessage(), __METHOD__);
    		throw new Anole_ActiveRecord_Exception("Find data error:".$e->getMessage());
    	}
    	$this->_result_data = $result;
    	$this->_buildFindResult();
    	$this->afterFind($_first);
    	
    	return $this;
    }
    /**
     * 查找匹配指定条件的记录的数量，如果没有匹配则返回0
     *
     * @param string $condition
     * @param array $vars
     * @param string $table
     * 
     * @return int
     */
    public function countIf($condition=null,$vars=array(),$table=null){
    	if(is_null($table)){
    		$table = $this->tablelize();
    	}else{
    		$table = $this->tablelize($table);
    	}
    	$sql = 'SELECT COUNT(*) AS cnt FROM `'.$table.'`';
    	if(!empty($condition)){
    		$sql .= " WHERE $condition";
    	}
    	try{
    		$rows = self::getDba()->query($sql,1,1,$vars);
    	}catch(Anole_Dba_Exception $e){
    		self::warn("count data error:".$e->getMessage(), __METHOD__);
    	}
    	return $rows[0]['cnt'];
    }
    /**
     * 数据库中是否存在指定id的model对象
     *
     * @param mixed $id
     * 
     * @return bool
     */
    public function has($id){
    	if(is_array($id)){
    		$condition = $this->_primary_key.' IN ('.$this->_createBindSqlHolders(count($id)).') ';
    		$vars = $id;
    	}else{
    		$condition = $this->_primary_key.'=? ';
    		$vars = array($id);
    	}
    	$cnt = $this->countIf($condition,$vars);
    	return ($cnt > 0);
    }
    /**
     * 数据库中是否存在符合条件的记录
     *
     * @param string $condition
     * @param array $vars
     * 
     * @return bool
     */
    public function hasIf($condition,$vars=null){
    	$cnt = $this->countIf($condition,$vars);
    	return ($cnt > 0);
    }
    /**
     * 将结果集以数组的形式返回
     * 
     * @return array
     */
    public function getResultArray(){
        return $this->_result->toArray();
    }
    /**
     * Build SQL语句
     *
     * @param array $options
     * @return string
     */
    protected function _buildSqlByOptions($options=array()){
        $select=null;
        $joins=null;
        $condition=null;
        $groupby=null;
        $order=null;
        
        extract($options,EXTR_IF_EXISTS);
        
        $sql = 'SELECT ';
        $sql .= $select ? $select : ' * ';
        $sql .= ' FROM `'.$this->tablelize().'`';
        
        if($joins){
            $sql .= " $joins ";	
        }
        if(!empty($condition)){
        	$sql .= " WHERE $condition ";
        }
        $sql .= $groupby ? " GROUP BY $groupby" : "";
        $sql .= $order ? " ORDER BY $order" : "";
        
        return trim($sql);
    }
    
    #----------relation data--------------
    /**
     * 当创建(更新除外)记录失败后，回滚创建的相关的model
     */
    protected function _rollbackCreatedRelationModel(){
    	foreach($this->_rollback_models as $key=>$ids){
    		if(empty($ids)){
    			continue;
    		}
    		$relation = $this->_relation_map[$key];
    		$class = $relation['class'];
    		$model = new $class();
    		try{
    			$model->destroy($ids);
    			unset($model);
    		}catch(Anole_ActiveRecord_Exception $e){
    			self::warn("rollback model failed,[key:$key class:$class ids:".@implode($ids), __METHOD__);
    		}
    	}
    }
    /**
     * 检查指定存在指定key的关系,如果存在则返回relation定义数组，否则抛出Anole_ActiveRecord_Exception异常
     *
     * @param string $key
     * @return array
     */
    private function _checkRelationKey($key){
    	if(!isset($this->_relation_map[$key])){
    		throw new Anole_ActiveRecord_Exception('invalid relation key:'.$key);
    	}
    	$relation = $this->_relation_map[$key];
    	$class = isset($relation['class']) ? $relation['class'] : null;
    	if(empty($class) || !class_exists($class)){
    		throw new Anole_ActiveRecord_Exception('relation class is null or not found:'.$class);
    	}
    	$type = isset($relation['type']) ? $relation['type'] : null;
    	if(empty($type)){
    		throw new Anole_ActiveRecord_Exception('relation type is NULL');
    	}
    	return $relation;
    }
    /**
     * 添加一条需要自动保存的关联model的数据，这些数据在当前model保存后将会自动保存相应的关联model中去
     *
     * @param string $key
     * @param array $data
     * 
     * @return Anole_ActiveRecord_Base
     */
    public function addRelationModelData($key,array $data){
    	$this->_checkRelationKey($key);
    	$relation_model_array = isset($this->_relation_models[$key]) ? $this->_relation_models[$key] : array();
    	$relation_model_array[] = $data;
    	$this->_relation_models[$key] = $relation_model_array;
    	
    	return $this; 
    }
    /**
     * 添加需要自动保存的关联model，在当前model保存后将会自动保存相应的关联model
     *
     * @param string $key
     * @param Anole_ActiveRecord_Base $model
     * 
     * @return Anole_ActiveRecord_Base
     * @throws Anole_ActiveRecord_Exception
     */
    public function addRelationModel($key,$model){
    	$relation = $this->_checkRelationKey($key);
    	$class = $relation['class'];
    	if(!$model instanceof $class){
    		throw new Anole_ActiveRecord_Exception("model[class:".get_class($model)."] is not a instance of given relation [key:$key class:$class]");
    	}
    	$relation_model_array = isset($this->_relation_models[$key]) ? $this->_relation_models[$key] : array();
    	$relation_model_array[] = $model->getRawData();
    	$this->_relation_models[$key] = $relation_model_array;
    	
        return $this;
    }
    
    /**
     * 保存从属BELONGS_TO关系类型的models
     * 注意:
     * 其他类型的关系使用saveRelationModels来保存
     * 
     * @return 
     * @see saveRelationModels
     */
    protected function saveBelongstoRelationModels(){
        if(empty($this->_relation_models)){
        	return $this;
        }
        foreach($this->_relation_models as $key=>$data){
        	$relation = $this->_relation_map[$key];
        	$type = $relation['type'];
        	$class = $relation['class'];
        	if($type != self::BELONGS_TO){
        		continue;
        	}
        	$model = new $class();
        	$other_table = $model->getTableName();
        	$id = $this->getId();
        	$other_key = $other_table.'_id';
        	
        	$foreign_key = empty($relation['foreign_key']) ? $other_key : $relation['foreign_key'];
        	foreach($data as $row){
        		$model->insert();
        		$model->setRawData($row);
        		if(!is_null($model->getId())){
        			$model->setIsNew(false);
        		}
        		if($model->isNew()){
        			$log_rollback = true;
        		}else{
        			$log_rollback = false;
        		}
        		$model->save();
        		if($log_rollback){
        			$this->_rollback_models[$key][] = $model->getId();
        		}
        		$this->set($foreign_key,$model->getId());
        	}
        	unset($model);
        }
        return $this;
    }
    /**
     * 保存HAS_ONE,HAS_MANY,HAS_AND_BELONGS_TO_MANY类型的关联model的数据
     * 
     * 注意:
     * BELONGS_TO类型的关系使用 saveBelongstoRelationModels 来保存
     * 
     * @param array $relation_type 要保存的Relation的类型,省略则保存self::$AutoSaveRelationType中定义的全部类型
     * 
     * @return Anole_ActiveRecord_Base
     * @see saveBelongstoRelationModels
     */
    protected function saveRelationModels($relation_type=null){
        if(empty($this->_relation_models)){
        	return $this;   
        }
        if(is_null($relation_type)){
        	$relation_type = $this->AutoSaveRelationType;
        }
        if(empty($relation_type)){
        	return $this;
        }
        foreach($this->_relation_models as $key=>$data){
        	$relation = $this->_relation_map[$key];
        	$type = $relation['type'];
        	$class = $relation['class'];
        	$options = isset($relation['options']) ? $relation['options'] : array();
        	
        	if($type == self::BELONGS_TO || !in_array($type, $relation_type)){
        		continue;
        	}
        	
        	$model = new $class();
        	$id = $this->getId();
        	$this_foreign_key = $this->_table_name.'_id';
        	$other_table = $model->getTableName();
        	$other_foreign_key = $other_table.'_id';
        	
        	//多对多关系表的数据可以一次性全部处理
        	if($type == self::HAS_AND_BELONGS_TO_MANY){
        	    $this_foreign_key = empty($relation['this_foreign_key']) ? $this_foreign_key : $relation['this_foreign_key'];
        	    $other_foreign_key = empty($relation['other_foreign_key']) ? $other_foreign_key : $relation['other_foreign_key'];
        	    $join_table = empty($relation['join_table']) ? $this->getJoinTableName($this->_table_name,$other_table) : $relation['join_table'];
        	    $habm_other_fields = empty($relation['habm_other_fields']) ? array() : $relation['habm_other_fields'];
        	    
        	    $condition = empty($relation['condition']) ? null : $relation['condition'];
        	    $vars = empty($relation['vars']) ? array() : $relation['vars'];
        	    //first,delete relation last records
        	    $this->deleteHABMTableData($join_table,$this_foreign_key,$id,$condition,$vars);
        	    //second,insert all new records
        	    $this->saveHABMTableData($this_foreign_key,$other_foreign_key,$join_table,$data,$habm_other_fields);
        	    
        	    unset($model);
        	    continue;
        	}
        	//其他类型的关系
        	foreach($data as $row){
        		$model->insert();
        		$model->setRawData($row);
        		if(!is_null($model->getId())){
        			$model->setIsNew(false);
        		}
        		if($model->isNew()){
        			$log_rollback = true;
        		}else{
        			$log_rollback = false;
        		}
        		switch ($type){
        			case self::HAS_ONE:
        				break;
        			case self::HAS_MANY:
        				$foreign_key = empty($relation['foreign_key']) ? $this_foreign_key : $relation['foreign_key'];
        				$model->set($foreign_key,$id);
        				$model->save();
        				if($log_rollback){
        					$this->_rollback_models[$key][] = $model->getId();
        				}
        				break;
        			default:
        				continue;
        		}
        	}
        	unset($model);
        }
        return $this;
    }
    /**
     * 更新多对多关联表记录
     * 
     * 本方法删除当前model在关联表中的所有记录，然后再插入新的关联记录。
     * 
     */
    protected function saveHABMTableData($this_foreign_key,$other_foreign_key,$table,$rows,$habm_other_fields=array()){
        foreach($rows as $row){
        	$fields = array();
        	$vars = array();
        	
        	$fields[] = $this_foreign_key;
        	$vars[] = $this->getId();
        	
        	$fields[] = $other_foreign_key;
        	$vars[] = $row[$other_foreign_key];
        	if(!empty($habm_other_fields)){
        		foreach($habm_other_fields as $f){
        			if(isset($row[$f])){
        				$fields[] = $f;
        				$vars[] = $row[$f];
        			}
        		}
        	}
        	$sql = "INSERT INTO $table (";
        	$sql .= implode(',',$fields).') ';
        	$sql .= "VALUES (".$this->_createBindSqlHolders(count($fields)).')';
        	try{
        		self::getDba()->execute($sql,$vars);
        	}catch(Anole_Dba_Exception $e){
        		self::warn('Save habm data failed,dba error:'.$e->getMessage(),__METHOD__);
        		throw new Anole_ActiveRecord_Exception('Save habm data failed,dba error:'.$e->getMessage());
        	}
        }
    }
    /**
     * 删除多对多关联表中的匹配外键的记录
     *
     * @param string $table
     * @param string $foreign_key
     * @param string $foreign_value
     * @param string $condition
     * @param array $vars
     */
    protected function deleteHABMTableData($table,$foreign_key,$foreign_value,$condition=null,$vars=array()){
        $condition = empty($condition) ? "$foreign_key=? " : $condition." AND $foreign_key=? ";
        $sql = "DELETE FROM $table WHERE $condition";
        $vars[] = $foreign_value;
        try{
        	self::getDba()->execute($sql, $vars);
        }catch(Anole_Dba_Exception $e){
        	self::warn("deleteHABMTableData failed,dba error:".$e->getMessage(),__METHOD__);
        	throw new Anole_ActiveRecord_Exception("deleteHABMTableData failed,dba error:".$e->getMessage());
        }
    }
    /**
     * 查找当前model的关联对象
     * 
     * 可以指定一个options数组，数组的格式和 Anole_ActiveRecord_Base::_relation_map
     * 中的options数组一样，从而覆盖_relation_map中的预定义的参数
     * 
     * $foreign_key_value 是用于查询这个关联所需的外键的字段值，默认使用get*方法从model的当前属性里获取，通常是当前的id值,
     * BELONGS_TO关系则是通过调用get(foreign_key)来获得,
     * 因此你也可以直接提供需要查询外键字段的值。
     * 
     * @param string $key 在_relation_map中定义的关联的key
     * @param array $options 可选的关联选项
     * @param mixed $foreign_key_value 关系外键的值，可选，默认使用get*方法从model的当前属性里获取，通常是当前的id值
     * 
     * @throws Anole_ActiveRecord_Exception
     * @return Anole_ActiveRecord_Base
     */
    public function findRelationModel($key,$options=array(),$foreign_key_value=null){
    	$relation = $this->_checkRelationKey($key);
    	$type = $relation['type'];
    	$class = $relation['class'];
    	$options = isset($relation['options']) ? array_merge($relation['options'],$options) : $options;
    	
    	$model = new $class();
    	$id = $this->getId();
    	$this_foreign_key = $this->_table_name.'_id';
    	$other_table = $model->getTableName();
    	$other_foreign_key = $other_table.'_id';
    	
    	$foreign_key = isset($relation['foreign_key']) ? $relation['foreign_key'] : $this_foreign_key;
    	$vars = isset($options['vars']) ? $options['vars'] : array();
    	$condition = !empty($options['condition']) ? $options['condition']." AND $foreign_key=? " : "$foreign_key=? ";
    	
    	switch($type){
    		case self::HAS_MANY:
    			$vars[] = is_null($foreign_key_value) ? $this->getId() : $foreign_key_value;
    			$options['vars'] = $vars;
    			$options['condition'] = $condition;
    			return $model->find($options);
    		case self::HAS_ONE:
    			$vars[] = is_null($foreign_key_value) ? $this->getId() : $foreign_key_value;
                $options['vars'] = $vars;
                $options['condition'] = $condition;
                return $model->findFirst($options);
    		case self::BELONGS_TO:
    			$other_id = $model->getPrimaryKey();
                $condition = !empty($options['condition']) ? $options['condition']." AND $other_id=? " : " $other_id=? ";
                $foreign_key = isset($relation['foreign_key']) ? $relation['foreign_key'] : $other_foreign_key;
                $vars[] = is_null($foreign_key_value) ? $this->get($foreign_key) : $foreign_key_value;
                $options['vars']=$vars;
                $options['condition']=$condition;
                return $model->findFirst($options);
    		case self::HAS_AND_BELONGS_TO_MANY:
    			$join_table = $this->tablelize(isset($relation['join_table']) ? $relation['join_table'] : $this->getJoinTableName($this->tableName, $other_table));
                $this_foreign_key = isset($relation['this_foreign_key']) ? $relation['this_foreign_key'] : $this_foreign_key;
                $other_foreign_key = isset($relation['other_foreign_key']) ? $relation['other_foreign_key'] : $other_foreign_key;
                
                $joins = ' LEFT JOIN '. $this->tablelize($join_table).' ON '.$model->tablelize().'.'.$model->getPrimaryKey()." = $join_table.$other_foreign_key ";
                if (!empty($options['joins']))
                        $joins .= " " . $options['joins'];
        
                $options['joins'] = $joins;
                               
                $condition = " $join_table.$this_foreign_key = ? ";
                $vars[] = is_null($foreign_key_value) ? $this->getId() : $foreign_key_value;
                
                if(!empty($options['condition'])){
                    $condition = ' ( '.$options['condition']." ) AND ( $condition ) ";
                }
                $options['condition']=$condition;
                $options['vars']=$vars;
                
                return $model->find($options);
    		default:
    			throw new Anole_ActiveRecord_Exception('Unknow relation type:'.$type);
    	}
    }
    /**
     * 删除依赖当前model的关联model。
     * 
     * 默认会删除HAS_MANY,HAS_ONE,HABTM表数据
     * 
     * @return Anole_ActiveRecord_Base
     */
    protected function destoryDependedModel(){
    	if(empty($this->_relation_map)){
    		return $this;
    	}
    	$relation_type = $this->AutoDeleteRelationType;
    	foreach($this->_relation_map as $key=>$relation){
    		$type = $relation['type'];
    		$class = $relation['class'];
    		
    		$options = isset($relation['options']) ? $relation['options'] : array();
    		$depend = isset($relation['depend']) ? $relation['depend'] : true;
    		
    		if(!$depend || $type==self::BELONGS_TO || !is_array($type,$relation_type)){
    			continue;
    		}
    		$id = $this->getId();
    		$model = new $class;
    		$this_foreign_key = $this->_table_name.'_id';
    		$other_table = $model->getTableName();
    		$other_foreign_key = $other_table.'_id';
    		
    		//多对多关系,删除中间表数据
    		if($type == self::HAS_AND_BELONGS_TO_MANY){
    			$this_foreign_key = empty($relation['this_foreign_key']) ? $this_foreign_key : $relation['this_foreign_key'];
                $other_foreign_key = empty($relation['other_foreign_key']) ? $other_foreign_key : $relation['other_foreign_key'];
                $join_table = empty($relation['join_table']) ? $this->getJoinTableName($this->tableName,$other_table) : $relation['join_table'];
                
                $condition = empty($options['condition']) ? null : $options['condition'];
                $vars      = empty($options['vars']) ? array() : $options['vars'];
                try{
                	$this->deleteHABMTableData($join_table,$this_foreign_key,$id,$condition,$vars);
                }catch(Anole_ActiveRecord_Exception $e){
                	self::warn("DELETE HABTM table FAILED,unlink FAILED,error:".$e->getMessage(),__METHOD__);
                }
                continue;
    		}
    		//其它类型关系，直接delete对应表里数据
    		$foreign_key = empty($relation['foreign_key']) ? $this_foreign_key : $relation['foreign_key'];
            
            $condition = empty($options['condition']) ? " $foreign_key=? " : $options['condition']." AND $foreign_key=? ";
            $vars = empty($options['vars']) ? array() : $options['vars'];
            $vars[] = $id;
            try{
            	$this->deleteAll($condition,$vars);
            }catch(Anole_ActiveRecord_Exception $e){
            	self::warn("DESTORY depended model [key:$key => class:$class ] FAILED,error:".$e->getMessage(),__METHOD__);
            }
    		unset($model);
    	}
    	return $this;
    }
    
    /**
     * 标识model当前是新记录还是旧记录
     *
     * @param bool $new
     * @return Anole_ActiveRecord_Base
     */
    public function setIsNew($new){
        $this->_new = $new;
        return $this;
    }
    /**
     * 当前model是否为新记录
     *
     * @return bool
     */
    public function isNew(){
        return $this->_new;
    }
    /**
     * return real table name
     *
     * @param string $name
     * @return string
     */
    public function tablelize($name=null){
        if(empty($name)){
            $name = strtolower($this->_table_name);
        }
        return $this->_table_name_prefix.$name.$this->_table_name_suffix;
    }
    /**
     * 初始化ActiveRecord对应的Attributes信息
     *
     * 读取ActiveRecord对应的表的字段并缓存
     *
     * @return boolean
     */
    private function _initializeAttributes(){
        $table = $this->tablelize();
        try{
            $fields = self::getDba()->getFieldMetaList($table);
        }catch(Anole_Dba_Exception $e){
            self::warn("Error while get table[$table] fields:".$e->getMessage(), __METHOD__);
            return false;
        }
        $this->_attributes = $fields;
        return true;
    }
    /**
     * create ? holders
     *
     * @param int $size
     * @return string
     */
    protected function _createBindSqlHolders($size){
    	$holds = array_pad(array(),$size,'?');
    	return implode(',',$holds);
    }
    /**
     *  Returns a the name of the join table that would be used for the two
     *  tables.  The join table name is decided from the alphabetical order
     *  of the two tables.  e.g. "genres_movies" because "g" comes before "m"
     *
     * @param string $first
     * @param string $second
     * @return string
     */
    public function getJoinTableName($first, $second) {
        $tables = array();
        $tables["one"] = $first;
        $tables["many"] = $second;
        @asort($tables);
        return $this->tablelize(@implode("_", $tables));
    }
    /**
     * Build internal data result ArrayObject
     * 内部对应关系的record
     */
    protected function _buildFindResult(){
    	if($this->_row_mode){
    		$this->_result = new Anole_ActiveRecord_Base_ResultRow($this->_result_data,$this,$this->_relation_map);
    	}else{
    		$this->_result = new Anole_ActiveRecord_Base_ResultSet($this->_result_data,$this,$this->_relation_map);
    	}
    }
    /**
     * 生成用于新Record的ID号
     *
     * 默认情况下，使用RecordActive的类名作为SequenceName，从DBA中
     * 返回一个Seq的当前值。
     * 你可以重写此方法来实现自己的ID生成策略
     *
     * @return int
     */
    public function genId(){
        return self::getDba()->genSeq($this->_sequence_name);
    }
    /**
     * 返回所有的校验错误信息
     *
     * @return string
     */
    protected function popValidateError(){
    	$data = implode("\n",$this->_validate_errors);
    	//empty
    	$this->_validate_errors = array();
    	return $data;
    }
    /**
     * 保存报错信息
     *
     * @param string $msg
     * 
     * @return Anole_ActiveRecord_Base
     */
    public function pushValidateError($msg){
    	$this->_validate_errors[] = $msg;
    	return $this;
    }
    /**
     * 检验所需字段是否已经赋值
     *
     * @param array $fields
     * 
     * @return  boolean
     */
    public function validateRequird($fields=array()){
    	$ok = true;
    	for($i=0;$i<count($fields);$i++){
    		$f = $fields[$i];
    		$v = $this->get($f);
    		if(empty($v) && $v !== 0){
    			$this->pushValidateError($f.' is NULL');
    			$ok = false;
    		}
    	}
    	return $ok;
    }
    /**
      * 将attributes中的属性值应用到当前ActiveRecord对象中
      * 
      * @param array $attributes
      * 
      * @return Anole_ActiveRecord_Base
      */
    protected function _apply($attributes){
    	if(!empty($attributes)){
    		foreach($attributes as $key=>$value){
    			$this->set($key,$value);
    		}
    	}
    	return $this;
    }
    /**
      * 获取model的虚拟字段的值
      * 
      * 这是默认的实现，在调用虚拟字段的方法前，将设置当前model
      * 的属性为rowData，调用后恢复原始值
      * 
      * 注意:子类可以重载此方法
      * 
      * @param string $name 要获取的字段名称
      * @param array $rowData 当前记录的裸数据
      * @return mixed
      */
     public function _magicField($name,$rowData){
         if(!isset($this->_magic_field[$name])){
             return null;
         }
         $method = $this->_magic_field[$name];
         $_data = $this->getRawData();
         $this->setRawData($rowData);
         
         if(is_array($method)){
             $result = call_user_func($method);
         }else{
             $result = $this->$method();
         }
         $this->setRawData($_data);
         return $result;
     }
    
     /**
      * 添加/修改magic field的实现
      * 
      * @param string $name
      * @param mixed $callback 回调函数,可以是string或者callback形式(类名/对象,方法名)
      * 
      * @return Anole_ActiveRecord_Base
      */
     public function setMagicField($name,$callback){
         $this->_magic_field[$name] = $callback;
         return $this;
     }
    #-------------------attributes------------------
    public function set($name,$value){
        $this->_data[$name] = $value;
        return $this;
    }
    public function get($name){
        return isset($this->_data[$name]) ? $this->_data[$name] : null;
    }
    /**
     * 设置Model对应表的主键
     *
     * @param  string $key
     * @return Anole_ActiveRecord_Base
     */
    public function setPrimaryKey($key){
        $this->_primary_key = $key;
        return $this;
    }

    /**
     * 返回Model表的主键名
     * 
     * @return string
     */
    public function getPrimaryKey(){
        return $this->_primary_key;
    }
    /**
     * set primary_key value
     *
     * @param string $value
     * @return Anole_ActiveRecord_Base
     */
    public function setId($value){
        $this->set($this->_primary_key,$value);
        return $this;
    }
    /**
     * get primary_key value
     *
     * @return int
     */
    public function getId(){
        return $this->get($this->_primary_key);
    }
    /**
     * set model raw data
     *
     * @param array $data
     * @return Anole_ActiveRecord_Base
     */
    public function setRawData($data){
        $this->_data = $data;
        $this->setIsNew($this->getId()==null);
        return $this;
    }
    public function getRawData(){
        return $this->_data;
    }
    /**
     * Set model's sequence name
     *
     * @param string $sn
     * @return Anole_ActiveRecord_Base
     */
    public function setSequenceName($sn){
        $this->_sequence_name = $sn;
        return $this;
    }
    /**
     * Return model's sequence name
     *
     * @return string
     */
    public function getSequenceName(){
        return $this->_sequence_name;
    }
    /**
     * 返回当前model的类名
     *
     * @return string
     */
    public function getClassName(){
        return $this->_class_name;
    }
    /**
     * 返回model对应表的表名(不包括表前后缀)
     *
     * @return string
     */
    public function getTableName(){
        return $this->_table_name;
    }
    public function setTableName($table){
        $this->_table_name = $table;
        return $this;
    }
    /**
     * set table prefix
     *
     * @param string $key
     * @return Anole_ActiveRecord_Base
     */
    public function setTableNamePrefix($key){
        $this->_table_name_prefix = $key;
        return $this;
    }
    /**
     * get table prefix
     *
     * @return string
     */
    public function getTableNamePrefix(){
        return $this->_table_name_prefix;
    }
    /**
     * set table suffix
     *
     * @param string $key
     * @return Anole_ActiveRecord_Base
     */
    public function setTableNameSuffix($key){
        $this->_table_name_suffix = $key;
        return $this;
    }
    /**
     * get table suffix
     *
     * @return string
     */
    public function getTableNameSuffix(){
        return $this->_table_name_suffix;
    }
    
    //**********------SPL-----*************************************
    # override interface method
    //--------Countable接口－－－－－－－－－
    /**
     * @return int
     */
    public function count(){
    	if(is_null($this->_result)){
    		 
    	}
    	return $this->_result->count();
    }
    //-------ArrayAccess接口－－－－－－－－－
    /**
     *
     * @param int $offset or key
     * 
     * @return bool
     */
    public function offsetExists($offset){
    	if(is_null($this->_result)){
    		$this->_buildFindResult();
    	}
    	return $this->_result->offsetExists($offset);
    }
    /**
     *
     * @param int $offset or key
     * 
     * @return mixed
     */
    public function offsetGet($offset){
    	if(is_null($this->_result)){
    		$this->_buildFindResult();
    	}
    	return $this->_result->offsetGet($offset);
    }
    /**
     * @param int $offset or key
     * @param mixed $value
     */
    public function offsetSet($offset,$value){
    	if(is_null($this->_result)){
    		$this->_buildFindResult();
    	}
    	$this->_result->offsetSet($offset,$value);
    }
    /**
     * @param int $offset or key
     */
    public function offsetUnset($offset){
    	if(is_null($this->_result)){
    		$this->_buildFindResult();
    	}
    	$this->_result->offsetUnset($offset);
    }
    //-----------iterator接口------------------
    /**
     * @return mixed
     */
    public function current(){
        if(is_null($this->_result)){
            $this->_buildFindResult();
        }
        return $this->_result->current();
    }
    /**
     * @return mixed
     */
    public function key(){
        if(is_null($this->_result)){
            $this->_buildFindResult();
        }
        return $this->_result->key();
    }
    public function next(){
        if(is_null($this->_result)){
            $this->_buildFindResult();
        }
        $this->_result->next();
    }
    public function rewind(){
        if(is_null($this->_result)){
            $this->_buildFindResult();
        }
        $this->_result->rewind();
    }
    /**
     * @return bool
     */
    public function valid(){
        if(is_null($this->_result)){
            $this->_buildFindResult();
        }
        return $this->_result->valid();
    }
    
    //*************************************************************
    //
    //----------------可重载的callbacks事件-----------
    //
    /**
     *
     * 以下是各个事件的触发时序表:
     *
     * create时：
     *     - beforeValidation
     *     - validate
     *     - afterValidataion
     *     - beforeSave
     *     - beforeCreate
     *     - afterCreate
     *     - afterSave
     *
     * update时:
     *     - beforeValidation
     *     - validate
     *     - afterValidation
     *     - beforeSave
     *     - afterUpdate
     *     - beforeUpdate
     *     - afterSave
     *
     * destroy:
     *     - beforeDestory
     *     - afterDestory
     *
     *
     */
    
    /**
     * before_create callback
     * @abstract
     * @return bool
     */
    protected function beforeCreate(){return true;}
    /**
     * after_create callback
     * @abstract
     * @return bool
     */
    protected function afterCreate(){return true;}

    /**
     * before_save callback
     * @abstract
     * @return bool
     */
    protected function beforeSave(){return true;}
    /**
     * after_save callback
     * @abstract
     * @return bool
     */
    protected function afterSave(){return true;}
    /**
     * before_update callback
     * @abstract
     * @return bool
     */
    protected function beforeUpdate(){return true;}

    /**
     * after_update callback
     * @abstract
     * @return bool
     */
    protected function afterUpdate(){return true;}

    /**
     * before_validation callback
     * @abstract
     * @return bool
     */
    protected function beforeValidation(){return true;}
    /**
     * after_validation callback
     * @abstract
     * @return bool
     */
    protected function afterValidation(){return true;}

    /**
     * 校验数据有效性方法,用户应重载以便实现数据检查
     * @abstract
     * @return bool
     * @throw Doggy_ActiveRecord_ValidateException
     */
    protected function validate(){return true;}

    /**
     * before_destory callback
     * @return bool
     */
    protected function beforeDestroy(){}
    /**
     * after_destroy callback
     * @return bool
     */
    protected function afterDestroy(){}
    /**
     * callback after find got result but before return it
     * @param boolean $singelMode 指示当前是否为单记录模式
     */
    protected function afterFind($singelMode){}
    
    /**
     * 保存失败的时候回调
     * 
     * @param Doggy_ActiveRecord_Exception $e
     * @return boolean 是否要抑制异常,true则不向上抛出异常
     */
    protected function onSaveError($e){}
    /**
     * 创建失败的时候回调
     * 
     * @param Doggy_ActiveRecord_Exception $e
     */
    protected function onCreateError($e){}
    
    /**
     * 更新失败的时候回调
     * 
     * @param  Doggy_ActiveRecord_Exception $e
     */
    protected function onUpdateError($e){}
    
}

/**
 * 行记录,代表查找结果集的一条记录
 * 
 * 内部类,用于实现SPL接口和关联对象的LazyLoad
 */
class Anole_ActiveRecord_Base_ResultRow extends Anole_Object implements ArrayAccess,Countable,Iterator {
	protected $_data = array();
    /**
     * @var Anole_ActiveRecord_Base
     */
    public $_model;
    protected $_relations;
    
    public function __construct(&$data,$model,$relations){
    	$this->_data = $data;
    	$this->_model = $model;
    	$this->_relations = $relations;
    }
    public function offsetExists($offset){
        return isset($this->_data[$offset]);
    }
    public function offsetGet($offset){
        if(!isset($this->_data[$offset])){
            if(isset($this->_relations[$offset])){
                $_bak = $this->_model->getRawData();
                $this->_model->setRawData($this->_data);
                $relation = $this->_model->findRelationModel($offset);
                $this->_model->setRawData($_bak);
                return $relation;
            }
            return $this->_model->_magicField($offset,$this->_data);
        }
        return $this->_data[$offset];
    }
    public function offsetSet($offset,$value){
        $this->_data[$offset] = $value;
    }
    public function offsetUnset($offset){
        unset($this->_data[$offset]);
    }
    //countable
    public function count(){
        return count($this->_data);
    }
    //iterator
    public function key(){
        return key( $this->_data);
    }
    public function rewind(){
        return reset( $this->_data );
    }
    public function valid(){
        return current( $this->_data ) !== false;
    }
    public function current(){
        return current($this->_data);
    }
    public function next(){
        return next( $this->_data);
    }
    public function toArray(){
        return $this->_data;
    }
    public function keys(){
        return array_keys($this->_data);
    }
}

/**
 * 查找结果集
 * 
 * 内部类,用于封装找到的所有行记录(Find类方法的结果)
 */
class Anole_ActiveRecord_Base_ResultSet extends Anole_ActiveRecord_Base_ResultRow {
	
    protected $_rows = array();
    
    protected $_pointer = 0;
    
    public function offsetGet($offset){
        if(isset($this->_data[$offset])){
            if(!isset($this->_rows[$offset])){
                $this->_rows[$offset] = new Anole_ActiveRecord_Base_ResultRow($this->_data[$offset],$this->_model,$this->_relations);
            }
            return $this->_rows[$offset];
        }else{
            return null;
        }
    }
    
    public function offsetUnset($offset){
        unset($this->_rows[$offset]);
        return parent::offsetUnset($offset);
    }
    
    public function current(){
        if(!$this->valid()){
            return false;
        }
        return $this->offsetGet($this->_pointer);
    }
    
    public function rewind(){
        $this->_pointer = 0;
    }
    
    public function next(){
        return ++$this->_pointer;
    }
    
    public function valid(){
        return $this->_pointer < $this->count();
    }
    
    public function key(){
        return $this->_pointer;
    }
}

/**vim:sw=4 et ts=4 **/
?>