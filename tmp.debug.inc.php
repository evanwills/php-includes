<?php
/**
 * Debug include functions, Make it easier to debug your PHP code.
 *
 * This file contains a number of functions to make it easier to debug
 * your PHP scripts.
 *
 * @author Evan Wills <evan.wills@acu.edu.au>
 * @version 1.0
 * @package Debug
 */


/**
 * debug() displays the file name and line number it is being called
 * from (relative to the original script), a user defined message and
 * (if supplied) the contents of an array.
 *
 * @param $msg string (but accepts arrays)
 * @param $arr array show the contents of the array
 * @param $max_times integer maximum number of times debug executes
 * @param $force_die kills the script when the maximum number if executions has been reached.
 */
function debug( $msg = '' , $arr = array() , $max_times = 0 , $force_die = false )
{
	debug_conf();
	$output = '';
	$included = debug_backtrace();
	$debug_line = $included[0]['line'];
	$debug_file = $included[0]['file'];
	unset($included);


	// ==================================================================
	// START: debug__max_times sub function

//	$continue = debug__max_times( $max_times , $debug_line , $debug_file );
	if(!is_numeric($max_times))
	{
		$max_times = 0;
	}
	elseif($max_times > 100)
	{
		$max_times = 100;
	};
	$time_id = preg_replace('/[^a-z0-9]+/i','_',$debug_file.'_'.$debug_line);
	if( $max_times > 0 )
	{
		if( !defined('DEBUG__DO_'.$time_id.'1') )
		{
			while($max_times > 0)
			{
				if(!defined('DEBUG__DO_'.$time_id.'_'.$max_times))
				{
					define('DEBUG__DO_'.$time_id.'_'.$max_times,TRUE);
					$continue = true;
//					return true;
				};
				--$max_times;
			};
		}
		else
		{
			$continue = false;
//			return false;
		};
	}
	else
	{
		$continue = true;
//		return true;
	};

	// END: debug__max_times sub function
	// ==================================================================

	if( ( DEBUG__STATE === true && $continue === true)
	{
		if(is_array($msg))
		{
			$arr = $msg;
			$msg = '';
		};
		if(!empty($arr))
		{
			$arr = '[[line]]'.htmlspecialchars(print_r($arr,true));
		}
		else
		{
			$arr = '';
		};

		// ==================================================================
		// START: debug__wrap_text sub function

//		$output = debug__wrap_text( $msg.$arr , $debug_line , $debug_file );
		$date = '';
		$time = '';
		$timestamp = debug__time_adjust();

		if( DEBUG__SHOW_DATE === true )
		{
			$date = date(' Y-m-d',$timestamp);
		};
		if( DEBUG__SHOW_TIME == true )
		{
			$time = date(' H:i',$timestamp);
		};
		$meta = str_replace(
			 array( '[[LINE_NUMBER]]','[[CURRENT_FILE]]','[[DATE]]','[[TIME]]')
			,array($included[0]['line'],$included[0]['file'],$date,$time)
			,DEBUG__MSG_META
		);
		$meta_length = strlen($meta);
		if($meta_length > DEBUG__META_MAX_LENGHT)
		{
			$meta_lentgh = 10;
			$meta .= '[[LINE]]'
		};
		$leading_space = '';
		for($a = $meta_length ; $a > 0 ; --$a )
		{
			$leading_space .= ' ';
		};
		$break = str_replace('[[LEADING_SPACE]]',$leading_space,DEBUG__MSG_BREAK);
	
//		$msg = str_ireplace('[[LINE]]',$break,$msg);
	
		$output = str_ireplace(array('[[META]]','[[DEBUG__MESSAGE]]','[[LINE]]'),array($meta,$msg,$break),DEBUG__MSG_WRAPPER);
	
		unset( $date , $time , $timestamp , $meta , $meta_length , $leading_space , $break );
		switch(DEBUG__MODE)
		{
			case 'log_clean':
			case 'log_append':
				debug__log($output);
				$output = '';
				break;
			case 'return':
				break;
			default:
				echo $output;
				$output = '';
				break;
		};

		// END: debug__wrap_text sub function
		// ==================================================================

		// ==================================================================
		// START: debug__log sub function

//		debug__log($output);
		if(DEBUG__MODE == 'log_clean' || DEBUG__MODE == 'log_append' )
		{
			if(DEBUG__FORMAT == 'html')
			{
				$log_content = file_get_contents(DEBUG__LOG_FILE);
				$output = str_ireplace('</body>',"\n$input\n\t</body>",
				file_put_contents(DEBUG__LOG_FILE,$output);
			}
			else
			{
				file_put_contents(DEBUG__LOG_FILE,$output,FILE_APPEND);
			};
		};

		// END: debug__log sub function
		// ==================================================================

		// ==================================================================
		// START: debug__die sub function

//		debug__die( $max_times , $force_die , $debug_line , $debug_file );
		if(defined('DEBUG__DO_1') && $force_die === true)
		{
			if($max_times > 1)
			{
				$suffix = 's';
			}
			else
			{
				$suffix = '';
			};
			die('debug() has killed your script after '.$max_times.' time'.$suffix.'. (at your request)');
		};

		// END: debug__die sub function
		// ==================================================================

	};
	if(!empty($output))
	{
		return $output;
	};
};

/**
 * @function debug_conf() defines configuration constants used by debug() and its sub functions
 * @param $varX string
 */
function debug_conf( $var1='' , $var2='' , $var3='' , $var4='' , $var5='' , $var6='' , $var7='' , $var8='' , $var9 , $var10 )
{
	if( !defined('DEBUG__CONF') )
	{
		$enviro_tmp = array( $var1 , $var2 , $var3 , $var4 , $var5 , $var6 , $var7 , $var8 , $var9 , $var10 );

		error_reporting( E_ALL | E_STRICT );

		$status = true;
		$show_file = true;
		$show_date = false;
		$show_time = false;
		$format = 'html'; // html, text, comment, log
		$mode = 'echo'; // echo, return, log
		$full_path = false;
		$log_file = '';
		$root_path = '';
		$time_adjust = 0;
		$meta_max_lenght = 40;

		foreach($enviro_tmp as $setting)
		{
			$setting = strtoupper($setting);
			switch($setting)
			{
				case 'DEBUG__OFF':
					$status = false;
					break;
				case 'HIDE_FILE':
					$show_file = false;
					break;
				case 'TEXT':
				case 'TXT':
					$format = 'text';
					break;
				case 'COMMENT':
					$format = 'comment';
					break;
				case 'RETURN'
					$mode = 'return';
				case 'LOG':
					$mode = 'log_clean';
					break;
				case 'APPEND':
					$mode = 'log_append';
				case 'FULL_PATH':
					$full_path = true;
					break;
				case 'TIME':
					$show_time = true;
					break;
				case 'DATE':
					$show_date = true;
					break;
				default:if( is_writable($setting) )
					{
						if( is_file($setting) )
						{
							$log_file = $setting;
						}
						elseif( is_dir($setting) )
						{
							preg_match('/[\\/]/',$setting,$slash);
							$setting = preg_replace('/\\'.$slash[0].'$/','',$setting);
							$log_file = $setting.$slash[0].'.__DEBUG__LOG.txt';
						};
					}
					elseif(is_numeric($setting))
					{
						$meta_max_lenght = round($setting);
					}
					else( preg_match('/^time[-_ ]adjust=([0-9]+(?:\.[0-9]{2})?)$/i',$setting,$matches))
					{
						$time_adjust = $matches[1];
					};
					break;
			};
		};

		if(defined(DEBUG__STATUS))
		{
			$status = debug__status_check(DEBUG__STATUS);
			switch(strtolower(DEBUG__STATUS))
			{
				case true:
				case 'on':
				case 'debug':
				case 'dev':
				case 'test':
				case 'testing':
					$status = true;
					break;
				case 'get':
				case 'request':
				case 'live':
					if(isset($_GET['debug']))
					{
						if( $_GET['debug'] == 'true' || $_GET['debug'] == 'debug'] ) )
						{
							$status = true;
						}
						else
						{
							$status = false;
						};
					};
					break;
				case false:
				case 'off':
					$status = false;
					break;

			};
		};

		if( $format == 'log' && empty($log_file) )
		{
			$format = 'comment';
			$mode = 'echo';
		};
		if(isset($_SERVER['SCRIPT_FILENAME'])) // running via the web
		{
			$root_path = preg_replace('/\/[^\/]+$/','/',$_SERVER['SCRIPT_FILENAME']);
			debug__define('DEBUG__FORMAT','html',$format);	
		}
		elseif(isset($_SERVER['PWD'])) // running via the command line
		{
			$root_path = $_SERVER['PWD'].'/';
			debug__define('DEBUG__FORMAT','text',$format);	
		}
		else
		{
			debug__define('DEBUG__FORMAT','html',$format);
		};
		debug__define('DEBUG__STATE',$status);
		debug__define('DEBUG__FULL_PATH',$full_path);
		debug__define('DEBUG__SHOW_FILE',$show_file);
		debug__define('DEBUG__SHOW_TIME',$show_time);
		debug__define('DEBUG__SHOW_DATE',$show_date);
		debug__define('DEBUG__MODE',$mode);
		debug__define('DEBUG__ROOT_PATH',$root_path);
		debug__define('DEBUG__META_MAX_LENGHT',$meta_max_lenght);
		debug__define('DEBUG__TIME_ADJUST',$time_adjust);

		// ==================================================================
		// START: debug__initialise_txt_wrapper()
//		debug__initialise_txt_wrapper();
		$current_file = '';
		$open = '';
		$close = '';
		$break = "\n[[LEADING_SPACE]]";
		$meta = '';

		if(!DEBUG__SHOW_FILE)
		{
			$current_file = '';
		}
		else
		{
			$current_file = DEBUG__ROOT_PATH.'[[CURRENT_FILE]]';;
		};

		if(empty($current_file))
		{
			$meta = 'Line [[LINE_NUMBER]] ';
		}
		else
		{
			$meta = $current_file.' [[LINE_NUMBER]]';
		};
		
		if( DEBUG__SHOW_DATE === true )
		{
			$meta .= ' [[DATE]]';
		};
		if( DEBUG__SHOW_TIME == true )
		{
			$meta .= ' [[TIME]]';
		};
		$meta = '('.$meta.')';

		switch(DEBUG__FORMAT)
		{
			case 'text':
				$open = "\n-----------------------------------------------------------\n[[META]] ";
				$close = "\n";
				break;
			case 'comment':
				$open = "\n<!--\n[[META]]\n";
				$close = "\n-->\n";
				break;
			case 'html':
				$open = "\n<pre class=\"debug-msg\"><strong>[[META]]</strong> ";
				$close = "</pre>\n";
				$break = "<br />\n[[LEADING_SPACE]]";
				break;
		};

		debug__define('DEBUG__MSG_WRAPPER',$open.'[[DEBUG__MESSAGE]]'.$close);
		debug__define('DEBUG__MSG_BREAK',$break);
		debug__define('DEBUG__MSG_META',$meta);

		// END: debug_intialise_txt_wrapper()
		// ==================================================================

		if( $mode == 'log' )
		{
			// ==================================================================
			// START: debug__log_initialise sub function
//			debug__log_initialise($log_file);
			$included = debug_backtrace();
			$debug_file = $included[0]['file'];
			$timestamp = debug__time_adjust();
			$heading = 'Debug output for '.$debug_file.' ('.date('Y-m-d H:i',$timestamp).')';
			debug__define('DEBUG__LOG_FILE',$log_file);

			switch(DEBUG__MODE)
			{
				case 'log_clean':
					switch(DEBUG__FORMAT)
					{
						case 'html':
							$content = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>'.$heading.'</title>
		
		<link rel="StyleSheet" href="comp_winner.css" type="text/css" media="all" />
	</head>
	<body>
		<h1>'.$heading.'</h1>
	</body>
</html>
';
							break;
						default:
							$content = "====================================================================\n$heading\n---------------------------------------------------------------------";
							break;
					
					};

					break;
				case 'log_append':
					$content = file_get_contents($log_file);
					switch(DEBUG__FORMAT)
					{
						case 'html':
							$contents = str_ireplace('</body>',"\t<hr />\n\t\t<h1>$heading</h2>\n\t</body>",$content);
							break;
						default:
							$content .= "\n\n====================================================================\n$heading\n---------------------------------------------------------------------\n";
							break;
					};
			};
			file_put_contents(DEBUG__LOG_FILE,$contents);
			// END: debug__log_initialise sub function
			// ==================================================================
		};
		define('DEBUG__CONF',true);
	};
};

