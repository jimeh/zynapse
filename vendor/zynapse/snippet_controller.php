<?php
/*

   Zynapse SnippetController
    - call and/or render snippets of code, based on ActionController


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


class SnippetController {
	
	private $snippet;
	private $action;
	private $args = array();
	private $snippets_path;
	private $helpers_path;
	private $helper_file;
	private $layouts_path;
	private $layout_file;
	private $views_base_path;
	private $views_path;
	private $view_file;
	private $snippets_class_file;
	private $snippets_helper_file;
	private $helpers = array();
	private $before_filters = array();
	private $after_filters = array();
	private $paths_loaded = false;
	private $currently_rendering_file;
	private $content_for_layout = null;
	private $default_action = 'index';
	
	public $action_called = false;
	public $snippet_file;
	public $class_name;
	public $snippet_object;
	public $render_layout = false;
	public $render_view = true;
	public $render_action = null;
	public $render_performed = false;
	public $result = null;
	
	
	function __construct () {
		// doesn't need to do anything at the moment
	}
	
	
	function initialize ($snippet, $action, $args) {
		if ( $snippet != null ) {
			$this->snippet = $snippet;
			
			if ( $action != null ) {
				$this->action = $action;
			}
			
			if ( is_array($args) && count($args) > 0 ) {
				$this->args = $args;
			}
			
			if ( !$this->paths_loaded ) {
				$this->init_paths();
			}
			
			$this->views_path = Znap::$snippet_views_path.'/'.$this->snippet;
			
			$this->snippet_file = $this->snippets_path.'/'.$this->snippet.'_snippet.php';
			$this->helper_file  = $this->helpers_path.'/'.$this->snippet.'_snippet_helper.php';
			$this->class_name   = Inflector::camelize($this->snippet).'Snippet';
			
			return true;
		}
		return false;
	}
	
	function init_paths () {
		$this->snippets_path        = Znap::$snippets_path;
		$this->helpers_path         = Znap::$snippet_helpers_path;
		$this->layouts_path         = Znap::$snippet_layouts_path;
		$this->views_base_path      = Znap::$snippet_views_path;
		$this->snippets_class_file  = $this->snippets_path.'/snippets.php';
		$this->snippets_helper_file = $this->helpers_path.'/snippets_helper.php';
	}
	
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
	
	
	function call_snippet ($snippet = null, $action = null, $args = array()) {
		$this->render_layout = false;
		$this->render_view = false;
		return $this->render_snippet($snippet, $action, $args);
	}
	
	function render_snippet ($snippet = null, $action = null, $args = array()) {
		if ( !$this->initialize($snippet, $action, $args) ) {
			return null;
		}
		
		if ( is_file($this->snippets_class_file) ) {
			include_once($this->snippets_class_file);
		}
		
		include_once($this->snippet_file);
		if ( class_exists($this->class_name, false) ) {
			if ( !isset(Znap::$current_snippet_objects[$this->snippet]) ) {
				$class = $this->class_name;
				$this->snippet_object = new $class();
				if ( is_object($this->snippet_object) ) {
					$this->snippet_object->init_filters();
					$this->snippet_object->snippet         = $this->snippet;
					$this->snippet_object->action          = $this->action;
					$this->snippet_object->args            = $this->args;
					$this->snippet_object->snippets_path   = &$this->snippets_path;
					$this->snippet_object->helpers_path    = &$this->helpers_path;
					$this->snippet_object->layouts_path    = &$this->layouts_path;
					$this->snippet_object->views_base_path = &$this->views_base_path;
					$this->snippet_object->views_path      = $this->views_path;
					$this->snippet_object->class_name      = $this->class_name;
					$this->snippet_object->render_layout   = $this->render_layout;
					$this->snippet_object->render_view     = $this->render_view;
					
					Znap::$current_snippet_objects[$this->snippet] = &$this->snippet_object;
				}
			} elseif ( is_object(Znap::$current_snippet_objects[$this->snippet]) ) {
				$this->snippet_object = &Znap::$current_snippet_objects[$this->snippet];
				$this->snippet_object->action     = $this->action;
				$this->snippet_object->args       = $this->args;
			}
			
			if ( is_object($this->snippet_object) ) {
				
				// include main snippets helpers
				if ( is_file($this->snippets_helper_file) ) {
					include_once($this->snippets_helper_file);
				}
				
				if ( ($before_filters_result = $this->snippet_object->execute_before_filters()) === true ) {
					
					// include snippet specific preferences
					if ( isset($this->snippet_object->has_prefs) && Znap::$prefs->read('snippet_'.$this->snippet, true) ) {
						$snippet = 'snippet_'.$this->snippet;
						$this->snippet_object->prefs = &Znap::$prefs->$snippet;
					}
					
					// supress output for capture
					ob_start();
					
					// include snippet specific helper file
					if ( is_file($this->helper_file) ) {
						include_once($this->helper_file);
					}
					
					// call default action/method if none is defined
					if ( $this->action === null ) {
						$this->action = $this->default_action;
					}
					
					// execute main method
					if ( method_exists($this->snippet_object, $this->action) && !in_array($this->action, get_class_methods('SnippetController')) ) {
						$action = $this->action;
						$this->result = call_user_func_array(array($this->snippet_object, $action), $this->args);
					} elseif ( is_file($this->views_path.'/'.$this->action.'.'.Znap::$views_extension) ) {
						$action = $this->action;
					} else {
						$this->raise('Snippet action "'.$this->action.'" not found in the '.$this->snippet.' snippet class.');
						ob_end_clean();
						return false;
					}
					
					$this->snippet_object->execute_after_filters();
					$this->snippet_object->action_called = true;
					
					// include any additionaly defined helpers
					if ( count($this->snippet_object->helpers) ) {
						foreach( $this->snippet_object->helpers as $helper ) {
							if ( is_file($this->helpers_path.'/'.$helper.'_helper.php') ) {
								include_once($this->helpers_path.'/'.$helper.'_helper.php');
							}
						}
					}
					
					// if render_text is defined as a string, render it instead of layout & view files
					if ( isset($this->snippet_object->render_text) && $this->snippet_object->render_text != '' ) {
						$this->render_text($this->snippet_object->render_text);
					}
					
					// if render_action is defined, used it as the render action
					if ( isset($this->snippet_object->render_action) && $this->snippet_object->render_action != '' ) {
						$action = $this->snippet_object->render_action;
					}
					
					// render view file
					if ( !$this->snippet_object->render_action($action) ) {
						$this->raise('No "'.$action.'" view file found for the "'.$this->snippet.'" snippet.');
						ob_end_clean();
						return false;
					}
					
					// grab captured output
					$this->snippet_object->content_for_layout = ob_get_contents();
					ob_end_clean();
					
					// render or not to render layout (that is the question)
					if ( $this->snippet_object->render_layout !== false && ($layout_file = $this->snippet_object->determine_layout()) ) {
						if ( !$this->snippet_object->render_file($layout_file) ) {
							echo $this->snippet_object->content_for_layout;
						}
					} else {
						echo $this->snippet_object->content_for_layout;
					}
					return $this->result;
				} else {
					$this->raise('The "'.$before_filters_result.'" snippet before filter failed.');
					return false;
				}
			} else {
				$this->raise('Failed to initiate snippet object "'.$this->snippet.'".');
				return false;
			}
		} else {
			$this->raise('Snippet "'.$this->snippet.'" not found.');
			return false;
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
			$layout = ( isset($this->layout) && $this->layout != '' ) ? $this->layout : $this->snippet ;
		}
		
		$default_layout_file = $this->layouts_path.'/snippets.'.Znap::$views_extension;
		
		if ( !$full_path && $layout ) {
			return $layout;
		} elseif ( is_file($this->layouts_path.'/'.$layout.'.'.Znap::$views_extension) ) {
			$layout_file = $this->layouts_path.'/'.$layout.'.'.Znap::$views_extension;
		} else {
			$layout_file = $this->layouts_path.'/snippets.'.Znap::$views_extension;
		}
		
		return $layout_file;
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

	function add_before_filter ($filter) {
		$this->add_items_to_list($filter, 'before_filters');
	}

	function add_after_filter ($filter) {
		$this->add_items_to_list($filter, 'after_filters');
	}


	function add_helper ($helper) {
		$this->add_items_to_list($helper, 'helpers');
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
		if ( $this->render_performed || $this->render_view === false ) {
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
			if ( strstr($this->view_file, '/') && is_file($this->views_base_path.'/'.$this->view_file) ) {
				$this->view_file = $this->views_base_path.'/'.$this->view_file;
			} elseif ( is_file($this->views_path.'/'.$this->view_file) ) {
				$this->view_file = $this->views_path.'/'.$this->view_file;
			}
		} else {
			$this->view_file = $this->views_path.'/'.$action.'.'.Znap::$views_extension;
		}	
		
		$this->render_performed = true;
		
		return $this->render_file($this->view_file);
	}


	function render_file ($file, $locals = array(), $use_full_path = false) {
		
		if ( $use_full_path ) {
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
			
			Znap::$currently_rendering_snippet = $this->snippet;
			$this->currently_rendering_file = $file;
			include($file);
			Znap::$currently_rendering_snippet = null;
			return true;
		}
		return false;
	}
	
	
	function render_partial ($partial, $options = array()) {
		
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
		if ( strstr($file_name, '/') && is_file($this->views_base_path.'/'.$file_name) ) {
			$file_name = $this->views_base_path.'/'.$file_name;
		} elseif ( is_file(dirname($this->currently_rendering_file).'/'.$file_name) ) {
			$file_name = dirname($this->currently_rendering_file).'/'.$file_name;
		} elseif ( is_file($this->views_path.'/'.$file_name) ) {
			$file_name = $this->views_path.'/'.$file_name;
		} elseif ( is_file($this->layouts_path.'/'.$file_name) ) {
			$file_name = $this->layouts_path.'/'.$file_name;
		} else {
			return false;
		}
		
		// continue if partial file exists
		if ( is_file($file_name) ) {
			$locals = ( array_key_exists('locals', $options) ) ? $options['locals'] : array() ;
			
			// use collections to render a partial multiple times with new variables available to it each time
			if ( array_key_exists('collection', $options) && is_array($options['collection']) ) {
				
				// spacer template to be rendered between each collection item's partial rendering
				if ( array_key_exists('spacer', $options) || array_key_exists('spacer_template', $options) ) {
					$spacer_path = (array_key_exists('spacer', $options)) ? $options['spacer'] : $options['spacer_template'];
					if ( strstr($spacer_path, '/') ) {
						$spacer_file = substr(strrchr($spacer_path, '/'), 1);
						$spacer_path = substr($spacer_path, 0, strripos($path, '/'));
						$spacer_file_path = Znap::$views_path.'/'.$spacer_path.'/_'.$spacer_file.'.'.Znap::$views_extension;
					} else {
						$spacer_file = $spacer_path;
						$spacer_file_path = $this->views_path.'/'.$spacher_file.'.'.Znap::$views_extension;
					}
					if ( is_file($spacer_file_path) ) {
						$add_spacer = true;
					}
				}
				
				// start the rendering
				${$partial.'_counter'} = 0;
				foreach( $options['collection'] as $tmp_value ) {
					${$file.'_counter'}++;
					$locals[$partial] = $tmp_value;
					$locals[$partial.'_counter'] = ${$partial.'_counter'};
					unset($tmp_value);
					$this->render_performed = false;
					$this->render_file($file_name, $locals);
					if ( isset($add_spacer) && ${$partial.'_counter'} < count($options['collection']) ) {
						$this->render_performed = false;
						$this->render_file($spacer_file_path, $locals);
					}
				}
				$this->render_performed = true;
			} else {
				return $this->render_file($file_name, $locals);
			}
		}
	}
	
	
	function raise ($message = 'UNKNOWN SNIPPET ERROR') {
		error_log($message, 0);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}

?>