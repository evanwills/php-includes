<style type="text/css">
#debug__help { font-family: arial,helvetica,sans-serif; line-height:1.2em; }
#debug__help p.top-h2 { border-top: 0.1em solid #000; padding-top: 1em; margin: 3em 0% 0.5em 0%; }
 #debug__help h2
,#debug__help h3
,#debug__help h4
,#debug__help dl dt
,#debug__help p.note:first-child { font-family: verdana, arial, helvetica, sans-serif; }
#debug__help h2 { margin:0%; }
#debug__help dl dd { margin-top: -1.0em; margin-left: 8em; margin-bottom:1em; }
#debug__help dl.config dd.wide { margin-top: -1.2em; margin-left: 11.5em; }
#debug__help dl.config dd samp { font-size: 120%; padding-right: 1em; display:block; margin-bottom: -1.3em; }
#debug__help dl.config dd span { display:block; margin-left:7em; margin-bottom: 1.2em; }
#debug__help dl.config dd span.wide { margin-left:6em; text-indent: 5em; }
#debug__help dl.config dd.wide span { margin-left:3.5em; }
#debug__help dl dt { font-weight: bold; font-size: 105%; margin-top: 1.5em; }
 #debug__help dl.config dd samp:after
,#debug__help dl.config dd samp:before { content: \'"\'; }
#debug__help dl.config dd.default samp { font-weight: bold; }
#debug__help pre#info { border: 0.1em solid #000; padding:1em 1em 1em 3em; width: 50em; background-color:#eee; }
#debug__help p.info-info strong samp { font-size: 130%; color: #007; padding: 0% 0.3em; }
#debug__help pre.samp { border:0.1em dashed #0c0; padding: 1em; background-color: #efe; }
#debug__help pre.output { border:0.1em dotted #00c; padding: 1em; background-color: #eef; }
#debug__help p.note { margin-left: 3.3em; text-indent:-3.3em; }
#debug__help p.note strong { padding-right:0.5em;; }
	</style>
