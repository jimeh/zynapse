<?php

	
class AdminController extends ApplicationController {
	
	function index () {
		echo ' controller called ';
	}
	
	function view () {
		$this->render_layout = false;
		echo ' controller called ';
	}
}

?>