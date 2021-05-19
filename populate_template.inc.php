<?php

/**
 * @function populate_template() populates a templates with the
 * contents of an associative array.
 * (A poor man's templating system)
 *
 * NOTE:  It assumed that you don't want to see unreplaced keywords
 *        in the final output, so they are removed. This can be
 *        overridden by defining the constant
 *        POPULATE_TEMPLATE_KEEP_KEYWORDS with a value of true
 *
 * @param string $template filename of the template to be used.
 * @param array $input associative array with the key matching key
 *        words in the template.
 * @param boolean $hide_error show or hide error messages.
 *        true (default) - do not return error message
 *        false - return error message wrapped withing HTML comments
 *        NOTE:  error messages won't be visible in HTML anyway as
 *               they are inserted as HTML comments.)
 * @param boolean $strip_for_plain_text when removing unused keywords
 *        strip preceeding whitespace
 *        true - strip whitespace before a unused keywords when
 *               removing them
 *        false (default) - leave preceeding white space when
 *               removing unused keywords
 *
 * @constant PATH_TO_TEMPLATE path to the template directory/folder.
 *        (Not requried) (If not defined elsewhere defaults to empty
 *	   string the first time populate_template() is called)
 *
 * @constant [template name] the content of the template file.
 *         (Defined the first time a template is used)
 *
 * @constant DELIM_START opening delimiter for the key word.
 *        (If not defined elsewhere, defaults to '{' the first time
 *         populate_template() is called
 *
 * @constant DELIM_END closing delimiter for the key word.
 *        (If not defined elsewhere, defaults to '}' the first time
 *         populate_template() is called)
 *
 * @constant KWD_MODIFIER_DELIM keyword modifier delimiter.
 *        (If not defined elsewhere, defaults to '^' the first time
 *         populate_template() is called)
 *
 * @constant KWD_MODIFIER_IF if a keyword has an if/else part this
 *        (defines the start of the "if" part.
 *         If not defined elsewhere, defaults to ':' the first time
 *         populate_template() is called)
 *
 * @constant KWD_MODIFIER_ELSE if a keyword has an if/else part this
 *        (defines the start of the "else" part.
 *         If not defined elsewhere, defaults to '~' the first time
 *         populate_template() is called)
 *
 * @constant POPULATE_TEMPLATE_KEEP_KEYWORDS keep keywords even if
 *         there is no replacment value for them.
 *         (Not requried) defaults to false if not defined elsewhere
 *
 * @return string containing the template populated with content or
 *         an error message if the template is empty, is not a file
 *         or the array is empty.
 */
function populate_template( $template , $input , $hide_error = true , $strip_for_plain_text = false )
{
	set_populate_template_constants();

	$full_path = PATH_TO_TEMPLATE.$template;
	$template_content = '';
	$template_constant = constantify($template);
	
// ------------------------------------------------------------------
// START: get template content (and define template constant)

	if(!defined($template_constant))
	{
		if(is_file($full_path))
		{
			$fresh_template = file_get_contents($full_path);
			$test_empty = trim($fresh_template);
			if(!empty($test_empty))
			{
				define( $template_constant , $fresh_template);
				$template_content = $fresh_template;
			}
			else
			{
				define($template_constant , '');
				if($hide_error !== true)
				{
					return "<!--\n\t$full_path\n\tThe template was empty so is useless.\n-->";
				};
			};
		}
		else
		{
			define($template_constant , '');
			if($hide_error !== true)
			{
				return "<!--\n\t$full_path is not a propper file or is missing.\n-->";
			};
		};
	}
	else
	{
		$template_content = constant($template_constant);
		$test_empty = trim($template_content);
		if(empty($test_empty) && $hide_error !== true)
		{
			return "<!--\n\t$full_path\n\tThe template was empty so is useless.\n-->";
		};
	};
// END: get template content (and define template constant)
// ------------------------------------------------------------------

	if(is_array($input) && !empty($input))
	{
		return template_find_replace( $input , $template_content );
	}
	else
	{
		if($hide_error !== true)
		{
			return '<!-- There was nothing to put into the template -->'."\n";
		};
	};
};


