<?php
/*

   Database configuration
    - only mysql is supported at this time

*/


$database_settings = array(

	// database settings for development environment
	'development' => array(
		'host'         => 'localhost',
		'database'     => 'zynapse_development',
		'username'     => 'devuser',
		'password'     => 'devpass',
		'persistent'   => true,
		'table_prefix' => '',
	),

	// database settings for testing environment
	'test' => array(
		'use' => 'development',
	),
	
	// database settings for production environment
	'production' => array(
		'host'         => 'localhost',
		'database'     => 'database_name',
		'username'     => 'user',
		'password'     => 'password',
		'persistent'   => true,
		'table_prefix' => '',
	),
	
);


?>