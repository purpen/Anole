<?php
include3rd('Smarty/Smarty.class.php');
include3rd('Smarty/Smarty_Compiler.class.php');
/**
 * 改进Smarty,添加自定义标签
 * 
 * - 根据模板的tag自动注册和加载tag所在的plugin
 * - 模板中可定义outputfilter,postfilter
 */
class Anole_Util_Smarty_Base extends Smarty_Compiler {
	
	private $inited = false;
	protected $_included_tpl = array();
	protected $read_vars = array();
	public $default_resource_type = "anole";
	
	public $compiler_class = 'Anole_Util_Smarty_Base';
    public $compiler_file = 'Anole/Util/Smarty/Base.php';
	
    /**
     * 跟踪从模板中动态加载的plugin
     */
    private static $_anole_smarty_plugin = array();
    /**
     * 跟踪从模板中动态加载的plugin/tag
     */
    private static $_anole_smarty_tags = array();
    /**
     * 跟踪在模板中定义的outputfilter
     */
    private static $_anole_output_filter = array();
    /**
     * plugin的别名，用于查找别名和真实类名的对照
     */
    private static $_anole_plugin_alias = array();
    /**
     * 类名空间分隔符
     */
    const ANOLE_PLUGIN_SP ='_';
    
	public function __construct(){
		parent::Smarty_Compiler();
		parent::Smarty();
		$this->register_resource('anole',array($this,
            'getResourceContent',
            'getResourceTimestamp',
            'getResourceSecure',
            'getResourceTrusted'));
		$this->register_prefilter(array($this,'parseSmartyAnoleTag'));
	}
	/**
	 * init runtime var
	 *
	 * @param array $configs
	 */
	public function initRuntimeDirectory($configs=array()){
		if(empty($configs)){
			$this->cache_dir = Anole_Config::get('smarty.cache_dir');
			$this->compile_dir = Anole_Config::get('smarty.compile_dir');
			$this->template_dir = Anole_Config::get('smarty.template_dir');
		}else{
			$this->cache_dir = $configs['smarty.cache_dir'];
            $this->compile_dir = $configs['smarty.compile_dir'];
            $this->template_dir = $configs['smarty.template_dir'];
		}
		$this->inited = true;
	}
	/**
	 * Check wheater initialized
	 *
	 * @return bool
	 */
	public function isInitialized(){
		return $this->inited;
	}
	/**
	 * handle set initialized
	 *
	 * @param bool $v
	 */
	public function setInitialized($v){
		$this->inited = $v;
	}
    /**
     * 解析转换模板名称到模板实际路径
     * 
     * @param string $resource_name
     * @return string
     */
    protected function _resolveResourcePath($resource_name){
        if(is_file($resource_name) && is_readable($resource_name)) {
        	return $resource_name;
        }
        $resource_name = str_replace('.','/',$resource_name);
        $resource_name = $resource_name.'.tpl';
        if(is_readable($resource_name)){
            return $resource_name;
        }
        $resource_name = $this->template_dir.'/'.$resource_name;
        Anole_LogFactory::getLog()->debug("template file:$resource_name......",__METHOD__);
        if(is_readable($resource_name)){
            return $resource_name;
        }
        
        return false;
    }
    /**
     * get template content 
     * overlay smarty default function
     * 
     * @param string $tplName
     * @param string $tplContent
     * @param Anole_Util_Smarty_Base $smarty
     * @return bool
     */
    protected function getResourceContent ($tplName, &$tplContent,&$smarty){
        $tplContent = $this->_readAnoleResource($tplName);
        if($tplContent===false){
            throw new Doggy_Util_Smarty_Exception('Cannot read template resource:'.$tplName);
        }
        return true;
    }
    
    public function isResourceReadable($resource){
        return $this->_resolveResourcePath($resource)!==false;
    }
    /**
     * read content of the file
     *
     * @param string $resource
     * @return string
     */
    private function _readAnoleResource($resource){
        $resource = $this->_resolveResourcePath($resource);
        if($resource===false) return false;
        return file_get_contents($resource);
    }
    
