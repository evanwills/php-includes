<?php

function get_info( $file , $file_extention = '.info' , $display_fail = false )
{
/**
 * get_info() pulls the contents of a .info file and puts it in a key/value array
 *
 * @param string $file: path to an info file or directory containing an info file.
 * If $file is a directory, get_info() will search for the first info file it finds and pull the details from that file into the array.
 * If other info files are present in the directory, they will be ignored.
 *
 * @param string $display_fail: false (do not display fail (DEFAULT)), 'return' (return fail message), 'die' (display fail message then die).
 * @param string $file_extention: the file extention of the info files you're using.
 *
 * @return: if successful, returns a key/value array of variables from the info file.
 *		if unsuccessful, returns an error message and if told to, dies
 */

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
					};
				};
			};
			closedir($dir_open);
			if($is_set = !isset($info_file) || $is_empty = empty($info_file))
			{
				if(isset($is_set) && $is_set == true)
				{
					$fail_output = '<p>There was no "'.$file_extention.'" file.</p>';
				};
				if(isset($is_empty) && $is_empty == true)
				{
					$fail_output = '<p>There was nothing in the "'.$file_extention.'" file.</p>';
				};
				$fail = true;
			};
		};
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
	};

	if($fail == false && isset($info_file) && !empty($info_file))
	{
		$info_file = str_replace( array( "\n\r" , "\r\n" , "\r" ) , "\n" , $info_file );
		$info_file = explode("\n", $info_file);

		$info = array();
		$info_array['path'] = $path;

		foreach($info_file as $line)
		{
			if(!empty($line))
			{
				$line = explode(';',$line);
				if(!empty($line[0]))
				{
					$key_value = explode('=',$line[0]);

					$key = explode( '[' , trim($key_value[0]) );
					$value = trim( isset($key_value[1]) ? $key_value[1] : '' );

					$key_0 = strtolower($key[0]);
					$key_1 = isset($key[1])?$key[1]:'';
					$key_2 = isset($key[2])?$key[2]:'';

					if(!empty($key_2)) // Line is part of a three dimensional array
					{
						$key_1 = strtolower(str_replace( ']' , '' , $key_1 ));
						if($key_2 == ']')
						{
							$info_array[$key_0][$key_1][] = $value;
						}
						else
						{
							$key_2 = strtolower(str_replace( ']' , '' , $key_2 ));
							$info_array[$key_0][$key_1][$key_2] = $value;
						};
					}
					elseif(!empty($key_1)) // Line is part of a two dimensional array
					{
						if($key_1 == ']')
						{
							$info_array[$key_0][] = $value;
						}
						else
						{
							$key_1 = strtolower(str_replace( ']' , '' , $key_1 ));
							$info_array[$key_0][$key_1] = $value;
						};
					}
					else // Line is part of a single dimension array
					{
						$info_array[$key_0] = $value;
					};
				};
			};
		};
	};
	
	if(!empty($info_array))
	{
		return $info_array;
	}
	else
	{
		$fail = true;
		$fail_ouput = '<p>There was nothing in the info file.</p>';
	};

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
		};
	};
};
?>