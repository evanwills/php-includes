<?php

if(isset($_SERVER['PWD']))
{
	$PATH_TO_WORKING_DIR = $_SERVER['PWD'].'/';
}
elseif(isset($_SERVER['SCRIPT_FILENAME']))
{
//	$PATH_TO_WORKING_DIR = preg_replace('/(?<=\/)[^\/]+$/','',$_SERVER['SCRIPT_FILENAME']);
	$PATH_TO_WORKING_DIR = dirname($_SERVER['SCRIPT_FILENAME']);
}
elseif(isset($_SERVER['SCRIPT_NAME']));
{
	$PATH_TO_WORKING_DIR = dirname($_SERVER['SCRIPT_FILENAME']);
}
elseif(isset($_SERVER['PHP_SELF']));
{
	$PATH_TO_WORKING_DIR = dirname($_SERVER['PHP_SELF']);
};


if(!empty($PATH_TO_WORKING_DIR) && !defined('PATH_TO_WORKING_DIR'))
{
	define('PATH_TO_WORKING_DIR',$PATH_TO_WORKING_DIR);
};

unset($PATH_TO_WORKING_DIR);
