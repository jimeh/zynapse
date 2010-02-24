<?php
/*

   Dispatch Zynapse
    - the first step

*/


# path to config directory
$znap_config_path = dirname(dirname(__FILE__)).'/config'; 


// environment setup
require_once($znap_config_path.'/boot.php');


// start the junk :D
Dispatcher::dispatch();

?>