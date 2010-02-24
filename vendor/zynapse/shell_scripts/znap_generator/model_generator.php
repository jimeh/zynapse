<?php
/*

   Model Generator - generate (super)models :D


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


class ModelGenerator extends ZnapGenerator {
	
	public
		$class_name = null;
		
	
	function generate () {
		$this->name = Inflector::singularize($this->name);
		$this->class_name = Inflector::camelize($this->name);
		
		$model_template = $this->templates_path.'/model.php';
	
		if ( stristr($this->name, '_') ) {
			$model_file = Znap::$models_path.'/'.strtolower($this->name).'.php';
		} else {
			$model_file = Znap::$models_path.'/'.Inflector::underscore($this->name).'.php';
		}
		
		if ( !is_file($model_file) ) {
			if ( is_file($model_template) ) {
				$model_data = file_get_contents($model_template);
				$model_data = str_replace('[class_name]', $this->class_name, $model_data);
				if ( file_put_contents($model_file, $model_data) ) {
					$this->echo_create($model_file);
				} else {
					$this->echo_create_error($model_file, 'model');
				}
			} else {
				$this->echo_template_error($model_template, 'model');
				exit;
			}
		} else {
			$this->echo_exists($model_file);
		}
		return true;
	}
	
	function help_summary () {
		echo "Generate Model:\n";
		echo "  ./script/generate model ModelName\n";
		echo "  for more model info: ./script/generate model\n";
	}
	
	function help () {
		echo "\n";
		echo "Usage: ./script/generate model ModelName\n";
		echo "\n";
      echo "Description:\n";
      echo "\tThe model generator creates functions for a new model.\n";
      echo "\tThe generator takes a model name as its argument.  The model name\n";
      echo "\tmay be given in CamelCase or under_score and should not be suffixed\n";
      echo "\twith 'Model'. The generator creates a model class in app/models.\n";
		echo "\n";
      echo "Example:\n";
      echo "\t./script/generate model Account\n";
      echo "\tThis will create an Account model:\n";
      echo "\t\tModel:      app/models/account.php\n";
		echo "\n";
	}
	
}


?>