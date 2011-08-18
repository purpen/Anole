<?php
define('ANOLE_PROJECT', 'Anole');
define('ANOLE_LIB_ROOT','/Users/purpen/working/Anole/src');
define('ANOLE_DATA_ROOT','/Users/purpen/working/Anole/data');
require ANOLE_LIB_ROOT.'/Anole.php';
Anole::boot();

$result = Anole_Config::get('database.dev');
print_r($result);
Anole_Dispatcher_Server::run();
?>