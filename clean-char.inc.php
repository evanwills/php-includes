<?php
function clean_char($string,$mode = 'text')
{

// ==============================================
//	This funciton cleans bad characters from a
//		string, replacing them with the 
//		appropriate plain text or HTML 
//		character entity
//
//	$string	= a string from an input or textarea
//	$mode	= 	0 or text or txt	(plain text)
//			1 or html or htm	(simple HTML)
//			2 or curly or 		(html with curly quotes)
//			3 DO NOTHING
//
//	$mode == text is the default operation
//
// --------------------------------------------
//	how each entry of $replace should be used
//		0	TEXT
//		1	HTML
//		2	HTML with curly quotes
//		3	HTML and HTML with curly quotes
//		4	TEXT and HTML
//		5	All (TEXT, HTML and HTML with curly quotes)
// --------------------------------------------
//
// ==============================================


unset($replace_csv);


	$replace_csv = "
&&	&	0	ampersand entity with standard ampersand (33)
&amp;	&	0	ampersand entity with standard ampersand (33)
&#38;	&	0	ampersand entity with standard ampersand (34)
&#038;	&	0	ampersand entity with standard ampersand (35)
&amp;amp;	&	0	ampersand entity with standard ampersand (36)
&amp;amp;	&	0	ampersand entity with standard ampersand (37)
";


	$replace_csv .= "
&#160;	 	0	ASCII code non-breaking space with standard space (38)
&nbsp;	 	0	HTML NBSP with standard space (39)
&#8194;	 	0	ASCII code non-breaking space with standard space (38)
&#8195;	 	0	ASCII code non-breaking space with standard space (38)
&thinsp;	 	0	ASCII code non-breaking space with standard space (38)
&#8201;	 	0	ASCII code non-breaking space with standard space (38)

•	*	0	Bullet point with ASCI asterisk (40)
&#149;	*	0	Bullet point with ASCI Bullet Point (41)
&#8226;	*	0	Bullet point with ASCI Bullet Point (42)

–	-	0	En Dashs with hyphen (42)
&ndash;	-	0	En Dashs with hyphen (43)
&#150;	-	0	En Dashs with hyphen (44)
&#8211;	-	0	Em Dashs with hyphen (45)

—	-	0	Em Dashs with hyphen (47)
&mdash;	-	0	Em Dashs with hyphen (48)
&#151;	-	0	Em Dashs with hyphen (49)
&#8212;	-	0	Em Dashs with hyphen (50)

™	(TM)	0	TM symbols with HTML (52)
&153;	(TM)	0	TM symbols with HTML (53)
&trade;	(TM)	0	TM symbols with HTML (54)

®	(R)	0	Registered symbol with plain text R (56)
&#174;	(R)	0	Registered symbol with plain text R (57)
&reg;	(R)	0	Registered symbol with plain text R (58)

»	>>	0	HTML double angle brackets (60)
&#187;	>>	0	HTML double angle brackets (61)
&raquo;	>>	0	HTML double angle brackets (62)
";


	$replace_csv .= "
•	&bull;	2	Bullet point with HTML Bullet Point (64)
&#149;	&bull;	2	Bullet point with HTML Bullet Point (65)
&#8226;	&bull;	2	Bullet point with HTML Bullet Point (66)

…	&hellip;	2	Ellipsis with HTML Ellipsis (68)
...	&hellip;	2	Ellipsis with HTML Ellipsis (69)
&#133;	&hellip;	2	Ellipsis with HTML Ellipsis (70)
&#8230;	&hellip;	2	Ellipsis with HTML Ellipsis (71)

‘	&lsquo;	2	left single curly quotes with normal (73)
’	&rsquo;	2	right curly single quotes with normal (74)
“	&ldquo;	2	left curly double quotes with normal (75)
”	&rdquo;	2	right curly double quotes with normal (76)

&#8216;	&lsquo;	2	left curly single quotes with normal (78)
&#8217;	&rsquo;	2	right curly single quotes with normal (79)
&#8220;	&ldquo;	2	left curly double quotes with normal (80)
&#8221;	&rdquo;	2	right curly double quotes with normal (81)
";


	$replace_csv .= "
&  	&amp; 	3	ampersand entity with standard ampersand (83)
&#38;	&amp;	3	ampersand entity with standard ampersand (84)
&#038;	&amp;	3	ampersand entity with standard ampersand (85)
&amp;amp;	&amp;	3	ampersand entity with standard ampersand (86)
";


	$replace_csv .= "
&#160;	&nbsp;	3	ASCII code non-breaking space with HTML non-breaking space	(86a)
&#8194;	&nbsp;	3	ASCII code non-breaking space with standard space (38)
&#8195;	&nbsp;	3	ASCII code non-breaking space with standard space (38)
&thinsp;	&nbsp;	3	ASCII code non-breaking space with standard space (38)
&#8201;	&nbsp;	3	ASCII code non-breaking space with standard space (38)

&nbps;&nbsp;&nbsp;&nbsp;&nbsp;	&nbsp;	3	Quintupple NBSP to single NBSP
&nbsp;&nbsp;&nbsp;&nbsp;	&nbsp;	3	Quadruple NBSP to single NBSP
&nbsp;&nbsp;&nbsp;	&nbsp;	3	Tripple NBSP to single NBSP
&nbsp;&nbsp;	&nbsp;	Double NBSP to single NBSP
<td> </td>	<td>&nbsp;</td>	3	filling empty table cells
";


	$replace_csv .= "
–	&ndash;	3	En Dashs with HTML En Dash (87)
&#150;	&ndash;	3	En Dashs with HTML En Dash (88)
&#8211;	&ndash;	3	Em Dashs with HTML En Dash (89)

—	&mdash;	3	Em Dashs with HTML Em Dash (91)
&#151;	&mdash;	3	Em Dashs with HTML Em Dash (92)
&#8212;	&mdash;	3	Em Dashs with HTML Em Dash (93)

&153;	&trade;	3	TM symbols with HTML (95)
™	&trade;	3	TM symbols with HTML (96)

®	&reg;	3	Registered symbol with HTML entity (98)
&#174;	&reg;	3  	Registered symbol with HTML entity (99)

»	&raquo;	3	HTML double angle brackets (101)
&#187;	&raquo;	3	HTML double angle brackets (102)

&#65533;	'	3	left single curly quotes with normal
.	.	3	left single curly quotes with normal
‘	'	4	left single curly quotes with normal (106)
’	'	4	right curly single quotes with normal (107)
“	\"	4	left curly double quotes with normal (108)
”	\"	4	right curly double quotes with normal (109)

‘	'	4	left single curly quotes with normal (111)
’	'	4	right curly single quotes with normal (112)
“	\"	4	left curly double quotes with normal (113)
”	\"	4	right curly double quotes with normal (114)
	
…	...	4	Ellipsis with 3 full stops (116)
&#8230;	...	4	Ellipsis with 3 full stops (117)
&#133;	...	4	Ellipsis with 3 full stops (118)
&hellip;	...	4	Ellipsis with 3 full stops (119)

&#8216;	'	4	left curly single quotes with normal (121)
&#8217;	'	4	right curly single quotes with normal (122)
&#8220;	\"	4	left curly double quotes with normal (123)
&#8221;	\"	4	right curly double quotes with normal (124)

&lsquo;	'	4	replaces left curly single quotes with normal (126)
&rsquo;	'	4	replaces right curly single quotes with normal (127)
&ldquo;	\"	4	replaces left curly double quotes with normal (128)
&rdquo;	\"	4	replaces right curly double quotes with normal (129)
";


	$replace_csv .= "
&#64979;	-	5	replaces non-breaking hyphen with hyphen (131)

&#160;	 	5	ASCII code non-breaking space with standard space (38)
&nbsp; 	 	5	HTML NBSP with standard space (39)
 &nbsp;	 	5	HTML NBSP with standard space (39)
&#8194;	 	5	ASCII code non-breaking space with standard space (38)
&#8195;	 	5	ASCII code non-breaking space with standard space (38)
&thinsp;	 	5	ASCII code non-breaking space with standard space (38)
&#8201;	 	5	ASCII code non-breaking space with standard space (38)
";

//-------------------------------------------

	$replace_csv .= "
&&amp;	&amp;	5	fix bodgy ampersands (132)
&amp;&amp;	&amp;	5	fix bodgy ampersands (133)
";


	$replace_csv = $replace_csv."
&amp; 	&	0	ampersand entity with standard ampersand (135)
&amp;&amp;	&amp;	0	ampersand entity with standard ampersand (137)
&amp;&amp;	&amp;	0	ampersand entity with standard ampersand (137)
&amp;amp;	&amp;	0	ampersand entity with standard ampersand (136)
";

	$replace_csv = $replace_csv."
    	 	3	multiple spaces (4 > 1)
   	 	3	multiple spaces (3 > 1)
  	 	3	multiple spaces (2 > 1)
";
/*
	$replace_csv = $replace_csv."
   \n	\n	5	spaces at the end of lines (two)
  \n	\n	5	spaces at the end of lines (two)
 \n	\n	5	spaces at the end of lines (two)
. \n	\n	5	spaces at the end of lines (two)
> \n	\n	5	spaces at the end of lines (two)
; \n	\n	5	spaces at the end of lines (two)
: \n	\n	5	spaces at the end of lines (two)
' \n	\n	5	spaces at the end of lines (two)
\" \n	\n	5	spaces at the end of lines (two)
";
*/
	$replace_csv = explode("\n", $replace_csv);
	for( $rep = 0 ; $rep < count($replace_csv) ; ++$rep )
	{
		$row = trim($replace_csv[$rep]);
		if(!empty( $row ))
		{
			$tmp_replace_csv[] = explode("\t", $replace_csv[$rep]);
		};
	};
	$replace_csv = $tmp_replace_csv;



	switch($mode){
		case 1:
		case "html":
		case "htm":
			for( $rep = 0 ; $rep < count($replace_csv) ; ++$rep )
			{
				switch($replace_csv[$rep][2])
				{
					case 1:
					case 3:
					case 4:
					case 5:
						$first = $replace_csv[$rep][0];
						$second = $replace_csv[$rep][1];
						$string = str_replace("$first","$second",$string);
						break;
				}
				$rep++;
			}
			break;
		
		case 2:
		case "curly":
			for( $rep = 0 ; $rep < count($replace_csv) ; ++$rep )
			{
				switch($replace_csv[$rep][2])
				{
					case 2:
					case 3: 
					case 5:	
						$first = $replace_csv[$rep][0];
						$second = $replace_csv[$rep][1];
						$string = str_replace("$first","$second",$string);
						break;
				}
				$rep++;
			}
			break;
		case 3:
		case "nothing": break;

		default:
			for( $rep = 0 ; $rep < count($replace_csv) ; ++$rep )
			{
				switch($replace_csv[$rep][2])
				{
					case 0:
					case 4: 
					case 5:	
						$first = $replace_csv[$rep][0];
						$second = $replace_csv[$rep][1];
						$string = str_replace("$first","$second",$string);
						break;
				}
				$rep++;
			}
	}

	$string = str_replace("& ","&amp; ",$string);
	return	$string;
}
?>