/**
 * debug__define() checks if a constant has been defined and
 * then, if not, defines it.
 *
 * $constant_name =	string
 * $default_value =	mixed var (usually boolean)
 * $user_value =	mixed var (usually boolean)
 * $case_insensitive =	boolean
 *
 * returns true if function defines constant or
 *         false if the constant is already defined.
 */
function debug__define($constant_name , $default_value , $user_value = '' , $case_insensitive = false)
{
	if(!defined($constant_name))
	{
		if($default_value === true)
		{
			$other_value = false;
		}
		else
		{
			$other_value = true;
		};
		
		if($case_insensitive !== false)
		{
			$case_insensitive = true;
		};

		if($user_value !== $other_value)
		{
			define($constant_name , $default_value , $case_insensitive);
		}
		else
		{
			define($constant_name , $other_value , $case_insensitive);
		};
		return true;
	}
	else
	{
		return false;
	};
};

/**
 * @fuction debug__initialise_txt_wrapper() sets up the appropariate strings to wrap
 * various bits of the debug output in.
 *
 * defines three constants: 
 *	'DEBUG__MSG_WRAPPER' - the whole debug message wrapper
 *	'DEBUG__MSG_BREAK'  -  line break replacement
 *	'DEBUG__MSG_META'   -  line number and file name replacement string.
 * 
 * These constants will be used by debut__wrap_text()
 */
