<?php
/*

   Layout Generator - generate layouts


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


class LayoutGenerator extends ZnapGenerator {
	
	public
		$class_name = null;
		
	
	function generate () {	
		$layout_template = $this->templates_path.'/layout.phtml';
		
		if ( is_file($layout_template) ) {
			
			$this->name = Inflector::singularize($this->name);
			$this->class_name = Inflector::camelize($this->name);
			
			if ( stristr($this->name, '_') ) {
				$layout_filename = strtolower($this->name).'.'.Znap::$views_extension;
			} else {
				$layout_filename = Inflector::underscore($this->name).'.'.Znap::$views_extension;
			}
			
			$view_paths = glob(Znap::$app_path.'/views*');
			
			foreach( $view_paths as $path ) {
				$layout_file = $path.'/__layouts/'.$layout_filename;
				if ( !is_file($layout_file) ) {
					$layout_data = file_get_contents($layout_template);
					$layout_data = str_replace('[layout]', $this->name, $layout_data);
					if ( file_put_contents($layout_file, $layout_data) ) {
						$this->echo_create($layout_file);
					} else {
						$this->echo_create_error($layout_file, 'layout');
						exit;
					}
				} else {
					$this->echo_exists($layout_file);
				}
			}
		} else {
			$this->echo_template_error($layout_template, 'layout');
			exit;
		}
		return true;
	}
	
	function help_summary () {
		echo "Generate Layout for specific Controller:\n";
		echo "  ./script/generate layout controller_name\n";
		echo "  for more layout info: ./script/generate layout\n";
	}
	
	function help () {
		echo "\n";
		echo "Usage: ./script/generate layout controller_name\n";
		echo "\n";
		echo "Description:\n";
		echo "\tThe layout generator creates layout files for existing controllers.\n";
		echo "\tThe generator takes a controller name as its argument.\n";
		echo "\tController name may be given in CamelCase or under_score and should\n";
		echo "\tnot be suffixed with 'Controller'. The generator creates a layout\n";
		echo "\tfile in app/views/__layouts.\n";
		echo "\n";
		echo "Example:\n";
		echo "\t./script/generate layout Account\n";
		echo "\tThis will create an Account layout:\n";
		echo "\t\tLayout:      app/views/__layout/account.phtml\n";
		echo "\n";
	}
	
}



?>