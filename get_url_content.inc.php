<?php

// check if curl is available on this server
if( defined('CURLOPT_URL')):

/**
 * @function get_protected_content() connects to a supplied URL,
 * authenticates using supplied login credentials and returns the
 * password protected content from that URL as a string. Or an array
 * of error messages
 *
 * This function relies on a few assumptions:
 *   1  that pages may have multiple forms but the login form on any
 *      given page is the first or only one with a "password" type
 *      input field
 *
 *   2  that hidden or submit type inputs should retain the values
 *      set by the generating server.
 *
 *   3  that you supply the values for any non hidden/submit type
 *      fields (via the $login_values associative array).
 *
 *   4  that the login page returns you to the page you originally
 *      requested.
 *
 *   5  that the login form doesn't do anything funky with JavaScript
 *      (like encode/encrypt the username or password before
 *       submission).
 *-------------------------------------------------------------------
 *
 * @param $url string URL for page to be retrieved
 *
 * @param $login_values array associative array where the key matches
 *        a login form field name and the value is the value that
 *        should be submitted for that field
 *      NOTE: the array keys must be identical to the INPUT /
 *            OPTION / TEXTAREA field names used in the form.
 *	NOTE ALSO: you can also provide for HTTP authentication
 *            with the 'httpauth' key. If using the same login
 *            credentials, the value should be an empty array or
 *            nothing. Otherwise use 'username' and 'password' as the
 *            respective keys.
 *            Plus the 'authmethod' for determining the
 *            authentication method (see PHP's cURL documentation)
 *            If no username and password are set, it assumes the
 *            first two key/value pairs in $login_values are the
 *            username and password fields respectively.
 *      FINALLY: you can also specify proxy authentication
 *            credentials using the 'proxy' key. Its value must be
 *            another associative array that MUST contain the 'url'
 *            key/value pair and if different login credentials are
 *            required, the 'username' and 'password' key/value pairs.
 *            Plus the 'authmethod' if not the default
 *            (CURLAUTH_ANYSAFE). (see PHP's cURL documentation)
 *            If no username and password are set, it assumes the
 *            first two key/value pairs in $login_values are the
 *
 *        e.g. (basic username and password login to web form)
 *		array (
 *			 [username] => johnsmith
 * 			,[password] => 12345 
 *		)
 *        or (using proxy with same credentials)
 *	 	array (
 *			 [username] => johnsmith
 * 			,[password] => 12345
 *			,[proxy] => array (
 *				 [url] => http://proxy.host.com/
 *				,[noauth] =>
 *			 )
 *		)
 *        or (using proxy with different login credentials)
 *		array (
 *			 [username] => johnsmith
 * 			,[password] => 12345
 *			,[otherfield] => blah
 *			,[proxy] => array (
 *				 [url] => http://proxy.host.com/
 *				,[username] => jsmith
 *				,[password] => 54321
 *			 )
 *             )
 *        or (basic with HTTP authentication using same login credentials)
 *		array (
 *			 [username] => johnsmith
 * 			,[password] => 12345
 *			,[httpauth] => array() // no values needed
 *		)
 *        or (using HTTP authentication with different login credentials)
 *		array (
 *			 [username] => johnsmith
 * 			,[password] => 12345
 *			,[httpauth] => array (
 *				 [username] => jsmith
 *				,[password] => 54321
 *				,[authmethod] => CURLAUTH_ANY
 * 			 )
 *		)
 *
 * @param $use_cookie boolean if TRUE HTTP header values are included
 *        in the content of the login form page (to get cookies).
 *        Cookies are then returned with the next HTTP request.
 *
 * @return mixed
 *        string if the content of the URL as viewed by an
 *        authenticated user was available. 
 *        array of error messages if authenticated content was not
 *        found.
 */
