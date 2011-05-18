<?php
$include_path = '/var/www/includes/';
echo microtime()."\n";
include($include_path.'debug.inc.php');
include($include_path.'regex_safe.inc.php');
include($include_path.'delimited_strings.inc.php');
//echo microtime()."\n";
//echo delim_str__csv_rows_to_columns("row_1__col_1,row_1__col_2\nrow_2__col_1,row_2__col_2\nrow_3__col_1,row_3__col_2",',',"\n");
//echo microtime()."\n";
//delim_str__csv_rows_to_columns(array());
//debug(delim_str__array_from(file_get_contents('/var/www/includes/sample_csv.csv')));
debug('server');debug('backtrace');
//delim_str__array_from(file_get_contents('/var/www/includes/sample_csv.csv'),"\n",',','"');
