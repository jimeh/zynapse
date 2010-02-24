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


class UrlHelper extends Helpers {
	

	function link_to ($name = null, $link_to = array(), $properties = array()) {
		$properties = $this->convert_confirm_property_to_javascript($properties);
		
		if ( is_string($link_to) ) {
			$url = $link_to;
		} elseif ( is_array($link_to) ) {
			$url = $this->url_for($link_to);
		}
		
		$properties['href'] = $url;
		if ( $name == null || $name == false ) {
			$name = $link_to;
		}
		
		return $this->content_tag('a', $name, $properties);
	}
	
	function convert_confirm_property_to_javascript ($properties) {
		if ( array_key_exists('confirm', $properties) ) {
			$properties['onclick'] = 'return confirm(\''.addslashes($properties['confirm']).'\');';
			unset($properties['confirm']);
		}
		return $properties;
	}
	
	
	function url_for ($options = array()) {
		if ( is_string($options) ) {
			return $options;
		} elseif ( is_array($options) ) {
			
			// host
			$url = $_SERVER['HTTP_HOST'];
			if ( substr($url, -1) == '/' ) {
				$url = substr($url, 0, -1);
			}
			
			// port
			if ( $_SERVER['SERVER_PORT'] == 80 ) {
				$url = 'http://'.$url;
			} elseif ( $_SERVER['SERVER_PORT'] == 443 ) {
				$url = 'https://'.$url;
			} elseif (!empty($_SERVER['SERVER_PORT'])) {
				$url = 'http://'.$url.':'.$_SERVER['SERVER_PORT'];
			}
			
			// prefix
			if ( Znap::$url_prefix != null ) {
				$url = (Znap::$url_prefix{0} != '/') ? '/'.Znap::$url_prefix : Znap::$url_prefix ;
			}
			
			$paths = array();
			
			// controller
			if ( array_key_exists(':controller', $options) && $options[':controller'] != '' ) {
				$paths[] = $options[':controller'];
			}
			
			// action
			if ( count($paths) && array_key_exists(':action', $options) ) {
				$paths[] = $options[':action'];
			}
			
			// id
			if ( count($paths) > 1 && array_key_exists(':id', $options) ) {
				if ( is_object($options[':id']) && isset($options[':id']->id) ) {
					$paths[] = $options[':id']->id;
				} elseif ( !is_object($options[':id']) ) {
					$paths[] = $options[':id'];
				}
			}
			
			$extra_params = array();
			if ( count($options) ) {
				foreach( $options as $key => $value ) {
					if ( !strpos($key, ':') ) {
						$extra_params[$key] = $value;
					}
				}
			}
			
			if ( substr($url, -1) != '/' ) {
				$url .= '/';
			}
			
			return $url . implode('/', $paths) . (count($extra_params)) ? '?'.http_build_query($extra_params) : null ;
		}
	}

	
}



/*

   root scope functions

*/

function link_to () {
	$helper = new UrlHelper();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'link_to'), $args);
}

function url_for () {
	$helper = new UrlHelper();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'url_for'), $args);
}










?>