function get_protected_content( $url , $login_values , $use_cookie = true , $cookie_jar_file = '' )
{
	$post_array = array();
	$error = array();
	$password = false;
	$use_cookie_jar = false;

	$regex = array(
		 // 'headers finds the header content
		 'headers'	 => '/^(.*?)<(?:!DOCTYPE|HTML).*$/is'
		 // 'cookie' finds cookie data for supplied URL
		,'cookie'        => '/Set-Cookie: ([^\r\n]+)/'
		 // 'form' finds form attributes and content
		,'form'          => '/<form(.*?)>(.*?)<\/form>/is'
		 // 'action' finds the action value for a given form
		,'action'        => '/ action=(?:([\'"])([^\'"]*)[\'"]|([^ >]+?))(?=[ >])/is'
		 // 'domain' matches HTTP domains
		,'domain'        => '/^(https?:\/\/[^.\/]+(?:\.[^.\/]+)+)\/.*$/i'
		 // 'relative_path' filters the file name other data from a URL for later use in the action URL
		,'relative_path' => '/(?<=\/)[^\/]+$/i'
		 // 'password' finds a password input field in a given form
		,'password'      => '/<input[^>]*? type=(?:[\'"]password[\'"]|password)(?=[^>]*[ >])/is'
		 // 'fields' finds all the form input fields for a given form
		,'fields'        => '/<(?:input|select|button|textarea).*?>/is'
		 // 'attributes' finds input field attributes for a given input
		,'attributes'    => '/ (type|name|value|disabled)=(?:([\'"])([^\2]*?)\2|([^ >]+?))(?=[ >])/is'
	);

// Setting up how to handle cookies (if required)
	if( $use_cookie === true )
	{
		if(is_writable('./'))
		{
			$use_cookie_jar = true;
			$cookie_jar = 'cookies_'.date('Y-m-d_H-m').'.txt';
		}
		elseif( $cookie_jar_file != '' && is_writable(dirname($cookie_jar_file)) )
		{
			$use_cookie_jar = true;
			$cookie_jar = $cookie_jar_file;
		};
	};
	$action_url = $url;

	$z = curl_init();

	curl_setopt( $z , CURLOPT_URL , $url );
	curl_setopt( $z , CURLOPT_RETURNTRANSFER , 1 );
	curl_setopt( $z , CURLOPT_FOLLOWLOCATION , 1 );
	curl_setopt( $z , CURLOPT_USERAGENT , 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.04 (lucid) Firefox/3.6.13');
	curl_setopt( $z , CURLOPT_HEADER , 1 );
	curl_setopt( $z , CURLOPT_FRESH_CONNECT , 1 );

// Preparing HTTP authentication settings
	if( isset($login_values['httpauth']))
	{
		curl_setopt( $z , CURLOPT_HTTPAUTH , valid_auth_method($login_values ,'httpauth') );
		$username = set_login_detail($login_values , 'username' , 'httpauth' );
		$password = set_login_detail($login_values , 'password' , 'httpauth' );
		curl_setopt( $z , CURLOPT_USERPWD , "$username:$password" );
	};

// Preparing proxy settings
	if( isset($login_values['proxy']) && isset($login_values['proxy']['url']))
	{
		curl_setopt( $z , CURLOPT_PROXY , $login_values['proxy']['url'] );
		if(!isset($login_values['proxy']['noauth']))
		{
			curl_setopt( $z , CURLOPT_PROXYAUTH , valid_auth_method($login_values ,'httpauth') );
			$username = set_login_detail($login_values , 'username' , 'httpauth' );
			$password = set_login_detail($login_values , 'password' , 'httpauth' );
			curl_setopt( $z , CURLOPT_PROXYUSERPWD , "$username:$password" );
		};
	};

// Set up handling cookies (if required)
	if( $use_cookie === true )
	{
		if( $use_cookie_jar === true )
		{
			curl_setopt( $z , CURLOPT_COOKIEJAR , $cookie_jar );
		};
	};

	$login_content = curl_exec($z);
	$headers = preg_replace( $regex['headers'] , '\1' , $login_content );

	if( curl_errno($z) )
	{
		return array( 'cURL has issues with the URL: '.$url , curl_error($z) );
	};

// Check headers for HTTP errors (page not found, forbidden, etc)
	if(preg_match('/^[^ ]+ ([0-9]{3})[\t ]([^\r\n]+)/is' , $headers , $http ))
	{
		if( $http[1] > 400 )
		{
			return array( "The server reported a {$http[1]} error Page \"{$http[2]}\" for the URL:<br /><a href=\"$url\">$url</a>");
		};
	};

	if( $use_cookie === true && $use_cookie_jar === false )
	{
		if(preg_match_all( $regex['cookie'] , $headers , $cookies , PREG_SET_ORDER))
		{
			$cookie_count = count($cookies) - 1;
			$cookie = $cookies[$cookie_count][1];
		}
		else
		{
			$use_cookie = false;
		};
	};

	$login_content = preg_replace(array('/^.*?<body[^>]+>/is','/<\/body>.*$/is','/<!--.*?-->/s'),array('','',''),$login_content);

	if(preg_match_all($regex['form'],$login_content,$match_form,PREG_SET_ORDER))
	{
		for($a = 0 ; $a < count($match_form) ; ++$a )
		{
			if(preg_match($regex['password'],$match_form[$a][2]))
			{
// this form has a password field and so we think it's a/the login form
				$password = true;
				if(preg_match($regex['action'],$match_form[$a][1],$action_value))
				{
//  we've found a form action attribute
					$action_url = $action_value[2];
					if(isset($action_value[3]))
					{
						$action_url .= $action_value[3];
					};
					if(substr_compare($action_url , '/' , 0 , 1 ) === 0 )
					{
						$action_url = preg_replace( $regex['domain'] , '\1'.$action_url , $url );
					}
					elseif( !preg_match( $regex['domain'] , $action_url ) )
					{
						$action_url = preg_replace( $regex['relative_path'] , '\1'.$url_action , $url );
					};
				}
				else
				{
					$error[] = 'NO ACTION FOUND at '.$url; // No action found - Can't login.
				};
				if(preg_match_all($regex['fields'],$match_form[$a][2],$form_fields))
				{
// We've found login form fields
					for( $b = 0 ; $b < count($form_fields[0]) ; ++$b )
					{
						if(preg_match_all($regex['attributes'],$form_fields[0][$b],$field_attr,PREG_SET_ORDER))
						{
// This field has 'type' and/or 'name' and/or 'value' and/or 'disabled' attributes
							$tmp_array = array('type'=>'','name'=>'','value'=>'','disabled'=>'');
							for( $c = 0 ; $c < count($field_attr) ; ++$c )
							{
								$key = strtolower($field_attr[$c][1]);
								$value = $field_attr[$c][3];
								if(isset($field_attr[$c][4]))
								{
									$value .= $field_attr[$c][4];
								};
								$tmp_array[$key] = $value;
							};
							$name = $tmp_array['name'];
							$type = strtolower($tmp_array['type']);
// Ignore field if 'disabled' not empty.
							if( $tmp_array['disabled'] == '' )
							{
								if( $type == 'hidden' || $type == 'submit' )
								{
// This field is a hidden or submit field so we'll use the form's values in the post
									$post_array[$name] = $tmp_array['value'];
								}
								else
								{
// This field is a normal input field so we'll try and use supplied values
									$post_array[$name] = isset($login_values[$name])?$login_values[$name]:'';
								};
							};
							unset($tmp_array,$name,$type);
						};
					};
				}
				else
				{
					$error[] =  'NO LOGIN FORM FIELDS FOUND at '.$url;
				};
// Generate the post string
				$space = '';
				$post_string = '';
				foreach($post_array as $field => $value)
				{
					$post_string .= $space.$field.'='.urlencode($value);
					$space = '&';
				};

				curl_setopt( $z , CURLOPT_URL , $action_url );
				curl_setopt( $z , CURLOPT_RETURNTRANSFER , 1 );
				if( $use_cookie === true )
				{
					if( $use_cookie_jar === true )
					{
						curl_setopt( $z , CURLOPT_COOKIEFILE , $cookie_jar );
					}
					else
					{
						curl_setopt( $z , CURLOPT_COOKIE , $cookie );
					};
				};

				curl_setopt( $z , CURLOPT_REFERER , $url );
				curl_setopt( $z , CURLOPT_HEADER , 0 );
				curl_setopt( $z , CURLOPT_POST , 1);
				curl_setopt( $z , CURLOPT_POSTFIELDS , $post_string );
				curl_setopt( $z , CURLOPT_FRESH_CONNECT , 1 );

				$logged_in_content = curl_exec($z);
				$c_errno = curl_errno($z);
				$c_error = curl_error($z);

				curl_close( $z );

				if( $use_cookie_jar === true )
				{
					unlink($cookie_jar);
				};

				if( $c_errno )
				{
					$error[] = 'cURL has issues with the URL: '.$url;
					$error[] = $c_error;
				}
				else
				{
					if(preg_match($regex['password'],$logged_in_content))
					{
						$error[] = 'Authentication failed. You were returned to the login page: '.$url;
						$error[] = print_r($post_array,true);
					}
					else
					{
						return $logged_in_content;
					};
				};
			}
			else
			{
				$error[] = 'NO PASSWORD TYPE INPUT FIELD WAS FOUND at '.$url;
			};
		};
	}
	else
	{
		$error[] = 'NO FORMS WERE FOUND at '.$url;
	};

	if( $password === true && !empty($error))
	{
		return $error;
	}
	else
	{
		return array('There\'s something wrong with this code!!!');
	};
};


/**
 * @function get_public_content() pulls content using cURL from a
 * given URL ad returns it.
 *
 * @param url string URL for desired webpage
 * @return string content of desired webpage
 */
function get_public_content( $url )
{
	$z = curl_init();

	curl_setopt( $z , CURLOPT_URL , $url );
	curl_setopt( $z , CURLOPT_FOLLOWLOCATION , 1 );
	curl_setopt( $z , CURLOPT_RETURNTRANSFER , 1 );
	curl_setopt( $z , CURLOPT_USERAGENT , 'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.04 (lucid) Firefox/3.6.13' );

	$content = curl_exec($z);
	$c_errno = curl_errno($z);
	$c_error = curl_error($z);

	curl_close( $z );

	if( $c_errno >= 400 )
	{
		$error[] = 'cURL has issues with the URL: '.$url;
		$error[] = $c_error;
	}
	else
	{
		return $content;
	};
};


function valid_auth_method( $login_values , $option = 'httpauth' )
{
	$output = 'CURLAUTH_ANYSAFE';
	if( isset($loging_values[$option]['authmethod']))
	{
		switch(strtoupper($login_values[$option]['authmethod']))
		{
			case 'BASIC':
			case 'CURLAUTH_BASIC':
				$output = 'CURLAUTH_BASIC';
				break;
			case 'DIGEST':
			case 'CURLAUTH_DIGEST':
				$output = 'CURLAUTH_DIGEST';
				break;
			case 'GSSNEGOTIATE':
			case 'CURLAUTH_GSSNEGOTIATE':
				$output = 'CURLAUTH_GSSNEGOTIATE';
				break;
			case 'NTLM':
			case 'CURLAUTH_NTLM':
				$output = 'CURLAUTH_NTLM';
				break;
			case 'ANY':
			case 'CURLAUTH_ANY':
				$output = 'CURLAUTH_ANY';
				break;
		};
	};
	return $output;
};


function set_login_detail( $login_values , $type = 'password' , $option = 'httpauth' )
{
	if( isset($login_values[$option][$type]) )
	{
		return $login_values[$option][$type];
	}
	else
	{
		if($type == 'password')
		{
			$b = 1;
		}
		else
		{
			$b = 0;
		};
		$a = 0;
		foreach( $login_values as $value )
		{
			if( $a == $b )
			{
				return $value;
			};
			++$a;
		};
	};
};

else:
/**
 * The following versions of the above functions handle the fact that
 * the PHP cURL module is not installed on this server.
 */
function get_protected_content( $url , $login_values , $use_cookie = true )
{
	die("cURL is not available on this server. Cannot authenticate without it.\nContact your server administrator to get the PHP cURL moduled installed and or enabled.\n\n");
};

function get_public_content( $url )
{
	return file_get_contents($url);
};
endif;

