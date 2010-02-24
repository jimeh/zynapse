<?php
/*

   Zynapse Preference Class

   This class is used to store preferences, it is humanly editable
   and requires no overhead processing to be loaded.

*/

class _internals_preferences extends PreferenceContainer {
	
	public $js_url;
	public $js_libs_url;
	public $js_libs;
	public $js_charset;
	
	function __construct () {
		parent::__construct();
		
		$this->js_url = "/javascripts";
		$this->js_libs_url = "/javascripts/libs";
		$this->js_libs = array(
			'jquery' => "jquery-1.2.1.pack.js",
		);
		$this->js_charset = "utf-8";
	}
	
}

?>