function debug__initialise_txt_wrapper()
{
	$current_file = '';
	$open = '';
	$close = '';
	$break = "\n[[LEADING_SPACE]]";
	$meta = '';

	if(!DEBUG__SHOW_FILE)
	{
		$current_file = '';
	}
	else
	{
		$current_file = DEBUG__ROOT_PATH.'[[CURRENT_FILE]]';;
	};
	if(empty($current_file))
	{
		$meta = 'Line [[LINE_NUMBER]] ';
	}
	else
	{
		$meta = $current_file.' [[LINE_NUMBER]]';
	}
	if( DEBUG__SHOW_DATE === true )
	{
		$meta .= ' [[DATE]]';
	};
	if( DEBUG__SHOW_TIME == true )
	{
		$meta .= ' [[TIME]]';
	};
	$meta = '('.$meta.')';

	switch(DEBUG__FORMAT)
	{
		case 'text':
			$open = "\n-----------------------------------------------------------\n[[META]] ";
			$close = "\n";
			break;
		case 'comment':
			$open = "\n<!--\n[[META]]\n";
			$close = "\n-->\n";
			break;
		case 'html':
			$open = "\n<pre class=\"debug-msg\"><strong>[[META]]</strong> ";
			$close = "</pre>\n";
			$break = "<br />\n[[LEADING_SPACE]]";
			break;
	};

	debug__define('DEBUG__MSG_WRAPPER',$open.'[[DEBUG__MESSAGE]]'.$close);
	debug__define('DEBUG__MSG_BREAK',$break);
	debug__define('DEBUG__MSG_META',$meta);

};


