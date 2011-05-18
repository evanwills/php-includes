<?php

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
 *             'train,engind' and/or bus and/or 'truck'
 *        Unescaped white space is removed from begining and end of
 *        each part
 *        e.g. 'train, car, bike, \ wheel\ ' would match files
 *             containing, 'train' and/or 'car' and/or bike and/or
 *             ' wheel '
 * @param FLAGS strings the following values can be passed in any
 *        order
 *        'prefix' match the file part if it is at the begininga of
 *              the file name
 *        'suffix' match the file part if it at the end of the file
 *              name but before the file extension
 *        'anywhere' check if the file part is anywhere in the file
 *              name string
 *        NOTE: By default, the file part to be matched is the file
 *              extension. To change what part of the file is checked
 *              you can pass any of the following.
 *        NOTE: It doesn't make sense to pass all three but if you do
 *              the last one will be the one used.
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
 */
function build_file_list()
{
	$retrieved_args = func_get_args();
	$start_location = $retrieved_args[0];
	$file_name_parts = array();
	build_file_list__explode($retrieved_args[1],$file_name_parts);
	$part_pos = 'extension';
	$insensitive = true;
	$exclude_hidden = true;
	$exclude = array();
	$args = func_num_args();
	$extra_args = array();
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

		for( $a = 0 ; $a < count($extra_args) ; ++$a )
		{
			$this_arg = strtolower($extra_args[$a]);
			switch($this_arg)
			{
				case 'extension':
				case 'prefix':
				case 'suffix':
				case 'anywhere':
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
		if(!empty($exclude))
		{
			for( $a = 0 ; $a < count($exclude) ; ++$a )
			{
				$exclude[$a] = strtolower($exclude[$a]);
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
						$error = '$file_path contains no .'.$file_name_parts.' files';
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
				return '"'.$start_location.'" contained no '.func_get_arg(1).' files';
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
