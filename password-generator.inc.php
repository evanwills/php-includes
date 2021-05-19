<?php

/**
 * @function password() generates a random password based on supplied
 * characters
 *
 * NOTE:  for the character lists if you only want to specify the
 *	  characters to omit from that group, prepend the list with a
 *	  carrat '^'. (This means you CAN NOT start the $special list
 *	  with a carrat as it will cause everything else you list to
 *	  be excluded)
 *
 * @param $length integer (defaults to 8) number of characters the
 *	  password should be
 *
 * @param $special string list of non alpha-numeric characters found
 *	  on a standard US layout QUERTY keyboard
 *	  (if the string is preceded by a carrat, it is the list of
 *	   non-aplha-numeric characters to be omitted from the
 *	   standard list of non-alpha-numeric characters)
 *	  By default the standard list omits:
 *		"'" (single quote)
 * 		'"' (double qoute),
 *		'`' (back quote) and
 *		'\' (backslash)
 *
 * @param $nubers string list of numbers password may include
 *	  (if string is preceeded by a carrat, it is a list of
 *	   numeric characters to be omitted from the standard list of
 *	   numeric characters a password may include)
 *	  By default the stand list omits
 *		'0' (zero)
 *
 * @param $alphabet string list of alphabetical characters a password
 *	  may include
 *	  (if string is preceeded by a carrat, it is a list of
 *	   alphabetical characters to be omitted from the standard
 *	   list of alphabetical characters a password may include)
 *	  By default the stand list omits
 *		'o' (lower case O)
 *		'O' (upper case o)
 *		'i' (lower case I)
 *		'I' (upper case i)
 *		'l' (lower case L)
 * @param $extras string comma separated list of variants to how the
 *	  characters are selected
 *		'a+', 'a+1'
 *		'n+' or 'n+1'
 *		's+' or 's+1'
 *			repeat the list of alphabetical, numeri or
 *			non-alpha-numeric characters respectively so
 *			that they are twice as likely to be included
 *			in a password.
 *		'a++', 'a+2' 
 *		'n++', 'n+2' 
 *		's++', 's+2' 
 *			same as above but repeat the respective list
 *			twice so there are tree times as many
 *			characters in that list to be drawn on.
 *		'a+++', 'a+3' 
 *		'n+++', 'n+3' 
 *		's+++', 's+3' 
 *			same as above but repeat the respective list
 *			tree times so there are four times as many
 *			characters in that list to be drawn on.
 *		'both-hands'
 *			make sure there's the same number (or almost
 *			the same if $length is odd) of characters
 *			typed by both hands
 *		'alt'
 *			make sure that each character typed is uses
 *			the alternate hand
 *		'left'	left hand characters only
 *		'right'	right hand characters only
 *		'number-pad' number pad characters only
 *
 *	e.g. password
 */
function password(
	 $length = 8
	,$special = ' ~!@#$%^&*()_+-=[]{}|;:,.?/' // missing "'", (single quote) '"' (double qoute), '`' (back quote) & '\' (backslash)
	,$numbers = '123456789' // missing '0' (zero)
	,$alphabet = 'aAbBcCdDeEfFgGhHjJkKLmMnNpPqQrRsStTuUvVwWxXyYzZ' // missing: 'o', 'O', 'i', 'I' & 'l' (lower case L)
	,$extra = ''
)
{
	$length = password__dud_arg($length,1);
	$special = password__prep_char($special,2);
	$numbers = password__prep_char($numbers,3);
	$alphabet = password__prep_char($alphabet,4);
	
	$pool = password__extra( $extra , $alphabet , $numbers , $special );

	$seed = explode(' ',microtime());
	mt_srand( (($seed[1] + $seed[0]) * 100000) );

	$c = '';
//	if( !defined('SEED') )
//	{
//		define('SEED',$seed[0]);
//	}
//	mt_srand( SEED ) ;
	if( isset($extra['alt']) && $extra['alt'] === true )
	{
		$alt = mt_rand(0,1);
	}
	else
	{
		$alt = 0;
	}
	$tmp = '';
	while($length > 0)
	{
		$b = mt_rand(0,$max);
		$tmp .= ','.$b;
		$c .= $input[$alt][$b];
		--$length;
		if( $extra['alt'] === true )
		{
			if($alt = 0)
			{
				$alt = 1;
			}
			else
			{
				$alt = 0;
			}
		}
	}
	return $c;
}

/**
 * @function password__dud_arg() checkds whether an argument is valid
 *
 * @param $input integer
 * @param $place integer
 *
 * @return integer or false;
 */

