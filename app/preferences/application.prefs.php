<?php
/*

   Zynapse Preference Class

   This class is used to store preferences, it is humanly editable
   and requires no overhead processing to be loaded.

*/

class application_preferences extends PreferenceContainer {
	
	public $language;
	
	function __construct () {
		parent::__construct();
		
		$this->language = "english";
	}
	
}

?>