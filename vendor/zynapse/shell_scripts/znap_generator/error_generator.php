<?php
/*

   Error Generator - generate error templates


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


class ErrorGenerator extends ZnapGenerator {
	
	public
		$error_code = null,
		$environment = null,
		$errors_path = null;
		
	
	
	function generate () {	
		
		if ( preg_match('/^(?:[0-9]{1,3}|default)$/', $this->name) ) {
			$this->error_code = &$this->name;
		} else {
			$this->echo_error_code();
			exit;
		}
		
		if ( isset($this->args[1]) && $this->args[1] != '' ) {
			if ( preg_match('/^(?:development|test|production)$/i', $this->args[1]) ) {
				$this->environment = $this->args[1];
				$this->errors_path = str_replace(Znap::$views_path.'/', '', Znap::$errors_path).'/'.$this->environment;
			} else {
				$this->echo_error_env();
				exit;
			}
		} else {
			$this->errors_path = str_replace(Znap::$views_path.'/', '', Znap::$errors_default_path);
		}
		
		$template_file = $this->determine_template();
		
		$view_paths = glob(Znap::$app_path.'/views*');
		
		foreach( $view_paths as $path ) {
			if ( $this->validate_path($path.'/'.$this->errors_path) ) {
				$error_file = $path.'/'.$this->errors_path.'/'.$this->error_code.'.'.Znap::$views_extension;
				$template_data = file_get_contents($template_file);
				if ( file_put_contents($error_file, $template_data) ) {
					$this->echo_create($error_file);
				} else {
					$this->echo_create_error($error_file, 'error');
					exit;
				}
			} else {
				$this->echo_create_error($path.'/'.$this->errors_path);
			}
		}
		return true;
	}
	
	
	function determine_template () {
		if ( $this->environment != null && is_file($this->templates_path.'/error_'.$this->environment.'_'.$this->error_code.'.'.Znap::$views_extension) ) {
			return $this->templates_path.'/error_'.$this->environment.'_'.$this->error_code.'.'.Znap::$views_extension;
		} elseif ( $this->environment != null && is_file($this->templates_path.'/error_'.$this->environment.'_default.'.Znap::$views_extension) ) {
			return $this->templates_path.'/error_'.$this->environment.'_default.'.Znap::$views_extension;
		} elseif ( is_file($this->templates_path.'/error_'.$this->error_code.'.'.Znap::$views_extension) ) {
			return $this->templates_path.'/error_'.$this->error_code.'.'.Znap::$views_extension;
		} elseif ( is_file($this->templates_path.'/error_default.'.Znap::$views_extension) ) {
			return $this->templates_path.'/error_default.'.Znap::$views_extension;
		} else {
			echo "ERROR: No suitable generate template file found.\n";
			exit;
		}
	}
	
	
	function echo_error_code () {
		echo "\n";
		echo "ERROR: Invalid error code given.\n";
		echo "Valid error codes are numbers 0 through 999 and \"default\".\n";
		echo "\n";
	}
	
	function echo_error_env () {
		echo "\n";
		echo "ERROR: Invalid environment specified.\n";
		echo "Valid environment values are:\n";
		echo "\t- development\n";
		echo "\t- test\n";
		echo "\t- production\n";
		echo "\n";
	}
	
	
	function help_summary () {
		echo "Generate Error Template:\n";
		echo "  ./script/generate error error_code [environment]\n";
		echo "  for more layout info: ./script/generate error\n";
	}
	
	function help () {
		echo "\n";
		echo "Usage: ./script/generate error error_code [environment]\n";
		echo "\n";
		echo "Description:\n";
		echo "\tGenerate error templates used for \"404 Page Not Found\" errors and\n";
		echo "\tmore.\n";
		echo "\n";
		echo "\tSpecifying an error code is required. Valid error code values are\n";
		echo "\tany of the standard HTTP errors, like 404 for example. Also\n";
		echo "\t\"default\" can be used as the error code to generate the default\n";
		echo "\terror template used when there is no template for the reported\n";
		echo "\terror code.\n";
		echo "\n";
		echo "\tEnvironment can also be specified, but is not required. If an\n";
		echo "\tenvironment is not specified, default will be used and the\n";
		echo "\tresulting error template will apply for all environments if there\n";
		echo "\tis no error template file for the current error code and\n";
		echo "\tenvironment.\n";
		echo "\n";
		echo "\tValid environment values are \"development\", \"test\", and\n";
		echo "\t\"production\".\n";
		echo "\n";
		echo "Examples:\n";
		echo "\t./script/generate error 404\n";
		echo "\tThis will create a 404 error layout for any environment:\n";
		echo "\t\tError Template:      app/views/__errors/default/404.phtml\n";
		echo "\n";
		echo "\t./script/generate error 404 production\n";
		echo "\tThis will create a 404 error layout for the production environment:\n";
		echo "\t\tError Template:      app/views/__errors/production/404.phtml\n";
		echo "\n";
	}
	
}



?>