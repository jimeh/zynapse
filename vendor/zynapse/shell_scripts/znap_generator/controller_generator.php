<?php
/*

   Controller Generator - generate controllers


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


class ControllerGenerator extends ZnapGenerator {
	
	public
		$class_name = null,
		$extra_path = null,
		$controller_path = null,
		$views_path = null,
		$view_root_paths = array(),
		$helper_path = null,
		$methods = array();
		
		
	function generate () {
		if ( !preg_match('/^[a-zA-Z0-9\/\\\_-]+$/', $this->name) ) {
			echo "ERROR: Invalid snippet name given.\n";
			exit;
		}
		
		if ( strstr($this->name, '/') ) {
			$this->extra_path = substr($this->name, 0, strripos($this->name, '/'));
			$this->name = Inflector::underscore(substr(strrchr($this->name, '/'), 1));
			$this->views_path = '/_'.$this->extra_path.'/'.$this->name;
			$this->controller_path = '/'.$this->extra_path;
			$this->helper_path = '/'.$this->extra_path;
		} else {
			$this->name = Inflector::underscore($this->name);
			$this->views_path = '/'.$this->name;
		}
		
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
		
		// validate that target controller path exists, or attempt to create if it doesn't
		if ( !$this->validate_path(Znap::$controllers_path.$this->controller_path) ) {
			return false;
		}

		// validate that target helper path exists, or attempt to create if it doesn't
		if ( !$this->validate_path(Znap::$helpers_path.$this->helper_path) ) {
			return false;
		}
		
		// validate that target view paths exists, or attempt to create if it doesn't
		foreach( $this->view_root_paths as $key => $path ) {
			if ( !$this->validate_path($path.$this->views_path) ) {
				return false;
			}
		}
		
		$this->create_controller();
		$this->create_helper();
		$this->create_views();
		return true;
	}
	
	
	function create_controller () {
		$controller_template = $this->templates_path.'/controller.php';
		$controller_file = Znap::$controllers_path.$this->controller_path.'/'.$this->name.'_controller.php';
		
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
		$helper_template = $this->templates_path.'/helper.php';
		$helper_file = Znap::$helpers_path.$this->helper_path.'/'.$this->name.'_helper.php';
		
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
		$view_template = $this->templates_path.'/view.phtml';
		
		if ( is_file($view_template) ) {
			foreach( $this->view_root_paths as $path ) {
				foreach( $this->methods as $view ) {
					$view_file = $path.$this->views_path.'/'.$view.'.'.Znap::$views_extension;
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
		echo "Generate Controller:\n";
		echo "  ./script/generate controller controller_name [view1 view2 ...]\n";
		echo "  for more controller info: ./script/generate controller\n";
	}
	
	function help () {
		echo "\n";
		echo "Usage: ./script/generate controller ControllerName [view1 view2 ...]\n";
		echo "\n";
		echo "Description:\n";
		echo "\tThe controller generator creates functions for a new controller and\n";
		echo "\tits views.\n";
		echo "\n";
		echo "\tThe generator takes a controller name and a list of views as arguments.\n";
		echo "\tThe controller name may be given in CamelCase or under_score and should\n";
		echo "\tnot be suffixed with 'Controller'.  To create a controller within a\n";
		echo "\tmodule, specify the controller name as 'folder/controller'.\n";
		echo "\tThe generator creates a controller class in app/controllers with view\n";
		echo "\ttemplates in app/views/controller_name.\n";
		echo "\n";
		echo "Example:\n";
		echo "\t./script/generate controller CreditCard open debit credit close\n";
		echo "\n";
		echo "\tCredit card controller with URLs like /credit_card/debit.\n";
		echo "\t\tController: app/controllers/credit_card_controller.php\n";
		echo "\t\tViews:      app/views/credit_card/debit.phtml [...]\n";
		echo "\t\tHelper:     app/helpers/credit_card_helper.php\n";
		echo "\n";
		echo "Module/Folders Example:\n";
		echo "\t./script/generate controller admin/credit_card suspend late_fee\n";
		echo "\n";
		echo "\tCredit card admin controller with URLs /admin/credit_card/suspend.\n";
		echo "\t\tController: app/controllers/admin/credit_card_controller.php\n";
		echo "\t\tViews:      app/views/_admin/credit_card/suspend.phtml [...]\n";
		echo "\t\tHelper:     app/helpers/credit_card_helper.php\n";
		echo "\n";
	}
}

?>