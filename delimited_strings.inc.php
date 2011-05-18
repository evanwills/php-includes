<?php

/**
 * These functions depend on regex_safe() declared in regex_safe.inc.php
 */
if(!function_exists('regex_safe'))
{
	include('/var/www/includes/regex_safe.inc.php');
};

if(!function_exists('debug'))
{
	include('/var/www/includes/debug.inc.php');
};





/** 
 * rows_to_columns() takes a string formatted as tab or comma delimited (spreadsheet) and makes the rows columns and the columns into rows. *
 * @param $input string 2D delimited string (e.g. CSV file or tab delimite text file, etc)
 * @param $col_delim string column delimiter character (can be any character)
 * @param $row_delim string row delimiter character (can be any character)
 * @param $field_wrap string character to encapsulate field
 *
 * @return string
 */

function delim_str__csv_rows_to_columns( $input , $col_delim = ',' , $row_delim = "\n" , $field_wrap = '' )
{
	$all_keys_are_int = true;

	$input_array = delim_str__array_from( $input , $col_delim , $row_delim , $field_wrap );
	$output_array = delim_str__rows_to_cols( $input_array , $all_keys_are_int );
	return delim_str__build_csv( $output_array , $col_delim , $row_delim , $field_wrap , $all_keys_are_int );
};

function delim_str__validate_parameters( $input , $col_delim , $row_delim , $field_wrap )
{
	if(!is_string($input))
	{
		debug__die_with_info('First parameter for csv_rows_to_columns() must be a string',$input,2);
	};
	if(!is_string($col_delim))
	{
		debug__die_with_info('Second parameter for csv_rows_to_columns() must be a string',$input,2);
	}
	elseif(preg_match("/[\r\n]+/",$col_delim,$match_col))
	{
		echo '<!-- It is somewhat unconventional to use line breaks for delimiting columns'.debug__die_with_info('',DEBUG__DIE_WITH_INFO__NO_VAR,2).' -->';
		define('COL_DELIM',$match_col[0]);
		$input = str_replace(COL_DELIM,"\n",$input);
	}
	elseif($col_delim == '')
	{
		debug__die_with_info('csv_rows_to_columns() second parameter must not be an empty string',DEBUG__DIE_WITH_INFO__NO_VAR,2);
	}
	elseif(!strstr($input,$col_delim))
	{
		debug__die_with_info('csv_rows_to_columns() first parameter does not contain any occurences of "'.$col_delim.'" (second parameter)',DEBUG__DIE_WITH_INFO__NO_VAR,2);
	};
	if(!is_string($row_delim))
	{
		debug__die_with_info('Third parameter for csv_rows_to_columns() must be a string ',$input,2);
	}
	elseif($col_delim == '')
	{
		debug__die_with_info('csv_rows_to_columns() third parameter must not be an empty string',DEBUG__DIE_WITH_INFO__NO_VAR,2);
	}
	elseif(!strstr($input,$row_delim))
	{
		debug__die_with_info('csv_rows_to_columns() first parameter does not contain any occurences of "'.$row_delim.'" (third parameter)',DEBUG__DIE_WITH_INFO__NO_VAR,2);
	};
	if(!is_string($field_wrap))
	{
		debug__die_with_info('Fourth (and last)  parameter for csv_rows_to_columns() must be a string ',$input,DEBUG__DIE_WITH_INFO__NO_VAR,2);
	} ;
};

function delim_str__td_to_tr( $input , $mode = 'html' )
{
	
	
};



