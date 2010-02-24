<?php
/*

   Znap - zynapse main class


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


class Znap {
	
	public static
		$action_controller = null,
		$snippets_controller = null,
		$prefs = null,
		$controllers_path = null,
		$helpers_path = null,
		$models_path = null,
		$views_path = null,
		$layouts_path = null,
		$snippets_path = null,
		$snippet_helpers_path = null,
		$snippet_views_path = null,
		$snippet_layouts_path = null,
		$prefs_path = null,
		$errors_path = null,
		$errors_default_path = null,
		$config_path = null,
		$environments_path = null,
		$strings_path = null,
		$lib_path = null,
		$app_path = null,
		$log_path = null,
		$vendor_path = null,
		$shell_scripts_path = null,
		$public_path = null,
		$tmp_path = null,
		$cache_path = null,
		$added_path = null,
		$url_prefix = null,
		$protected_method_prefix = '_',
		$views_extension = 'phtml',
		$path_seperator = ':',
		$current_controller_path = null,
		$current_controller_name = null,
		$current_controller_class_name = null,
		$current_action_name = null,
		$current_controller_object = null,
		$current_route = null,
		$current_snippet_objects = array(),
		$keep_flash,
		$timer_enabled = false,
		$allow_dangerous_url_paths = false,
		$use_development_errors = false,
		$currently_rendering_snippet = null,
		
		// mkdir command
		$mkdir_cmd = 'mkdir',
		
		// chmod command
		$chmod_cmd = 'chmod',
		
		// fill with language specific strings - use App::$strings
		$strings = array();
		
		

	function initialize () {
		
		
		// OS is Windows?
		if ( substr(PHP_OS, 0, 3) == 'WIN' ) {
			self::$path_seperator = ";";
		}
		
		
		// set paths
		self::$app_path             = ZNAP_ROOT.'/app';
		self::$controllers_path     = self::$app_path.'/controllers';
		self::$helpers_path         = self::$app_path.'/helpers';
		self::$models_path          = self::$app_path.'/models';
		self::$prefs_path           = self::$app_path.'/preferences';
      self::$snippets_path        = self::$app_path.'/snippets';
		self::$snippet_helpers_path = self::$helpers_path.'/snippet_helpers';

		// display mode path
		if ( ZNAP_MODE != 'web' && is_dir(self::$app_path.'/views-'.ZNAP_MODE) ) {
			self::$views_path = self::$app_path.'/views-'.ZNAP_MODE;
		} else {
			self::$views_path = self::$app_path.'/views';
		}
		
		// set more paths
		self::$layouts_path         = self::$views_path.'/__layouts';
		self::$errors_path          = self::$views_path.'/__errors';
		self::$errors_default_path  = self::$views_path.'/__errors/default';
		self::$snippet_views_path   = self::$views_path.'/__snippets';
		self::$snippet_layouts_path = self::$views_path.'/__snippets/__layouts';

		self::$config_path          = ZNAP_ROOT.'/config';
		self::$environments_path    = self::$config_path.'/environments';
		self::$strings_path         = self::$config_path.'/strings';
		
		self::$tmp_path             = ZNAP_ROOT.'/tmp';
		self::$cache_path           = self::$tmp_path.'/cache';

		self::$lib_path             = ZNAP_ROOT.'/libs';
		self::$log_path             = ZNAP_ROOT.'/logs';
		self::$public_path          = ZNAP_ROOT.'/public';
		self::$vendor_path          = ZNAP_ROOT.'/vendor';
		self::$shell_scripts_path   = ZNAP_LIB_ROOT.'/shell_scripts';


		// logging setup
		if ( ZNAP_ENABLE_LOGGING && !defined('ZNAP_SHELL_SCRIPT') ) {
			ini_set('log_errors', 'On');
		} else {
			ini_set('log_errors', 'Off');
		}
		if ( ZNAP_INTERNAL_LOGGING && !defined('ZNAP_SHELL_SCRIPT') ) {
	      ini_set('error_log', self::$log_path.'/'.ZNAP_ENV.'.log');
		}
		
		if ( ZNAP_ENV == 'development' ) {
			// display errors in browser in development mode for easy debugging
			ini_set('display_errors', 'On');
			// ini_set('error_reporting', 'E_ALL'); //FIXME using ini_set() to change "error_reporting" stops error messages from displaying
		} else {
			// hide errors from browser if not in development mode
			ini_set('display_errors', 'Off');
		}


		// setup include paths
		ini_set('include_path',
			'.'.self::$path_seperator.
			ZNAP_LIB_ROOT.self::$path_seperator.
			ZNAP_LIB_ROOT.'/shell_scripts'.self::$path_seperator.
			self::$lib_path.self::$path_seperator.
			ini_get('include_path')
		);
		
		
		// load the zynapse libs
		require_once('session.php');
		require_once('preferences.php');
		// require_once('input_filter.php'); //TODO require InputFilter class when (if ever) it is created
		require_once('active_record.php');
		require_once('action_controller.php');
		require_once('snippet_controller.php');
		require_once('action_view.php');
		require_once('dispatcher.php');
		require_once('inflector.php');
		require_once('router.php');
		require_once('timer.php');
		require_once('znap_error.php');
		
		if ( defined('ZNAP_SHELL_SCRIPT') ) {
			require_once('shell_script.php');
		}
		
		
		// load and set database configuration
		if ( file_exists(self::$config_path.'/database.php') ) {
			include_once(self::$config_path.'/database.php');
			if ( !empty($database_settings) ) {
				ActiveRecord::$settings = $database_settings;
			}
		}
		
		
		// set url prefix path if needed
		if ( defined('URL_PREFIX') ) {
			Znap::$url_prefix = URL_PREFIX;
		}
		
		
		// load default strings
		require_once('strings.php');
		self::$strings = $strings;
		unset($strings);
		
		
		// load app's global strings
		if ( is_file(self::$strings_path.'/_global.php') ) {
			require_once(self::$strings_path.'/_global.php');
			if ( !empty($strings) ) {
				self::$strings = $strings;
				unset($strings);
			}
		}
		
		// initialize preference system
		self::$prefs = new PreferenceCollection( array(self::$prefs_path, self::$tmp_path) );
		self::$prefs->read('_internals');
		self::$prefs->read('application');
		self::$prefs->read('cache', true, self::$tmp_path);
		
		// initialize the App class - read the comment on the class for more info
		App::initialize();
	}
	
	
	// load language specific strings - called by ActionController when needed
	function load_strings () {
		
		// check application prefrences for default language
		$language = ( isset(App::$prefs->language) && App::$prefs->language != '' ) ? App::$prefs->language : 'english' ;
		
		// check current session is set to use non-default language
		$language = ( isset($_SESSION['language']) && $_SESSION['language'] != '' ) ? $_SESSION['language'] : $language ;
		
		if ( is_file(self::$strings_path.'/'.strtolower($language).'.php') ) {
			include(self::$strings_path.'/'.strtolower($language).'.php');
			if ( !empty($strings) && is_array($strings) ) {
				self::$strings = array_merge(self::$strings, $strings);
			}
		}
	}
	

	function start_timer () {
		Timer::start();
		self::$timer_enabled = true;
	}

}



/*

   The App class is designed to store application specific
   information. "App::$prefs" for exaple stores the application
   preferences. These can also be accessed with
   "Znap::$prefs->application".

*/

