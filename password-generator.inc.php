<?php

function password( $num_chars = 8 , $limit = 35 )
{
	$c = '';
	for( $a = 0 ; $a < $num_chars ; ++$a )
	{
		$b = rand(1,$limit);
		switch($b)
		{	
			case 1: 
			case 2: 
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
			case 8:
			case 9: $c .= $b; break;
			case 10: $c .= 'a'; break;
			case 11: $c .= 'b'; break;
			case 12: $c .= 'c'; break;
			case 13: $c .= 'd'; break;
			case 14: $c .= 'e'; break;
			case 15: $c .= 'f'; break;
			case 16: $c .= 'g'; break;
			case 17: $c .= 'h'; break;
			case 18: $c .= 'i'; break;
			case 19: $c .= 'j'; break;
			case 20: $c .= 'k'; break;
			case 21: $c .= 'l'; break;
			case 22: $c .= 'm'; break;
			case 23: $c .= 'n'; break;
			case 24: $c .= 'o'; break;
			case 25: $c .= 'p'; break;
			case 26: $c .= 'q'; break;
			case 27: $c .= 'r'; break;
			case 28: $c .= 's'; break;
			case 29: $c .= 't'; break;
			case 30: $c .= 'u'; break;
			case 31: $c .= 'v'; break;
			case 32: $c .= 'w'; break;
			case 33: $c .= 'x'; break;
			case 34: $c .= 'y'; break;
			case 35: $c .= 'z'; break;
			case 36: $c .= '_'; break;
			case 37: $c .= '-'; break;
			case 38: $c .= '~'; break;
			case 39: $c .= '!'; break;
			case 40: $c .= '@'; break;
			case 41: $c .= '#'; break;
			case 42: $c .= '$'; break;
			case 43: $c .= '%'; break;
			case 44: $c .= '^'; break;
			case 45: $c .= '&'; break;
			case 46: $c .= '*'; break;
			case 47: $c .= '('; break;
			case 48: $c .= ')'; break;
			case 49: $c .= '+'; break;
			case 50: $c .= '='; break;
			case 51: $c .= '{'; break;
			case 52: $c .= '}'; break;
			case 53: $c .= '['; break;
			case 54: $c .= ']'; break;
			case 55: $c .= '|'; break;
			case 56: $c .= '\\'; break;
			case 57: $c .= ':'; break;
			case 58: $c .= ';'; break;
			case 59: $c .= '<'; break;
			case 60: $c .= '>'; break;
			case 61: $c .= ','; break;
			case 62: $c .= '.'; break;
			case 63: $c .= '?'; break;
			case 64: $c .= '/'; break;
			case 65: $c .= '\''; break;
			case 66: $c .= '"'; break;
/*			
			case 67: $c .= ''; break;
			case 68: $c .= ''; break;
			case 69: $c .= ''; break;
			case 70: $c .= ''; break;
			case 71: $c .= ''; break;
			case 72: $c .= ''; break;
			case 73: $c .= ''; break;
			case 74: $c .= ''; break;
			case 75: $c .= ''; break;
			case 76: $c .= ''; break;
			case 77: $c .= ''; break;
			case 78: $c .= ''; break;
			case 79: $c .= ''; break;
			case 80: $c .= ''; break;
			case 81: $c .= ''; break;
			case 82: $c .= ''; break;
			case 83: $c .= ''; break;
			case 84: $c .= ''; break;
			case 85: $c .= ''; break;
			case 86: $c .= ''; break;
			case 87: $c .= ''; break;
			case 88: $c .= ''; break;
			case 89: $c .= ''; break;
			case 90: $c .= ''; break;
*/
		};
	};
	return $c;
};

/*
for( $a = 0 ; $a < 10 ; ++$a )
{
	$tmp = password( 8,55);
	echo "$tmp\n";
}
*/