function delim_str__wrap_in_html_table( $input_array , $header_row = false ,  $header_col = false  )
{
	$output = "\n<table>\n";

	if( $header_row === true)
	{
		$cell_1_open = '<th>';
		$cell_1_close = '</th>';
	}
	else
	{
		$cell_1_open = '<td>';
		$cell_1_close = '</td>';
	};
	if( $header_col === true )
	{
		$a = 1;
		$output .= "\t<thead>\n\t\t<tr>\n";
		for( $b = 0 ; $b < count($input_array[0]) ; ++$b )
		{
			$output .= "\t\t\t<th>{$input_array[0][$b]}</th>\n";
		};
		$output .= "\t\t</tr>\n\t</thead>\n";

	}
	else
	{
		$a = 0;
	};
	$output = "\t<tbody>\n";
	for( $a ; $a < count($input_array) ; ++$a )
	{
		$output .= "\t\t<tr>\n\t\t\t".$cell_1_open.$input_array[$a][0].$cell_1_close."\n";
		for( $b = 1 ; $b < count($input_array[$a]) ; ++$b )
		{
			$output .= "\t\t\t<td>{$input_array[$a][$b]}</td>\n";
		};
		$output .= "\t\t</tr>\n";
	};
	$output .= "</table>\n";
	return $output;
};


function delim_str__array_from( $input , $col_delim = ',' , $row_delim = "\n" , $field_wrapper = '' )
{
	delim_str__validate_parameters( $input , $col_delim , $row_delim , $field_wrapper );

	if($field_wrapper == '' )
	{
		$output_array = explode($row_delim,$input);
		for( $a = 0 ; $a < count($output_array) ; ++$a )
		{
			$output_array[$a] = explode($col_delim,$output_array[$a]);
		};
		return $output_array;
	}
	else
	{
		$row_delim = regex_safe($row_delim);
		$col_delim = regex_safe($col_delim);
		$field_wrapper = regex_safe($field_wrapper);

		$find_row = "/(?<=^|$row_delim)((?U:.*))(?=$row_delim|$)/s";
		$find_col = "/(?<=^|$col_delim)(?U:$field_wrapper(.*)$field_wrapper|([^$col_delim]*))(?=$col_delim|$)/is";
		$output_array = array();
		if(preg_match_all( $find_row , $input , $match_rows , PREG_SET_ORDER ))
		{
			for( $a = 0 ; $a < count($match_rows) ; ++$a )
			{
				if(preg_match_all( $find_col , $match_rows[$a][1] , $match_cols , PREG_SET_ORDER ))
				{
					$temp_array = array();
					for( $b = 0 ; $b < count($match_cols) ; ++$b )
					{
						$temp_content = isset($match_cols[$b][1])?$match_cols[$b][1]:'';
						$temp_content .= isset($match_cols[$b][2])?$match_cols[$b][2]:'';
						$temp_array[] = $temp_content;
					};
					$output_array[] = $temp_array;
				};
			};
		};
		return $output_array;
	};
};

function delim_str__safe_chars_for_regex($input)
{
	return preg_replace(
				 array(
					'/([\\\\$!^()\[\]{}?*+<>#| 	])/'
					,"/\r/s"
					,"/\t/s"
					,"/\n/s"
				 )
				,array(
					'\\\1'
					,'\\r'
					,'\\t'
					,'\\n'
				 )
				,$input
	);
};

function delim_str__make_rows_have_equal_cols($input_array , &$all_keys_are_int = true )
{
	$output_array = array();
	$max_cols = 0;
	$max_rows = count($input_array);

	foreach( $input_array as $row_key => $row_value )
	{	
		$cols = count($input_array[$row_key]);
		if($cols > $max_cols)
		{
			$max_cols = $cols;
			$col_key_array = array_keys($input_array[$row_key]);
		};
		if( $all_keys_are_int === true )
		{
			if(!is_int($row_key))
			{
				$all_keys_are_int = false;
			};
			if( $all_keys_are_int === true )
			{
				foreach( $row_value as $col_key => $col_value)
				{
					if(!is_int($col_key))
					{
						$all_keys_are_int = false;
					};
				};
			};
		};
	};

	if($all_keys_are_int === true)
	{
		for( $a = 0 ; $a < $max_rows ; ++$a )
		{
			for( $b = 0 ; $b < $max_cols ; ++$b )
			{
				if(isset($input_array[$a][$b]))
				{
					$output_array[$a][$b] = $input_array[$a][$b];
				}
				else
				{
					$output_array[$a][$b] = '';
				};
	
			};
		};
		return $output_array;
	}
	else
	{
		foreach( $input_array as $row_key => $row_value )
		{
			$cols_max = $max_cols;
			foreach( $col_key_array as $col_key )
			{
				if(!isset($output_array[$col_key]))
				{
					$output_array[$col_key] = array();
				};
				if(isset($input_array[$row_key][$col_key]))
				{
					$output_array[$col_key][$col_key] = $input_array[$row_key][$col_key];
				}
				else
				{
					$output_array[$col_key][$row_key] = '';
				};
				--$cols_max;
			};
		};
		return $output_array;
	};
};

