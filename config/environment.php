<?php
/*

   Zynapse Environment
    - configure server environments and display modes

*/



# Default environment - Overridden by host specific
# configurations.
#
# ( development | test | staging | production )
$this->env = 'development';


# Default server display mode - Overridden by host
# specific configurations.
$this->mode = 'web';


# When enabled, environment and more is set based
# on the current domain that zynapse is running from.
#
# Configure hosts in "config/hosts.php".
$enable_host_specific_configuration = true;


?>