<?php
/*

   Form Tag Helpers - helpers to create forms


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


class FormTagHelper extends Helpers {
	
	
	function form_tag ($name = null, $url = '', $properties = array()) {
		if ( !empty($name) ) {
			$properties = array_merge(array('name' => $name, 'id' => $name), $properties);
		}
		$properties = array_merge(array('method' => 'post'), $properties);
		
		if ( array_key_exists('multipart', $properties) && $properties['multipart'] != false) {
			$properties['enctype'] = 'multipart/form-data';
			unset($properties['multipart']);
		}
		
		$properties['action'] = $url;
		return $this->tag('form', $properties);
	}
	
	function hidden_field ($name = '', $value = null, $properties = array()) {
		$this->initiate($name, $value);
		
		$base_properties = array(
			'type' => 'hidden',
			'name' => $name,
			'id' => $name,
			'value' => @htmlspecialchars($this->value),
		);
		return $this->tag('input', array_merge($properties, $base_properties), false);
	}
	
	function text_field ($name = '', $value = null, $properties = array()) {
		$this->initiate($name, $value);
		
		$base_properties = array(
			'type' => 'text',
			'name' => $name,
			'id' => $name,
			'value' => @htmlspecialchars($this->value),
		);
		return $this->tag('input', array_merge($properties, $base_properties), false);
	}
	
	
	function password_field ($name = '', $auto_fill = false, $properties = array()) {
		$this->initiate($name, null, $auto_fill);
		
		$base_properties = array(
			'type' => 'password',
			'name' => $name,
			'id' => $name,
			'value' => ($auto_fill === false ? '' : @htmlspecialchars($this->value)),
		);
		return $this->tag('input', array_merge($properties, $base_properties), false);
	}
	
	function file_field ($name = '', $auto_fill = false, $properties = array()) {
		$this->initiate($name, null, $auto_fill);
		
		$base_properties = array(
			'type' => 'file',
			'name' => $name,
			'id' => $name,
			'value' => ($auto_fill === false ? '' : @htmlspecialchars($this->value)),
		);
		return $this->tag('input', array_merge($properties, $base_properties), false);
	}
	
	function textarea ($name = '', $value = null, $properties = array()) {
		$this->initiate($name, $value);
		
		$base_properties = array(
			'name' => $name,
			'id' => $name,
		);
		
		return $this->content_tag('textarea', @htmlspecialchars($this->value), array_merge($properties, $base_properties));
	}
	
	
	function select_tag ($name, $options = array(), $properties = array(), $selected = null) {
		$this->initiate($name, $selected);
		
		if ( $options == null ) $options = array();
		if ( $properties == null ) $properties = array();
		
		$base_properties = array(
			'name' => $name,
			'id' => $name,
		);
		
		if ( array_key_exists('prefix', $properties) ) {
			$prefix = $properties['prefix'];
			unset($properties['prefix']);
		} elseif ( array_key_exists('blank_prefix', $properties) ) {
			$prefix = ' ';
			unset($properties['blank_prefix']);
		}
		
		$content = "\n".$this->option_tags($options, (isset($prefix)) ? $prefix : null )."\n";
		return $this->content_tag('select', $content, array_merge($properties, $base_properties));
	}
	
	
	function option_tags ( $options, $prefix = null ) {
		$html = array();
		if ( $prefix != null ) {
			$html[] = $this->option_tag('', $prefix);
		}
		foreach( $options as $key => $value ) {
			$html[] = $this->option_tag($key, $value, ($this->value == $key) ? true : false );
		}
		return (count($html)) ? implode("\n", $html) : '';
	}
	
	
	function option_tag ($value, $title, $selected = false) {
		$properties['value'] = $value;
		if ( $selected ) {
			$properties['selected'] = 'selected';
		}
		return $this->content_tag('option', $title, $properties);
	}
	
	
}



/*

   root scope functions

*/


function form_tag () {
	$helper = new FormTagHelper();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'form_tag'), $args);
}

function hidden_field () {
	$helper = new FormTagHelper();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'hidden_field'), $args);
}

function text_field () {
	$helper = new FormTagHelper();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'text_field'), $args);
}

function file_field () {
  $helper = new FormTagHelper();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'file_field'), $args);
	
}

function password_field () {
	$helper = new FormTagHelper();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'password_field'), $args);
}

function textarea () {
	$helper = new FormTagHelper();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'textarea'), $args);
}

function select_tag () {
	$helper = new FormTagHelper();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'select_tag'), $args);
}

function option_tags () {
	$helper = new FormTagHelper();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'option_tags'), $args);
}

function option_tag () {
	$helper = new FormTagHelper();
	$args = func_get_args();
	return call_user_func_array(array($helper, 'option_tag'), $args);
}






?>