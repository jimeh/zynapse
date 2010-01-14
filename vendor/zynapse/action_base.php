<?php
/*

   ActionBase - the core that does all the heavy lifting


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


class ActionBase {
	
	public
		
		# Components
		$env,     // ActionEnvironment
		$view,    // ActionView
		$log,     // ActiveLog
		$locale,  // ActiveLocale
		$session, // ActiveSession
		
		# Paths
		$apps_path,
		$lib_path,
		$log_path,
		$public_path,
		$tmp_path,
		$cache_path,
		$script_path,
		
		# Misc.
		$started = false;
	
	
	public function __construct () {
		
	}
	
	public function __sleep () {
		$blacklist = array_flip(array("env", "view", "log", "locale"));
		$save = array();
		foreach( $this as $key => $value ) {
			if ( !array_key_exists($key, $blacklist) ) {
				$save[] = $key;
			}
		}
		return $save;
	}
	
	public function __wakeup () {
		
	}
	
	public function init () {
		$this->set_paths();
		$this->started = true;
	}
	
	private function set_paths () {
		$this->apps_path = ZNAP_ROOT."/apps";
		$this->lib_path = ZNAP_ROOT."/lib";
		$this->log_path = ZNAP_ROOT."/log";
		$this->public_path = ZNAP_ROOT."/public";
		$this->tmp_path = ZNAP_ROOT."/tmp";
		$this->cache_path = $this->tmp_path."/cache";
		$this->script_path = ZNAP_ROOT."/script";
	}

}

?>