function password__dud_arg( $input , $place )
{
	$type = 'string';
	switch( $place )
	{
		case 1:	$pos = 'first';
			$type = 'numeric';
			break;
		case 2:	$pos = 'second';
			break;
		case 3:	$pos = 'third';
			break;
		case 4:	$pos = 'fourth';
			break;
		case 5:	$pos = 'fifth';
			break;
	}
	if( $type == 'numeric' )
	{
		if( !is_numeric($input) || is_bool($input) || ( is_string($input) && !empty($input) ) )
		{
			trigger_error('password() expects '.$pos.' argument to be an integer. '.ucfirst(gettype($input)).' supplied'.var_dump($input),E_USER_ERROR);
		}
		elseif( is_float($input) )
		{
			$input = round($input);
			settype($input,'integer');
			trigger_error('password() expects '.$pos.' argument to be an integer. '.ucfirst(gettype($input)).' supplied. Defaulting to '.$input,E_USER_WARNING);
		}
		elseif( is_string($input) && empty($input) )
		{
			$input = 8;
		}
		elseif( $place == 1 && $input < 3 )
		{
			trigger_error('password() expects '.$pos.' argument to be an integer greater than 3. Passwords of less than six characters are not particularly secure. Passwords less than three characters are virtually worthless. '.$input.' was supplied',E_USER_NOTICE);
		}
	}
	else
	{
		if( !is_string($input) )
		{
			trigger_error('password() expects '.$pos.' argument to be a string. '.ucfirst(gettype($input)).'" supplied',E_USER_ERROR);
		}
	}
	return $input;
}

function password__prep_char($input,$place)
{
	$input = password__dud_arg($input,$place);
	switch($place)
	{
		case 2:	$output = '`~!@#$%^&*()_+-={}|[]\;\':",./<>?';
			break;
		case 3:	$output = '0123456789';
			break;
		case 4:	$output = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;

	}
	if( substr_compare('^',$input,0,1) == 0 )
	{
		$output = preg_replace('/['.preg_quote(preg_replace('/^\^/','',$input)).']+/','',$output);
		return $output;
	}
	else
	{
		return $input;
	}
}

function password__extra( &$extra , $alphabet , $numbers , $special )
{
	$extra_in = '';
	$extra = array(
		 'hands' => false // could equal left, right, both, alt or num-pad
		,'max' => 0
	);

	if($extra_in == '')
	{
		$pool = str_split($alphabet);
		$pool = array_merge($pool,str_split($numbers));
		$pool = array_merge($pool,str_split($special));
		$extra['max'] = count($pool) - 1;
	}
	else
	{
		$extra_in = explode(',',$extra_in);
		$a = false; // options for alphabetical characters have not yet been defined
		$n = false; // options for numeric chars not yet defined
		$s = false; // options for non-alpha-numeric chars not yet defined
		$h = false; // options for hand preferences not yet defined
		for( $b = 0 ; $b < count($extra_in) ; ++$b )
		{
			$c = trim(strtolower($extra_in[$b]));
			if(preg_match('/([ans])(\++)(?:([0-4])|([0-9]\.(?:25|5|75|3|6)))?/',$c,$match))
			{
				switch($c)
				{
					case 'a+':
					case 'a+1':
						if($a === false)
						{
							$alphabet .= $alphabet;
							$a = true;
						}
						break;
					case 'a++':
					case 'a+2':
						if($a === false)
						{
							$alphabet .= $alphabet.$alphabet;
							$a = true;
						}
						break;
					case 'a+++':
					case 'a+3':
						if($a === false)
						{
							$alphabet .= $alphabet.$alphabet.$alphabet;
							$a = true;
						}
						break;
					case 'a++++':
					case 'a+4':
						if($a === false)
						{
							$alphabet .= $alphabet.$alphabet.$alphabet.$alphabet;
							$a = true;
						}
						break;
/*					case '':
						if($n === false)
						{
							$n = true;
						}
						break;
					case '':
						if($s === false)
						{
							$s = true;
						}
						break;
					case '':
					case '':
					case '':
					case '':
					case '':
						if($h === false)
						{
							$h = true;
						}
						break;
*/				}
			}
		}
	}
}

/*
for( $a = 0 ; $a < 10 ; ++$a )
{
	$tmp = password( 8,55);
	echo "$tmp\n";
}
*/
$dir_ = dirname(__FILE__).'/';
if( !function_exists('error') && is_file($dir_.'error.inc.php') )
{
	include($dir_.'error.inc.php');
}
if( !function_exists('debug') && is_file($dir_.'debug.inc.php') )
{
	include($dir_.'debug.inc.php');
}
unset($dir_);
