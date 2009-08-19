<?php
/*

   ActiveSession - session handling and flash messages


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


class ActiveSession {
	
	public
	
		# client user agent (OS, browser, etc.)
		$user_agent = null,
		
		# client's remote ip address
		$ip = null,
	
		# session id
		$id = null,
		
		# session key to store verification data in
		$key = '____active_session_verification_data____',
		
		# Session class has been started?
		$started = false;
	
	
	function __construct () {
		
	}
	
	function start () {
		session_start();
	}
	
	function init () {
		//TODO validate and init zynapse's session features
      $this->id = session_id();
		$this->started = true;
	}
	
}

?>