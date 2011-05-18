<?php

/**
 * @function time_ellapsed() renders the difference (in human
 * readable format) between the current time (when the function is
 * called) and the timestamp provided.
 *
 * @param $start integer previously generated timestamp
 * @param $show_unit boolean show or hide the units being rendered
 * @param $show_day boolean if true, show days plus hours otherwise
 *        show total hours even if they exceed 24.
 *
 * @const TIME_DELIMITER string to delimit hours, minutes and seconds
 *        if not predefined, defaults to ':'
 *
 * @return string human readable representation of the time
 *        difference between the current timestamp and the supplied
 *        timestamp
 */
function time_ellapsed( $start , $show_unit = true , $show_day = true )
{
	$days = 0;
	$hours = 0;
	$total_hours = 0;
	$minutes = 0;
	$seconds = 0;

	$output = '';
	$unit = 'seconds';

	if( !defined('TIME_DELIMITER') )
	{
		define('TIME_DELIMITER',':');
	};

	if($show_unit === false)
	{
		$show_day = false;
	};

	$tm = ( time() - $start );
	if( $tm > 59 )
	{
		$unit = 'minutes';
		if( $tm > 3599 )
		{
			$unit = 'hours';
			if( $tm == 86400 )
			{
				return "1 day";
			}
			elseif( $tm > 86399 && $show_day === true )
			{
				$days = floor( $tm / 86400 );
				$unit = 'days';
			}
			else
			{
				$show_day = false;
			};
			$total_hours = floor( $tm / 3600 );
			$hours = floor( ( $tm - ( $days * 86400 ) ) / 3600 );

			if( $show_day === true )
			{
				$output .= "$days ".plural('day',$days).", $hours".TIME_DELIMITER;
				$unit = '';
			}
			else
			{
				$output .= $total_hours.TIME_DELIMITER;
			};
		};
		$minutes = floor( ( $tm - ( $total_hours * 3600 ) ) / 60 );
		if($output != '')
		{
			$output .= pre0($minutes).TIME_DELIMITER;
		}
		else
		{
			$output .= $minutes.TIME_DELIMITER;
		};
	}
	else
	{
		if( $show_unit === true )
		{
			$output = "$tm ".plural('seconds',$seconds);
		}
		else
		{
			$output = $tm;
		}
		return $output;
	};
	$seconds = ( $tm - ( ( $total_hours * 3600 ) + ( $minutes * 60 ) ) );
	if($output != '')
	{
		$output .= pre0($seconds);
	}
	else
	{
		$output .= $seconds;
	};

	if( $show_unit === true && isset($$unit) )
	{
		$output .= ' '.plural($unit,$$unit);
	};
	return $output;
};

/**
 * @function plural() appends an 's' if it's gramatically appropriate
 * If the number supplied is not equal to one, a string will be
 * appended to the supplied string.
 * If the string is already teminated with an 's', the 's' is removed
 * before checking whether it's needed.
 *
 * @param $str string (assumed to describe the number)
 * @param $num mixed (integer or double/float) number to be checked
 *
 * @return string
 */
function plural( $str , $num )
{
	if( $str != '' )
	{
		if( substr_compare( $str , 's' , -1 , 1 ) == 0 )
		{
			$str = substr_replace( $str , '' , -1 , 1 );
		};
		if( is_numeric($num) && $num != 1 )
		{
			return $str.'s';
		};
	};
	return $str;
};

/**
 * @function pre0() checks if the input supplied is less than 10, if
 * so, it prepends a zero to the output
 *
 * @param $input integer
 * @return mixed string if input is numeric and less than 10
 *        otherwise, the same as $input
 */
function pre0( $input )
{
	if( is_numeric($input) && $input < 10 )
	{
		return "0$input";
	};
	return $input;
};


function micro_time_ellapsed($start)
{
};

