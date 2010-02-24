<?php
/*

   Session - session handling and flash notices


   http://www.zynapse.org/
   Copyright (c) 2009 Jim Myhrberg.

   ----------
   Permission is hereby granted, free of charge, to any person obtaining
   a copy of this software and associated documentation files (the
   "Software"), to deal in the Software without restriction, including
   without limitation the rights to use, copy, modify, merge, publish,
   distribute, sublicense, and/or sell copies of the Software, and to
   permit persons to whom the Software is furnished to do so, subject to
   the following conditions:

   The above copyright notice and this permission notice shall be
   included in all copies or substantial portions of the Software.

   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
   EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
   MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
   NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
   LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
   OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
   WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
   ----------

*/


	
class Session {
	
	# session name
	const ZNAP_SESSION_NAME = 'PHPSESSID';
	
	# session cookie_lifetime - defined in minutes
	const ZNAP_SESSION_LIFETIME = 0;
	
	# max session lifetime - defined in minutes
	const ZNAP_SESSION_MAXLIFETIME = 30;
	
	# php.ini setting: session.use_only_cookies
	const ZNAP_SESSION_USE_ONLY_COOKIES = false;
	
	# php.ini setting: session.gc_probability
	const ZNAP_SESSION_GC_PROBABILITY = 1;
	
	# php.ini setting: session.gc_divisor
	const ZNAP_SESSION_GC_DIVISOR = 100;
	
	# php.ini setting: session.cache_limiter
	const ZNAP_SESSION_CACHE_LIMITER = 'nocache';
	
	# session security features
	#   0 = no extra security features
	#   1 = user agent string is verified
	#   2 = user agent string, and client ip address are verified
	const ZNAP_SESSION_SECURITY = 1;
	
	
	public static
	
		# client user agent (OS, browser, etc.)
		$user_agent = null,
		
		# client's remote ip address
		$ip = null,
	
		# session id
		$id = null,
		
		# session key to store verification data in
		$key = '____zynapse_secure_session_data_verification____',
		
		# Session class has been started?
		$started = false;


	# internal vars
	protected static
		$session_name,
		$session_lifetime,
		$session_maxlifetime,
		$session_use_only_cookies,
		$session_gc_probability,
		$session_gc_divisor,
		$session_cache_limiter,
		$session_security;

		
		
	function start () {
		if ( !self::$started ) {
			self::var_setup();
			self::ini_setup();
			
			self::$user_agent = $_SERVER['HTTP_USER_AGENT'];
			self::$ip = $_SERVER['REMOTE_ADDR'];
			
			if ( !self::$session_use_only_cookies && array_key_exists('sess_id', $_REQUEST) ) {
				session_id($_REQUEST['sess_id']);
			}
			
			session_start();
         self::$id = session_id();
         self::$started = true;

			self::validate();
		}
	}
	
	function session_destroy () {
		session_destroy();
	}
	
	function validate () {
		if ( isset($_SESSION[self::$key]) && count($_SESSION[self::$key]) ) {
			$valid = true;
			if ( self::$session_security > 0 && (!isset($_SESSION[self::$key]['user_agent']) || $_SESSION[self::$key]['user_agent'] != self::$user_agent) ) {
				$valid = false;
			}
			if ( self::$session_security > 1 ) {
				if ( !self::is_aol_host() && (!isset($_SESSION[self::$key]['ip']) || $_SESSION[self::$key]['ip'] != self::$ip) ) {
					$valid = false;
				}
			}
			if ( !$valid ) {
				$_SESSION = array();
				self::validate();
			}
		} else {
			$_SESSION[self::$key] = array(
				'user_agent' => self::$user_agent,
				'ip' => self::$ip,
			);
		}
	}
	
	function is_aol_host () {
		if ( stristr(self::$user_agent, 'AOL') || preg_match('/proxy\.aol\.com$/i', gethostbyaddr(self::$ip)) ) {
			return true;
		}
		return false;
	}
	
	function var_setup () {
		self::$session_name             = defined('ZNAP_SESSION_NAME')             ? ZNAP_SESSION_NAME             : self::ZNAP_SESSION_NAME ;
		self::$session_lifetime         = defined('ZNAP_SESSION_LIFETIME')         ? ZNAP_SESSION_LIFETIME         : self::ZNAP_SESSION_LIFETIME ;
		self::$session_maxlifetime      = defined('ZNAP_SESSION_MAXLIFETIME')      ? ZNAP_SESSION_MAXLIFETIME      : self::ZNAP_SESSION_MAXLIFETIME ;
		self::$session_use_only_cookies = defined('ZNAP_SESSION_USE_ONLY_COOKIES') ? ZNAP_SESSION_USE_ONLY_COOKIES : self::ZNAP_SESSION_USE_ONLY_COOKIES ;
		self::$session_gc_probability   = defined('ZNAP_SESSION_GC_PROBABILITY')   ? ZNAP_SESSION_GC_PROBABILITY   : self::ZNAP_SESSION_GC_PROBABILITY ;
		self::$session_gc_divisor       = defined('ZNAP_SESSION_GC_DIVISOR')       ? ZNAP_SESSION_GC_DIVISOR       : self::ZNAP_SESSION_GC_DIVISOR ;
		self::$session_cache_limiter    = defined('ZNAP_SESSION_CACHE_LIMITER')    ? ZNAP_SESSION_CACHE_LIMITER    : self::ZNAP_SESSION_CACHE_LIMITER ;
		self::$session_security         = defined('ZNAP_SESSION_SECURITY')         ? ZNAP_SESSION_SECURITY         : self::ZNAP_SESSION_SECURITY ;
	}

	function ini_setup () {
		ini_set('session.name', self::$session_name);
		ini_set('session.cookie_lifetime', self::$session_lifetime);
		ini_set('session.gc_maxlifetime', self::$session_maxlifetime);
		ini_set('session.use_only_cookies', self::$session_use_only_cookies);
		ini_set('session.gc_probability', self::$session_gc_probability);
		ini_set('session.gc_divisor', self::$session_gc_divisor);
		ini_set('session.cache_limiter', self::$session_cache_limiter);
	}
	
	
	
	function flash ($key, $value = null) {
		if ( $value !== null ) {
			$_SESSION['flash'][$key] = $value;
		} else {
			$value = $_SESSION['flash'][$key];
			if ( !Znap::$keep_flash ) unset($_SESSION['flash'][$key]);
			return $value;
		}
	}
	
	function isset_flash ($key) {
		if ( isset($_SESSION['flash'][$key]) ) {
			return true;
		}
		return false;
	}

	
}

?>