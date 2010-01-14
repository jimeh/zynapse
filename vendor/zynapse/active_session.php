<?php
/*

   ActiveSession - session handling and flash messages


   http://www.zynapse.org/
   Copyright (c) 2010 Jim Myhrberg.

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


class ActiveSession {
	
	public
		
		# session key to store verification data in
		$key = '____active_session_verification_data____',
		
		# Session class has been started?
		$started = false,


		##
		# PHP Session settings
		##
		
		# session name
		$name = "PHPSESSID",
		
		# session cookie_lifetime - defined in minutes
		$lifetime = 0,
		
		# max session lifetime - defined in minutes
		$maxlifetime = 30,
		
		# php.ini setting: session.use_only_cookies
		$use_only_cookies = false,
		
		# php.ini setting: session.gc_probability
		$gc_probability = 1,
		
		# php.ini setting: session.gc_divisor
		$gc_divisor = 100,
		
		# php.ini setting: session.cache_limiter
		$cache_limiter = "nocache",
		
		# session security features
		#   0 = no extra security features
		#   1 = user agent string is verified
		#   2 = user agent string, and client ip address are verified
		$security = 1;
	
	
	function __construct () {
		
	}
	
	function init () {
		$this->ini_setup();
		session_start();
		$this->validate();
		$this->started = true;
	}
	
	function validate () {
		if ( isset($_SESSION[$this->key]) && count($_SESSION[$this->key]) ) {
			$valid = true;
			if ( $this->security > 0 ) {
				if ( !isset($_SESSION[$this->key]['user_agent']) || $_SESSION[$this->key]['user_agent'] != $_SERVER['HTTP_USER_AGENT'] ) {
					$valid = false;
				}
			}
			if ( $this->security > 1 ) {
				if ( !$this->is_aol_host() && (!isset($_SESSION[$this->key]['ip']) || $_SESSION[$this->key]['ip'] != $_SERVER['REMOTE_ADDR']) ) {
					$valid = false;
				}
			}
			if ( !$valid ) {
				$_SESSION = array();
				$this->set_verification_data();
			}
		} else {
			$this->set_verification_data();
		}
	}
	
	function set_verification_data () {
		$_SESSION[$this->key] = array(
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'ip' => $_SERVER['REMOTE_ADDR'],
		);
	}
	
	function is_aol_host () {
		if ( stristr($_SERVER['HTTP_USER_AGENT'], 'AOL') || preg_match('/proxy\.aol\.com$/i', gethostbyaddr($_SERVER['REMOTE_ADDR'])) ) {
			return true;
		}
		return false;
	}
	
	function ini_setup () {
		ini_set('session.name', $this->name);
		ini_set('session.cookie_lifetime', $this->lifetime);
		ini_set('session.gc_maxlifetime', $this->maxlifetime);
		ini_set('session.use_only_cookies', $this->use_only_cookies);
		ini_set('session.gc_probability', $this->gc_probability);
		ini_set('session.gc_divisor', $this->gc_divisor);
		ini_set('session.cache_limiter', $this->cache_limiter);
	}
	
}

?>