    protected function getResourceTimestamp($tplName,&$timestamp,&$smarty){
        $resource = $this->_resolveResourcePath($tplName);
        if($resource===false) return false;
        $timestamp=filemtime($resource);
        return true;
    }
    public function getResourceSecure($tplName,&$smarty){
        return true;
    }
    public function getResourceTrusted($tplName,&$smarty){
        return true;
    }
    /**
     * Reset class scope variable
     *
     * @return void
     */
    public function resetAnolePlugin(){
    	self::$_anole_output_filter = array();
    	self::$_anole_plugin_alias  = array();
    	self::$_anole_smarty_plugin = array();
    	self::$_anole_smarty_tags   = array();
    }
    /**
     * 处理自定义的一些tag,用于加载fliter.
     * @support:
     * {smarty_plugin plugin_name}
     * {smarty_post_filter filter_name}
     * {smarty_outputfilter filter_name}
     * {smarty_include resource_name}
     *
     * @param string $tpl_source
     * @param Anole_Util_Smarty_Base $smarty
     * @return unknown
     */
    protected function parseSmartyAnoleTag($tpl_source){
    	$re = '!{(smarty_plugin|smarty_postfilter|smarty_outputfilter|smarty_include|smarty_read_content)\s+(\S+)\s*}!i';
    	return preg_replace_callback($re,array($this,'handleSmartyAnoleTag'),$tpl_source);
    }
    /**
     * handle many type smarty tag
     *
     * @param array $matches
     * @return mixed
     */
    protected function handleSmartyAnoleTag($matches){
    	$type = $matches[1];
    	$tag  = $matches[2];
    	switch($type){
    		case 'smarty_plugin':
    			list($plugin,$alias) = explode(',',$tag);
    			$this->loadSmartyPlugin($plugin,$alias);
    			break;
    		case 'smarty_postfilter':
    			$this->loadPostFilter($tag);
    			break;
    		case 'smarty_outputfilter':
    			$this->loadOutputFilter($tag);
    			break;
    		case 'smarty_include':
    			return $this->handleSmartyInclude($tag);
    		case 'smarty_read_content':
    			return $this->handelSmartyReadContent($tag);
    	}
    	return '';
    }
    /**
     * @param string $tag
     */
    protected function loadOutputFilter($tag){
        if(isset(self::$_anole_output_filter[$this->compile_id][$tag])){
        	return true;
        }
        if(is_callable($tag)){
        	Anole_LogFactory::getLog()->debug("register $tag as outputfilter",__CLASS__);
        	self::$_anole_output_filter[$this->compile_id][$tag] = true;
        	$this->register_outputfilter($tag);
        }
        $tag = self::findTagAlias($tag);
        $callback = self::parseTagCallback($tag,'smarty_outputfilter');
        if($callback){
        	Anole_LogFactory::getLog()->debug("register $tag as outputfilter",__CLASS__);
            self::$_anole_output_filter[$this->compile_id][$tag] = true;
            $this->register_outputfilter($callback);
        }
    }
    /**
     * @param string $tag
     */
    private function loadPostFilter($tag){ 
        if(is_callable($tag)){
        	Anole_LogFactory::getLog()->debug("register $tag as postfilter",__CLASS__);
        	$this->register_postfilter($tag);
        }
        $tag = self::findTagAlias($tag);
        $callback = self::parseTagCallback($tag,'smarty_postfilter');
        if($callback){
        	Anole_LogFactory::getLog()->debug("register $tag as postfilter",__CLASS__);
        	$this->register_postfilter($callback);
        }
    }
    