/**
 * rows_to_cols() takes a 
 */
function delim_str__rows_to_cols( $input_array , &$all_keys_are_int = true )
{
	$input_array = delim_str__make_rows_have_equal_cols($input_array , $all_keys_are_int);
	$output_array = array();

	unset($input_array['all_keys_are_int']);

	if( $all_keys_are_int === true )
	{
		for( $a = 0 ; $a < count($input_array) ; ++$a )
		{
			for( $b = 0 ; $b < count($input_array[$a]) ; ++$b )
			{
				if(!isset($output_array[$b]))
				{
					$output_array[$b] = array();
				};
				$output_array[$b][$a] = $input_array[$a][$b];
			};
		};
	}
	else
	{
		foreach( $input_array as $row_key => $row_value )
		{
			foreach( $row_value as $col_key => $col_value )
			{
				if(!isset($output_array[$col_key]))
				{
					$output_array[$col_key] = array();
				};
				$output_array[$col_key][$row_key] = $row_value[$col_key];
			};
		};
	};
	return $output_array;
	
};



function delim_str__build_csv( $input_array , $col_delim , $row_delim , $field_wrap = '' )
{
	$output = '';
	$delim_row = $delim_col = '';
//	$output_array = array();
	foreach( $input_array as $row_key => $row_value)
	{
		$output .= $delim_row;
		foreach($row_value as $col_key => $col_value )
		{
			$output .= $delim_col.$field_wrap.$input_array[$row_key][$col_key].$field_wrap;
			$delim_col = $col_delim;
		};
		$delim_col = '';
		$delim_row = $row_delim;
	};
	return $output;
};


if($_SERVER['argc'] > 1 && is_file($_SERVER['argv'][1]))
{
	$all_keys_are_int = true;
	for( $a = 1 ; $a < $_SERVER['argc'] ; ++$a )
	{
		if(is_file($_SERVER['argv'][$a]))
		{
			$input_file = $_SERVER['argv'][$a];
			$output_file = preg_replace( '/(?<=^|\/)([^\/]+\.[a-z]+)$/i' , 'r2c__\1' , $input_file );
			$input = file_get_contents($input_file);
			$tabs = substr_count($input,"\t");
			$commas = substr_count($input,',');
			if($tabs >= $commas)
			{
//				debug('$delims = ',$delims[2]);exit;
				$col_delim = "\t";
			}
			else
			{
				$col_delim = ',';
			};
			if(strstr($input,"\r\n"))
			{
				$row_delim = "\r\n";
			}
			elseif(strstr($input,"\n\r"))
			{
				$row_delim = "\n\r";
			}
			elseif(strstr($input,"\n"))
			{
				$row_delim = "\n";
			}
			elseif(strstr($input,"\r"))
			{
				$row_delim = "\r";
			};
			$input_array = delim_str__array_from( $input , $col_delim , $row_delim );
			$output_array = delim_str__rows_to_cols( $input_array , $all_keys_are_int );
			$output = delim_str__build_csv( $output_array , $col_delim , $row_delim );

			file_put_contents( $output_file, $output );
		};
	};
};
