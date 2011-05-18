<?php

function check_non_public()
{
	if( isset($_SERVER['HTTP_HOST']) )
	{
		$host = $_SERVER['HTTP_HOST'];
		if( 
			strlen($host) > 11 &&
			substr_compare($host,'.acu.edu.au',-11,11) == 0 &&
			$host != 'apps.acu.edu.au' &&
			$host != 'blogs.acu.edu.au' &&
			$host != 'student-blogs.acu.edu.au'
		)
		{
			return false;
		}
		else
		{	
			return true;
		};
	}
	else
	{
		return true;
	};
};

function check_user()
{
	$home = scandir('/home/');
	if( isset($_REQUEST['user']) )
	{
		$user = $_REQUEST['user'];
		foreach( $home as $home_user )
		{
			if($home_user == $user)
			{
				return $user;
			};
		};
	}
	elseif(isset($_SERVER['REMOTE_ADDR']))
	{
		switch($_SERVER['REMOTE_ADDR'])
		{
			case '':
				return 'victoriaw';
			case '':
				return 'robins';
			case '':
				return '';
			case '203.10.46.1':
				return 'evanw';
		};
	};
	return '';
};
