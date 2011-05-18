<?php

/**
 * @function s() checks if a given number is greater than 1 or is
 * floating point number then returns an 's' if so.
 *
 * @param $num integer;
 * @return string 's' or '';
 */
function s($num)
{
	if( is_numeric($num) && ( $num > 1 || is_float($num) || $num == 0 ))
	{
		return 's';
	};
};
