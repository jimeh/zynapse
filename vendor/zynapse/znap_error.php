<?php
/*

   Zynapse Error Class - error handling


   http://www.zynapse.org/
   Copyright (c) 2009 Jim Myhrberg.
   
   Based on action_controller.php from PHPOnTrax.

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



/*

   ZnapError
    - zynapse base exception class

*/
class ZnapError extends Exception {
	
	protected $details = null;
	
   function __construct($message = null, $details = null, $code = 0) {
		$this->details = $details;
		parent::__construct($message, $code);
	}
	
	public function getDetails () {
		return $this->details;
	}
	
	public function get_trace () {
		return str_replace(ZNAP_ROOT.'/', '', $this->getTraceAsString());
	}

}


/* 
   Action Controller's Exception handling class
*/
class ActionControllerError extends ZnapError {}


/* 
   Active Record's Exception handling class
*/
class ActiveRecordError extends ZnapError {}


/* 
   Snippet Controller's Exception handling class
*/
class SnippetControllerError extends ZnapError {}

?>