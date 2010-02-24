<?php
/*

   Zynapse Boot
    - final base setup and zynapse initialization

*/


// include required environment setup
require_once(dirname(__FILE__).'/environment.php');


// include shell script enviroment settings
if ( defined('ZNAP_SHELL_SCRIPT') && is_file(dirname(__FILE__).'/environments/_shell.php') ) {
	require_once(dirname(__FILE__).'/environments/_shell.php');
}


// set zynapse root path
if ( !defined('ZNAP_ROOT') ) {
	define('ZNAP_ROOT', dirname(dirname(__FILE__)));
}


// find zynapse libs
if ( !empty($zynapse_libs) && is_file($zynapse_libs.'/zynapse.php') ) {
	define('ZNAP_LIB_ROOT', $zynapse_libs);
} elseif ( is_file(ZNAP_ROOT.'/vendor/zynapse/zynapse.php') ) {
	define('ZNAP_LIB_ROOT', ZNAP_ROOT.'/vendor/zynapse');
} elseif ( is_file(dirname(ZNAP_ROOT).'/vendor/zynapse/zynapse.php') ) {
	define('ZNAP_LIB_ROOT', dirname(ZNAP_ROOT).'/vendor/zynapse');
}


// figure out environment related settings
if ( defined('ZNAP_SHELL_SCRIPT') ) {
	$environment = $shell_environment;
} elseif ( !empty($_SERVER['ZNAP_ENV']) ) {
	$environment = $_SERVER['ZNAP_ENV'];
} elseif ( $enable_advanced_host_config == true && ($current_config = match_host($host_config)) !== false ) {
	if ( !empty($current_config['environment']) ) $environment = $current_config['environment'];
	if ( !empty($current_config['mode']) ) $mode = $current_config['mode'];
	if ( !empty($current_config['root']) ) {
		if ( !empty($_SERVER['REQUEST_URI']) ) $_SERVER['REQUEST_URI'] = '/'.$current_config['root'].$_SERVER['REQUEST_URI'];
		if ( !empty($_SERVER['REDIRECT_URL']) ) $_SERVER['REDIRECT_URL'] = '/'.$current_config['root'].$_SERVER['REDIRECT_URL'];
	}
}


// define environment and display mode
define('ZNAP_ENV', $environment);
define('ZNAP_MODE', $mode);


// include environment specific settings
if ( is_file(dirname(__FILE__).'/environments/'.ZNAP_ENV.'.php') ) {
	include_once(dirname(__FILE__).'/environments/'.ZNAP_ENV.'.php');
}


// set url prefix if needed
if ( !empty($url_prefix) ) {
	define('URL_PREFIX', $url_prefix);
}


// php error logging
define('ZNAP_ENABLE_LOGGING', $enable_logging);
define('ZNAP_INTERNAL_LOGGING', $internal_logging);


// include and initialize main zynapse class
require_once(ZNAP_LIB_ROOT.'/zynapse.php');
Znap::initialize();
if ( !empty($timer_enabled) || ZNAP_ENV != 'production' ) {
	Znap::start_timer();
}



// function to get all matched host settings
function match_host ($list = array()) {
	if ( is_array($list) && !empty($list) ) {
		$new_config = array();
		foreach( $list as $host => $settings ) {
			$regex = preg_quote($host, '/');
			$regex = str_replace('\*', '.*', $regex);
			$http_host = (substr($_SERVER['HTTP_HOST'], 0, 4) == 'www.') ? substr($_SERVER['HTTP_HOST'], 4) : $_SERVER['HTTP_HOST'] ;
			if ( preg_match('/^'.$regex.'$/i', $http_host) ) {
				foreach( $settings as $key => $value ) {
					if ( !array_key_exists($key, $new_config) ) {
						$new_config[$key] = $value;
					}
				}
			}
		}
	}
	return (!empty($new_config)) ? $new_config : false ;
}


?>