    protected static function findTagAlias($tag){
        return isset(self::$_anole_plugin_alias[$tag]) ? self::$_anole_plugin_alias[$tag] : $tag;
    }
    /**
     * 将tag转换为可用的(class,method) callback array
     * 
     * @param string $tag
     * @param string $prefix
     * @return array
     */
    private static function parseTagCallback($tag,$prefix){
        list($class,$cmd) = self::_parseTagCommandToken($tag);
        $method = $prefix.'_'.$cmd;
        if($class && method_exists($class,$method)){
            return array($class,$method);
        }
        return null;
    }
    /**
     * 逆向匹配可能的plugin class和tag command
     */
    public static function _parseTagCommandToken($tag){
    	
        $tokens = explode(self::ANOLE_PLUGIN_SP, $tag);
        
        $plugin = '';
        $command = array_pop($tokens);
        while($tokens){
            $plugin = implode('_', $tokens);
            if(self::parsePluginClass($plugin)){
                Anole_LogFactory::getLog()->debug("Parsed plugin:$plugin command:$command ",__CLASS__);
                return array($plugin,$command);
            }else{
                $t = array_pop($tokens);
                $command = empty($command) ? $t : $t.'_'.$command;
            }
            $plugin = '';
        }
        Anole_LogFactory::getLog()->debug("fallback,Parsed plugin:$plugin command:$command ",__CLASS__);
        
        return array($plugin,$command);
    }
    /**
     * 搜索和加载自定义的Smarty plugin
     *
     * @param string $plugin
     * @param string $alias
     * @return bool
     */
    public function loadSmartyPlugin($plugin,$alias=null){
        if(isset(self::$_anole_smarty_plugin[$this->compile_id][$plugin])){
        	return true;
        }
        $class = self::parsePluginClass($plugin);
        if(!$class){
        	return false;
        }
        $methods = get_class_methods($class);
        $re = '!smarty_(function|block|modifier|postfilter|compiler|outputfilter)_([a-zA-Z_0-9].+)!i';
        foreach($methods as $method){
        	if(preg_match($re,$method,$matches)){
        		$tag = $plugin.self::ANOLE_PLUGIN_SP.$matches[2];
        		if($alias){
        			$alias_tag = $alias.self::ANOLE_PLUGIN_SP.$matches[2];
        		}
        		switch($matches[1]){
        			case 'function':
        				self::$_anole_smarty_tags[$this->compile_id][] = $tag;
        				$this->register_function($tag,array($class,$method));
        				break;
        			case 'block':
        				self::$_anole_smarty_tags[$this->compile_id][] = $tag;
        				$this->register_block($tag,array($class,$method));
        				break;
        			case 'modifier':
        				self::$_anole_smarty_tags[$this->compile_id][] = $tag;
        				$this->register_modifier($tag,array($class,$method));
        				break;
        			case 'compiler':
        				self::$_anole_smarty_tags[$this->compile_id][] = $tag;
        				$this->register_compiler_function($tag,array($class,$method));
        				break;
        		}
        	}//endif
        }//endfor
        self::$_anole_smarty_plugin[$this->compile_id][$plugin] = true;
        return true;
    }
    
    /**
     * Parse plugin-tag的Class,如果没有可用的class，则返回NULl
     */
    private static function parsePluginClass($plugin){
        $class = Anole_Util_Inflector::classify($plugin);
        if(!class_exists($class,true)){
        	Anole_LogFactory::getLog()->debug("Cant find Smarty plugin[ $plugin ] class:: $class",__CLASS__);
        	return null;
        }
        return $class;
    }
    /**
     * 读取模板变量的内容合并到模板中
     *
     * @param string $var
     * @return string
     */
    protected function handelSmartyReadContent($var){
    	if(in_array($var,$this->read_vars)){
            Anole_LogFactory::getLog()->error("Found circular template reference:[ $var ],current include stack:".@implode('=>',$this->read_vars),__CLASS__);
            throw new Anole_Util_Smarty_Exception("Found circular template reference:[ $var ],current include stack:".@implode('=>',$this->read_vars));
        }
        array_push($this->read_vars,$var);
        $content = isset($this->_tpl_vars[$var]) ? $this->_tpl_vars[$var] : null;
        if(!empty($content)){
            $content = $this->parseSmartyAnoleTag($content);
        }
        array_pop($this->read_vars);
        
        return $content;
    }
    /**
     * 将smarty_include指定的文件包含到当前模板
     * 
     * 如果被包含的模板中嵌套有其他的SmartyTag（smarty_plugin,smarty_postfilter,smarty_outputfilter,smarty_include)
     * 也会自动解析。如果又含有smarty_include,则递归解析，直到没有需要需要include的模板为止.
     * 注意:
     * 如果模板a include b，b又include了a，这将导致循环嵌套，当检测到这种情况时
     * 会抛出一个Smarty_Exception
     * 
     * @param string $tpl
     * @return string
     */
    protected function handleSmartyInclude($tpl){
    	if(in_array($tpl,$this->_included_tpl)){
    		throw new Anole_Util_Smarty_Exception("Found circular template reference:[ $tpl ],current include stack:".@implode('=>',$this->_included_tpl));
    	}
    	array_push($this->_included_tpl,$tpl);
    	$content = $this->_readAnoleResource($tpl);
    	if(!empty($content)){
    		$this->parseSmartyAnoleTag($content);
    	}
    	array_pop($this->_included_tpl);
    	return $content;
    }
    
    protected function _initSmartyOutputFilters($plugins){
        foreach($plugins as $plugin){
            $this->loadOutputFilter($plugin);
        }
    }
    
