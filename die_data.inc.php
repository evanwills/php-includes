<?php

function die_data($input,$level = 0)
{
	$d_data = debug_backtrace();
	$input = wordwrap($input,50);
	if(count($d_data) < $level)
	{
		$level = count($d_data);
	}
	elseif(count($d_data) == $level)
	{
		$level = ( count($d_data) - 1 );
	};
	$ln = $d_data[$level]['line'];
	$fl = $d_data[$level]['file'];
	return "

===========================================================
($fl - line $ln)
$input
===========================================================

";
}

function die_data_db( $sql , $level = 0 )
{
	$prefix = '';
	if(preg_match('/^[\r\n\t ]*([a-z]+)[\r\n\t ]+/i',$sql,$matches))
	{
		$prefix = ucfirst(strtolower($matches[1])).' ';
	};
	$d_data = debug_backtrace();
	if(count($d_data) < $level)
	{
		$level = count($d_data);
	}
	elseif(count($d_data) == $level)
	{
		$level = ( count($d_data) - 1 );
	};
	$ln = $d_data[$level]['line'];
	$fl = $d_data[$level]['file'];
	return "

===========================================================
($fl - line $ln)
{$prefix}query
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
".trim($sql)."
- - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
Failed with mysql error:
".mysql_error()."
===========================================================

";
};

function sql_query( $query , &$result )
{
	if( $query == 'help' || $query == '?' || $query == '' )
	{
		debug('[[line]]sql_query() processes a mysql query. If there is an error in the[[line]]query it the script dies and with an error outlining the SQL[[line]]statement and the mysql error it caused.[[line]]');
	};
	$result = mysql_query($query) or die(die_data_db($query,1));
};

