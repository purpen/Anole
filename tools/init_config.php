<?php
/**
 * 所有PHP脚本运行的初始环境装配
 * @see app
 * 
 * @version $Id$
 * @author purpen
 */
define('ANOLE_PROJECT', 'Anole');
define('ANOLE_LIB_ROOT','/Users/purpen/working/Anole/src');
define('ANOLE_DATA_ROOT','/Users/purpen/working/Anole/data');
require ANOLE_LIB_ROOT.'/Anole.php';
Anole::boot();
?>