<?php

if( !class_exists('time__ellapsed') )
{
	require_once(dirname(__FILE__).'/time/time_ellapsed.class.php');
}

abstract class write_log
{

	protected $level = 0;

	protected $default_level = 0;

	protected $time = 0; 

	protected $show_time = false;
	protected $show_trace = false;

/**
 * @var $output string template for backtrace part of message
 */
	protected $output = '';

	public function __construct( $log_level , $output = '' )
	{
		$this->level = $log_level;
		$this->default_level = $log_level;
		if( is_string($output))
		{
			if( '' == trim($output) || substr_count( $output , '[[MSG]]' ) == 0 )
			{
				$this->output = "\n[[MSG]]";
				$keywords = array('MSG');
			}
			else
			{
				if( preg_match_all('/\[\[(A-Z)\]\]/',$output,$matches) )
				{
					debug($matches);
				}
				if( substr_count( $output , '[[TIME]]' ) > 0 )
				{
					$this->show_time = true;
				}
				if( substr_count( $output , '[[file]]' ) > 0 || substr_count( $output , '[[line]]' ) )
				{
					$this->show_trace = true;
				}
			}
		}
		elseif( is_array($output) )
		{
			if( in_array( 'time' , $output ) )
			{
				$this->show_time = true;
			}
			if( in_array( 'line' , $output ) || in_array( 'file' , $output ) )
			{
				$this->show_trace = true;
			}
		}
		if( $this->show_time === true )
		{
			$this->time = new time__ellapsed( true , true , ':' , true );
		}
		else
		{
			$this->time = new time__ellapsed_null();
		}
	}
/**
 * @function write() checks the LOG_LEVEL (set in
 * PHPlist_sync-user.config.php) against the level for that message.
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
		}
		if( $die == true )
		{
			exit;
		}
	}

	abstract protected function do_write( $input );

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
			}
		}

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
		}

		return round($value,2).$type;
	}

/**
 * @method show_output() does the job of actually formatting the output
 */
	protected function show_output($msg)
	{//debug($msg);
		if( $this->output != '' )
		{
			$tmp = trim($this->output);
			if( $tmp == '[[MSG]]' )
			{
				return str_replace('[[MSG]]',$msg,$this->output);
			}
			else
			{
				$find = array('[[MSG]]');
				$replace = array($msg);

				$trace = debug_backtrace();
				if( isset($trace[2]) )
				{		
					$find = array('[[MSG]]');
					$replace = array($msg);
					if( $this->show_time === true )
					{
						$find[] = '[[TIME]]';
						$replace[] = $this->time->time_ellapsed();
					}
					if( $this->show_trace === true )
					{
						foreach( $trace[$a] as $key => $value )
						{
							$find[] = '[['.strtoupper($key).']]';
							if( $key != 'args' )
							{
								$replace[] = $value;
							}
//							else
//							{
//								$replace[] = var_export($value,true);
//							}
						}
					}
				}
				return str_replace( $find , $replace , $this->output );
			}
		}
		else
		{
			return '';
		}
	}


/**
 * @method __clone() resets the start time.
 */
	public function __clone()
	{
		if( $this->show_time === true )
		{
			$this->time = new time__ellapsed( true , true , ':' , true );
		}
	}

}

class log_to_file extends write_log
{
	protected $file_handle = null;
	protected $file_name = '';

	public function __construct( $log_level , $output = '' , $file , $empty_file = true )
	{
		parent::__construct($log_level , $output );
		if( is_readable(dirname($file)) && is_writable(dirname($file)))
		{
			if( is_file($file) && ( !is_readable($file) || !is_writable($file) ))
			{
				$this->file_name = dirname($file).'/logging-output_'.date('Y-m-d_H-m').'.txt';
			}
			else
			{
				$this->file_name = $file;
			}
		}
		elseif( is_readable('./') && is_writable('./'))
		{
			$this->file_name = './logging-output_'.date('Y-m-d_H-m').'.txt';
		}

		if( $empty_file === false )
		{
			$rw_mode = 'a+';
		}
		else
		{
			$rw_mode = 'w+';
		}
		if($this->file_name != '' )
		{
			$this->file_handle = fopen($this->file_name,$rw_mode);
		}
		if( $this->file_handle === null )
		{
			die('something went wrong');
		}
	}

