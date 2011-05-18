<?php

/**
 * The following IF statement allows debug.inc.php to be included any
 * number of times so files that have been independantly tested won't
 * throw errors.
 */
if(!function_exists('debug')): 

class debug4
{
	var $root_dir = '';
	var $status = true;
	var $show_file = true;
	var $show_date = false;
	var $show_time = false;
	var $format = 'html'; // html, text, comment, log
	var $mode = 'echo'; // echo, return, log
	var $full_path = false;
	var $log_file = '';
	var $root_path = '';
	var $time_adjust = 0;
	var $timezone = 'Australia/Sydney';
	var $meta_max_length = 40;
	var $max_max_times = 100;
	var $original_vars = array();
	var $original_const = array();
	var $max_times_array = array();


	public function __construct()
	{
		error_reporting( E_ALL | E_STRICT );
	}

	public function do_debug($input)
	{
		$this->test .= $input;
		echo print_r($GLOBALS,true);
		echo $this->test."\n";

	}
}

$id = uniqid('debug__');
define('DEBUG_CLASS_ID',$id);
$$id = new debug4;

function debug()
{
	$id = constant('DEBUG_CLASS_ID');
	global $$id;
	$$id->do_debug(func_get_args());
}


debug('blah');
endif;
