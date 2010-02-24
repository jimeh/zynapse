<?php
/*

   Zynapse Router - url path parser


   http://www.zynapse.org/
   Copyright (c) 2009 Jim Myhrberg.
   
   Based on router.php from PHPOnTrax.

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


class Router {
	
	private $routes = array();
	private $selected_route = null;
	
	public $default_route_path = ':controller/:action/:id';
	public $routes_count = 0;
	public $url;
	
	
	function get_selected_route () {
		return $this->selected_route;
	}
	
	function connect ( $url, $params = null ) {
		if ( !is_array($params) ) $params = null;
		$this->routes[] = array(
			'path' => $url,
			'params' => $params,
		);
		$this->routes_count++;
	}
	
	function find_route ($url = null) {
		if ( $this->url === null ) $this->url = $this->get_url_path();
		if ( $this->routes_count == 0 ) $this->connect($this->default_route_path);
		foreach( $this->routes as $key => $route ) {
			$regex = $this->build_regex_path($route['path']);
			if ( $regex['regex'] != '' ) {
				$route['path'] = $regex['path'];
			}
			$regex = $regex['regex'];
			if ( $url == '' && $regex == '' ) {
				$selected_route = $route;
				break;
			} elseif ( $regex != '' && preg_match('/^'.$regex.'$/i', $url) ) {
				$selected_route = $route;
				break;
			} elseif ( $route['path'] == $this->default_route_path ) {
				$selected_route = $route;
				break;
			}
		}
		if ( isset($selected_route) ) {
			$this->selected_route = $selected_route;
			return $selected_route;
		} else {
			$this->selected_route = null;
			return false;
		}
	}
	
	
	function build_regex_path ($path) {
		if ( is_array($path) ) {
			return $path;
		} else {
			$path = explode('/', $path);
			$regex = array();
			$new_path = array();
			$regex_foot = '';
			if ( count($path) ) {
				foreach( $path as $element ) {
					$elm_foot = '';
					if ( strlen($element) > 0 && substr($element, -1, 1) == '?' ) {
						$elm_foot = '(?:';
						$regex_foot .= '|$)';
						$element = substr($element, 0, strlen($element)-1);
					}
					if ( preg_match('/^(:[a-z0-9_\-]+)\((.*)\)$/i', $element, $capture) ) {
						$regex[] = '(?:'.$capture[2].')'.$elm_foot;
						$new_path[] = $capture[1];
					} elseif ( preg_match('/^:[a-z0-9_\-]+$/i', $element) ) {
						$regex[] = '(?:[a-z0-9_\-]+?)'.$elm_foot;
						$new_path[] = $element;
					} elseif ( preg_match('/^[a-z0-9_\-]+$/i', $element) ) {
						$regex[] = $element.$elm_foot;
						$new_path[] = $element;
					} elseif ( Znap::$allow_dangerous_url_paths ) {
						$regex[] = $element.$elm_foot;
						$new_path[] = $element;
					}
				}
			}
			$regex = implode('\/', $regex).$regex_foot;
			$new_path = implode('/', $new_path);
			return array('regex' => $regex, 'path' => $new_path);
		}
	}
	
	function get_url_path ($prefix = null) {
		if ( isset($_SERVER['REDIRECT_URL']) && !stristr($_SERVER['REDIRECT_URL'], 'dispatch.php') ) {
			$url = $_SERVER['REDIRECT_URL'];
		} elseif ( isset($_SERVER['REQUEST_URI']) ) {
			if ( !strstr($_SERVER['REQUEST_URI'], '?') ) {
				$url = $_SERVER['REQUEST_URI'];
			} else {
				$url = substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'));
			}
		}
		if ( isset($url) ) {
			$url = trim($url, '/');
			if ( !is_null(Znap::$url_prefix) && substr($url, 0, strlen(Znap::$url_prefix)) == Znap::$url_prefix ) {
				$url = ltrim(substr($url, strlen(Znap::$url_prefix)), '/');
			}
			return $url;
		}
		return false;
	}
	
}

?>