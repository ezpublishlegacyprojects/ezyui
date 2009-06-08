<?php

$Module = array( 'name' => 'eZYui Module and Views' );


$ViewList = array();

$ViewList['hello'] = array(
    'functions' => array(  ),
    'script' => 'hello.php',
    'params' => array( 'with_pagelayout' )
    );
    
$ViewList['call'] = array(
    'functions' => array( 'call' ),
    'script' => 'call.php',
    'params' => array( 'function_arguments', 'type', 'interval' )
    );

$ViewList['run'] = array(
    'functions' => array( 'run' ),
    'script' => 'run.php',
    'params' => array( )
    );

$FunctionList = array();
$FunctionList['call'] = array();
$FunctionList['run'] = array();




?>