<div id="debug__help">
	<h1 id="top">debug() documentation</h1>

	<ul>
		<li><a href="#auth">Author and copyright</a></li>
		<li><a href="#intro">Introduction</a></li>
		<li><a href="#basic">Basic usage</a>
			<ul>
				<li><a href="#basic">Most common usage</a></li>
				<li><a href="#normal">Normal usage</a></li>
				<li><a href="#simple">Simplest usage</a></li>
			</ul>
		</li>
		<li><a href="#special">Reserved strings</a></li>
		<li><a href="#advanced">Advanced usage</a>
			<ul>
				<li><a href="#advanced">Unlimited arguments</a></li>
				<li><a href="#max_times">max_times</a></li>
				<li><a href="#force_die">force_die</a></li>
			</ul>
		</li>
		<li><a href="#config">Configuration</a>
			<ul>
				<li><a href="#options">Options</a></li>
				<li><a href="#sample-config">Sample Config file</a></li>
			</ul>
		</li>
	</ul>

	<p class="top-h2"><a href="#top" name="auth">top</a></p>
	<h2>Author and copyright</h2>

	<p>This script was written by Evan Wills with technical assistance and advice from Trevor Goodall, Brent Knigge and Ivan Wills.</p>
	<p>It is released under the GPL2</p>

	<p class="top-h2"><a href="#top" name="intro">top</a></p>
	<h2>Introduction</h2>

	<p><samp>debug()</samp> is intended make it easy to output debug messages when developing or debugging a PHP script.</p>

	<p>By default it outputs to screen the original calling script\'s file name, the line number the debug function is called from and the actual debug message or the name of a supplied variable and it\'s value. The script should handle any type of variable and will, depending on the type identify, that variable\'s type.</p>

	<p>debug() can accept any number of arguments. The value of each argument along with its variable name (if there is one) will be printed on a new line.</p>

	<p class="top-h2"><a href="#top" name="install">top</a></p>
	<h2>Installation</h2>

	<p>To make debug() work all you need to do is include debug.inc.php in your script (preferably as early possible)</p>

	<p>To get the most out of it, you should also have a copy of debug.info in the same directory/folder as the calling script (same directory as $_SERVER[\'SCRIPT_NAME\'])</p>

	<p class="top-h2"><a href="#top" name="basic">top</a></p>
	<h2>Basic usage</h2>

	<h3>Most common usage</h3>
	<p>The most comon usage for debug() is to output the value of a variable (at least that\'s how I mostly use it)</p>
	<div class="example">
	<pre class="samp"><samp>$var1 = array( \'string\', 123643, true );</samp>
<samp>define(\'CONST_1\',\'this is a const\');
<samp>debug($var1);debug(CONST_1);</samp></pre>
		<p>This will output</p>
		<pre class="output"><strong>(my_script.php Line 231)</strong>
    $var1 = Array
(
    [0] =&gt; \'string\'
    [1] =&gt; 123643
    [2] =&gt; 1
)

<strong>(my_script.php Line 231)</strong>
    CONST_1 = this is a const</pre>
	</div>
	<p class="note"><strong>Note:</strong> The variable (and constant) names are shown. This relies on the finding an identical match for the variable\'s (or constant\'s) value and type. Depending on your coding style it may list all the possible variables (and/or constants) you have assigned that value to. If there are a number of variables (or constants) with identical values, it will list the names of all them in order of assignment. This is particularly an issue if you have a number of variables (or constants) with either an empty string or boolean or NULL values.</p>

	<p class="note"><strong>Note:</strong> Because constants are global and PHP creates so many when it starts (and because debug() itself creates a few of it\'s own constants), this script excludes all constants created before the end of debug.inc.php. This is so you don\'t end up with 10 or 20 constant names who\'s values also match PHP defined constants. If you are using constants (and even if you\'re not) you should include debug.inc.php as early as possible in the script.</p>

	<p class="top"><a href="#top" name="normal">top</a></p>
	<h3>Normal usage</h3>
	<p>Often when debugging you just want to let yourself know you\'ve reached a given point in the script and that something has happened. If you just want to output a string:</p>

	<div class="example">
		<pre class="samp"><samp>debug(\'this is a debug message\');</samp></pre>
		<p>This will output</p>
		<pre class="output"><strong>(my_script.php Line 231)</strong>
    this is a debug message</pre>
	</div>

	<p class="top"><a href="#top" name="simple">top</a></p>
	<h3>Simplest usage</h3>
	<p>Sometimes all you need to know is that the script has processed a given line. If that is the case just call debug() with no arguments</p>

	<div class="example">
		<pre class="samp"><samp>debug();</samp></pre>
		<p>This will output</p>
		<pre class="output"><strong>(my_script.php Line 231)</strong></pre>
	</div>

	<p class="top-h2"><a href="#top" name="special">top</a></p>
	<h2>Reserved strings</h2>
	<p>There are a number of reserved strings that if passed will cause debug() to do special things.</p>
	<p class="note"><strong>Note:</strong> these reserved strings are case insensitive.</p>
	<dl>
		<dt>help or \'?\'</dt>
			<dd>Outputs help documentation about the debug function</dd>
		<dt>backtrace</dt>
			<dd>Outputs the contents of an array generated by debug_backtrace()</dd>
		<dt>server</dt>
			<dd>Outputs the contents of the $_SERVER global array</dd>
		<dt>request</dt>
			<dd>Outputs the contents of the $_REQUEST global array</dd>
		<dt>get</dt>
			<dd>Outputs the contents of the $_GET global array</dd>
		<dt>post</dt>
			<dd>Outputs the contents of the $_POST global array</dd>
		<dt>env</dt>
			<dd>Outputs the contents of the $_ENV global array</dd>
		<dt>files</dt>
			<dd>Outputs the contents of the $_FILES global array</dd>
		<dt>session</dt>
			<dd>Outputs the contents of the $_SESSION global array</dd>
		<dt>cookie</dt>
			<dd>Outputs the contents of the $_COOKIE global array</dd>
		<dt>globals</dt>
			<dd>Outputs the contents of the $GLOBALS global array (will also accept global)</dd>
		<dt>max_times=X</dt>
			<dd>defines the maximum number of times debug() will be called within a given loop</dd>
			<dd>(will also accept "max times = X", "maxtimes =X", "max-times X" (<strong>note:</strong> spaces, hyphens and underscores can be used inter-changably or omitted. equals can also be omitted)</dd>
			<dd>(see <a href="#max_times">Advanced usage &gt; max_times for more info</a>)</dd>
		<dt>force_die</dt>
			<dd>cause the debug function to kill the script after a given number of times (defined by max_times)</dd>
			<dd>(will also accept \'force die\', \'force-die\', \'forcedie\')</dd>
			<dd>(see <a href="#force_die">Advanced usage &gt; force die for more info</a>)</dd>
		<dt>config</dt>
			<dd>Outputs configuration info for debug()</dd>
			<dd>(NOTE: this has not been implemented yet so it will only tell you that it hasn\'t been implmented)
	</dl>

	<p class="top-h2"><a href="#top" name="advanced">top</a></p>
	<h2>Advanced usage</h2>
	<h3>Unlimited arguments</h3>
	<p>debug() can accept an unlimited number of arguments, in any order. Each supplied argument is handled individually (see <a href="#exception">exception</a>) and can be of any type.</p>
	<p>As each argument is processed, it is checked for type and handled appropriately. Then output starting on a new line</p>

	<div class="example">
		<pre class="samp"><samp>$var1 = array( \'string\', 123643, true );</samp>
<samp>$var2 = 1234589.32;</samp>

<samp>debug($var1,\'server\',\'post\',This is a debug function call\',$var2);</samp></pre>
		<p>This will output</p>
		<pre class="output"><strong>(my_script.php Line 231)</strong>
$var1 = Array
(
    [0] =&gt; string
    [1] =&gt; 123643
    [2] =&gt; 1
)

$_SERVER: Array
(
    [HTTP_HOST] =&gt; localhost
    [HTTP_USER_AGENT] =&gt; Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.12) Gecko/20101027 Ubuntu/10.04 (lucid) Firefox/3.6.12
    [HTTP_ACCEPT] =&gt; text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8
    [HTTP_ACCEPT_LANGUAGE] =&gt; en-us,en;q=0.5
    [HTTP_ACCEPT_ENCODING] =&gt; gzip,deflate
    [HTTP_ACCEPT_CHARSET] =&gt; ISO-8859-1,utf-8;q=0.7,*;q=0.7
    [HTTP_KEEP_ALIVE] =&gt; 115
    [HTTP_CONNECTION] =&gt; keep-alive
    [HTTP_REFERER] =&gt; http://localhost/eNewsletter-prep/
    [HTTP_PRAGMA] =&gt; no-cache
    [HTTP_CACHE_CONTROL] =&gt; no-cache
    [CONTENT_TYPE] =&gt; application/x-www-form-urlencoded
    [CONTENT_LENGTH] =&gt; 101
    [PATH] =&gt; /usr/local/bin:/usr/bin:/bin
    [SERVER_SIGNATURE] =&gt; <address>Apache/2.2.14 (Ubuntu) Server at localhost Port 80</address>
    [SERVER_SOFTWARE] =&gt; Apache/2.2.14 (Ubuntu)
    [SERVER_NAME] =&gt; localhost
    [SERVER_ADDR] =&gt; ::1
    [SERVER_PORT] =&gt; 80
    [REMOTE_ADDR] =&gt; ::1
    [DOCUMENT_ROOT] =&gt; /var/www
    [SERVER_ADMIN] =&gt; webmaster@localhost
    [SCRIPT_FILENAME] =&gt; /var/www/eNewsletter-prep/index.php
    [REMOTE_PORT] =&gt; 51515
    [GATEWAY_INTERFACE] =&gt; CGI/1.1
    [SERVER_PROTOCOL] =&gt; HTTP/1.1
    [REQUEST_METHOD] =&gt; POST
    [QUERY_STRING] =&gt; 
    [REQUEST_URI] =&gt; /eNewsletter-prep/
    [SCRIPT_NAME] =&gt; /eNewsletter-prep/index.php
    [PHP_SELF] =&gt; /eNewsletter-prep/index.php
    [REQUEST_TIME] =&gt; 1290144742
)

$_POST: Array
(
    [template] =&gt; ACU_Update_2009
    [submit] =&gt; Prepare email
    [ga_source] =&gt; 
    [ga_medium] =&gt; 
    [ga_campaign] =&gt; 
    [ga_content] =&gt; 
    [ga_term] =&gt; 
)

This is a debug function call
$var2 = 1234589.32 (double)</pre>
	</div>
	<p class="top"><a href="#top" name="max_times">top</a></p>
	<h3>max_times</h3>
	<p>By calling debug(\'max_times=X\'); from within a loop you cause that instance of debug() to output only the supplied number of times (or if no number is supplied, the default maximum number of times.</p>
	<p>How does it do this you might ask without using global variables or objects. Well it does it by creating a constant for every time that instance of debug() is called until the maximum is reached.</p>
	<p>You can have any number of max_times set, as they are independant.</p>
	<p>The default for max_times is 100 but can be raised or lowerd via the debug.info config file (see more on <a href="#config">debug.info</a>)</p>

	<p class="top"><a href="#top" name="force_die">top</a></p>
	<h3>force_die</h3>
	<p>
		If your script is getting stuck in an infinite loop you can use debug(\'max_times=30\',\'force_die\') to kill the script after a given number calles to the debug() function from a specific line (in this case 30 times)<br />
		<strong>NOTE:</strong> this functionality has <strong>not</strong> be exhaustively tested. In all my testing it has worked but...)
	</p>
	<p>(<strong>Note:</strong> force_die cannot work without max_times being defined in the same function call)</p>


	<p class="top-h2"><a href="#top" name="config">top</a></p>
	<h2>Configuration</h2>
	<p>By default debug() outputs directly to screen in HTML format but this can be over ridden by having a debug.info file in the same directory/folder as the script being run.</p> 

	<p class="top"><a href="#top" name="options">top</a></p>
	<h3>Options</h3>
	<p>(<strong>Note:</strong> default values are in bold)</p>


	<dl class="config">
		<dt>status</dt>
			<dd class="default">
				<samp>true</samp>
				<span>Always show debug() output</span>
			</dd>
			<dd>
				<samp>false</samp>
				<span>never show debug output</span>
			</dd>
			<dd>
				<samp>debug</samp>
				<span>Only show debug() output if $_GET[\'debug\'] is set and equals true.<br />(Useful for debugging live or production scripts)</span>
			</dd>
		<dt>show_file</dt>
			<dd class="default">
				<samp>true</samp>
				<span>Show name of file currently being processed in debug output.<br />(Useful if you are working with a script with multiple inlcudes)</span>
			</dd>
			<dd>
				<samp>false</samp>
				<span>Never show current file name</span>
			</dd>
		<dt>show_date</dt>
			<dd class="default">
				<samp>false</samp>
				<span>Never show today\'s date</span>
			</dd>
			<dd>
				<samp>true</samp>
				<span>Show today\'s date in debug() output.<br />(Useful if you\'ve got other people testing a script and you\'re writing debug output to a log file)</span>
			</dd>
		<dt>show_time</dt>
			<dd class="default">
				<samp>false</samp>
				<span>Never show the time debug() is called</span>
			</dd>
			<dd>
				<samp>true</samp>
				<span>Show the time in the debug() output.<br />(Useful if you\'re trying to work out where your script is slow or if you\'re writing to log file)</span>
			</dd>
		<dt>format</dt>
			<dd class="default">
				<samp>html</samp>
				<span>Format debug() output as HTML (recommended if you\'re testing via a browser)</span>
			</dd>
			<dd>
				<samp>text</samp>
				<span>Format debug() output in plain text.<br />(Useful if you\'re testing from the command line or writing to a log file)</span>
			</dd>
			<dd>
				<samp>comment</samp>
				<span>Format debug() output as plain text but Wrap it in HTML comments.<br />(Useful if you\'re debugging a live script and don\'t want people to see the inappropriate stuff)</span>
			</dd>
			<dd>
				<samp>log</samp>
				<span>Same as text</span>
			</dd>
		<dt>mode</dt>
			<dd class="default">
				<samp>echo</samp>
				<span>Display debug() output to screen (or terminal) (normal if \'format\' is HTML or comment</span>
			</dd>
			<dd>
				<samp>return</samp>
				<span>Return debug() output as a string to be used elsewhere in your code</span>
			</dd>
			<dd>
				<samp>log</samp>
				<span>Write debug() output to an empty log file</span>
			</dd>
			<dd>
				<samp>log-clean</samp>
				<span>same as log</span>
			</dd>
			<dd>
				<samp>clean</samp>
				<span>same as log</span>
			</dd>
			<dd>
				<samp>log-append</samp>
				<span>Append debug() output to an existing log file</span>
			</dd>
			<dd>
				<samp>append</samp>
				<span>same as log-append</span>
			</dd>
		<dt>full_path</dt>
			<dd class="default">
				<samp>false</samp>
				<span>Never show full path for current file</span>
			</dd>
			<dd>
				<samp>true</samp>
				<span>Show full/absolute path to current file<br />(Useful if you\'re including php files outside the directory the original script is called from)<br />(NOTE: this only works if you\'ve got \'show_file\' set to TRUE)</span>
			</dd>
		<dt>log_file</dt>
			<dd>
				<samp>[empty]</samp>
				<span>The file name to output log to (if logging is active it defaults to ".debug__log.[CALLING SCRIP\'S NAME].log"</span>
			</dd>
	<!--	<dt>root_path</dt>
			<dd>
				<samp>[empty]</samp>
				<span></span>
			</dd>
-->
		<dt>time_adjust</dt>
			<dd>
				<samp>0</samp>
				<span>The number of seconds (or minutes or hours) difference between the server time and your local time.<br />(this is only useful if your server is <strong>NOT</strong> set to GMT or your local time)</span>
			</dd>
		<dt>timezone</dt>
			<dd class="default">
				<samp>Australia/Sydney</samp>
				<span class="wide">
					timezone string to be used by <code>date_default_timezone_set()</code><br />
					<strong>NOTE:</strong> timezone is case sensitive<br />
					(See <a href="/timezones.php">PHP\'s Timezone documentation for other timezones</a>)
				</span>
			</dd>
		<dt>meta_max_length</dt>
			<dd class="wide">
				<samp>40</samp>
				<span>the maximum number of total characters the file name, line number, date and time can be before the debug output is pushed to a new line</span>
			</dd>
		<dt>max_max_times</dt>
			<dd class="wide">
				<samp>100</samp>
				<span>
					if the max-times option is passed for a particular debug() call, max_max_times is the default maximum number of times a that particular call to debug() will be rendered within a loop.<br />
					<strong>NOTE:</strong> if force-die is also passed, when the max-times is reach the script will stop processing all together. This is to help debug scripts with infinite loops.
				</span>
			</dd>
	</dl>

	<p class="top"><a href="#top" name="sample-config">top</a></p>
	<h3>Sample config file</h3>

	<p class="info-info">This file must be named <strong><samp>debug.info</samp></strong> and should be in the same directory/folder that the original script is called from.</p>
	<pre id="info">
; debug info file - This file defines DEBUG config for the current app

; -----------------------------------------------
; status defines whether or not debug will output
; anything.
; The options are:
;    'true' (default) or 'on' or 'test' or 'testing' -
;          debug() will always be shown if called
;    'false' or 'off' - debug() will never be
;          shown
;    'debug' - debug() will only be shown if
;          $_GET['debug'] is set and equal to
;          TRUE or 'debug'

status = true;


; -----------------------------------------------
; show_file defines whether the name of the file
; debug() is being called from is rendered in the
; debug output.
; The options are:
;     true (default) or false

show_file = true; true/false


; -----------------------------------------------
; show_date defines whether the current date is
; rendered in the debug output.
; The options are:
;     true or false (default)

show_date = false; true/false


; -----------------------------------------------
; show_time defines whether the time a debug()
; call is parsed is rendered in the debug output.
; The options are:
;     true or false (default)

show_time = false; true/false


; -----------------------------------------------
; full_path defines whether the full path to the
; current file being processed is rendered in the
; debug output.
; (Note: This is only relevant if show_file is
;  set to TRUE)
; The options are:
;     true or false (default)

full_path = false;


; -----------------------------------------------
; format defines how the debug output will be
; written.
; The options are:
;     'html' (default) - Use HTML to mark up the
;          debug output
;     'comment' - wrap the output in HTML comment
;          tags
;     'text' or 'log' - render the output of
;          debug() in plain text

format = html; comment / html / log / text


; -----------------------------------------------
; mode defines where the output of debug is
; rendered. i.e. is it rendered to screen, to a
; log file or returned as a string for use
; elsewhere.
; The options are:
;     'append' or 'log_append' - add to an
;          existing log file
;     'clean' or 'log' or 'log_clean' - add to an
;          empty log file
;     'echo' (default) - render to screen
;     'return' - returned by the debug function
 
mode = echo; append / clean / echo / log / log_append / log_clean / return


; -----------------------------------------------
; log_file is the file name to output log to. If
; logging is active it defaults to 
; .debug__log.[initial file name].log
;log_file = ;


; -----------------------------------------------
; time_adjust is the number of seconds (or minutes
; or hours) difference between the server time
; and your local time)

;time_adjust 36000; 10 hours


; -----------------------------------------------
; timezone string to be used by
; date_default_timezone_set()
; (see www.php.net/manual/en/timezones.php)
; default timezone is Australia/Sydney;
; NOTE: timezone is case sensitive

timezone = Australia/Sydney;


; -----------------------------------------------
; meta_max_length is the maximum number of total
; characters the file name, line number, date and
; time can be before the debug output is pushed
; to a new line.

meta_max_length = 10;


; -----------------------------------------------
; if the max-times option is passed for a
; particular debug() call, max_max_times is the
; default maximum number of times a that
; particular call to debug() will be rendered
; within a loop.
; NOTE: if force-die is also passed, when the
;       max-times is reach the script will stop
;       processing all together. This is to help
;       debug scripts with infinite loops.

max_max_times = 13;</pre>
</div>
