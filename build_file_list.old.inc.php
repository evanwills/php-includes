<?php

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
 *
 * @returns array a two dimensional array:
 *	first dimension equates to a file
 *	second dimension contains
 *	'path' => path to the file
 *	'file' => name of the file
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
	$file_name_part_array = preg_split('/(?<!\\\\),/',$file_name_part);
	$file_list = array();
	$error = '';
	if(is_dir($start_location))
	{
		if(!preg_match('/\/$/',$start_location))
		{
			$start_location .= '/';
		};
		if ($dir_open = opendir($start_location))
		{
			while (false !== ($dir_file = readdir($dir_open)))
			{
				if ($dir_file != '.' && $dir_file != '..' && $dir_file != '.svn')
				{
					$file_path = $start_location.$dir_file;
					if(is_dir($file_path))
					{
						$returned_files = build_file_list($start_location , $file_name_part , $name_part_pos , $insensitive );
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
							$error = '$file_path contains no .'.$file_name_part.' files';
						};
					}
					else
					{
						foreach($file_name_part_array as $file_part)
						{
							$file_part = preg_quote($file_part);
							switch($name_part_pos)
							{
								case 'prefix':
									$file_name_regex = '/^'.$file_part.'/'.$regex_mod;
									break;
								case 'suffix':
									$file_name_regex = '/^[^\.]+?'.$file_part.'(?:\..+)?$/'.$regex_mod;
									break;
								case 'extension':
								default:
									$file_name_regex = '/.*\.'.$file_part.'$/U'.$regex_mod;
									break;
							};

							if( preg_match($file_name_regex , $file_path) || $file_part == 'BUILD_FILE_LIST_ALL' )
							{
								$file_list[] =  array(
										 'path' => $start_location
										,'file' => $dir_file
									);
							};
						};
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
				return '"'.$start_location.'" contained no .'.$file_name_part.' files';
			};
		}
		else
		{
			return $file_list;
		};
	}
	else
	{
		return '"'.$start_location.'" is not a valid direcotry';
	};
};


?>