function debug__time_adjust()
{
	$timestamp = 0;
	if( DEBUG__TIME_ADJUST > 0 )
	{
		if( DEBUG__TIME_ADJUST < 13 )
		{
			$timestamp = ($time() + ( 3600 * DEBUG__TIME_ADJUST ));
		}
		else
		{
			$timestamp = ($time() + DEBUG__TIME_ADJUST );
		};
	};
	return $timestamp;
};

function debug__log_initialise( $log_file )
{
	$included = debug_backtrace();
	$debug_file = $included[0]['file'];
	$timestamp = debug__time_adjust();
	$heading = 'Debug output for '.$debug_file.' ('.date('Y-m-d H:i',$timestamp).')';
	debug__define('DEBUG__LOG_FILE',$log_file);

	switch(DEBUG__MODE)
	{
		case 'log_clean':
			switch(DEBUG__FORMAT)
			{
				case 'html':
					$content = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>'.$heading.'</title>
		
		<link rel="StyleSheet" href="comp_winner.css" type="text/css" media="all" />
	</head>
	<body>
		<h1>'.$heading.'</h1>
	</body>
</html>
';
					break;
				default:
					$content = "====================================================================\n$heading\n---------------------------------------------------------------------";
					break;
					
			};

			break;
		case 'log_append':
			$content = file_get_contents($log_file);
			switch(DEBUG__FORMAT)
			{
				case 'html':
					$contents = str_ireplace('</body>',"\t<hr />\n\t\t<h1>$heading</h2>\n\t</body>",$content);
					break;
				default:
					$content .= "\n\n====================================================================\n$heading\n---------------------------------------------------------------------\n";
					break;
			};
	};
	file_put_contents(DEBUG__LOG_FILE,$contents);
};



