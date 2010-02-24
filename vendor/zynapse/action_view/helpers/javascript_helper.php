<?php
/*

   UrlHelper - URL and link related helpers


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


class JavascriptHelper extends Helpers {
	
	public
		$js_url,
		$libs_url,
		$lib_url_jquery,
		$default_charset,
		$libs = array();
	
	

	function __construct () {
		$this->js_url = Znap::$url_prefix.App::$_internals->js_url;
		$this->libs_url = Znap::$url_prefix.App::$_internals->js_libs_url;
		$this->default_charset = (!empty(App::$_internals->js_charset)) ? App::$_internals->js_charset : 'utf-8' ;
		if ( count(App::$_internals->js_libs) > 0 ) {
			$this->libs = App::$_internals->js_libs;
		}
	}
	


	function script_lib ($library) {
		if ( !empty($this->libs[$library]) ) {
			$library = $this->libs[$library];
		}
		if ( substr($library, -3) !== '.js' ) {
			$library .= '.js';
		}
		echo $this->script_tag($this->libs_url.'/'.$library, null, true);
	}
	
	function script_src ($file) {
		if ( substr($file, -3) !== '.js' ) {
			$file .= '.js';
		}
		echo $this->script_tag($this->js_url.'/'.$file, null, true);
	}
	
	function script_tag ($src = null, $charset = null, $close = false) {
		$properties = array(
			'src' => $src,
			'type' => 'text/javascript',
			'charset' => ($charset != null) ? $charset : $this->default_charset,
		);
		$return = $this->tag('script', $properties);
		if ( $close ) {
			$return .= '</script>';
		}
		return $return;
	}
	
	function script_content_tag ($src = null, $content = null, $charset = null) {
		return $this->script_tag($src, $charset).$content.'</script>';
	}

	
}



/*

   root scope functions

*/


function script_lib () {
	$helper = new JavascriptHelper();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'script_lib'), $args);
}

function script_src () {
	$helper = new JavascriptHelper();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'script_src'), $args);
}

function script_tag () {
	$helper = new JavascriptHelper();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'script_tag'), $args);
}

function script_content_tag () {
	$helper = new JavascriptHelper();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'script_content_tag'), $args);
}






?>