<?php

function __autoload($class){
	$full_path = str_replace('_','/',$class).'.php';
	require_once $full_path;
}
?>