/**
 * debug__info() defines debug config defaults based on values from debug.info file
 * makes it easier to config debug
 */
function debug__info()
{
	global $status , $show_file , $show_date , $show_time , $format , $mode , $full_path , $log_file , $time_adjust , $meta_max_lenght;

	if(file_exists('debug.info'))
	{
		$info = file_get_contents('debug.info');
		$info_regex = '/(?<=[\r\n]|^)([^a-z_]+)[\t\ ]+:[\t\ ]+([a-z]+|-?[0-9](?:\.[0-9]+)?)(?=.*;.*[\r\n])/isU';
		preg_match_all($info_regex,$info,$matches);
		if(!empty($matches))
		{
			foreach($matches as $conf)
			{
				$key = strtolower($conf[1]);
				$value = strtolower($conf[2]);
				switch($key)
				{
					case 'status':
						$$key = debug__status_check($value);
						break;
					case 'show_file':
					case 'show_date':
					case 'show_time':
					case 'full_path':
						switch($value)
						{
							case 1:
							case '1':
							case true:
							case 'true':
							case 'on':
								$$key = true;
								break;
							case 0:
							case '0':
							case false:
							case 'false':
							case 'off':
								$$key = false;
								break;
						};
						break;
					case 'format':
						switch($value)
						{
							case 'comment':
							case 'htm':
							case 'html':
							case 'log':
							case 'text':
							case 'txt':
							case 'xhtml':
								$$key = $value;
								break;
						};
						break;
					case 'mode':
						switch($value)
						{
							case 'echo':
							case 'log':
							case 'return':
								$$key = $value;
								break;
						};
						break;
					case 'log_file':
						if(is_file($conf[2]) && is_readable($conf[2]) && is_writable($conf[2]))
						{
							$$key = $conf[2];
						};
						break;
					case 'time_adjust':
					case 'meta_max_lenght':
						if(is_numeric($value))
							$$key = $conf[2];
						};
						break;

				};
			};
		};
	};
};

/**
 * debug__status_check() checks the valuse of $test_status against
 * allowable parameters and returns true or false accordingly.
 *
 * @param $test_status mixed (string or boolean)
 * @return $status boolean
 */
function debug__status_check($test_status)
{
	switch(strtolower($test_status))
	{
		case true:
		case 'on':
		case 'debug':
		case 'dev':
		case 'test':
		case 'testing':
			$status = true;
			break;
		case 'get':
		case 'request':
		case 'live':
			if(isset($_GET['debug']))
			{
				if( $_GET['debug'] == 'true' || $_GET['debug'] == 'debug'] ) )
				{
					$status = true;
				}
				else
				{
					$status = false;
				};
			};
			break;
		case false:
		case 'off':
			$status = false;
			break;
	};
	return $status;
};


// ==================================================================
// The following functions have been commented in favour of
// incorporating them into the function they are called in.
//
// Although it is cleaner to code them as separate functions it's
// simpler to code them within the main function as they are only
// called once.

/**
 * debug__max_times() is defining how many times the debug() function
 * gets called in script before it stops outputting debug messages
 *
 * @param $max_times integer The maximum number of times this
 *        function can be called.
 * @return boolean true if debugging should be shown, false if not
 */