	protected function do_write( $input )
	{
		fwrite( $this->file_handle , $this->show_output($input) );
	}
	
	public function __destruct()
	{
		if( fread($this->file_handle,1000) == '' )
		{
			$unlinkit = true;
		}
		else
		{
			$unlinkit = false;
		}
		fclose( $this->file_handle );
		if( $unlinkit === true )
		{
			unlink($this->file_name);
		}
	}
}


/**
 * @class log_to_db writes log info to a database;
 */
class log_to_db extends write_log
{
	private $db = null;
	private $table_name = '';

	public function __construct( $log_level , $db , $table_name , $output = array('line','file','msg','time','function') )
	{
		parent::__construct( $log_level , $output );
		$this->db = $db;
		$this->table_name = $table_name;
	}

	protected function do_write( $input )
	{
		$fields = show_output();
		$fields['msg'] = $input;
		$sql_fields = "\nINSERT INTO\t`{$this->table_name}`\n(\n\t ";
		$sql_values = "\n)\nVALUES\n(\n\t ";
		$sep = '';
		foreach( $fields as $key => $value )
		{
			$sql_fields .= $sep.$key;
			$sql_values .= $sep.$value;
			$sep = "\n\t,";
		}
		$this->db->query($sql_fields.$sql_values."\n);");
	}

	protected function show_output($msg)
	{
		$a = 2;
		$debug = debug_backtrace();
		if( $this->output != false )
		{
			if(isset($debug[$a]))
			{
				unset( $debug[$a]['args'] );
				return  $debug[$a] ;
			}
			else
			{
				return array(
					 'line' => 0
					,'file' => 'UNKNOWN'
					,'function' => 'UNKNOWN'
				);
			}
		}
	}
}


class log_to_debug extends write_log
{
	public function __construct( $log_level , $output = '' )
	{
		if( $output == '' )
		{
			$output = "

===========================================================
[[FILE]] - Line: [[LINE]] ([[FUNCTION]]
-----------------------------------------------------------
-----------------------------------------------------------
";
		}
		parent::__construct( $log_level , $output );
	}

	protected function do_write( $input )
	{
		echo $this->show_output($input);
	}

	public function __destruct()
	{
		echo "\n\n";
	}
}


class log_to_screen extends write_log
{
	public function __construct( $log_level , $output = '' )
	{
		parent::__construct( $log_level , $output );
		echo "\n\n";
	}

	protected function do_write( $input )
	{
		echo $this->show_output($input);
	}

	public function __destruct()
	{
		echo "\n\n";
	}
}



class log_to_null extends write_log
{
	public function __construct( $log_level , $output = '' ) {}
	protected function do_write( $input ) {}
}

/**
 * @class log_to_multi outputs to all of the log types supplied
 * In essecence it agregates the log types
 */
class log_to_multi
{
/**
 * @var $logs array of log objects 
 */
	protected $logs = array();


/**
 * @method __construct expects an array for each log type
 * The first item in each array must be the log type
 *	(e.g. 'file', 'db', 'screen', 'debug')
 * The second item must be an array of arguments (in correct order),
 * arguments you wish to supply for the class constructor
 *	NOTE:	arguments that are optional for the class constructor
 *		are also optional here.
 *
 * e.g.		array(
 *			 'file'
 *			,array(
 *				 2	 // log level
 *				,'/var/www/logs/test.log' Log file
 *				,true
 *				,"[line [[line]] - [[FILE]] ([[FUNCTION]])]\n"
 *			 )
 *		 )
 *		,array(
 *			 'debug'
 *			,array( // list of arguments );
 *				,2	// log level
 *			 )
 *		 )
 *	)
 */
	public function __construct( $log_objects )
	{
		if( is_array($log_objects) && !empty($log_objects) )
		{
			foreach( $log_objects as $log_object )
			{
				if( is_a( $log_object , 'write_log' ) )
				{
					$this->logs[] = $log_object;
				}
			}
		}
	}

	protected function write( $input , $level = 0 , $die = false )
	{
		foreach($this->logs as $log )
		{
			$log->write( $input , $level = 0 , $die = false );
		}
	}
}

