<?php
/*

   Zynapse - main class


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


class Zynapse {
	
	public static
	
		# Action Classes
		$env,    // ActionEnvironment
		$base,   // ActionBase
		$view,   // ActionView
		$log,    // ActionLog
		$locale; // ActiveLocale
	
	
	function init () {
		require_once(ZNAP_LIB_ROOT . "/action_environment.php");
		require_once(ZNAP_LIB_ROOT . "/action_base.php");
		require_once(ZNAP_LIB_ROOT . "/active_session.php");
		
		$start = microtime(true);
		self::$env = new ActionEnvironment();
		
		self::$base = new ActionBase();
		self::$base->init();
		
		echo microtime(true) - $start . "<br />\n";
		echo "hello world<br />\n";
		echo self::$env->environment . "<br />\n<br />\n";
		
		// echo serialize(self::$env) . "<br />\n<br />\n";
		// echo serialize(self::$base) . "<br />\n<br />\n";
	}
	
}

?>