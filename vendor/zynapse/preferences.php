<?php
/*

   Zynapse Preferences - simple and powerful preference management


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


class PreferenceCollection {
	
	public static
		$__current_pref_file = null;
	
	public
		$__pref_paths = array();


	function __construct ($pref_paths = array()) {
		if ( !empty($pref_paths) ) {
			if ( is_array($pref_paths) ) {
				$this->__pref_paths = $pref_paths;
			} elseif ( is_string($pref_paths) ) {
				$this->__pref_paths = array($pref_paths);
			}
		}
	}
	
	
	function __get ($property) {
		if ( isset($this->$property) ) {
			return $this->$property;
		} elseif ($this->read($property)) {
			return $this->$property;
		} else {
			return null;
		}
	}


	function read ($pref, $force_create = false, $create_path = null) {
		if ( !isset($this->$pref) && count($this->__pref_paths) > 0 ) {
			$valid_path = false;
			for ( $i=0; $i < count($this->__pref_paths); $i++ ) {
				if ( is_file($this->__pref_paths[$i].'/'.$pref.'.prefs.php') ) {
					$valid_path = $this->__pref_paths[$i];
					break;
				}
			}
			if ( $valid_path !== false ) {
				include_once($valid_path.'/'.$pref.'.prefs.php');
				$class = $pref.'_preferences';
				if ( class_exists($class) ) {
					self::$__current_pref_file = $valid_path.'/'.$pref.'.prefs.php';
					$this->$pref = new $class();
					self::$__current_pref_file = null;
					if ( is_object($this->$pref) ) {
						return true;
					}
				}
			} elseif ( $force_create ) {
				$object = new PreferenceContainer();
				if ( $create_path == null ) {
					$create_path = $this->__pref_paths[0];
				}
				if ( $object->save($pref, $create_path) ) {
					$this->read($pref);
					return true;
				} else {
					return false;
				}
			}
		}
		return false;
	}
	
	
}

class PreferenceContainer {
	
	
	private $__file = null;
	
	
	function __construct ($__file = null) {
		if ( PreferenceCollection::$__current_pref_file !== null ) {
			$this->__file = PreferenceCollection::$__current_pref_file;
		}
	}
	
	
	function save ($pref = null, $save_path = null, $data = null) {
		
		// use $this for data if none is specified
		if ( $data == null ) {
			$data = &$this;
		}
		
		// get class and preference name
		if ( empty($pref) ) {
			$class = get_class($this);
			$pref = substr($class, 0, -12);
		} else {
			$class = $pref.'_preferences';
		}
		
		// start building output
		$output  = "<?php\n";
		$output .= "/*\n";
		$output .= "\n";
		$output .= "   Zynapse Preference Class\n";
		$output .= "\n";
		$output .= "   This class is used to store preferences, it is humanly editable\n";
		$output .= "   and requires no overhead processing to be loaded.\n";
		$output .= "\n";
		$output .= "*/\n";
		$output .= "\n";
		$output .= 'class '.$class." extends PreferenceContainer {\n";
		$output .= "\t\n";
		
		foreach( $data as $key => $value ) {
			if ( $key != '__file' ) {
				$output .= "\tpublic \$".$key.";\n";
			}
		}
		
		$output .= "\t\n";
		$output .= "\tfunction __construct () {\n";
		$output .= "\t\tparent::__construct();\n";
		$output .= "\t\t\n";
		
		foreach( $data as $key => $value ) {
			if ( $key != '__file' ) {
				$output .= $this->output_var('this->'.$key, $value, 2);
			}
		}
		
		$output .= "\t}\n";
		$output .= "\t\n";
		$output .= "}\n";
		$output .= "\n";
		$output .= "?>";
		
		// determine output filename
		if ( $save_path == null ) {
			if ( !empty($this->__file) ) {
				$file = $this->__file;
			}
		} elseif ( is_dir($save_path) ) {
			$file = $save_path.'/'.$pref.'.prefs.php';
		}
		
		// output data to file
		if ( !empty($file) ) {
			if ( !is_file($file) ) {
				$creating = true;
			}
			if ( file_put_contents($file, $output) ) {
				if ( isset($creating) ) {
					chmod($file, 0666);
				}
				return true;
			}
		}
		
		return false;
	}
	
	function output_var ($var, $value = '', $indent = 0) {
		$output  = $this->output_indent($indent);
		$output .= '$'.$var.' = '.$this->output_value($value, $indent).";\n";
		return $output;
	}
	
	function output_value ($value = '', $indent = 0) {
		$output = '';
		if ( is_null($value) ) {
			$output .= 'null';
		} elseif ( is_bool($value) ) {
			$output .= ($value) ? 'true' : 'false' ;
		} elseif ( is_int($value) || is_float($value) ) {	
			$output .= $value;
		} elseif ( is_string($value) ) {
			$output .= $this->output_string($value);
		} elseif ( is_array($value) ) {
			$output .= $this->output_array($value, ($indent + 1));
		} elseif ( is_object($value) ) {
			$output .= 'unserialize('.$this->output_string(serialize($value)).')';
		}
		return $output;
	}
	
	function output_array ($array, $indent = 1) {
		$output = "array(\n";
		foreach( $array as $key => $value ) {
			$output .= $this->output_array_item($key, $value, $indent);
		}
		for ( $i=0; $i < ($indent - 1); $i++ ) {
			$output .= "\t";
		}
		return $output.')';
	}
	
	function output_array_item ($key, $value = '', $indent = 0, $register_objects = false) {
		$output  = $this->output_indent($indent);
		$output .= "'".$key."' => ";
		$output .= $this->output_value($value, $indent).",\n";
		return $output;
	}
	
	function output_string ($string) {
		$string = str_replace("\"", "\\\"", $string);
		$string = str_replace("\'", "\\\'", $string);
		$string = str_replace("\0", "\\0", $string);
		$string = str_replace("\n", "\\n", $string);
		$string = str_replace("\r", "\\r", $string);
		$string = str_replace("\t", "\\t", $string);
		return '"'.$string.'"';
	}
	
	function output_indent ($indent) {
		$output = '';
		for ( $i=0; $i < $indent; $i++ ) {
			$output .= "\t";
		}
		return $output;
	}
	
	
}

?>