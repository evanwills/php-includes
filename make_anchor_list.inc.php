<?php

function make_anchor_list( $file_location , $start_level = 2 )
{
	$levels = '';
	for( $start_level ; $start_level <= 6 ; ++$start_level )
	{
		$levels .= $start_level;
	};
	unset($start_level);
	$head_regex = '/<h(['.$levels.'])( id="([^"]+)")?>(.*?)<\/h\1>/i'; debug($head_regex);

	if(is_file($file_location) && is_readable($file_location))
	{
		$output = '';

		$input = file_get_contents($file_location);
		if(preg_match( $head_regex , $input , $headings , PREG_SET_ORDER ))
		{
			$level = 0;
			$anchor = ' class="anchor_list"';
			for( $a = 0 ; $a < count($headings) ; ++$a )
			{
				$last_level = $level;
				$level = $headings[$a][1];
				if($headings[$a][2] != '')
				{	
					$id = $headings[$a][2];
				}
				else
				{
					$id = preg_replace( array('/^[^a-z]+/i','/[^-a-z_0-9]+/i')  , array('','_') , $headings[$a][3] );
				};
				$heading = $headings[$a][3];
				$whole = $headings[$a][0];
				
				$find[] = $whole;
				$replace [] = "<p class=\"top\"><a href=\"#content_col\" id=\"$id\">top</a></p>\n<h$level>$heading</$level>";

				$link = "\n\t<li>\n\t\t<a href=\"#$id\">$heading</a>\n";
				if( $last_level == $level )
				{	
					$list .= "\n\t</li>$link";
				}
				elseif ($last_level > $level)
				{
					$list .= "\n\t</li>\n\t</ul>\n\t</li>$link";
				}
				else
				{
					$list .= "\n\t<ul$anchor>$link";
				};
				$anchor = '';

			};
			debug($list);
			return $list.str_replace($find,$replace,$input);
		}
		else
		{
			return "<!-- NO HEADINGS FOUND -->\n\n$input";
		};
	}
	else
	{
		die("\"$file_location\" is not a valid file or is not readable.");
	}
};