function template_find_replace( $input , $template_content )
{
	$keyword_regex = '/'.Q_DELIM_START.'([^\n \t\r].*?)'.Q_DELIM_END.'/s';
	if( preg_match_all( $keyword_regex , $template_content , $matches , PREG_SET_ORDER ) )
		{
			for( $a = 0 ; $a < count($matches) ; ++$a )
			{
				$find = $matches[$a][0];
				$keyword = explode(KWD_MODIFIER_DELIM,$matches[$a][1]);
				$replace = isset($input[$keyword[0]])?$input[$keyword[0]]:'';
				$ID = false;

				$Modifiers = array();
				$modifier = '';

// ------------------------------------------------------------------
// START: Processing keyword modifiers

				if( count($keyword) > 1 )
				{
					for( $b = 1 ; $b < count($keyword) ; ++$b )
					{
						$Modifiers[] = $keyword[$b];
					};
					for( $b = 0 ; $b < count($Modifiers) ; ++$b )
					{
						foreach($Modifiers as $Modifier)
						{
							$modifier = strtolower($Modifier);
							switch($modifier)
							{
								case 'heading': // convert to lower case then UPPER CASE first letter in every word
								case 'titleize': // for mySource matrix compatibility
									$replace = ucwords(strtolower($replace));
									break;
								case 'id': // make ok for use as value in ID 
									$ID = true;
								case 'class': // make ok for use as value in Class
//									debug($replace);
									$replace = attr($replace,$modifier);
//									debug($replace);
									break;
								case 'lowercase': // convert string to lower case
									$replace = strtolower($replace);
									break;
								case 'multispace': // make multiple spaces (including lines and tabs) into a single space
									$replace = preg_replace('/[\r\n\t ]+/',' ',$replace);
									break;
								case 'nocomment': // strip HTML comments
								case 'stripcomment':
									$replace = strip_comments($replace);
									break;
								case 'nolines':
									$replace = strip_extra_lines($replace,2);
									break;
								case 'sentance': // convert to lower case the uppercase the first character in the string
								case 'capitalize': // for mySource matrix compatibility
									$replace = ucfirst(strtolower($replace));
									break;
								case 'space': // convert underscores to spaces
									$replace = str_replace('_',' ',$replace);
									break;
								case 'text':
								case 'plaintext': // make string plain text
									$replace = strip_code($replace);
									break;
								case 'stripcdata':
									$replace = strip_cdata($replace);
									break;
								case 'trim': // remove white space from begining and end of string
									$replace = trim($replace);
									break;
								case 'underscore': // convert spaces to underscores
									$replace = str_replace(' ','_',$replace);
									break;
								case 'uppercase': // convert string to upper case
									$replace = strtoupper($replace);
									break;
								case 'url_decode':
								case 'urldecode':
									$replace = urldecode($replace);
									break;
								case 'url_encode':
								case 'urlencode':
									$replace = urlencode($replace);
									break;

								default:

	// ------------------------------------------------------------------
	// START: Processing multi-part keyword modifiers
/*
									$multi_part_mod_regex = '/^([^'.Q_KWD_MODIFIER_IF.']+)'.Q_KWD_MODIFIER_IF
										.'([^'.Q_KWD_MODIFIER_ELSE.']*)(?:'.Q_KWD_MODIFIER_ELSE.'(.*))?$/s';
									if(preg_match($multi_part_mod_regex,$Modifier,$mod_match))
									{
										$trim_r = strip_code($replace,1);
										$kwd = strtolower($mod_match[1]);
										$val_if = isset($mod_match[2])?$mod_match[2]:'';
										$val_else = isset($mod_match[3])?$mod_match[3]:'';
*/
									$multi_pt = str_replace(Q_KWD_MODIFIER_IF,KWD_MODIFIER_IF,preg_split('/(?<!\\\\)'.Q_KWD_MODIFIER_IF.'/',$Modifier));
									if( $multi_pt[0] != '' )
									{
										$kwd = strtolower($multi_pt[0]);
										$trim_r = strip_code($replace,1);
										$val_if = isset($multi_pt[1])?$multi_pt[1]:'';
										$val_else = isset($multi_pt[2])?$multi_pt[2]:'';

										switch($kwd)
										{
											case 'characters':
											case 'chars':
												if( is_numeric(trim($val_if)) )
												{
													$replace = substr(strip_code($replace,1),0,$val_if);
												};
												break;
											case 'class': // make ok for use as value in Class
//												debug($replace);
												$replace = attr($replace,$kwd,$val_if);
//												debug($replace);
												break;
											case 'empty':
												if( empty($trim_r) )
												{
													$replace = $val_if;
												}
												else
												{
													insert_keyword_anyway( $val_else , $keyword[0] , $replace );
													$replace = $val_else;
												};
												break;
											case 'id': // make ok for use as value in ID 
												$ID = true;
												break;
											case 'nolines':
												if( is_numeric(trim($val_if)) )
												{
													$replace = strip_extra_lines($replace,$val_if);
												}
												elseif( $val_if == '' )
												{
													$replace = strip_extra_lines($replace,2);
												};
												break;
											case 'notempty':
												//if( !empty($trim_r) )
												$trim_if = trim($replace);debug($val_if);debug($val_if,$val_else,$keyword[0],$replace);
												if( !empty($replace) )
												{	
													insert_keyword_anyway( $val_if , $keyword[0] , $replace );
													$replace = $val_if;
												}
												else
												{
													$replace = $val_else;
												};
												break;
											case 'sentances':
												if( is_numeric(trim($val_if)) )
												{
													$val_if = round(trim($val_if));
													$replace = preg_replace('/^((?:[^\r\n!?.]+(?:[.?!]+|[\r\n]+)[\t \r\n]*){0,'.$val_if.'}).*$/s','\1',strip_code($replace,1));
												};
												break;
											case 'words':
												if( is_numeric(trim($val_if)) )
												{
													$val_if = round(trim($val_if));
													$replace = preg_replace('/^([-\w]+(?:\W+[-\w]+){0,'.$val_if.'}).*$/is','\1',strip_code($replace,1));
												};
												break;
										};
										unset($trim_r,$kwd,$val_if,$val_else);
										break;
									};
	// END: Processing multi-part keyword modifiers
	// ------------------------------------------------------------------

							};
						};
					};
					if( $replace == '' && $keyword[0] == 'INSERT_EXTERNAL' && count($keyword) == 2 && $keyword[1] != '' )
					{
						$replace = @file_get_contents($keyword[1]);
					};

				};

// END: Processing keyword modifiers
// ------------------------------------------------------------------

				if( $ID !== true )
				{
					$template_content = str_replace($find,$replace,$template_content);
				}
				else
				{
					$template_content = preg_replace('/^(.*?)'.preg_quote($find).'/s','\1'.$replace,$template_content);
				}
				unset($find,$replace);
			};
		};
		return $template_content;
}