    /**
     * Wrape smarty syntax error to throw a Anole_Util_Smarty_Exception
     *
     * @param string $error_msg
     * @param string $error_type
     * @param string $file
     * @param string $line
     * @throw Anole_Util_Smarty_Exception
     */
    public function _trigger_fatal_error($error_msg, $tpl_file = null, $tpl_line = null,
            $file = null, $line = null, $error_type = E_USER_ERROR){
        Anole_LogFactory::getLog()->error('Smarty Syntax Error:'.$error_msg.' file:'.$file.' line:'.$line,__CLASS__);
        throw new Anole_Util_Smarty_Exception($error_msg.' file:'.$file.' line:'.$line);
    }
    /**
     * trigger Smarty error
     *
     * @param string $error_msg
     * @param integer $error_type
     */
    public function trigger_error($error_msg, $error_type = E_USER_WARNING){
        Anole_LogFactory::getLog()->warn("Smarty error: $error_msg", __CLASS__);
    }
    
    public function _syntax_error($error_msg, $error_type = E_USER_ERROR, $file=null, $line=null){
        throw new Anole_Util_Smarty_Exception("syntax error: $error_msg file:[ $file ] line: [ $line ]");
    }
    
    ##------------override parent method-----------
    /**
     * parse modifier chain into PHP code
     *
     * sets $output to parsed modified chain
     * @param string $output
     * @param string $modifier_string
     */
    public function _parse_modifiers(&$output, $modifier_string){
        preg_match_all('~\|(@?\w+)((?>:(?:'. $this->_qstr_regexp . '|[^|]+))*)~', '|' . $modifier_string, $_match);
        list(, $_modifiers, $modifier_arg_strings) = $_match;

        for ($_i = 0, $_for_max = count($_modifiers); $_i < $_for_max; $_i++) {
            $_modifier_name = $_modifiers[$_i];

            if($_modifier_name == 'smarty') {
                // skip smarty modifier
                continue;
            }
            
            //edit by purpen
            $_modifier_name = self::findTagAlias($_modifier_name);
            //endedit

            preg_match_all('~:(' . $this->_qstr_regexp . '|[^:]+)~', $modifier_arg_strings[$_i], $_match);
            $_modifier_args = $_match[1];

            if (substr($_modifier_name, 0, 1) == '@') {
                $_map_array = false;
                $_modifier_name = substr($_modifier_name, 1);
            } else {
                $_map_array = true;
            }

            if (empty($this->_plugins['modifier'][$_modifier_name])
                && !$this->_get_plugin_filepath('modifier', $_modifier_name)
                && function_exists($_modifier_name)) {
                if ($this->security && !in_array($_modifier_name, $this->security_settings['MODIFIER_FUNCS'])) {
                    $this->_trigger_fatal_error("[plugin] (secure mode) modifier '$_modifier_name' is not allowed" , $this->_current_file, $this->_current_line_no, __FILE__, __LINE__);
                } else {
                    $this->_plugins['modifier'][$_modifier_name] = array($_modifier_name,  null, null, false);
                }
            }
            $this->_add_plugin('modifier', $_modifier_name);

            $this->_parse_vars_props($_modifier_args);

            if($_modifier_name == 'default') {
                // supress notifications of default modifier vars and args
                if(substr($output, 0, 1) == '$') {
                    $output = '@' . $output;
                }
                if(isset($_modifier_args[0]) && substr($_modifier_args[0], 0, 1) == '$') {
                    $_modifier_args[0] = '@' . $_modifier_args[0];
                }
            }
            if (count($_modifier_args) > 0)
                $_modifier_args = ', '.implode(', ', $_modifier_args);
            else
                $_modifier_args = '';

            if ($_map_array) {
                $output = "((is_array(\$_tmp=$output)) ? \$this->_run_mod_handler('$_modifier_name', true, \$_tmp$_modifier_args) : " . $this->_compile_plugin_call('modifier', $_modifier_name) . "(\$_tmp$_modifier_args))";

            } else {

                $output = $this->_compile_plugin_call('modifier', $_modifier_name)."($output$_modifier_args)";

            }
        }
    }
    
    /**
     * compile a resource
     *
     * sets $compiled_content to the compiled source
     * @param string $resource_name
     * @param string $source_content
     * @param string $compiled_content
     * @return true
     */
    
