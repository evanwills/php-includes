<?php

include('debug.inc.php');
include('build_file_list.new.inc.php');
$bla = 'test,toast,txt,sthis\,df';
debug(preg_split('/(?<!\\\\),/',$bla));
//debug(build_file_list('/var/www/eNewsletter-prep/eNewsletter-prep/templates/alumni' , 'story,break' , 'prefix' ,'exclude=draft','exclude=data'));
