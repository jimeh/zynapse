<?php
/*

   Zynapse Generator - generate controllers, models, and more


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


class ZnapGenerator extends ShellScript {
	
	private
		$generator_file = null,
		$generator_class = null,
		$generator_object = null;
		
	public
		$args = array(),
		$type = null,
		$name = null,
		$templates_path = null;
	
	

	function run () {
		if ( $this->get_args() ) {
			$this->templates_path = Znap::$shell_scripts_path.'/znap_generator/templates';
			$this->generator_file = Znap::$shell_scripts_path.'/znap_generator/'.$this->type.'_generator.php';
			$this->generator_class = Inflector::camelize($this->type).'Generator';

			if ( is_file($this->generator_file) ) {
				require_once($this->generator_file);
				if ( class_exists($this->generator_class) ) {
					$class = $this->generator_class;
					$this->generator_object = new $class();
					if ( is_object($this->generator_object) ) {
						$this->generator_object->type = $this->type;
						$this->generator_object->templates_path = $this->templates_path;
						if ( !is_null($this->name) ) {
							$this->generator_object->name = $this->name;
							$this->generator_object->args = $this->args;
							return $this->generator_object->generate();
						} else {
							$this->generator_object->help();
						}	
					} else {
						echo "\nERROR: Failed to initiate generator object.\n\n";
					}
				} else {
					echo "\nERROR: Invalid generate type.\n\n";
				}
			} else {
				echo "\nERROR: Invalid generate type.\n\n";
			}
		} else {
			$this->generator_help();
		}
	}
	
	
	function get_args () {
		if ( !empty($this->argv[0]) ) {
			$this->args = $this->argv;
			$this->type = array_shift($this->args);
			if ( isset($this->args[0]) && $this->args[0] != '' ) {
				$this->name = $this->args[0];
			}
			return true;
		}
		return false;
	}
	
	
	function generator_help () {
		$generators = glob(Znap::$shell_scripts_path.'/znap_generator/*_generator.php');
		
		echo "\n";
		echo "Usage:\n";
		echo "---------------------------------\n";

		if ( ($count = count($generators)) > 0 ) {
			for ( $i=0; $i < $count; $i++ ) {
				include_once($generators[$i]);
				$filename = basename($generators[$i]);
				$name = substr($filename, 0, strrpos($filename, '_'));
				call_user_func(array(Inflector::camelize($name).'Generator', 'help_summary'));
				if ( $i < $count - 1 ) {
					echo "\n";
				}
			}
		}
		
		echo "---------------------------------\n";
		echo "\n";
	}
	
	
	function echo_create ($item) {
		echo "\tcreate  ".str_replace(ZNAP_ROOT.'/', '', $item)."\n";
	}
	
	function echo_exists ($item) {
		echo "\texists  ".str_replace(ZNAP_ROOT.'/', '', $item)."\n";
	}
	
	function echo_create_error ($item, $type = null) {
		echo 'ERROR: Could not create';
		if ( $type !== null && is_string($type) ) {
			echo ' '.$type.' file';
		}
		echo ': '.str_replace(ZNAP_ROOT.'/', '', $item)."\n";
	}
	
	function echo_template_error ($item, $type = null) {		
		echo 'ERROR: ';
		if ( $type !== null && is_string($type) ) {
			echo ucfirst($type).' template';
		} else {
			echo 'Template';
		}
		echo ' file does not exist: '.str_replace(ZNAP_ROOT.'/', '', $item)."\n";
	}
	
	function validate_path($path) {
		if ( is_dir($path) ) {
			$this->echo_exists($path.'/');
		} elseif ( $this->dir_exists($path) ) {
			$this->echo_create($path.'/');
		} else {
			$this->echo_create_error($path.'/');
			return false;
		}
		return true;
	}
	
	function dir_exists ($path) {
		if ( !is_dir($path) ) {
			if ( strstr($path, '/') ) {
				$path_array = explode('/', $path);
			} else {
				$path_array = array($path);
			}
			$current_dir = $path_array[0];
			unset($path_array[0]);
			foreach( $path_array as $dir ) {
				$current_dir .= '/'.$dir;
				if ( $current_dir != '' && !is_dir($current_dir) ) {
					exec(Znap::$mkdir_cmd.' "'.$current_dir.'"');
				}
			}
			if ( !is_dir($path) ) {
				return false;
			}
		}
		return true;
	}
	
	
}


















?>