    public function _compile_file($resource_name, $source_content, &$compiled_content){

        if ($this->security) {
            // do not allow php syntax to be executed unless specified
            if ($this->php_handling == SMARTY_PHP_ALLOW &&
                !$this->security_settings['PHP_HANDLING']) {
                $this->php_handling = SMARTY_PHP_PASSTHRU;
            }
        }

        $this->_load_filters();

        $this->_current_file = $resource_name;
        $this->_current_line_no = 1;
        $ldq = preg_quote($this->left_delimiter, '~');
        $rdq = preg_quote($this->right_delimiter, '~');

        /* un-hide hidden xml open tags  */
        $source_content = preg_replace("~<({$ldq}(.*?){$rdq})[?]~s", '< \\1', $source_content);

        // run template source through prefilter functions
        if (count($this->_plugins['prefilter']) > 0) {
            foreach ($this->_plugins['prefilter'] as $filter_name => $prefilter) {
                if ($prefilter === false) continue;
                if ($prefilter[3] || is_callable($prefilter[0])) {
                    $source_content = call_user_func_array($prefilter[0],
                                                            array($source_content, &$this));
                    $this->_plugins['prefilter'][$filter_name][3] = true;
                } else {
                    $this->_trigger_fatal_error("[plugin] prefilter '$filter_name' is not implemented");
                }
            }
        }

        /* fetch all special blocks */
        $search = "~{$ldq}\*(.*?)\*{$rdq}|{$ldq}\s*literal\s*{$rdq}(.*?){$ldq}\s*/literal\s*{$rdq}|{$ldq}\s*php\s*{$rdq}(.*?){$ldq}\s*/php\s*{$rdq}~s";

        preg_match_all($search, $source_content, $match,  PREG_SET_ORDER);
        $this->_folded_blocks = $match;
        reset($this->_folded_blocks);

        /* replace special blocks by "{php}" */
        $source_content = preg_replace($search.'e', "'"
                                       . $this->_quote_replace($this->left_delimiter) . 'php'
                                       . "' . str_repeat(\"\n\", substr_count('\\0', \"\n\")) .'"
                                       . $this->_quote_replace($this->right_delimiter)
                                       . "'"
                                       , $source_content);

        /* Gather all template tags. */
        preg_match_all("~{$ldq}\s*(.*?)\s*{$rdq}~s", $source_content, $_match);
        $template_tags = $_match[1];
        /* Split content by template tags to obtain non-template content. */
        $text_blocks = preg_split("~{$ldq}.*?{$rdq}~s", $source_content);

        /* loop through text blocks */
        for ($curr_tb = 0, $for_max = count($text_blocks); $curr_tb < $for_max; $curr_tb++) {
            /* match anything resembling php tags */
            if (preg_match_all('~(<\?(?:\w+|=)?|\?>|language\s*=\s*[\"\']?php[\"\']?)~is', $text_blocks[$curr_tb], $sp_match)) {
                /* replace tags with placeholders to prevent recursive replacements */
                $sp_match[1] = array_unique($sp_match[1]);
                usort($sp_match[1], '_smarty_sort_length');
                for ($curr_sp = 0, $for_max2 = count($sp_match[1]); $curr_sp < $for_max2; $curr_sp++) {
                    $text_blocks[$curr_tb] = str_replace($sp_match[1][$curr_sp],'%%%SMARTYSP'.$curr_sp.'%%%',$text_blocks[$curr_tb]);
                }
                /* process each one */
                for ($curr_sp = 0, $for_max2 = count($sp_match[1]); $curr_sp < $for_max2; $curr_sp++) {
                    if ($this->php_handling == SMARTY_PHP_PASSTHRU) {
                        /* echo php contents */
                        $text_blocks[$curr_tb] = str_replace('%%%SMARTYSP'.$curr_sp.'%%%', '<?php echo \''.str_replace("'", "\'", $sp_match[1][$curr_sp]).'\'; ?>'."\n", $text_blocks[$curr_tb]);
                    } else if ($this->php_handling == SMARTY_PHP_QUOTE) {
                        /* quote php tags */
                        $text_blocks[$curr_tb] = str_replace('%%%SMARTYSP'.$curr_sp.'%%%', htmlspecialchars($sp_match[1][$curr_sp]), $text_blocks[$curr_tb]);
                    } else if ($this->php_handling == SMARTY_PHP_REMOVE) {
                        /* remove php tags */
                        $text_blocks[$curr_tb] = str_replace('%%%SMARTYSP'.$curr_sp.'%%%', '', $text_blocks[$curr_tb]);
                    } else {
                        /* SMARTY_PHP_ALLOW, but echo non php starting tags */
                        $sp_match[1][$curr_sp] = preg_replace('~(<\?(?!php|=|$))~i', '<?php echo \'\\1\'?>'."\n", $sp_match[1][$curr_sp]);
                        $text_blocks[$curr_tb] = str_replace('%%%SMARTYSP'.$curr_sp.'%%%', $sp_match[1][$curr_sp], $text_blocks[$curr_tb]);
                    }
                }
            }
        }

        /* Compile the template tags into PHP code. */
        $compiled_tags = array();
        for ($i = 0, $for_max = count($template_tags); $i < $for_max; $i++) {
            $this->_current_line_no += substr_count($text_blocks[$i], "\n");
            $compiled_tags[] = $this->_compile_tag($template_tags[$i]);
            $this->_current_line_no += substr_count($template_tags[$i], "\n");
        }
        if (count($this->_tag_stack)>0) {
            list($_open_tag, $_line_no) = end($this->_tag_stack);
            $this->_syntax_error("unclosed tag \{$_open_tag} (opened line $_line_no).", E_USER_ERROR, __FILE__, __LINE__);
            return;
        }

        /* Reformat $text_blocks between 'strip' and '/strip' tags,
           removing spaces, tabs and newlines. */
        $strip = false;
        for ($i = 0, $for_max = count($compiled_tags); $i < $for_max; $i++) {
            if ($compiled_tags[$i] == '{strip}') {
                $compiled_tags[$i] = '';
                $strip = true;
                /* remove leading whitespaces */
                $text_blocks[$i + 1] = ltrim($text_blocks[$i + 1]);
            }
            if ($strip) {
                /* strip all $text_blocks before the next '/strip' */
                for ($j = $i + 1; $j < $for_max; $j++) {
                    /* remove leading and trailing whitespaces of each line */
                    $text_blocks[$j] = preg_replace('![\t ]*[\r\n]+[\t ]*!', '', $text_blocks[$j]);
                    if ($compiled_tags[$j] == '{/strip}') {                       
                        /* remove trailing whitespaces from the last text_block */
                        $text_blocks[$j] = rtrim($text_blocks[$j]);
                    }
                    $text_blocks[$j] = "<?php echo '" . strtr($text_blocks[$j], array("'"=>"\'", "\\"=>"\\\\")) . "'; ?>";
                    if ($compiled_tags[$j] == '{/strip}') {
                        $compiled_tags[$j] = "\n"; /* slurped by php, but necessary
                                    if a newline is following the closing strip-tag */
                        $strip = false;
                        $i = $j;
                        break;
                    }
                }
            }
        }
        $compiled_content = '';

        /* Interleave the compiled contents and text blocks to get the final result. */
        for ($i = 0, $for_max = count($compiled_tags); $i < $for_max; $i++) {
            if ($compiled_tags[$i] == '') {
                // tag result empty, remove first newline from following text block
                $text_blocks[$i+1] = preg_replace('~^(\r\n|\r|\n)~', '', $text_blocks[$i+1]);
            }
            $compiled_content .= $text_blocks[$i].$compiled_tags[$i];
        }
        $compiled_content .= $text_blocks[$i];

        // remove \n from the end of the file, if any
        if (strlen($compiled_content) && (substr($compiled_content, -1) == "\n") ) {
            $compiled_content = substr($compiled_content, 0, -1);
        }

        if (!empty($this->_cache_serial)) {
            $compiled_content = "<?php \$this->_cache_serials['".$this->_cache_include."'] = '".$this->_cache_serial."'; ?>" . $compiled_content;
        }

        // remove unnecessary close/open tags
        $compiled_content = preg_replace('~\?>\n?<\?php~', '', $compiled_content);

        // run compiled template through postfilter functions
        if (count($this->_plugins['postfilter']) > 0) {
            foreach ($this->_plugins['postfilter'] as $filter_name => $postfilter) {
                if ($postfilter === false) continue;
                if ($postfilter[3] || is_callable($postfilter[0])) {
                    $compiled_content = call_user_func_array($postfilter[0],
                                                              array($compiled_content, &$this));
                    $this->_plugins['postfilter'][$filter_name][3] = true;
                } else {
                    $this->_trigger_fatal_error("Smarty plugin error: postfilter '$filter_name' is not implemented");
                }
            }
        }

        // put header at the top of the compiled template
        $template_header = "<?php /* Smarty version ".$this->_version.", created on ".strftime("%Y-%m-%d %H:%M:%S")."\n";
        $template_header .= "         compiled from ".strtr(urlencode($resource_name), array('%2F'=>'/', '%3A'=>':'))." */ ?>\n";

        //anole hacking begin------------++++++++++++++++
        $anole_code = '';
        
        
        //anole hacking,should load outputfilter that defined in current template.
        if(!empty(self::$_anole_output_filter[$this->compile_id])){
        	$s = var_export(array_keys(self::$_anole_output_filter[$this->compile_id]),true);
        	$anole_code .= '<?php '. '$this'."->_initSmartyOutputFilters($s); \n".'?>';
        }
        
        $template_header .= $anole_code;
        
        /* Emit code to load needed plugins. */
        $this->_plugins_code = '';
        
        $anole_tags = self::$_anole_smarty_tags[$this->compile_id];
        
        if (count($this->_plugin_info)) {
            $_plugins_params = "array('plugins' => array(";
            foreach ($this->_plugin_info as $plugin_type => $plugins) {
                foreach ($plugins as $plugin_name => $plugin_info) {
                    
                    //anole hacking dont load dynamic plugins,because these plugin will
                    // be loaded use anole's builtin classloader
                    if(!empty($anole_tags) && in_array($plugin_name,$anole_tags))continue;
                     
                    $_plugins_params .= "array('$plugin_type', '$plugin_name', '" . strtr($plugin_info[0], array("'" => "\\'", "\\" => "\\\\")) . "', $plugin_info[1], ";
                    $_plugins_params .= $plugin_info[2] ? 'true),' : 'false),';
                }
            }
            $_plugins_params .= '))';
            $plugins_code = "<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');\nsmarty_core_load_plugins($_plugins_params, \$this); ?>\n";
            $template_header .= $plugins_code;
            $this->_plugin_info = array();
            $this->_plugins_code = $plugins_code;
        }

        if ($this->_init_smarty_vars) {
            $template_header .= "<?php require_once(SMARTY_CORE_DIR . 'core.assign_smarty_interface.php');\nsmarty_core_assign_smarty_interface(null, \$this); ?>\n";
            $this->_init_smarty_vars = false;
        }

        $compiled_content = $template_header . $compiled_content;
        return true;
    }
    
