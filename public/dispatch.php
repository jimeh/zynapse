<?php
/*

   Dispatch Zynapse
    - the first step

*/


# path to config directory
$config_path = dirname(dirname(__FILE__)).'/config'; 


// initial boot and environment setup
require_once($config_path.'/init/boot.php');


// initialize zynapse
Zynapse::init();

?>