/*
function debug__max_times($max_times,$debug_line,$debug_file)
{
	if(!is_numeric($max_times))
	{
		$max_times = 0;
	}
	elseif($max_times > 100)
	{
		$max_times = 100;
	};
	$time_id = preg_replace('/[^a-z0-9]+/i','_',$debug_file.'_'.$debug_line);
	if( $max_times > 0 )
	{
		if( !defined('DEBUG__DO_'.$time_id.'1') )
		{
			while($max_times > 0)
			{
				if(!defined('DEBUG__DO_'.$time_id.'_'.$max_times))
				{
					define('DEBUG__DO_'.$time_id.'_'.$max_times,TRUE);
//					$continue = true;
					return true;
				};
				--$max_times;
			};
		}
		else
		{
//			$continue = false;
			return false;
		};
	}
	else
	{
//		$continue = true;
		return true;
	};
};
*/



/**
 * debug__die() kills your script after debug() has been called a
 * specified number of times
 *
 * When $max_times has been exhausted, and force_die is true, this
 * kills the whole script. This is useful when trying to debug a
 * script with an infinite loop.
 * 
 * @param $max_times integer number of times debug should run
 * @param $force_die boolean kill the script or not.
 */
/*
function debug__die( $max_times , $force_die , $debug_line , $debug_file)
{
	if(defined('DEBUG__DO_1') && $force_die === true)
	{
		if($max_times > 1)
		{
			$suffix = 's';
		}
		else
		{
			$suffix = '';
		};
		die('debug() has killed your script after '.$max_times.' time'.$suffix.'. (at your request)');
	};
};
*/


/**
 * debug__log() writes debug output to log file
 *
 * @param $input string debugging info to be added to the log file.
 */
/*
function debug__log($input)
{
	if(DEBUG__MODE == 'log_clean' || DEBUG__MODE == 'log_append' )
	{
		if(DEBUG__FORMAT == 'html')
		{
			$log_content = file_get_contents(DEBUG__LOG_FILE);
			$output = str_ireplace('</body>',"\n$input\n\t</body>",
			file_put_contents(DEBUG__LOG_FILE,$output);
		}
		else
		{
			file_put_contents(DEBUG__LOG_FILE,$output,FILE_APPEND);
		};
	};
};
*/
/**
 * debug__wrap_text() applies formatting to the debug message.
 *
 * @param $msg string debug message to be displayed or logged
 *
 * @const DEBUG__MSG_WRAPPER string that will be used to format the
 *        whole message
 * @const DEBUG__MSG_META strng that will contain the file name and
 *        line number debug was called from.
 * @const DEBUG__MSG_BREAK string that will be used to substitute
 *        line break and leading white space
 */
/*
function debug__wrap_text( $msg , $debug_line , $debug_file )
{
	$date = '';
	$time = '';
	$timestamp = debug__time_adjust();

	if( DEBUG__SHOW_DATE === true )
	{
		$date = date(' Y-m-d',$timestamp);
	};
	if( DEBUG__SHOW_TIME == true )
	{
		$time = date(' H:i',$timestamp);
	};
	$meta = str_replace(
		 array( '[[LINE_NUMBER]]','[[CURRENT_FILE]]','[[DATE]]','[[TIME]]')
		,array($included[0]['line'],$included[0]['file'],$date,$time)
		,DEBUG__MSG_META
	);
	$meta_length = strlen($meta);
	if($meta_length > DEBUG__META_MAX_LENGHT)
	{
		$meta_lentgh = 10;
		$meta .= '[[LINE]]'
	};
	$leading_space = '';
	for($a = $meta_length ; $a > 0 ; --$a )
	{
		$leading_space .= ' ';
	};
	$break = str_replace('[[LEADING_SPACE]]',$leading_space,DEBUG__MSG_BREAK);

//	$msg = str_ireplace('[[LINE]]',$break,$msg);
	
	$output = str_ireplace(array('[[META]]','[[DEBUG__MESSAGE]]','[[LINE]]'),array($meta,$msg,$break),DEBUG__MSG_WRAPPER);

	unset( $date , $time , $timestamp , $meta , $meta_length , $leading_space , $break );
	switch(DEBUG__MODE)
	{
		case 'log_clean':
		case 'log_append':
			debug__log($output);
			break;
		case 'return':
			return $output;
			break;
		default:
			echo $output;
			break;
	};
	return '';
};
*/
