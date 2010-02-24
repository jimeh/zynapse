<?php

class TestSnippet extends Snippets {
	
	function index () {
		$this->message = 'hello world';
	}
	
	function wiiee () {
		$this->message = 'helppppp';
		$this->render_action = 'index';
	}
	
}

?>