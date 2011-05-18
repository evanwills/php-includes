<?php

/**
 * set_pwd() defines the PATH_TO_WORKING_DIR constant and returns it.
 *
 * @param void
 * @return string path to current working directory (the directory
 *         the intitial script was called from)
 */
function set_pwd()
{
	if(!defined('PATH_TO_WORKING_DIR'))
	{
		if(isset($_SERVER['PWD']))
		{
			$pwd = $_SERVER['PWD'].'/';
		}
		elseif(isset($_SERVER['SCRIPT_FILENAME']))
		{
			$pwd = preg_replace('/(?<=\/)[^\/]+$/','',$_SERVER['SCRIPT_FILENAME']);
		};
		if(!empty($pwd))
		{
			define('PATH_TO_WORKING_DIR',$pwd);
		};
		return $pwd;
	}
	else
	{
		return PATH_TO_WORKING_DIR;
	};
};


/**
 * BUILD_FILE_LIST() recursively reads through directories finding
 * any files matched by their extention and adding them to a list
 * (array) for later handling.
 *
 * @param $start_location string a directory containing the files you
 * might want or sub-directories containing those files
 *
 * @param $file_name_part string matching the part of the name of
 * appropriate file. (do NOT include the preceeding stop.) To list all files in a directory (and sub directories) 'BUILD_FILE_LIST_ALL'
 *
 * @param $name_part_pos string identifies which part of the file
 * name is to be matches.
 *	'extension' (default) match file by extension type
 *	'prefix' match file by the first part of the file name
 *	'suffix' match by the last part of the file name (but before the file extension)
 *      'anywhere' match the string anywhere in the file name
 *
 * @returns array a two dimensional array:
 *	first dimension equates to a file
 *	second dimension contains
 *	'path' = path to the file
 *	'file' = name of the file
	 */

function build_file_list($start_location , $file_name_part , $name_part_pos = 'extension' , $insensitive = true )
{
	if($insensitive == true)
	{
		$regex_mod = 'i';
	}
	else
	{	
		$regex_mod = '';
	};
	switch($name_part_pos)
	{
		case 'prefix':
		case 'start':
			$file_name_regex = '/^'.$file_name_part.'/'.$regex_mod;
			break;

		case 'suffix':
		case 'end':
			$file_name_regex = '/^[^\.]+?'.$file_name_part.'(?:\..+)?$/'.$regex_mod;
			break;

		case 'anywhere':
			$file_name_regex = '/'.$file_name_part.'/'.$regex_mod;
			break;

		case 'extension':
		default:
			$file_name_regex = '/.*\.'.str_replace( '.' , '\.' , $file_name_part ).'$/U'.$regex_mod;
			break;
	};
	if(!preg_match('/^\//',$start_location))
	{
		$start_location = set_pwd().$start_location;
	};
	if(!preg_match('/\/$/',$start_location))
	{
		$start_location .= '/';
	};

	$file_array = array();
	if( is_string($start_location) && is_dir($start_location) )
	{
		if( !is_readable($start_location) )
		{
			return;
		};
		$start_dir_array = scandir($start_location);
		for( $a = 0 ; $a < count($start_dir_array) ; ++$a )
		{
			$full_path = $start_location.$start_dir_array[$a].'/';
			$file_only = $start_dir_array[$a];
			switch($file_only)
			{
				case '.':
				case './':
				case '..':
				case '../':
					break;
				default:
					if(is_dir($full_path))
					{
						$tmp_file_array =  build_file_list($full_path , $file_name_part , $name_part_pos , $insensitive );
						if(isset($tmp_file_array[0]['path']))
						{
							for( $b = 0 ; $b < count($tmp_file_array) ; ++$b )
							{
								$file_array[] = $tmp_file_array[$b];
							};
						}
						elseif(isset($tmp_file_array['path']))
						{
							$file_array[] = $tmp_file_array;
						};
					}
					else
					{
						if( preg_match($file_name_regex , $file_only ) || $file_name_part == 'BUILD_FILE_LIST_ALL' )
						{
							$file_array[] = array(
								 'path' => $start_location
								,'file' => $file_only
							);
						};
					};
					break;
			};
		};
		return $file_array;
/*	}
	else
	{
		$bk_trc = debug_backtrace();
		die('ERROR: "'.$start_location.'" is not a valid directory.'."\n".'build_file_list() expects first paramet to be a valid directory on line '.$bk_trc[0]['line'].' in '.$bk_trc[0]['file']."\n");
*/	};
};


/**
 * delete_old_files() takes a list of files and deletes files older
 * than the specified number of seconds.
 *
 * @param $files_list mixed (either string or array)
 * @param $older_than integer minimum number of seconds old a file
 *        should be before it is deleted.
 */
