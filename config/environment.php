<?php
/*

   Zynapse Environment
    - configure server environments and display modes

*/



# default environment - overridden by $host_config
# ( development | test | production )
$environment = 'development';


# default server display mode - overridden by $host_config
$mode = 'web';


# if you don't need any of the advanced host-specific
# configuration features, you can disable it as it becomes
# excess code which you don't need.
$enable_advanced_host_config = true;


# host configuration
# - set environment, display mode, and root path for
# specific hosts. available options are "environment",
# "mode", and "root".
$host_config = array(
	// 'zynapse' => array(
	// 	
	// ),
	// 'wap.zynapse' => array(
	// 	'mode' => 'wap',
	// ),
	// 'admin.zynapse' => array(
	// 	'root' => 'admin',
	// ),
	// 'zynapse.org' => array(
	// 	'environment' => 'production',
	// ),
	// 'admin.zynapse.org' => array(
	// 	'environment' => 'production',
	// 	'root' => 'admin',
	// ),
);


# set custom path to zynapse libs
$zynapse_libs = '';


# Timer enabled in production environment?
# - its always enabled in development and test environments
$timer_enabled = false;


# enable php error logging? - recommended
$enable_logging = true;

# enable internal error logging? - recommended
$internal_logging = true;


# if zynapse's root is not the root of the server, define
# the prefix path (without leading or trailing slashes).
$url_prefix = '';



?>