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
		$env,     // ActionEnvironment
		$base,    // ActionBase
		$view,    // ActionView
		$log,     // ActionLog
		$locale,  // ActiveLocale
		$session; // ActiveSession
	
	
	function init () {
		require_once(ZNAP_LIB_ROOT."/action_environment.php");
		require_once(ZNAP_LIB_ROOT."/action_base.php");
		require_once(ZNAP_LIB_ROOT."/action_view.php");
		require_once(ZNAP_LIB_ROOT."/active_session.php");
		
		// Enable PHP sessions
		ActiveSession::start();
		
		// Init the session control system (ActiveSession)
		self::$session = new ActiveSession();
		self::$session->init();
		
		// Init the environment system (ActionEnvironment)
		self::$env = new ActionEnvironment();
		self::$env->session =& self::$session;
		self::$env->init();
		
		// Init the core controller system (ActionBase)
		self::$base = new ActionBase();
		self::$base->init();
		
		// Init the output and page rendering system (ActionView)
		self::$view = new ActionView();
		self::$view->init();
		
		echo "hello world<br />\n";
		echo self::$env->environment."<br />\n<br />\n";
	}
	
}

?>