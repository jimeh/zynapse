<?php

class Page extends ActiveRecord {
	
	public $has_many = 'comments';
	public $has_and_belongs_to_many = 'categories';
	
	
	
	// public $has_many = array(
	// 	'comments' => array(
	// 		'class_name' => 'Comment',
	// 		'foreign_key' => 'page_id',
	//			'conditions' => array('is_public' => 1),
	// 	),
	// );
	
	
	// public $table_name = 'whatever';
	// public $primary_key = 'id';
	
	
	// public $type = 'mysql';
	// public $host = 'db.domain.com';
	// public $database = 'librarious_db';
	// public $username = 'root';
	// public $password = '';
	// public $persistent = false;
	// public $table_prefix = 'znap_';
	// public $table_name = 'collection';
	
	
	function validate_title () {
		if ( $this->title == '' ) {
			$this->error_title = 'Title can not be empty.';
		}
	}
	
}

?>