class App {
	
	public static
		$_internals,
		$strings,
		$preferences,
		$cache,
		$str,
		$prefs;
		
	function initialize () {
		self::$_internals = &Znap::$prefs->_internals;
		self::$strings = &Znap::$strings;
		self::$preferences = &Znap::$prefs->application;
		self::$cache = &Znap::$prefs->cache;
		self::$str = &Znap::$strings;
		self::$prefs = &Znap::$prefs->application;
	}
}


// autoload magic
function __autoload ($class_name) {
	
	// check cache if specified class has been found before
	if ( !empty(App::$cache->autoload[$class_name]) ) {
		if ( is_file(App::$cache->autoload[$class_name]) ) {
			include_once(App::$cache->autoload[$class_name]);
			$found = true;
		} else {
			unset(App::$cache->autoload[$class_name]);
			$save_cache = true;
		}
	}
	
	if ( empty($found) ) {
		
		$name = Inflector::underscore($class_name);
	
		$file     = $name.'.php';
		$file_lib = $name.'.lib.php';
		$file_cla = $name.'.class.php';
	
		$org_file = $class_name.'.php';
		$org_lib  = $class_name.'.lib.php';
		$org_cla  = $class_name.'.class.php';
	
		$low      = strtolower($class_name);
		$low_file = $low.'.php';
		$low_lib  = $low.'.lib.php';
		$low_cla  = $low.'.class.php';

	
		$internal_paths = array(
			Znap::$models_path,
			Znap::$controllers_path,
			Znap::$current_controller_path,
		);
	
		// autoload model and controller classes
		foreach( $internal_paths as $path ) {
			if ( is_file($path.'/'.$file) ) {
				include_once($path.'/'.$file);
				App::$cache->autoload[$class_name] = $path.'/'.$file;
				$save_cache = true;
				$found = true;
				break;
			}
		}
	
		// autload classes from libs folder
		if ( empty($found) ) {
		
			$paths = array(
				Znap::$lib_path.'/'.$low,
				Znap::$lib_path.'/'.$class_name,
				Znap::$lib_path.'/'.$name,
				Znap::$lib_path,
			);
		
			$files = array(
				$low_lib, $low_cla, $low_file,
				$org_lib, $org_cla, $org_file,
				$file_lib, $file_cla, $file,
			);
		
			foreach( $paths as $path ) {
				if ( is_dir($path) ) {
					foreach( $files as $file ) {
						if ( is_file($path.'/'.$file) ) {
							include_once($path.'/'.$file);
							App::$cache->autoload[$class_name] = $path.'/'.$file;
							$save_cache = true;
							break 2;
						}
					}
				}
			}
		
		}
		
	}
	if ( !empty($save_cache) ) {
		App::$cache->save();
	}

}



?>