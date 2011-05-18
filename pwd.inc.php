<?php

if(isset($_SERVER['PWD']))
{
	$PATH_TO_WORKING_DIR = $_SERVER['PWD'].'/';
}
elseif(isset($_SERVER['SCRIPT_FILENAME']))
{
	$PATH_TO_WORKING_DIR = preg_replace('/(?<=\/)[^\/]+$/','',$_SERVER['SCRIPT_FILENAME']);
};


if(!empty($PATH_TO_WORKING_DIR))
{
	define('PATH_TO_WORKING_DIR',$PATH_TO_WORKING_DIR);
};

