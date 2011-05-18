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
 *        (Not requried) (Defined elsewhere in the system, probably
 *         config.)
 * @constant [template name] the contence of the template file.
 *        (Defined the first time a template is used)
 * @constant DELIM_START opening delimiter for the key word.
 *         Defaults to '{'.  (If not defined elsewhere, defined the
 *         first time a populate_template() is called)
 * @constant DELIM_END closing delimiter for the key word.
 *         Defaults to '}'.  (If not defined elsewhere, defined the
 *         first time a populate_template() is called)
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
	if(defined('PATH_TO_TEMPLATE'))
	{
		$full_path = PATH_TO_TEMPLATE.$template;
	}
	else
	{
		$full_path = $template;
	};

	if(!defined('DELIM_START'))
	{
		define('DELIM_START' , '{');
		define('DELIM_END' , '}');
	};

	if(!defined('POPULATE_TEMPLATE_KEEP_KEYWORDS'))
	{
		define('POPULATE_TEMPLATE_KEEP_KEYWORDS' , false);
	};

	$template_content = '';
	$template_constant = strtoupper( preg_replace( '/[^a-z_0-9]+/i' , '_' , $template ) );
	
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

	if(is_array($input) && !empty($input))
	{
		$output = $template_content;
		$keyword_regex = '/([\r\n\t ]*)'.preg_quote(DELIM_START).'([^\r\n\t \^'.preg_quote(DELIM_END).']+)(?:\^([\^a-z_]+)?)?'.preg_quote(DELIM_END).'/';
		$keywords = '';
		if(preg_match_all(
			 $keyword_regex
			,$template_content
			,$matched_keywords
			,PREG_SET_ORDER)
		)
		{
			$sep = '';
			for( $a = 0 ; $a < count($matched_keywords) ; ++$a )
			{
				$keyword = $matched_keywords[$a][2];
				if(isset($input[$keyword]))
				{
					$replace = $input[$keyword];
					if(isset($matched_keywords[$a][3]))
					{
						$modifiers = explode('^',$matched_keywords[$a][3]);
						foreach($modifiers as $modifier)
						{
							switch($modifier)
							{
								case 'uppercase':
									$replace = strtoupper($replace);
									break;
								case 'uppercase':
									$replace = strtolower($replace);
									break;
								case 'sentance':
								case 'capitalize': // for mySource matrix compatibility
									$replace = ucfirst($replace);
									break;
								case 'heading':
									$replace = ucwords($replace);
									break;
								case 'underscore':
									$replace = str_replace(' ','_',$replace);
									break;
								case 'space':
									$replace = str_replace('_',' ',$replace);
									break;
								case 'text':
								case 'plaintext':
									$replace = strip_tags($replace);
									break;
								case 'trim':
									$replace = trim($replace);
									break;
								case 'id':
									$replace = preg_replace('/[^-_a-z0-9]+/i','_',$replace);
									break;
							};
						};
					};
					if( $strip_for_plain_text === true )
					{
						$replace = strip_tags($replace);
					}
					else
					{
						$replace = $matched_keywords[$a][1].$replace;
					};
					$output = str_replace( $matched_keywords[$a][0] , $replace , $output );

				}
				elseif( POPULATE_TEMPLATE_KEEP_KEYWORDS == false )
				{
					$output = str_replace( $matched_keywords[$a][0] , '' , $output );
				};
				$keywords .= $sep.preg_quote($matched_keywords[$a][1]);
				$sep = '|';
			};
		};
/*
		if($keywords != '')
		{
			$keywords = preg_quote(DELIM_START)."(?:$keywords)".preg_quote(DELIM_END);
			if( $strip_for_plain_text !== true )
			{
				$keywords = "/$keywords/";
			}
			else
			{
				$keywords = '/[\r\n\t ]'.$keywords.'/';
			};

			foreach($input as $key => $value)
			{
				$find[] = DELIM_START.$key.DELIM_END;
				if( $strip_for_plain_text === true )
				{
					$value = strip_tags($value);
				};
				$replace[] = $value;
			};
			$output = str_replace( $find , $replace , $template_content );

			if( POPULATE_TEMPLATE_KEEP_KEYWORDS == false )
			{
				$output = preg_replace( $keywords , '' , $output );
			};
		}
		else
		{
			$output = $template_content;
		};
*/
		return $output; 
	}
	else
	{
		if($hide_error !== true)
		{
			return '<!-- There was nothing to put into the template -->'."\n";
		};
	};
};



function keyword_replace( $input , $tmpl )
{
	for( $a = 0 ; $a < count($find) ; ++$a )
	{
		$key_regex = '/'.DELIM_START.'('.$find[$a].')(?:\^([a-z]+))?'.DELIM_END.'/';
		$find_find = $find[$a];
		$find_replace = isset($replace[$a])?$replace[$a]:'';

		if( preg_match_all( '/\{('.$find_find.')(?:\^([a-z\^]+))?\}/' , $input , $matches , PREG_SET_ORDER ) )
		{
			for( $b = 0 ; $b < count($matches) ; ++$b )
			{
				$keyword = $matches[$b][0];
				$modifiers = explode('^',strtolower(isset($matches[$b][1])?$matches[$b][1]:''));
				foreach($modifiers as $modifier)
				{
					switch($modifier)
					{
						case 'uppercase':
							$find_replace = strtoupper($find_replace);
							break;
						case 'uppercase':
							$find_replace = strtolower($find_replace);
							break;
						case 'sentance':
						case 'capitalize': // for mySource matrix compatibility
							$find_replace = ucfirst($find_replace);
							break;
						case 'heading':
							$find_replace = ucwords($find_replace);
							break;
						case 'underscore':
							$find_replace = str_replace(' ','_',$find_replace);
							break;
						case 'space':
							$find_replace = str_replace('_',' ',$find_replace);
							break;
					};
				};
			};
		};
		$input = str_replace($find_find,$find_replace,$input);
	};
	return $input;
};
?>
