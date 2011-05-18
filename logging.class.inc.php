<?php

class write_log
{
	public $level = 0;
	public $default_level = 0;
	public function __construct($log_level)
	{
		$this->level = $log_level;
		$this->default_level = $log_level;
	}
/**
 * @function write() checks the LOG_LEVEL (set in
 * PHPlist_sync-user.config.php) agains the level for that message.
 * If the message level is equal to or higher than the LOG_LEVEL the
 * message is logged.
 *
 * If LOG_LEVEL is 5 (debug mode) everything is written to screen.
 */
	public function write( $input , $level = 0 , $die = false )
	{
		if( $this->level >= $level )
		{
			$this->do_write($input);
		};
		if($die == true )
		{
			exit;
		};
	}

	public function level_update( $update )
	{
		if($update == 'restore')
		{
			$this->level = $this->default_level;
		}
		else
		{
			if(is_int($update) && $update >= 0 )
			{
				$this->level = $update;
			};
		};

	}
	public function format_time( $s_time , $e_time )
	{
		if(!is_array($s_time))
		{
			$s_time = array($s_time,$s_time);
		};
		if(!is_array($e_time))
		{
			$e_time = array($e_time,$e_time);
		};

		$tmp = $e_time[0] - $s_time[0];
		$unit = '';
		$output = '';
		if($tmp > 59)
		{
			$hours = floor($tmp/3600);
			$hours_in = $hours * 3600;
			$minutes = floor(($tmp - $hours_in)/60);
			$minutes_in = $minutes*60;
			$seconds = ($tmp - $hours_in - $minutes_in);
			if($hours > 0)
			{
				if($hours > 9)
				{
					$output .= "0$hours:";
				}
				else
				{
					$output .= "0$hours:";
				};
				$unit = '(HH:MM:SS)';
			};
			if($minutes > 0)
			{
				if($minutes > 9)
				{
					$output .= "$minutes:";
				}
				else
				{
					$output .= "0$minutes:";
				};
				if($unit == '')
				{
					$unit = '(MM:SS)';
				};
			}
			elseif($hours > 0)
			{	
				$output .= '00:';
			};
			if($seconds > 9)
			{
				$output .= $seconds;
			}
			elseif($seconds > 0)
			{
				$output .= "0$seconds";
			}
			else
			{
				$output .= "00";
			};
		}
		else
		{
			$tmp = (($e_time[0]+$e_time[1]) - ($s_time[0]+$s_time[1]));
			$output = round($tmp,2);
		};
		if($unit == '')
		{
			$unit = 'Seconds';
		};
		return "$output $unit";
	}

	public function format_file_size($file)
	{
		$bytes = filesize($file);
		if($bytes > (1024*1024))
		{	
			$type = 'MB';
			$value = ( ( $bytes / 1024 ) / 1024 );
		}
		elseif($bytes > 1024)
		{	
			$type = 'KB';
			$value = ( $bytes / 1024 );
		}
		else
		{	
			$type = 'B';
			$value = $bytes;
		};

		return round($value,2).$type;
	}

}

class log_to_file extends write_log
{
	protected $file_name = '';
	public function __construct( $log_level , $file , $empty_file = true )
	{
		parent::__construct($log_level);
		if( is_readable(dirname($file)) && is_writable(dirname($file)))
		{
			if( !is_file($file) || ( is_readable($file) && is_writable($file) ))
			{
				$this->file_name = $file;
			}
			else
			{
				$this->file_name = dirname($file).'/logging-output_'.date('Y-m-d_H-m').'.txt';
			}
		}
		elseif( is_readable('./') && is_writable('./'))
		{
			$this->file_name = './logging-output_'.date('Y-m-d_H-m').'.txt';
		};
		if($empty_file === true)
		{
			file_put_contents($this->file_name,'');
		};
	}

	protected function do_write( $input , $do_die = false )
	{
		file_put_contents($this->file_name,"\n$input",FILE_APPEND);
	}
}

class log_to_db extends write_log
{
	public function __construct( $config )
	{
		parent::__construct($config);
	}
}


class log_to_debug extends write_log
{
	public function __construct( $config )
	{
		parent::__construct($config);
	}

	protected function do_write( $input )
	{
		$tmp_debug = debug_backtrace();
		$br = "\n---------------------------------------------------------------------";
		if(isset($tmp_debug[1]))
		{
			echo "\n=====================================================================";
			echo "\n{$tmp_debug[1]['file']} - LINE: {$tmp_debug[1]['line']}$br";
			unset($tmp_debug[1]['file'],$tmp_debug[1]['line']);
			foreach($tmp_debug[1] as $key => $value )
			{
				echo "\n    $key = $value";
			};
			echo "$br\n$input\n";
		};
	}
}


class log_to_screen extends write_log
{
	public function __construct( $log_level )
	{
		parent::__construct( $log_level );
		echo "\n\n";
	}

	protected function do_write( $input )
	{
		echo "\n$input";
	}

	public function __destruct()
	{
		echo "\n\n";
	}
}

class log_to_screen_and_file extends log_to_file
{
	public function __construct( $log_level , $file , $empty_file = true )
	{
		parent::__construct($log_level, $file , $empty_file);
		echo "\n\n";
	}

	protected function do_write( $input , $do_die = false )
	{
		file_put_contents($this->file_name,"\n$input",FILE_APPEND);
		echo "\n$input";
	}
	public function __destruct()
	{
		echo "\n\n";
	}
}