function insert_keyword_anyway( &$input , $keyword , $replace )
{
	if( $input != '' )
	{
		if( substr_count($input , "`$keyword`") > 0 )
		{
			$input = str_replace( "`$keyword`" , $replace , $input);
		};
	};
};

/**
 * @function strip_code() removes all code from a string
 *
 * @param $input string
 * @param $trim boolean trim white space if true
 *
 * @return stirng
 */
function strip_code( $input , $trim = 0 )
{
	$output = strip_tags(strip_comments(strip_cdata($input)));

	if( $trim == 1 )
	{
		$output = trim($output);
	}
	return $output;
}

/**
 * @function strip_comments() strips HTML comments from a string.
 *
 * @param $input string
 * @return stirng
 */
function strip_comments($input)
{
	return preg_replace( '/<!--.*?-->/s' , '' , $input );
};

/**
 * @function strip_extra_lines() removes consecutive empty lines over
 * the number of empty lines specified
 *
 * @param $input string text to be cleaned
 * @param $lines integer number of empty lines above which all
 *	  consecutive, trailing, empty lines should be removed
 *
 * @return string
 */
function strip_extra_lines( $input , $lines = 2 )
{
	if(preg_match('/(\r\n|\n\r|\r|\n)/s',$input,$matches))
	{
		$line = $matches[1];
		$line_ = '';
		for( $a = 0 ; $a < $lines ; ++$a )
		{
			$line_ .= $line;
		};
		return preg_replace('/(?:[\t ]*'.$line.'){'.$lines.',}/s' , $line_ , $input );
	}
	else
	{
		return $input;
	};
};

/**
 * @function strip_cdata() strips CDATA code from a string.
 *
 * @param $input string
 * @return stirng
 */
function strip_cdata($input)
{
	return preg_replace( '/(?:\/\/[\t ]*)?<!\[CDATA\[.*?\]\]>/is' , '' , $input );
};


/**
 * @function set_populate_template_constants() sets any required
 * undefine constants the first time populate template is called in a
 * script.
 *
 * @param VOID
 * @return VOID
 */
