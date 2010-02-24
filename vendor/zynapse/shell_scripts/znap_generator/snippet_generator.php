<?php
/*

   Snippet Generator - generate snippets


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


class SnippetGenerator extends ZnapGenerator {
	
	public
		$class_name = null,
		$views_path = null,
		$view_root_paths = array(),
		$methods = array();
		
		
	function generate () {
		if ( !preg_match('/^[a-zA-Z0-9_-]+$/', $this->name) ) {
			echo "ERROR: Invalid snippet name given.\n";
			exit;
		}
		
		$this->name = Inflector::underscore($this->name);
		$this->views_path = '__snippets/'.$this->name;
		
		$this->name = Inflector::singularize($this->name);
		$this->class_name = Inflector::camelize($this->name);
		
		// get method/view arguments
		if ( isset($this->args[1]) ) {
			for ( $i=1; $i < count($this->args); $i++ ) {
				$this->methods[$i] = $this->args[$i];
			}
		}
		
		// find all "views*" paths
		$this->find_view_paths();
		
		// validate that target snippet path exists, or attempt to create if it doesn't
		if ( !$this->validate_path(Znap::$snippets_path) ) {
			return false;
		}

		// validate that target helper path exists, or attempt to create if it doesn't
		if ( !$this->validate_path(Znap::$snippet_helpers_path) ) {
			return false;
		}
		
		// validate that target view paths exists, or attempt to create if it doesn't
		foreach( $this->view_root_paths as $key => $path ) {
			if ( !$this->validate_path($path.'/'.$this->views_path) ) {
				return false;
			}
		}
		
		$this->create_snippet();
		$this->create_helper();
		$this->create_views();
		return true;
	}
	
	
	function create_snippet () {
		$controller_template = $this->templates_path.'/snippet.php';
		$controller_file = Znap::$snippets_path.'/'.$this->name.'_snippet.php';
		
		if ( !is_file($controller_file) ) {
			if ( is_file($controller_template) ) {
				// controller
				$controller = file_get_contents($controller_template);
				$controller = str_replace('[class_name]', $this->class_name, $controller);
				if ( count($this->methods) ) {
					$methods = array();
					foreach( $this->methods as $method ) {
						$methods[] = "\tfunction ".$method." () {\n\t\t\n\t}\n";
					}
					$controller = str_replace('[class_methods]', "\t\n".implode("\n", $methods), $controller);
				} else {
					$controller = str_replace('[class_methods]', "\t", $controller);
				}
				if ( file_put_contents($controller_file, $controller) ) {
					$this->echo_create($controller_file);
				} else {
					$this->echo_create_error($controller_file, 'controller');
					exit;
				}
			} else {
				$this->echo_template_error($controller_template, 'controller');
				exit;
			}
		} else {
			$this->echo_exists($controller_file);
		}
	}
	
	function create_helper () {
		$helper_template = $this->templates_path.'/snippet_helper.php';
		$helper_file = Znap::$snippet_helpers_path.'/'.$this->name.'_helper.php';
		
		if ( !is_file($helper_file) ) {
			if ( is_file($helper_template) ) {
				$helper = file_get_contents($helper_template);
				$helper = str_replace('[class_name]', $this->class_name, $helper);
				if ( file_put_contents($helper_file, $helper) ) {
					$this->echo_create($helper_file);
				} else {
					$this->echo_create_error($helper_file, 'helper');
				}
			} else {
				$this->echo_template_error($helper_template, 'helper');
			}
		} else {
			$this->echo_exists($helper_file);
		}
		
	}

	function create_views () {
		$view_template = $this->templates_path.'/snippet_view.phtml';
		
		if ( is_file($view_template) ) {
			foreach( $this->view_root_paths as $path ) {
				foreach( $this->methods as $view ) {
					$view_file = $path.'/'.$this->views_path.'/'.$view.'.'.Znap::$views_extension;
					if ( !is_file($view_file) ) {
						$view_data = file_get_contents($view_template);
						$view_data = str_replace('[class_name]', $this->class_name, $view_data);
						$view_data = str_replace('[view]', $view, $view_data);
						$view_data = str_replace('[controller]', $this->name, $view_data);
						$view_data = str_replace('[view_file]', str_replace(Znap::$app_path.'/', '', $view_file), $view_data);
						if ( file_put_contents($view_file, $view_data) ) {
							$this->echo_create($view_file);
						} else {
							$this->echo_create_error($view_file, 'view');
						}
					} else {
						$this->echo_exists($view_file);
					}
				}
			}
		} else {
			$this->echo_template_error($view_template, 'view');
		}
	}
	
	
	function find_view_paths () {
		$this->view_root_paths = glob(Znap::$app_path.'/views*');
	}
	
	
	function help_summary () {
		echo "Generate Snippet:\n";
		echo "  ./script/generate snippet snippet_name [action1 action2 ...]\n";
		echo "  for more controller info: ./script/generate snippet\n";
	}
	
	function help () {
		echo "\n";
		echo "Usage: ./script/generate snippet SnippetName [action1 action2 ...]\n";
		echo "\n";
		echo "Description:\n";
		echo "\tThe snippet generator creates a new snippet, it's actions and views,\n";
		echo "\twhich are easily reusable in controller view files.\n";
		echo "\n";
		echo "\tThe generator takes a snippet name and a list of actions as arguments.\n";
		echo "\tThe snippet name may be given in CamelCase or under_score and should\n";
		echo "\tnot be suffixed with 'Snippet'.\n";
		echo "\n";
		echo "\tThe generator creates a snippet class in app/snippets with view\n";
		echo "\ttemplates in app/views/__snippets/snippet_name.\n";
		echo "\n";
		echo "Example:\n";
		echo "\t./script/generate snippet TagCloud user all_users\n";
		echo "\n";
		echo "\tTag cloud snippet is usable with render_snippet() helper.\n";
		echo "\t\tSnippet: app/snippets/tag_cloud_snippet.php\n";
		echo "\t\tViews:   app/views/__snippets/tag_cloud/user.phtml [...]\n";
		echo "\t\tHelper:  app/helpers/snippet_helpers/tag_cloud_helper.php\n";
		echo "\n";
	}
}

?>