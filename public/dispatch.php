<?php
/*

   Dispatch Zynapse
    - the first step

*/


# path to config directory
$config_path = dirname(dirname(__FILE__))."/config"; 


$start = microtime(true);

// initial boot and environment setup
require_once($config_path."/init/boot.php");


// initialize zynapse
Zynapse::init();

echo "<br />\n<br />\nboot time: ".number_format(microtime(true) - $start, 6)." seconds<br />\n";

?>