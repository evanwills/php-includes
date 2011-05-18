<?php

/**
 * check_host() checks the HTTP_HOST and returns the correct argument
 * for that host
 *
 * This script is primarily aimed at being used in config files where
 * there will always be differences for various values across
 * different servers an appmay be deployed on.
 *
 * e.g. if used in a config file that had Database details you might have:
 *      $db_host = check_host('192.148.223.212','192.148.223.212','localhost','localhost');
 *	if the script was being called from apps or testapps it would return '192.148.223.212'.
 *      if it was being called from dev1 or localhost it would return 'localhost'.
 *
 * @param $param_1 mixed correct value to be used running on apps.acu.edu.au or blogs.acu.edu.au server
 * @param $param_2 mixed correct value to be used running on testapps.acu.edu.au or studentblogs.acu.edu.au server
 * @param $param_3 mixed correct value to be used running on dev1.acu.edu.au or dev-blogs.acu.edu.au server
 * @param $param_4 mixed correct value to be used running on localhost or dev.student.blogs.acu.edu.au server
 * @param $param_5 mixed correct value to be used running on an unknown server
 *
 * @return mixed
 *
 */
function check_host( $param_1 = '' , $param_2 = '' , $param_3 = '' , $param_4 = '' , $param_5 = '' )
{
	$output = '';
	if(!defined('CHECK_HOST__SERVER_HTTP_HOST'))
	{
		$host = 'unknown';
		if(isset($_SERVER['HTTP_HOST']))
		{
			if(preg_match('/^(?:(localhost)|(apps|testapps|dev1|(?:(?:dev\.?)?student\.?)?(dev[.-]?)?blogs)\.acu\.edu\.au)$/',$_SERVER['HTTP_HOST'],$matches))
			{
				$matches[2] = isset($matches[2])?$matches[2]:'';
				$host = $matches[1].$matches[2];
			};
		}
		elseif(isset($_SERVER['SESSION_MANAGER']))
		{
			if(preg_match('/(wombat|testapps|apps|deb1|blogs)/i',$_SERVER['SESSION_MANAGER'],$matches))
			{
				$host = $matches[1];
			};
		};
		define('CHECK_HOST__SERVER_HTTP_HOST',$host);
	};

	switch(CHECK_HOST__SERVER_HTTP_HOST)
	{
		case 'apps':
		case 'blogs':
			$output = $param_1;
			break;
		case 'testapps':
		case 'studentblogs':
		case 'student.blogs':
			$output = $param_2;
			break;
		case 'dev1':
		case 'dev-blogs':
		case 'dev.blogs':
			$output = $param_3;
			break;
		case 'localhost':
		case 'wombat':
		case 'dev.student.blogs':
		case 'student.dev.blogs':
			$output = $param_4;
			break;
		case 'unknown':
			$output = $param_5;
			break;
	};
	return $output;
};


