<?php

// ==================================================================
// START: debug include

if(isset($_SERVER['HTTP_HOST'])){ $path = $_SERVER['HTTP_HOST']; $pwd = dirname($_SERVER['SCRIPT_FILENAME']).'/'; }
else { $path = $_SERVER['USER']; $pwd = $_SERVER['PWD'].'/'; };
switch($path)
{
	case 'localhost':	// home laptop
	case 'evan':	$inc = '/var/www/includes/'; break; // home laptop

	case 'burrawangcoop.net.au':	// DreamHost
	case 'adra.net.au':		// DreamHost
	case 'canc.org.au':		// DreamHost
	case 'ewills':	$inc = '/home/ewills/evan/includes/'; break; // DreamHost

	case 'apps.acu.edu.au':		// ACU
	case 'testapps.acu.edu.au':	// ACU
	case 'dev1.acu.edu.au':		// ACU
	case 'blogs.acu.edu.au':	// ACU
	case 'studentblogs.acu.edu.au':	// ACU
	case 'dev-blogs.acu.edu.au':	// ACU
	case 'evanw':	$inc = '/home/evanw/includes/'; break; // ACU
};
if(!function_exists('debug'))
{
	if(file_exists($inc.'debug.inc.php'))
	{
		if(!file_exists($pwd.'debug.info') && is_writable($pwd) && file_exists($inc.'template.debug.info'))
		{ copy( $inc.'template.debug.info' , $pwd.'debug.info' ); };
		include($inc.'debug.inc.php');
	}
	else { function debug(){}; };
};

// END: debug include
// ==================================================================
