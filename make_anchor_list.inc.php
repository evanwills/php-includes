<?php

function make_anchor_list( $file_location , $start_level = 2 , $type = 'ul' , $top_anchor = 'content_col' )
{
	$levels = '';
	$list = '';
	for( $start_level ; $start_level <= 6 ; ++$start_level )
	{
		$levels .= $start_level;
	};
	unset($start_level);
	$depth = 0;
	if($type != 'ul')
	{
		$type = 'ol';
	};
	$head_regex = '/
([\t\ ]*)		# [1]	Capturing the leading white space to ensure formatting is kept nice
(?:
    <p\ class="top">	# In case this has already been run on a page, include the top link
	    <a\ href="#[^"]+"\ id="[^"]+">top<\/a>
    <\/p>
    [\r\n\t\ ]+
)?			# top link is optional
<h
    (['.$levels.'])	# [2]	Capture the heading level )
    (.*?)		# [3]	capture any other attributes assigned to the heading before the ID
    (?:\ id="		# Match the ID attribute - but do not capture it as a whole
	    ([^"]+)	# [4]	Capture the ID if it is set
    ")?			# ID attribute is optional
    (.*?)		# [5]	Capture any additional attributes assigned to the heading
>
	(.*?)		# [6]	Capture the heading text
<\/h\2>
/isx'; debug($head_regex);

	if(is_file($file_location) && is_readable($file_location))
	{
		$output = '';

		$input = file_get_contents($file_location);
		$input = preg_replace('/\n<!-- START ANCHOR LIST -->.*?<!-- END ANCHOR LIST -->/','',$input);
		if(preg_match_all( $head_regex , $input , $headings , PREG_SET_ORDER))
		{
			$level = 0;
			$anchor = ' class="anchor_list"';
			for( $a = 0 ; $a < count($headings) ; ++$a )
			{
				$last_level = $level;
				$space = $headings[$a][1];
				$level = $headings[$a][2];
				if($headings[$a][4] != '')
				{	
					$id = $headings[$a][4];
				}
				else
				{
					$id = preg_replace( array('/^[^a-z]+/i','/[^-a-z_0-9]+/i')  , array('','_') , $headings[$a][5] );
				};
				$heading_attr = $headings[$a][3];$headings[$a][5];
				$heading = $headings[$a][6];
				$whole = $headings[$a][0];
				
				$find[] = $whole;
				$replace [] =	 "$space<p class=\"top\"><a href=\"#$top_anchor\" id=\"$id\">top</a></p>\n"
						."$space<h$level{$heading_attr}>$heading</h$level>";

				if( $last_level == $level )
				{
					$tabs = tabs( $depth , '=' );
					$start = "\n{$tabs[1]}</li>";
				}
				elseif ($last_level > $level)
				{
					--$depth;
					$tabs = tabs( $depth , '>' );
					$start = "\n{$tabs[0]}</li>\n{$tabs[1]}</$type>\n{$tabs[2]}</li>";
				}
				else
				{
					++$depth;
					$tabs = tabs( $depth , '<' );
					$start = "\n{$tabs[2]}<{$type}$anchor>";
				};
				$list .= "$start\n{$tabs[1]}<li>\n{$tabs[1]}\t<a href=\"#$id\">$heading</a>";
				$anchor = '';

			};
			for( $depth ; $depth > 0 ; --$depth )
			{
				$tabs = tabs( $depth , '>' );
				$list .= "\n{$tabs[1]}</li>\n{$tabs[0]}</$type>";
			}

			$list = "\n<!-- START ANCHOR LIST -->\n$list\n\n<!-- END ANCHOR LIST -->";
			$input = str_replace($find,$replace,$input);
			return preg_replace('/^(.*?<body[^>]*>)?(.*)$/is' , '\1'.$list.'\2' , $input );
		}
		else
		{
			return "<!-- NO HEADINGS FOUND -->\n\n$input";
		};
	}
	else
	{
		die("\"$file_location\" is not a valid file or is not readable.");
	};
};

function tabs( $num , $type = '=' )
{
	$plus = "\t\t";
	$equals = "\t";
	$minus = '';
	for( $num ; $num > 1 ; --$num )
	{
		$plus .= "\t\t";
		$equals .= "\t\t";
		$minus .= "\t\t";
	}
	switch($type)
	{
		case '>':
			return array( $minus , $equals , $plus );
			break;
		case '<':
			return array( $plus , $equals , $minus );
			break;
		default:
			return array( '' , $equals , '' );
			break;
	};
}