function set_populate_template_constants()
{
	if( !defined('POPULATE_TEMPLATE_CONSTANTS'))
	{
		define('POPULATE_TEMPLATE_CONSTANTS','');

		if(!defined('PATH_TO_TEMPLATE'))
		{
			define('PATH_TO_TEMPLATE','');
		};

		if(defined('DELIM_START'))
		{
			define('Q_DELIM_START' , preg_quote(DELIM_START));
		}
		else
		{
			define('Q_DELIM_START' , preg_quote('{'));
		};

		if(defined('DELIM_END'))
		{
			define('Q_DELIM_END' , preg_quote(DELIM_END));
		}
		else
		{
			define('Q_DELIM_END' , preg_quote('}'));
		};

		if(defined('KWD_MODIFIER_DELIM') )
		{
			define('Q_KWD_MODIFIER_DELIM',preg_quote(KWD_MODIFIER_DELIM));
		}
		else
		{
			define('Q_KWD_MODIFIER_DELIM',preg_quote('^'));
			define('KWD_MODIFIER_DELIM','^');
		};

		if(defined('KWD_MODIFIER_IF') )
		{
			define('Q_KWD_MODIFIER_IF',preg_quote(KWD_MODIFIER_IF));
		}
		else
		{
			define('Q_KWD_MODIFIER_IF',preg_quote(':'));
			define('KWD_MODIFIER_IF',':');
		};
		if(defined('KWD_MODIFIER_ELSE') )
		{
			define('Q_KWD_MODIFIER_ELSE',preg_quote(KWD_MODIFIER_ELSE));
		}
		else
		{
			define('Q_KWD_MODIFIER_ELSE',preg_quote('~'));
			define('KWD_MODIFIER_ELSE','~');
		};

		if(!defined('POPULATE_TEMPLATE_KEEP_KEYWORDS'))
		{
			define('POPULATE_TEMPLATE_KEEP_KEYWORDS' , false);
		};
	};
};

/**
 * @function constantify() substitutes multiple non alpha numberic
 * characters with a single underscore
 * 
 * @param $input string
 * @return string without non alpha numeric characters.
 */
function constantify( $input )
{
	return strtoupper( preg_replace( '/[^a-z_0-9]+/i' , '_' , $input) );
};

/**
 * @function attr() converts a replacement string into either a
 * unique ID or a class value.
 *
 * If the replacement string is converted to an ID it is checked to
 * see if it has previously been used as an id. If so, it is
 * reprocessed until a unique id is found.
 *
 * A given string can generate up to 100 unique IDs
 *
 * @param $input string the replacement string to be converted to an ID
 * @param $attr string either
 *	  - 'id' (default)
 *	  - 'class'
 *	  - 'retry' same as 'id' but input is modified to help find
 *		unique ID
 * @param $length integer maximum number of characters long the class
 *	or ID should be
 *
 * @return string class or unique ID friendly version of $input
 */
function attr( $input , $attr = 'id' , $length = 0 )
{
	$uid = $input;
	$sep = '_';
	$sep_ = '|'.$sep.'+';
	switch( $attr )
	{
		case 'class':
			$sep = '-';
			break;
		case 'retry':
			$sep = '';
			$sep_ = '';
			$attr = 'id';
			break;
		default:
			$sep = '_';
			$attr = 'id';
			break;
	};
	$find = array(
			 '/(?:[^-_a-z0-9]+(?:-+[^-_a-z0-9]+)*)+/is'
			,'/(?:^[0-9'.$sep.']+'.$sep_.'$)/'
		);
	$replace = array( $sep , '' );

	$uid =	preg_replace( $find , $replace , strip_code($uid,1) );

	$uid_len = strlen($uid);

	if( $length > 0 && $uid_len > $length )
	{
		$uid = substr( $uid , 0 , $length );
	}
	elseif($uid_len == 0)
	{
		return '';
	};

	$original_uid = $original_original_uid = $uid;
	if( $attr == 'id' )
	{
		$uid_len = strlen($uid);
		$count_ = 0;
		$original_uid = $uid;
		$retry = true;
		$round = 0;
		while( defined("POPULATE_TEMPLATE__$uid") && $count_ < 10 )
		{
			$uid = $original_uid;
			if( $length > 0 && $uid_len > 2 )
			{
				$uid = substr_replace( $uid , "_$count_" , -2 , 2 );
			}
			else
			{
				$uid .= "_$count_";
			};

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// START: If ID already exists try playing with the string
			++$count_;
			if($count_ == 10 && $retry === true && defined("POPULATE_TEMPLATE__$uid") )
			{
				$uid = $input;
				$attr_ = $attr;
				if($round > 4 )
				{
					$uid = strrev($uid);
				};
				switch($round)
				{
					case 0: $attr_ = 'retry';
						break;
					case 1:
					case 6:	$uid = strtolower($uid);
						break;
					case 2:
					case 7:	$uid = strtoupper($uid);
						break;
					case 3:
					case 8:	$uid = ucfirst(strtolower($uid));
						break;
					case 4:
					case 9:	$uid = ucwords(strtolower($uid));
						break;
				};
				$uid = attr( $uid , $attr , $length );
				$retry = false;
				++$round;
			};
// END: If ID already exists try playing with the string
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
			
		};
		if($attr == 'id')
		{
			if(!defined("POPULATE_TEMPLATE__$uid"))
			{
				define("POPULATE_TEMPLATE__$uid",'');
			}
			else
			{ // Just in case it couldn't generate a unique ID
				$uid = $original_original_uid;
			};
		};
	};
	return $uid;
};

//include(dirname(__FILE__).'/populate_template.help.inc.php');
