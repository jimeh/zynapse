<?php
/*

   Zynapse Environment - detect and configure environment


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


class ActionEnvironment {
	
	public
	
		# main
		$env,
		$mode,
		$root,
	
		# paths
		$apps_path,
		$lib_path,
		$log_path,
		$public_path,
		$tmp_path,
		$cache_path,
		$scripts_path,
		
		# misc
		$is_windows,
		$path_separator;
	
	function __construct () {
		$this->set_paths();
		$this->set_include_paths();
		$this->load_environment_file();
	}
	
	function load_environment_file () {
		require_once(ZNAP_CONFIG . "/environment.php");
		$this->env = $environment;
		if ( !empty($enable_host_specific_configuration) ) {
			$this->load_hosts_file();
		}
	}
	
	function load_hosts_file () {
		require_once(ZNAP_CONFIG . "/hosts.php");		
		$host = $this->match_to_host($hosts);
		if ( !empty($host) ) {
			if ( !empty($host['environment']) ) $this->env = $host['environment'];
			if ( !empty($host['mode']) ) $this->mode = $host['mode'];
			if ( !empty($host['root']) ) {
				if ( !empty($_SERVER['REQUEST_URI']) ) $_SERVER['REQUEST_URI'] = '/'.$host['root'].$_SERVER['REQUEST_URI'];
				if ( !empty($_SERVER['REDIRECT_URL']) ) $_SERVER['REDIRECT_URL'] = '/'.$host['root'].$_SERVER['REDIRECT_URL'];
			}
		}
	}
	
	function set_paths () {
		$this->apps_path = ZNAP_ROOT . "/apps";
		$this->lib_path = ZNAP_ROOT . "/lib";
		$this->log_path = ZNAP_ROOT . "/log";
		$this->public_path = ZNAP_ROOT . "/public";
		$this->tmp_path = ZNAP_ROOT . "/tmp";
		$this->cache_path = $this->tmp_path . "/log";
		$this->scripts_path = ZNAP_LIB_ROOT . "/shell_scripts";
	}
	
	function set_include_paths () {
		if ( substr(PHP_OS, 0, 3) != 'WIN' ) {
			$this->is_windows = false;
			$this->path_seperator = ":";
		} else {
			$this->is_windows = true;
			$this->path_seperator = ";";
		}
		
		ini_set("include_path",
			'.' . $this->path_seperator .
			ZNAP_LIB_ROOT . $this->path_seperator .
			$this->scripts_path . $this->path_seperator .
			$this->lib_path . $this->path_seperator .
			ini_get('include_path')
		);
		
	}
	
	function match_to_host ($list = array()) {
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
	
	
}

?>