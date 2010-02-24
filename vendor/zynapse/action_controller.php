<?php
/*

   Zynapse ActionController - application logic


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


class ActionController {

	public $controller;
	public $action;
	public $id;
	private $added_path = '';
	private $action_params = array();
	private $controllers_path;
	private $helpers_path;
	private $helpers_base_path;
	private $layouts_path;
	private $layouts_base_path;
	private $url_path;
	private $helper_file;
	private $application_controller_file;
	private $application_helper_file;
	private $loaded = false;
	private $router_loaded = false;
	private $helpers = array();
	private $before_filters = array();
	private $after_filters = array();
	private $render_performed = false;
	private $action_called = false;
	private $router;
	private $currently_rendering_file;
	private $content_for_layout = null;
	private $default_action = 'index';

	public $controller_file;
	public $view_file;
	public $views_path;
	public $controller_class;
	public $controller_object;
	public $render_layout = true;
	public $keep_flash = false;
	public $route_params = array();
	public $current_route;
	public $requested_path;
	public $prefs = null;



	function __construct () {
		// doesn't need to do anything at the moment
	}

	//TODO permanently remove __set() function or not
	// -------------------------------------------------	
	// function __set ($key, $value) {
	// 	if ( $key == 'before_filter' ) {
	// 		$this->add_before_filter($value);
	// 	} elseif ( $key == 'after_filter' ) {
	// 		$this->add_after_filter($value);
	// 	} elseif ( $key == 'helper' ) {
	// 		$this->add_helper($value);
	// 	} elseif ( $key == 'render_text' ) {
	// 		$this->render_text($value);
	// 	} elseif ( $key == 'redirect_to' ) {
	// 		$this->redirect_to($value);
	// 	} else {
	// 		$this->$key = $value;
	// 	}
	// }

	//TODO permanently remove __call() function or not
	// -------------------------------------------------
	// function __call ($method, $params) {
	// 	if ( method_exists($this, $method) ) {
	// 		return call_user_func(array($this, $method), $params);
	// 	} else {
	// 		if ( $method == 'before_filter' ) {
	// 			return call_user_func(array($this, 'add_before_filter'), $params);
	// 		} elseif ( $method == 'after_filter' ) {
	// 			return call_user_func(array($this, 'add_after_filter'), $params);
	// 		} elseif ( $method == 'helper' ) {
	// 			return call_user_func(array($this, 'add_helper'), $params);
	// 		}
	// 	}
	// }
	
	
	function init_filters () {
		// check if any filters are pre-defined
		if ( isset($this->before_filter) ) {
			$this->add_before_filter($this->before_filter);
			unset($this->before_filter);
		}
		if ( isset($this->after_filter) ) {
			$this->add_after_filter($this->after_filter);
			unset($this->after_filter);
		}
		if ( isset($this->helper) ) {
			$this->add_helper($this->helper);
			unset($this->helper);
		}
	}
	
	
	
	function load_router() {
		$this->router_loaded = false;
		$router = new Router();

		// load defined routes
		require(Znap::$config_path."/routes.php");

		$this->router = $router;
		if ( is_object($this->router) ) {
			$this->router_loaded = true;
		}
	}
	
	
	function parse_request () {
		if ( !$this->router_loaded ) {
			$this->load_router();
		}

		$url_path = $this->router->get_url_path();
		$this->url_path = ( !empty($url_path) ) ? explode('/', $url_path) : array() ;
		$this->requested_path = $url_path;

		if ( $this->router->routes_count > 0 ) {
			$this->controllers_path = Znap::$controllers_path;
			$this->helpers_path = $this->helpers_base_path = Znap::$helpers_path;
			$this->application_controller_file = $this->controllers_path.'/application.php';
			$this->application_helper_file = $this->helpers_path.'/application_helper.php';
			$this->layouts_path = $this->layouts_base_path = Znap::$layouts_path;
			$this->views_path = Znap::$views_path;

			$route = $this->router->find_route($url_path);

			if ( is_array($route) ) {
				$this->check_paths();
				$this->current_route = &$this->router->current_route;

				$route_paths = explode('/', $route['path']);
				$route_params = ( is_array($route['params']) ) ? $route['params'] : array() ;
				$path_params = array();

				// find path components
				foreach( $route_paths as $key => $value ) {
					if ( substr($value, 0, 1) == ':' && strlen($value) >= 2 ) {
						$path_params[$value] = $key;
					}
				}

				// redirect_to?
				if ( array_key_exists(':redirect_to', $route_params) && $route_params[':redirect_to'] != '' ) {
					$this->redirect_to($route_params[':redirect_to']);
				}

				// controller
				if ( array_key_exists(':controller', $route_params) && $route_params[':controller'] != '' ) {
					$this->controller = strtolower($route['params'][':controller']);
				} elseif ( array_key_exists(':controller', $path_params) && @$this->url_path[$path_params[':controller']] != '' ) {
					$this->controller = strtolower($this->url_path[$path_params[':controller']]);
				}

				// action
				if ( array_key_exists(':action', $route_params) && $route_params[':action'] != '' ) {
					$this->action = strtolower($route['params'][':action']);
				} elseif ( array_key_exists(':action', $path_params) && @$this->url_path[$path_params[':action']] != '' ) {
					$this->action = strtolower($this->url_path[$path_params[':action']]);
				}

				// id
				if ( array_key_exists(':id', $route_params) && $route_params[':id'] != '' ) {
					$id = strtolower($route['params'][':id']);
				} elseif ( array_key_exists(':id', $path_params) && @$this->url_path[$path_params[':id']] != '' ) {
					$id = strtolower($this->url_path[$path_params[':id']]);
				}
				if ( isset($id) && preg_match('/^[0-9]+$/i', $id) ) {
					$this->id = $id;
					$_REQUEST['id'] = $this->id;
					$this->action_params['id'] = $this->id;
				}

				// store route params so they can be accessed from methods
				$this->route_params = &$route_params;


				// additional paths
				if ( !empty($path_params) ) {
					foreach( $path_params as $key => $value ) {
						if ( $key != ':controller' && $key != ':action' && $key != ':id' ) {
							if ( array_key_exists($value, $this->url_path) && $this->url_path[$value] != '' ) {
								$var = substr($key, 1);
								$_REQUEST[$var] = $this->url_path[$value];
								$this->action_params[$var] = $this->url_path[$value];
							}
						}
					}
				}

				// set the final stuff
				$this->controller_file = $this->controllers_path.'/'.$this->controller.'_controller.php';
				$this->helper_file = $this->helpers_path.'/'.$this->controller.'_helper.php';
				$this->controller_class = Inflector::camelize($this->controller).'Controller';
				$this->views_path .= '/'.$this->controller;
			}
		}

		if ( is_file($this->controller_file) ) {
			$this->loaded = true;
			return true;
		} else {
			$this->loaded = false;
			return false;
		}	
	}
	
	
	function process_route () {
		if ( !$this->loaded ) {
			if ( !$this->parse_request() ) {
				$this->raise('Controller "'.$this->controller.'" not found...', '...nor were any matching routes found.', 404);
			}
		}

		// include application controller
		if ( is_file($this->application_controller_file) ) {
			include_once($this->application_controller_file);
		}


		// include current controller
		include_once($this->controller_file);
		if ( class_exists($this->controller_class, false) ) {
			// create child controller object
			$class = $this->controller_class;
			$this->controller_object = new $class();

			if ( is_object($this->controller_object) ) {
				// set initial properties of child controller object
				$this->controller_object->init_filters();
				$this->controller_object->controller        = $this->controller;
				$this->controller_object->action            = $this->action;
				$this->controller_object->id                = $this->id;
				$this->controller_object->controllers_path  = &$this->controllers_path;
				$this->controller_object->helpers_path      = &$this->helpers_path;
				$this->controller_object->helpers_base_path = &$this->helpers_base_path;
				$this->controller_object->views_path        = $this->views_path;
				$this->controller_object->layouts_path      = &$this->layouts_path;
				$this->controller_object->layouts_base_path = &$this->layouts_base_path;
				$this->controller_object->route_params      = &$this->route_params;
				$this->controller_object->requested_path    = &$this->requested_path;
				$this->controller_object->current_route     = &$this->current_route;

				// set static Znap properties
				Znap::$current_controller_object     = &$this->controller_object;
				Znap::$current_controller_class_name = $this->controller_class;
				Znap::$current_controller_name       = $this->controller;
				Znap::$current_controller_path       = $this->controllers_path;
				Znap::$current_action_name           = $this->action;
				Znap::$current_route                 = &$this->current_route;


				// include main application helper file
				if ( is_file($this->application_helper_file) ) {
					include_once($this->application_helper_file);
				}
				
				// load language specific strings
				//  - defaults to english if Znap::$settings['language']
				//    is not defined by before filters
				Znap::load_strings();
				
				// include controller specific preferences
				if ( isset($this->controller_object->has_prefs) && Znap::$prefs->read($this->controller, true) ) {
					$this->controller_object->prefs = &Znap::$prefs->{$this->controller};
				}

				// execute before_filters, display an error page if any filter method returns false
				if ( ($before_filters_result = $this->controller_object->execute_before_filters()) === true ) {

					// supress output for capture
					ob_start();

					// include controller specific helper file
					if ( is_file($this->helper_file) ) {
						include_once($this->helper_file);
					}

					// call default action/method if none is defined
					if ( $this->action === null ) {
						$this->action = $this->default_action;
					}

					// execute main method
					if ( $this->valid_action($this->action) ) {
						$action = $this->action;
						$this->controller_object->$action($this->action_params);
					} elseif ( is_file($this->views_path.'/'.$this->action.'.'.Znap::$views_extension) ) {
						$action = $this->action;
					} else {
						$this->raise('Action "'.$this->action.'" not found.', null, 404);
					}

					$this->controller_object->execute_after_filters();
					$this->controller_object->action_called = true;

					// include any additionaly defined helpers
					if ( count($this->controller_object->helpers) ) {
						foreach( $this->controller_object->helpers as $helper ) {
							if ( is_file($this->helpers_base_path.'/'.$helper.'_helper.php') ) {
								include_once($this->helpers_base_path.'/'.$helper.'_helper.php');
							}
						}
					}

					// if true Session::flash() messages will be displayed till this is set to false on a page load.
					Znap::$keep_flash = $this->keep_flash;

					// check if $redirect_to was set and redirect accordingly if so
					if ( isset($this->controller_object->redirect_to) && $this->controller_object->redirect_to != '' ) {
						$this->redirect_to($this->controller_object->redirect_to);
					}

					// if render_text is defined as a string, render it instead of layout & view files
					if ( isset($this->controller_object->render_text) && $this->controller_object->render_text != '' ) {
						$this->render_text($this->controller_object->render_text);
					}

					// if render_action is defined, use it as the render action, if it is false, don't render action view file
					if ( isset($this->controller_object->render_action) ) {
						$action = $this->controller_object->render_action;
					}

					// render view file
					if ( $action !== false && !$this->controller_object->render_action($action) ) {
						$this->raise('No "'.$action.'" view file found.', null, 500);
					}

					// grab captured output
					$this->controller_object->content_for_layout = ob_get_contents();
					ob_end_clean();

					// render or not to render layout (that is the question)
					if ( $this->controller_object->render_layout !== false && ($layout_file = $this->controller_object->determine_layout()) ) {
						if ( Timer::$started ) ob_start();
						if ( !$this->controller_object->render_file($layout_file) ) {
							echo $this->controller_object->content_for_layout;
						}
						if ( Timer::$started ) ob_end_flush();
					} else {
						echo $this->controller_object->content_for_layout;
					}
				} else {
					$this->raise('The "'.$before_filters_result.'" before filter failed.', null, 500);
				}
			} else {
				$this->raise('Failed to initiate controller object "'.$this->controller.'".', null, 500);
			}
		} else {
			$this->raise('Controller class "'.$this->controller_class.'" not found.', null, 500);
		}
	}


	function process_exception ( $object ) {

		// load language specific strings which can be used in error files
		Znap::load_strings();

		$this->error = $object;
		$this->message = $object->getMessage();
		$this->details = $object->getDetails();
		$this->code = $object->getCode();
		$this->trace = $object->getTrace();

		if ( $this->code != 0 ) {
			header('Status: '.$this->code);
		}

		$ZNAP_ENV = ( Znap::$use_development_errors ) ? 'development' : ZNAP_ENV ;

		$paths = array(
			Znap::$errors_path.'/'.$ZNAP_ENV,
			Znap::$errors_default_path,
			ZNAP_LIB_ROOT.'/znap_error/'.$ZNAP_ENV,
			ZNAP_LIB_ROOT.'/znap_error/default',
		);

		foreach( $paths as $path ) {
			if ( is_file($path.'/'.$this->code.'.'.Znap::$views_extension) ) {
				$view_file = $path.'/'.$this->code.'.'.Znap::$views_extension;
				break;
			} elseif ( is_file($path.'/default.'.Znap::$views_extension) ) {
				$view_file = $path.'/default.'.Znap::$views_extension;
				break;
			}
		}

		$this->render_file($view_file);
	}


	function valid_action ($action) {
		if ( $action !== null && substr($action, 0, 1) != Znap::$protected_method_prefix ) {

			// get all methods
			$all_methods = get_class_methods($this->controller_object); 

			// get inherited methods
			$inherited_methods = array_merge(
				get_class_methods(__CLASS__),
				$this->controller_object->before_filters,
				$this->controller_object->after_filters
			);
			
			if ( class_exists('ApplicationController', false) ) {
				$inherited_methods = array_merge($inherited_methods, get_class_methods('ApplicationController'));
			}

			// validate action
			$valid_actions = array_diff($all_methods, $inherited_methods);
			if ( in_array($action, $valid_actions) ) {
				return true;
			}
		}
		return false;
		
	}



	function execute_filters ($filters) {
		if ( count($this->$filters) ) {
			foreach( $this->$filters as $filter ) {
				if ( method_exists($this, $filter) ) {
					if ( $this->$filter() === false ) {
						return $filter;
					}
				}
			}
		}
		return true;
	}

	function execute_before_filters () {
		return $this->execute_filters('before_filters');
	}

	function execute_after_filters () {
		return $this->execute_filters('after_filters');
	}


	function add_items_to_list ($filter, $list) {
		if ( is_string($filter) && !empty($filter) ) {
			if ( strpos($filter, ',') !== false ) {
				$filter = explode(',', $filter);
				foreach( $filter as $key => $value ) {
					if ( !in_array($value, $this->$list) ) {
						$this->{$list}[] = trim($value);
					}
				}
			} else {
				$this->{$list}[] = $filter;
			}
		} elseif ( is_array($filter) ) {
			if ( count($this->$list) ) {
				$this->$list = array_unique(array_merge($this->$list, $filter));
			} else {
				$this->$list = $filter;
			}
		}
	}

	function before_filter ($filter) {
		$this->add_items_to_list($filter, 'before_filters');
	}
	
	function add_before_filter ($filter) {
		$this->add_items_to_list($filter, 'before_filters');
	}

	function after_filter ($filter) {
		$this->add_items_to_list($filter, 'after_filters');
	}
	
	function add_after_filter ($filter) {
		$this->add_items_to_list($filter, 'after_filters');
	}


	function add_helper ($helper) {
		$this->add_items_to_list($helper, 'helpers');
	}



	function check_paths () {
		if ( is_array($this->url_path) ) {
			$controllers_path = $this->controllers_path;
			$extra_path = array();
			$new_path = array();
			foreach( $this->url_path as $key => $path ) {
				if ( is_dir($controllers_path.'/'.$path) ) {
					$extra_path[] = $path;
					$controllers_path .= '/'.$path;
				} else {
					$new_path[] = $path;
				}
			}
			if ( !empty($extra_path) ) {
				$extra_path = implode('/', $extra_path);
				$this->added_path = $extra_path;
				Znap::$added_path = $this->added_path;
				$this->controllers_path .= '/'.$extra_path;
				$this->helpers_path .= '/'.$extra_path;
				$this->layouts_path .= '/'.$extra_path;
				$this->views_path .= '/_'.$extra_path;
			}
			if ( !empty($new_path) ) {
				$this->url_path = $new_path;
			}
		}
	}



	function render ($options = array(), $locals = array(), $return_as_string = false) {
		if ( $this->render_performed && !$this->action_called ) {
			return true;
		}
		if ( $return_as_string ) {
			ob_start();
		}
		
		if ( is_string($options) ) {
			$this->render_file($options, $locals, true);
		} elseif ( is_array($options) ) {
			$options['locals'] = ( $options['locals'] ) ? $option['locals'] : array() ;
			$options['use_full_path'] = ( !$options['use_full_path'] ) ? true : $options['use_full_path'] ;
			if ( $options['text'] ) {
				$this->render_text($options['text']);
			} elseif ( $options['action'] ) {
				$this->render_action($optins['action'], $options);
			} elseif ( $options['file'] ) {
				$this->render_file($options['file'], array_merge($options['locals'], $locals), $options['use_full_path']);
			}
		}
		
		if ( $return_as_string ) {
			$result = ob_get_contents();
			ob_end_clean();
			return $result;
		}
		
	}
	
	
	function render_text ($text, $options = array()) {
		if ( isset($options['layout']) && $options['layout'] != '' ) {
			$locals['content_for_layout'] = &$text;
			$layout = $this->determine_layout();
			$this->render_file($layout, $locals);
		} else {
			echo $text;
		}
		exit;
	}
	
	
	function render_action ($action, $layout = null) {
		if ( $this->render_performed ) {
			return true;
		}
		
		if ( $layout != null ) {
			$this->layout = $layout;
		}
		
		if ( !empty($this->view_file) ) {
			$len = strlen('.'.Znap::$views_extension);
			if ( substr($this->view_file, -$len) != '.'.Znap::$views_extension ) {
				$this->view_file .= '.'.Znap::$views_extension;
			}
			if ( strstr($this->view_file, '/') && is_file(Znap::$views_path.'/'.$this->view_file) ) {
				$this->view_file = Znap::$views_path.'/'.$this->view_file;
			} elseif ( is_file($this->views_path.'/'.$this->view_file) ) {
				$this->view_file = $this->views_path.'/'.$this->view_file;
			}
		} else {
			$this->view_file = $this->views_path.'/'.$action.'.'.Znap::$views_extension;
		}
		
		$this->render_performed = true;
		
		return $this->render_file($this->view_file);
	}
	
	
	function render_file ($file, $collection = null, $locals = array(), $use_simple_name = false) {
		
		if ( $use_simple_name ) {
			$file = $this->views_path.'/'.$file.'.'.Znap::$views_extension;
		}
		
		if ( is_file($file) ) {
			if ( is_object($this) ) {
				foreach( $this as $tmp_key => $tmp_value ) {
					${$tmp_key} = &$this->$tmp_key;
				}
				unset($tmp_key, $tmp_value);
			}
			if ( $this->content_for_layout !== null ) {
				$content_for_layout = &$this->content_for_layout;
			}
			if ( count($locals) ) {
				foreach( $locals as $tmp_key => $tmp_value ) {
					${$tmp_key} = &$locals[$tmp_key];
				}
				unset($tmp_key, $tmp_value);
			}
			
			unset($use_full_path, $locals);
			
			$this->currently_rendering_file = $file;
			
			if ( empty($collection) ) {
				include($file);
			} elseif ( is_array($collection) ) {
				$var_name = basename($file, '.phtml');
				if ( $var_name{0} == '_' ) {
					$var_name = substr($var_name, 1);
				}
				${$var_name.'_collection'} = &$collection;
				$eval  = "foreach( \${$var_name}_collection as \${$var_name}_key => \${$var_name} ):\n?>";
				$eval .= file_get_contents($file);
				$eval .= "\n<?php endforeach; ?>";
				eval($eval);
			}
			
			return true;
		}
		return false;
	}
	
	
	function render_partial ($partial, $collection = null, $locals = array()) {
		
		// set file name
		if ( strstr($partial, '/') ) {
			$file = '_'.substr(strrchr($partial, '/'), 1).'.'.Znap::$views_extension;
			$path = substr($partial, 0, strrpos($partial, '/'));
			$file_name = $path.'/'.$file;
		} else {
			$path = '';
			$file_name = $file = '_'.$partial.'.'.Znap::$views_extension;
		}
		
		// determine file path
		if ( strstr($file_name, '/') && is_file(Znap::$views_path.'/'.$file_name) ) {
			$file_name = Znap::$views_path.'/'.$file_name;
		} elseif ( is_file(dirname($this->currently_rendering_file).'/'.$file_name) ) {
			$file_name = dirname($this->currently_rendering_file).'/'.$file_name;
		} elseif ( is_file($this->views_path.'/'.$file_name) ) {
			$file_name = $this->views_path.'/'.$file_name;
		} elseif ( is_file($this->layouts_path.'/'.$file_name) ) {
			$file_name = $this->layouts_path.'/'.$file_name;
		} elseif ( $this->layouts_path != $this->layouts_base_path && is_file($this->layouts_base_path.'/'.$file_name) ) {
			$file_name = $this->layouts_base_path.'/'.$file_name;
		} else {
			return false;
		}
		
		// continue if partial file exists
		if ( is_file($file_name) ) {
			return $this->render_file($file_name, $collection, $locals);
		}
	}



	function determine_layout ($full_path = true) {
		// if controller defines and sets $layout to NULL, don't use a layout
		if ( isset($this->layout) && is_null($this->layout) ) {
			return null;
		}
		
		// if _determine_layout() is defined in controller, call it to get layout name
		if ( method_exists($this, Znap::$protected_method_prefix.'determine_layout') ) {
			$determine_layout_method = Znap::$protected_method_prefix.'determine_layout';
			$layout = $this->$determine_layout_method();
		} else {
			$layout = ( isset($this->layout) && $this->layout != '' ) ? $this->layout : $this->controller ;
		}
		
		// defaults
		$layouts_base_path = Znap::$layouts_path;
		$default_layout_file = $layouts_base_path.'/application.'.Znap::$views_extension;
		
		if ( !$full_path && $layout ) {
			$layout_file =  $layout;
		} elseif ( strstr($layout, '/') && is_file($layouts_base_path.'/'.$layout.'.'.Znap::$views_extension) ) {
			$layout_file = $layouts_base_path.'/'.$layout.'.'.Znap::$views_extension;
		} elseif ( is_file($this->layouts_path.'/'.$layout.'.'.Znap::$views_extension) ) {
			$layout_file = $this->layouts_path.'/'.$layout.'.'.Znap::$views_extension;
		} elseif ( is_file($layouts_base_path.'/'.$layout.'.'.Znap::$views_extension) ) {
			$layout_file = $layouts_base_path.'/'.$layout.'.'.Znap::$views_extension;
		} else {
			$layout_file = $default_layout_file;
		}
		
		return $layout_file;
	}



	function redirect_to ( $options = null ) {
		if ( $options == 'back' ) {
			$url = $_SERVER['HTTP_REFERER'];
		} else {
			$url = url_for($options);
		}
		
		if ( headers_sent() ) {
			echo '<html><head><meta http-equiv="refresh" content="2;url='.$url.'"></head></html>';
		} else {
			header('Location: '.$url);
		}
		exit;
	}
	
	function raise ($message, $details, $code) {
		throw new ActionControllerError($message, $details, $code);
	}
	
	
}


?>