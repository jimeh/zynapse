<?php
/*

   Zynapse Environment
    - configure server environment

*/



# Default environment - Overridden by host specific
# configurations.
#
# ( development | test | staging | production )
$this->environment = "development";


# Default output format - Overridden by host
# specific configurations.
$this->format = "html";


# When enabled, environment and more is set based
# on the current domain that zynapse is running from.
#
# Configure hosts in "config/hosts.php".
$enable_host_specific_configuration = true;



##
# Session settings
##

# session name
# $this->session->name = "PHPSESSID";

# session cookie_lifetime - defined in minutes
# $this->session->lifetime = 0;

# max session lifetime - defined in minutes
# $this->session->maxlifetime = 30;

# php.ini setting: session.use_only_cookies
# $this->session->use_only_cookies = false;

# php.ini setting: session.gc_probability
# $this->session->gc_probability = 1;

# php.ini setting: session.gc_divisor
# $this->session->gc_divisor = 100;

# php.ini setting: session.cache_limiter
# $this->session->cache_limiter = "nocache";

# session security features
#   0 = no extra security features
#   1 = user agent string is verified
#   2 = user agent string, and client ip address are verified
# $this->session->security = 1;


?>