    /**
     * Compile a template tag
     *
     * @param string $template_tag
     * @return string
     */
    public function _compile_tag($template_tag){
        /* Matched comment. */
        if (substr($template_tag, 0, 1) == '*' && substr($template_tag, -1) == '*')
            return '';
        
        /* Split tag into two three parts: command, command modifiers and the arguments. */
        if(! preg_match('~^(?:(' . $this->_num_const_regexp . '|' . $this->_obj_call_regexp . '|' . $this->_var_regexp
                . '|\/?' . $this->_reg_obj_regexp . '|\/?' . $this->_func_regexp . ')(' . $this->_mod_regexp . '*))
                      (?:\s+(.*))?$
                    ~xs', $template_tag, $match)) {
            $this->_syntax_error("unrecognized tag: $template_tag", E_USER_ERROR, __FILE__, __LINE__);
        }
        
        $tag_command = $match[1];
        $tag_modifier = isset($match[2]) ? $match[2] : null;
        $tag_args = isset($match[3]) ? $match[3] : null;

        if (preg_match('~^' . $this->_num_const_regexp . '|' . $this->_obj_call_regexp . '|' . $this->_var_regexp . '$~', $tag_command)) {
            /* tag name is a variable or object */
            $_return = $this->_parse_var_props($tag_command . $tag_modifier);
            return "<?php echo $_return; ?>" . $this->_additional_newline;
        }

        /* If the tag name is a registered object, we process it. */
        if (preg_match('~^\/?' . $this->_reg_obj_regexp . '$~', $tag_command)) {
            return $this->_compile_registered_object_tag($tag_command, $this->_parse_attrs($tag_args), $tag_modifier);
        }
        
        switch ($tag_command) {
            case 'include':
                return $this->_compile_include_tag($tag_args);

            case 'include_php':
                return $this->_compile_include_php_tag($tag_args);

            case 'if':
                $this->_push_tag('if');
                return $this->_compile_if_tag($tag_args);

            case 'else':
                list($_open_tag) = end($this->_tag_stack);
                if ($_open_tag != 'if' && $_open_tag != 'elseif')
                    $this->_syntax_error('unexpected {else}', E_USER_ERROR, __FILE__, __LINE__);
                else
                    $this->_push_tag('else');
                return '<?php else: ?>';

            case 'elseif':
                list($_open_tag) = end($this->_tag_stack);
                if ($_open_tag != 'if' && $_open_tag != 'elseif')
                    $this->_syntax_error('unexpected {elseif}', E_USER_ERROR, __FILE__, __LINE__);
                if ($_open_tag == 'if')
                    $this->_push_tag('elseif');
                return $this->_compile_if_tag($tag_args, true);

            case '/if':
                $this->_pop_tag('if');
                return '<?php endif; ?>';

            case 'capture':
                return $this->_compile_capture_tag(true, $tag_args);

            case '/capture':
                return $this->_compile_capture_tag(false);

            case 'ldelim':
                return $this->left_delimiter;

            case 'rdelim':
                return $this->right_delimiter;

            case 'section':
                $this->_push_tag('section');
                return $this->_compile_section_start($tag_args);

            case 'sectionelse':
                $this->_push_tag('sectionelse');
                return "<?php endfor; else: ?>";
                break;

            case '/section':
                $_open_tag = $this->_pop_tag('section');
                if ($_open_tag == 'sectionelse')
                    return "<?php endif; ?>";
                else
                    return "<?php endfor; endif; ?>";

            case 'foreach':
                $this->_push_tag('foreach');
                return $this->_compile_foreach_start($tag_args);
                break;

            case 'foreachelse':
                $this->_push_tag('foreachelse');
                return "<?php endforeach; else: ?>";

            case '/foreach':
                $_open_tag = $this->_pop_tag('foreach');
                if ($_open_tag == 'foreachelse')
                    return "<?php endif; unset(\$_from); ?>";
                else
                    return "<?php endforeach; endif; unset(\$_from); ?>";
                break;

            case 'strip':
            case '/strip':
                if (substr($tag_command, 0, 1)=='/') {
                    $this->_pop_tag('strip');
                    if (--$this->_strip_depth==0) { /* outermost closing {/strip} */
                        $this->_additional_newline = "\n";
                        return '{' . $tag_command . '}';
                    }
                } else {
                    $this->_push_tag('strip');
                    if ($this->_strip_depth++==0) { /* outermost opening {strip} */
                        $this->_additional_newline = "";
                        return '{' . $tag_command . '}';
                    }
                }
                return '';

            case 'php':
                /* handle folded tags replaced by {php} */
                list(, $block) = each($this->_folded_blocks);
                $this->_current_line_no += substr_count($block[0], "\n");
                /* the number of matched elements in the regexp in _compile_file()
                   determins the type of folded tag that was found */
                switch (count($block)) {
                    case 2: /* comment */
                        return '';

                    case 3: /* literal */
                        return "<?php echo '" . strtr($block[2], array("'"=>"\'", "\\"=>"\\\\")) . "'; ?>" . $this->_additional_newline;

                    case 4: /* php */
                        if ($this->security && !$this->security_settings['PHP_TAGS']) {
                            $this->_syntax_error("(secure mode) php tags not permitted", E_USER_WARNING, __FILE__, __LINE__);
                            return;
                        }
                        return '<?php ' . $block[3] .' ?>';
                }
                break;

            case 'insert':
                return $this->_compile_insert_tag($tag_args);

            default:
                $tag_command_prefix='';
                if (substr($tag_command, 0, 1) == '/') {
                    $tag_command = substr($tag_command, 1);
                    $tag_command_prefix='/';
                }
                
                $tag_command = self::findTagAlias($tag_command);
                
                list($plugin,$tag_command)  = $this->_parseTagCommandToken($tag_command);
                if(!empty($plugin)){
                    $this->loadSmartyPlugin($plugin);
                }
                
                $tag_command = empty($tag_command_prefix) && empty($plugin)?$tag_command:$tag_command_prefix.$plugin.self::ANOLE_PLUGIN_SP.$tag_command;
                
                if ($this->_compile_compiler_tag($tag_command, $tag_args, $output)) {
                    return $output;
                } else if ($this->_compile_block_tag($tag_command, $tag_args, $tag_modifier, $output)) {
                    return $output;
                } else if ($this->_compile_custom_tag($tag_command, $tag_args, $tag_modifier, $output)) {
                    return $output;                    
                } else {
                    $this->_syntax_error("unrecognized tag '$tag_command'", E_USER_ERROR, __FILE__, __LINE__);
                }
        }
    }
}
/**vim:sw=4 et ts=4 **/
?>