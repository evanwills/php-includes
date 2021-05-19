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

/**
 * @function build_file_list() recursively reads through a given
 * directory tree and returns an array of files that match various
 * criteria.
 * By default build_file_list()
 *	-	assumes the second parameter is a file extention 
 *	-	is case insensitive
 *	-	excludes hidden files and directories
 *	but all of these can be over ridden (see extras below).
 *
 * @param $start_location string path to a directory to be searched
 *        (can be absolute or relative)
 * @param $file_parts string substring that must be matched in order
 *        for the file to be included.
 *        Multiple parts can be supplied separated by commas.
 *        e.g. txt,html would match files containing 'txt' and/or
 *             'html
 *        If a comma is required within a part it must be escaped
 *        with a back slash
 *        e.g. train\,engine,bus,truck would match files containing
 *             'train,engine' and/or bus and/or 'truck'
 *        Unescaped white space is removed from begining and end of
 *        each part
 *        e.g. 'train, car, bike, \ wheel\ ' would match files
 *             containing, 'train' and/or 'car' and/or 'bike' and/or
 *             ' wheel '
 * @param FLAGS string the following values can be passed in any
 *        order
 *	  'extension' [DEFAULT] match the file extension
 *		  NOTE: DO NOT include the first full stop
 *			e.g.	'.inc.php' use 'inc.php'
 *				'.html' use 'html'
 *        'prefix' match the file part if it is at the begininga of
 *              the file name
 *        'suffix' match the file part if it at the end of the file
 *              name but before the file extension
 *        'anywhere' check if the file part is anywhere in the file
 *              name string
 *	  'whole' check if the whole file part exactly matches the
 *		file name string
 *
 *        NOTE: It doesn't make sense to pass more than one of the
 *		above but if you do the last one will be the one used
 *
 *       'sensitive' forces the matching process to be case sensitive
 *       NOTE: it will also accept 'case sensitive', 'case_sensitive',
 *            'case-sensitive' or 'casesensitive'
 *
 *       FALSE for backwards compatibility, FALSE has the same effect
 *             as 'sensitive', 'case sensitive' etc
 *
 *       'hidden' forces build_file_list() to include hidden files
 *       NOTE: it will also accept: 'include hidden', 'includehidden',
 *             'include_hidden' or 'include-hidden'
 *
 *       'exclude=[FOO]' exclude files or subdirectories where FOO is
 *             the file name or directory name to be excluded
 *       NOTE: Like $file_parts multiple excludes can be passed at
 *             once separated by an unescaped comma or 'exclude=[foo]'
 *             can be passed multiple times.
 *             Also like $file_parts, unescaped white space is
 *             stripped from the begining and end of the string.
 *
 * @return array two dimensional e.g.
 *		array(
 *			 0 => array( 'path' => [path to file but not file itself] , 'file' => [file name] )
 *			,1 => array( 'path' => [path to file but not file itself] , 'file' => [file name] )
 *			,2 => array( 'path' => [path to file but not file itself] , 'file' => [file name] )
 *			,3 => array( 'path' => [path to file but not file itself] , 'file' => [file name] )
 *			,4 => array( 'path' => [path to file but not file itself] , 'file' => [file name] )
 *			,5 => array( 'path' => [path to file but not file itself] , 'file' => [file name] )
 *			,6 => array( 'path' => [path to file but not file itself] , 'file' => [file name] )
 *		)
 */
