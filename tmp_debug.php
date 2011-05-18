<?php

include('debug.inc.php');
//debug('?');
$var1 = array( 'blah', 'foo', 'bar' );
$var2 = array( 'name' => 'evan' , 'phone' => 123423450 );
$var3 = 'This is a string variable.';
$var4 = false;
$var5 = 0.34;
$var6 = 1234;

$var7 = true;
$var8 = '';
$var9 = 0;
$var10 = false;
$var11 = array( 'test' => array( 'foo' => 'bar' , 'poo' => 2 ) , 'toast' => array( 'spread' => 'jam' , 'flavour' => 'strawberry' ));
define('CONST_1','This is constant 1');
define('CONST_2',0.35);
define('CONST_3',25);
define('CONST_4',234);

debug( $var1 , $var2 , $var3 , $var4 , $var5 , $var6 );
debug( $var7 , $var8 , $var9 , CONST_1 , CONST_2 , CONST_3 , CONST_4 );
debug( 'asdr' , 9 , false , 0.44 , -3.34 , $var11['toast']);
//debug( 'constant' , 'config' );
