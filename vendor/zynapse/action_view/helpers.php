<?php
/*

   Helpers - view helpers


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


class Helpers {
	
	public $object_name = null;
	public $attribute_name = null;
	public $value = null;
	
	
	function __construct ($object_name = null, $attribute_name = null) {
		
	}
	
	protected function initiate ($name = null, $value = null, $auto_fill = true) {
		if ( $name !== null ) {
			
			if ( preg_match('/^(.+?)\[(.+)\]$/i', $name, $captured) ) {
				$this->object_name = $captured[1];
				$this->attribute_name = $captured[2];
			} else {
				$this->object_name = $name;
			}
			
			$property = &$this->object_name;
			$attribute = &$this->attribute_name;
		
			if ( $auto_fill !== false && array_key_exists($property, $_REQUEST) ) {
				if ( $attribute !== null && array_key_exists($attribute, $_REQUEST[$property]) ) {
					$this->value = &$_REQUEST[$property][$attribute];
				} else {
					$this->value = &$_REQUEST[$property];
				}
			} elseif ( $auto_fill !== false && property_exists(Znap::$current_controller_object, $property) ) {
				$object = &Znap::$current_controller_object;
				if ( $attribute !== null ) {
					if ( is_object($object->$property) && property_exists($object->$property, $attribute) ) {
						$this->value = &$object->$property->$attribute;
					} elseif ( is_array($object->$property) && array_key_exists($attribute, $object->$property) ) {
						$this->value = &$object->$property[$attribute];
					}
				} else {
					$this->value = &$object->$property;
				}
			} elseif ( $value !== null ) {
				$this->value = $value;
			}
		} elseif ( $value !== null ) {
			$this->value = $value;
		}
	}
	
	function tag ($name, $properties = null, $open = true) {
		$html = '<'.$name;
		if ( ($options = $this->tag_properties($properties)) != '' ) {
			$html .= ' '.$options;
		}
		$html .= ($open) ? '>' : ' />' ;
		return $html;
	}
	
	function content_tag ($name, $content = '', $properties = null) {
		if ( !empty($properties['strip_slashes']) ) {
			$content = stripslashes($content);
			unset($properties['strip_slashes']);
		}
		return $this->tag($name, $properties).$content.'</'.$name.'>';
	}
	
	function tag_properties ($properties = null) {
		if ( is_array($properties) && count($properties) ) {
			$html = array();
			foreach( $properties as $key => $value ) {
				$html[] = $key.'="'.@htmlspecialchars($value, ENT_COMPAT).'"';
			}
			// sort($html); //TODO decide if tag properties should be sorted or not
			return implode(' ', $html);
		} else {
			return '';
		}
   }
	
	function cdata_section ($content) {
		return '<![CDATA['.$content.']]>';
	}
	

	
}



/*

   root scope functions

*/

function tag () {
	$helper = new Helpers();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'tag'), $args);
}

function content_tag () {
	$helper = new Helpers();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'content_tag'), $args);
}

function tag_properties () {
	$helper = new Helpers();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'tag_properties'), $args);
}

function cdata_section () {
	$helper = new Helpers();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'cdata_section'), $args);
}










?>