function build_file_list()
{
	$retrieved_args = func_get_args();
	$start_location = $retrieved_args[0];
	$find_string = $retrieved_args[1];
	$file_name_parts = array();
	build_file_list__explode($retrieved_args[1],$file_name_parts);
	$part_pos = 'extension';
	$insensitive = true;
	$exclude_hidden = true;
	$exclude = array();
	$args = func_num_args();
	$extra_args = array();
	$error = '';
	if( $args > 2 )
	{
		if(is_array($retrieved_args[2]) && !empty($retrieved_args[2]))
		{
			$extra_args = $retrieved_args[2][0];
		}
		else
		{
			for( $a = 2 ; $a < count($retrieved_args) ; ++$a )
			{
				if( !empty($retrieved_args[$a]) ) 
				{
					$extra_args[] = $retrieved_args[$a];
				};
			};
		};

		if( is_string($extra_args) )
		{
			$extra_args = array($extra_args);
		};

		for( $a = 0 ; $a < count($extra_args) ; ++$a )
		{
			$this_arg = strtolower($extra_args[$a]);
			switch($this_arg)
			{
				case 'extension':
				case 'prefix':
				case 'suffix':
				case 'anywhere':
				case 'whole':
					$part_pos = $this_arg;
					break;
				case 'sensitive':
				case 'casesensitive':
				case 'case-sensitive':
				case 'case_sensitive':
				case 'case_sensitive':
					$insensitive = false;
					break;
				case 'hidden':
				case 'includehidden':
				case 'include hidden':
				case 'include_hidden':
				case 'include-hidden':
					$exclude_hidden = false;
				default:
					if($this_arg === false)
					{
						$insensitive = false;
					}
					elseif( strlen($this_arg) > 8 && substr_compare( $this_arg , 'exclude=' , 0 , 8 ) == 0 )
					{
						build_file_list_explode(str_replace( 'exclude=' , '' , $extra_args[$a] ),$exclude);
					};
			};
		};
	};

	if( $insensitive === true )
	{
		if(!empty($this->exclude))
		{
			for( $a = 0 ; $a < count($this->exclude) ; ++$a )
			{
				$this->exclude[$a] = strtolower($this->exclude[$a]);
			};
		};
		foreach( $file_name_parts as $key => $value )
		{
			$file_name_parts[$key] = strtolower($value);
		};
	};

	$file_list = array();
	if(is_dir($start_location))
	{
		if(substr_compare($start_location , '/' , -1 , 1 ) != 0 )
		{
			$start_location .= '/';
		};
		$dir_array = scandir($start_location);
		for( $a = 0 ; $a < count($dir_array) ; ++$a )
		{
			$this_file = $dir_array[$a];
			if( build_file_list__ok( $this_file , $insensitive , $exclude_hidden , $exclude ) === true)
			{
				if(is_dir($start_location.$this_file))
				{
					$returned_files = build_file_list( $start_location.$this_file , $retrieved_args[1] , $extra_args );
					if(is_array($returned_files))
					{
						foreach($returned_files as $returned)
						{
							if(is_array($returned) && isset($returned['path']))
							{
								$file_list[] = $returned;
							};
						};
					}
					elseif(!empty($returned_files))
					{
						$error = make_error($start_location , $retrieved_args[1] , $part_pos );
					};
				}
				else
				{
					if( build_file_list__include( $this_file , $insensitive , $file_name_parts , $part_pos ) === true )
					{
						$file_list[] =  array(
							 'path' => $start_location
							,'file' => $this_file
						);
					};
				};
			};
		};
		if(empty($file_list))
		{
			if(!empty($error))
			{
				return $error;
			}
			else
			{
				return make_error($start_location , $retrieved_args[1] , $part_pos );
			};
		}
		else
		{
			return $file_list;
		};
	}
	else
	{
		return "$start_location is not a valid direcotry\n";
	};
};

/**
 * @function build_file_list__ok() is the primary test to see if a
 * given file should be processed
 *
 * @param $this_file string file name
 * @param $insensitive boolean case sensitive or case insensitive
 * @param $exclude_hidden boolean include or exclude hidden files
 * @param $exclude array indexed array of files to omit.
 *        NOTE: exclude items compared against the whole $this_file
 *              string.
 *
 * @return boolean true if file should be included false otherwise
 */
function build_file_list__ok( $this_file , $insensitive , $exclude_hidden , $exclude )
{
	if( $this_file == '.' || $this_file == '..' || ( $exclude_hidden === true && substr_compare($this_file , '.' , 0 , 1) == 0 ) )
	{
		return false;
	};
	if( !empty($exclude) )
	{
		if($insensitive === true)
		{
			$this_file = strtolower($this_file);
		};
		foreach($exclude as $ex)
		{
			if($this_file == $ex)
			{
				return false;
			};
		};
	};
	return true;
};


/**
 * @function build_file_list__include() checks whether a give file
 * should be included.
 *
 * @param $this_file string file name
 * @param $insensitive boolean case sensitive or case insensitive
 * @param $file_name_parts array indexed array of name parts to be
 *        matches
 * @param $parts_pos string 'extension' 'prefix' 'suffix' 'anywhere'
 *
 * @return boolean true if file should be included false otherwise
 */
function build_file_list__include( $this_file , $insensitive , $file_name_parts , $part_pos )
{
	if($insensitive === true)
	{
		$this_file = strtolower($this_file);
	};
	foreach($file_name_parts as $part)
	{
		$func_name = "build_file_list__$part_pos";
		if( $func_name( $this_file , $part ) === true )
		{
			return true;
		};
	};
	return false;
};


/**
 * @function build_file_list__extension() checks if a given file name 
 * matches the supplied file extension.
 *
 * @param name string file name to test
 * @param part string string to find
 *
 * @return boolean true if string is present. false otherwise
 */
function build_file_list__extension( $name , $part )
{
	$ln = strlen(".$part");
	if( strlen($name) > $ln && substr_compare( $name , ".$part" , -$ln , $ln ) == 0 )
	{
		return true;
	};
	return false;
};


