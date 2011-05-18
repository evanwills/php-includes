<?php

include('debug.inc.php');
include('build_file_list.new.inc.php');
debug(PATH_TO_WORKING_DIR);
$output = build_file_list('../eNewsletter-prep/','info');
debug($output);
$output = build_file_list('../eNewsletter-prep/','prep','prefix');
debug($output);
$output = build_file_list('../eNewsletter-prep/','\.inc','anywhere');
debug($output);
$output = build_file_list('../eNewsletter-prep/','email','anywhere');
debug($output);
$output = build_file_list('../eNewsletter-prep/','send','end');
debug($output);
