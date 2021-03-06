<?php

function prep_key($key)
{
	return trim(strtolower(str_replace( ']' , '' , $key )));
}

/**
 * @function get_info() pulls the contents of a .info file and puts
 *	it in a key/value array
 *
 * @param string $file: path to an info file or directory containing
 	  an info file.
 *	  If $file is a directory, get_info() will search for the
 *		first info file it finds and pull the details from
 *		that file into the array.
 *	  If other info files are present in the directory, they will
 *		be ignored.
 *
 * @param string $file_extention: the file extention of the info
 	  files you're using.
 * @param string $display_fail:
 *	  false [default] - do not display fail,
 *	  'return' - return fail message,
 *	  'die' - display fail message then die
 *
 * @return: mixed if successful associative array of key/value pairs
 *	  of variables from the info file.
 *	  string or empty array if unsuccessful:
 *		empty array if $display_fail = false
 *		string error message if $display_fail = 'return'
 */

function get_info( $file , $file_extention = '.info' , $display_fail = false )
{
	$fail = false;
	$fail_ouput = '';
	$info = array();
	
	$extention_length = strlen($file_extention);
	$extention_offset = $extention_length - ( 2 * $extention_length );

	if(is_dir($file))
	{
		if ($dir_open = opendir($file))
		{
			$found_info = false;
			while (false !== ($dir_file = readdir($dir_open)) && $found_info == false)
			{
				if($dir_file != '.' && $dir_file != '..' && $dir_file != '.svn')
				{
					if(substr_compare($dir_file , $file_extention , $extention_offset , $extention_length) == 0)
					{
						$info_file = file_get_contents($file.'/'.$dir_file);
						$found_info = true;
						$path = $file.'/';
					}
				}
			}
			closedir($dir_open);
			if($is_set = !isset($info_file) || $is_empty = empty($info_file))
			{
				if(isset($is_set) && $is_set == true)
				{
					$fail_output = '<p>There was no "'.$file_extention.'" file.</p>';
				}
				if(isset($is_empty) && $is_empty == true)
				{
					$fail_output = '<p>There was nothing in the "'.$file_extention.'" file.</p>';
				}
				$fail = true;
			}
		}
	}
	elseif(is_file($file) && substr_compare($file , $file_extention ,  $extention_offset , $extention_length) == 0)
	{
		$info_file = file_get_contents($file);
		$path = dirname($file);
	}
	else
	{
		$fail = true;
		$fail_ouput = '<p>There was no "'.$file_extention.'" file to read!</p>';
	}

//	$regex = '/(?<=^|[\n\r])(?:([^;\n\r][^\[=:]+)(?:\[([^\]]*)(\]))?(?:\[([^\]]*)(\]))?(?:\[([^\]]*)(\]))?)[\t ]*(?:=|:)(.*)(?:[;\n\r])/iU';
	$regex = '/(?<=^|[\r\n])([^;\n\r][^\[=:]+)(?:\[(.*?)\])?(?:\[(.*?)\])?(?:\[(.*?)\])?[\t\ ]*(?:=|:)(.*?)(?<!\\\\)[;\r\n]/i';
	if($fail == false && isset($info_file) && !empty($info_file) && preg_match_all( $regex , $info_file , $key_value , PREG_SET_ORDER) )
	{
		foreach($key_value as $info_item)
		{
			$key_0 = prep_key($info_item[1]); // First dimension
			$key_1 = prep_key($info_item[2]); // Second dimension key
			$key_2 = prep_key($info_item[3]); // Third dimension key
			$key_3 = prep_key($info_item[4]); // Fourth dimension key
			$value = trim($info_item[5]);	// item value
			if( !empty($key_0) && !empty($key_1) && !empty($key_2) && !empty($key_3) )
			{ // All four dimensions are set
				$info_array[$key_0][$key_1][$key_2][$key_3] = $value;
			}
			elseif( !empty($key_0) && !empty($key_1) && !empty($key_2) && empty($key_3) )
			{ // Three out of Four dimensions are set
				$info_array[$key_0][$key_1][$key_2] = $value;
			}
			elseif( !empty($key_0) && !empty($key_1) && empty($key_2) && !empty($key_3) )
			{ // Three out of Four dimensions are set
				$info_array[$key_0][$key_1][$key_3] = $value;
			}
			elseif( !empty($key_0) && empty($key_1) && !empty($key_2) && !empty($key_3) )
			{ // Three out of Four dimensions are set
				$info_array[$key_0][$key_2][$key_3] = $value;
			}
			elseif( !empty($key_0) && !empty($key_1) && empty($key_2) && empty($key_3) )
			{ // Two out of Four dimensions are set
				$info_array[$key_0][$key_1] = $value;
			}
			elseif( !empty($key_0) && empty($key_1) && !empty($key_2) && empty($key_3) )
			{ // Two out of Four dimensions are set
				$info_array[$key_0][$key_2] = $value;
			}
			elseif( !empty($key_0) && empty($key_1) && empty($key_2) && !empty($key_3) )
			{ // Two out of Four dimensions are set
				$info_array[$key_0][$key_3] = $value;
			}
			elseif( !empty($key_0) && empty($key_1) && empty($key_2) && empty($key_3) )
			{ // Only one dimension is set
				$info_array[$key_0] = $value;
			}
		}
	}

	if(!empty($info_array))
	{
		return $info_array;
	}
	else
	{
		$fail = true;
		$fail_ouput .= '<p>There was nothing in the info file.</p>';
	}

	if($fail)
	{
		switch(strtolower($display_fail))
		{
			case 'die':
				echo $fail_output;
				echo '<p>&laquo; <a href="http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'">Back</a></p>';
				die;

			case 'return':
				return $fail_output;
		}
	}
}
?>