/**
 * @function build_file_list__prefix() checks if a given string 
 * is present at the start of a given file name.
 *
 * @param name string file name to test
 * @param part string string to find
 *
 * @return boolean true if string is present. false otherwise
 */
function build_file_list__prefix( $name , $part )
{
	$ln = strlen($part);
	if( strlen($name) > $ln && substr_compare( $name , $part , 0 , $ln ) == 0 )
	{
		return true;
	};
	return false;
};


/**
 * @function build_file_list__suffix() checks if a given string 
 * is present immediately precceding the file extension within a
 * given file name.
 *
 * @param name string file name to test
 * @param part string string to find
 *
 * @return boolean true if string is present. false otherwise
 */
function build_file_list__suffix( $name , $part )
{
	$name_arr = explode('.' , $name);
	$ln = strlen($part);
	if( strlen($name_arr[0]) > $ln && substr_compare( $name_arr[0] , $part , -$ln , $ln ) == 0 )
	{
		return true;
	};
	return false;
};


/**
 * @function build_file_list__anywhere() checks if a given string 
 * is present within a given file name.
 *
 * @param name string file name to test
 * @param part string string to find
 *
 * @return boolean true if string is present. false otherwise
 */
function build_file_list__anywhere( $name , $part )
{
	if( substr_count( $name , $part ) > 0 )
	{
		return true;
	};
	return false;
};


/**
 * @function build_file_list__whole() checks if a given string 
 * exactly matches a given file name.
 *
 * @param name string file name to test
 * @param part string string to find
 *
 * @return boolean true if string exactly matches. false otherwise
 */
function build_file_list__whole( $name , $part )
{
	if( $name == $part )
	{
		return true;
	};
	return false;
};


/**
 * @function build_file_list__explode() explodes a given string on
 * unescaped commas ',' it then strips unescaped commas and white
 * space from the begining and end of the string
 *
 * @param $input string containing parts to be exploded
 * @param $update the array to be updated.
 */
function build_file_list__explode($input,&$update)
{
	$throughput = preg_split( '/(?<!\\\\),/' , $input );
	for( $a = 0 ; $a < count($throughput) ; ++$a )
	{
		$throughput[$a]= str_replace('\,',',',preg_replace( '/(^[ \t\r\n]\+|(?<!\\\\)[\r\n\t ]+$)/s','',$throughput[$a]));
		if($throughput[$a] != '')
		{
			$update[] = $throughput[$a];
		};
	};
};

/**
 * @function make_error() generates an error string appropriate to
 * where the matching string is to be found in a file name
 *
 * @param $start_location string the start location currently being
 *	  searched
 * @param $file_part string the string a file must contain
 * @param $part_pos where the string is to be found
 *
 * @return string error message
 */
function make_error($start_location,$file_part,$part_pos)
{
	switch($part_pos)
	{
		case 'extension':
			$error = "with the extension \"$file_part\"";
			break;
		case 'prefix':
			$error = "starting with \"$file_part\"";
			break;
		case 'suffix':
			$error = "where the name part of the file ends with the string \"$file_part\"";
			break;
		case 'anywhere':
			$error = "that contain the string \"$file_part\"";
			break;
		case 'whole':
			$error = "mathing \"$file_part\"";
			break;
	};
	return wordwrap("$start_location has no files $error",70)."\n";
};

/**
 * ==================================================================
 * The following code allows the above functions to be used to find
 * files on your local system when you don't have something like
 * 'locate' (as is the case with one of our work servers)
 *
 * It only get run if this file is run directly and is passed at
 * least two additional parameter (other than the script name)
 *
 * The paramters are as follows:
 *	[0] -	this file's name (required)
 *	[1] -	the path to be searched (required)
 *	[3] -	the string to match files against (required)
 *	[4] -	the part of the file name the string is to be
 *		found. (optional)
 */

if( isset($_SERVER['argc']) && $_SERVER['argc'] > 2)
{
	$file_path = $_SERVER['argv'][1];
	$file_part = $_SERVER['argv'][2];
	$part_pos = '';
	if($_SERVER['argc'] > 3)
	{
		$part_pos = $_SERVER['argv'][3];
	};
	$list_ = build_file_list($file_path,$file_part,$part_pos);

	if( !empty($list_) )
	{
		if( is_array($list_) )
		{
			for($a = 0 ; $a < count($list_) ; ++$a )
			{
				if(isset($list_[$a]['path']) && isset($list_[$a]['file']))
				{
					echo "\n{$list_[$a]['path']}{$list_[$a]['file']}";
				};
			};
		}
		else
		{
			echo "\n$list_";
		};

		echo "\n";
	};
};

