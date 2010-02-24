<?php
/*

   Views Helper - shorthand render functions


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


function render_partial () {
	$args = func_get_args();
	if ( Znap::$currently_rendering_snippet === null ) {
		return call_user_func_array(array(Znap::$current_controller_object, 'render_partial'), $args);
	} elseif ( is_object(Znap::$current_snippet_objects[Znap::$currently_rendering_snippet]) ) {
		return call_user_func_array(array(Znap::$current_snippet_objects[Znap::$currently_rendering_snippet], 'render_partial'), $args);
	}
}

function render_snippet () {
	$args = func_get_args();
	if ( !is_object(Znap::$snippets_controller) ) {
		Znap::$snippets_controller = new SnippetController();
	}
	return call_user_func_array(array(Znap::$snippets_controller, 'render_snippet'), $args);
}

function call_snippet () {
	$args = func_get_args();
	if ( !is_object(Znap::$snippets_controller) ) {
		Znap::$snippets_controller = new SnippetController();
	}
	return call_user_func_array(array(Znap::$snippets_controller, 'call_snippet'), $args);
}

?>