<?php
/*

   Zynapse - main class


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


class Zynapse {
	
	public static
	
		# Action Classes
		$env,     // ActionEnvironment
		$base,    // ActionBase
		$view,    // ActionView
		$log,     // ActiveLog
		$locale,  // ActiveLocale
		$session; // ActiveSession
	
	
	function init () {
		require_once(ZNAP_LIB_ROOT."/action_environment.php");
		require_once(ZNAP_LIB_ROOT."/active_session.php");
		require_once(ZNAP_LIB_ROOT."/action_base.php");
		require_once(ZNAP_LIB_ROOT."/action_view.php");
		require_once(ZNAP_LIB_ROOT."/active_log.php");
		
		# Create component objects
		self::$env = new ActionEnvironment();
		self::$session = new ActiveSession();
		self::$base = new ActionBase();
		self::$view = new ActionView();
		self::$log = new ActiveLog();
		
		# Assign internal component references
		self::$env->session =& self::$session;
		self::$env->base =& self::$base;
		
		self::$base->env =& self::$env;
		self::$base->view =& self::$view;
		self::$base->log =& self::$log;
		self::$base->locale =& self::$locale;
		self::$base->session =& self::$session;
		
		self::$view->env =& self::$env;
		self::$view->base =& self::$base;
		self::$view->log =& self::$log;
		self::$view->locale =& self::$locale;
		self::$view->session =& self::$session;
		
		self::$log->env =& self::$env;
		self::$log->base =& self::$base;
		self::$log->view =& self::$view;
		self::$log->locale =& self::$locale;
		self::$log->session =& self::$session;
		
		
		# Init the environment system (ActionEnvironment)
		self::$env->init();
		
		# Init the session control system (ActiveSession)
		self::$session->init();
		
		# Init the core controller system (ActionBase)
		self::$base->init();
		
		# Init the logging system (ActiveLog)
		self::$log->init();
		
		# Init the output and page rendering system (ActionView)
		self::$view->init();
		
		echo "hello world<br />\n";
		echo self::$env->environment."<br />\n<br />\n";
	}
	
}

?>