function delete_old_files( $files_list , $older_than = 0 )
{
	if( is_string($files_list) && is_file($files_list) && is_writable($files_list) && ((time() - $older_than) > filemtime($files_list) ))
	{
		unlink($files_list);//debug('deleted: '.$files_list);
	}
	elseif(is_array($files_list))
	{
		if(isset($files_list['path']) && isset($files_list['file']))
		{
			delete_old_files($files_list['path'].$files_list['file'] , $older_than);
		}
		else
		{
			foreach($files_list as $file)
			{
				delete_old_files($file , $older_than );
			};
		};
	};
};


/**
 * extract_dot_info() takes the contents of an info file and converts
 * it to a multi dimensional array.
 *
 * @param $info_content string contents of a .info file
 * @return array multi dimensional array
 */
function extract_dot_info($info_content)
{
	$info_array = array();
	if(is_file($info_content) && !preg_match('/debug\.info/i',$info_content))
	{
		$info_content = file_get_contents($info_content);
	};
/*
	$info_regex = '/
(?<=^|[\n\r])
(?:
	([^;\n\r][^\[=:]+)	# $key_value[x][1]
	(?:
		\[
		([^\]]*)	# $key_value[x][2]
		(\])		# $key_value[x][3]
	)?
	(?:
		\[
		([^\]]*)	# $key_value[x][4]
		(\])		# $key_value[x][5]
	)?
	(?:
		\[
		([^\]]*)	# $key_value[x][6]
		(\])		# $key_value[x][7]
	)?
)
[\t ]*
(?:=|:)
(.*)	# $key_value[x][8]
(?:[;\n\r])
/iUx';
*/
	$info_regex = '/
(?<=^|[\r\n])
(
	[^;\n\r][^\[=:]+	# $key_value[x][1] - First dimension
)
(?:
	\[
		(.*?)		# $key_value[x][2] - Second dimension key
	(\])			# $key_value[x][3] - Second dimension test
)?
(?:
	\[
		(.*?)		# $key_value[x][4] - Third dimension key
	(\])			# $key_value[x][5] - Third dimension test
)?
(?:
	\[
		(.*?)		# $key_value[x][6] - Fourth dimension key
	(\])			# $key_value[x][7] - Fourth dimension test
)?
[\t\ ]*
(?:=|:)
(.*?)				# $key_value[x][8] - item value
(?<!\\\\)[;\r\n]
/ix';
	preg_match_all( $info_regex , $info_content , $key_value , PREG_SET_ORDER);	

	foreach($key_value as $info_item)
	{
		$key_0 = prep_key($info_item[1]); // First dimension
		$key_1 = prep_key($info_item[2]); // Second dimension key
		$key_test_1 = $info_item[3];	// Second dimension test
		$key_2 = prep_key($info_item[4]); // Third dimension key
		$key_test_2 = $info_item[5];	// Third dimension test
		$key_3 = prep_key($info_item[6]); // Fourth dimension key
		$key_test_3 = $info_item[7];	// Fourth dimension test
		$value = trim(str_replace('\;',';',$info_item[8]));	// item value

		if( !empty($key_0) && !empty($key_test_1) && !empty($key_test_2) && !empty($key_test_3) )
		{ // All four dimensions are set
			$info_array[$key_0][$key_1][$key_2][$key_3] = $value;
		}
		elseif( !empty($key_0) && !empty($key_test_1) && !empty($key_test_2) && empty($key_test_3) )
		{ // Three out of Four dimensions are set
			$info_array[$key_0][$key_1][$key_2] = $value;
		}
		elseif( !empty($key_0) && empty($key_test_1) && !empty($key_test_2) && !empty($key_test_3) )
		{ // Three out of Four dimensions are set
			$info_array[$key_0][$key_2][$key_3] = $value;
		}
		elseif( !empty($key_0) && !empty($key_test_1) && empty($key_test_2) && empty($key_test_3) )
		{ // Two out of Four dimensions are set
			$info_array[$key_0][$key_1] = $value;
		}
		elseif( !empty($key_0) && empty($key_test_1) && empty($key_test_2) && !empty($key_test_3) )
		{ // Two out of Four dimensions are set
			$info_array[$key_0][$key_3] = $value;
		}
		elseif( !empty($key_0) && empty($key_test_1) && empty($key_test_2) && empty($key_test_3) )
		{ // Only one dimension is set
			$info_array[$key_0] = $value;
		};
	};
	return $info_array;
};


function prep_key($key)
{
	return trim(strtolower(str_replace( ']' , '' , $key )));
};


