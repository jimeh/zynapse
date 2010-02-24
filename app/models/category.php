<?php

class Category extends ActiveRecord {
	
	public $has_and_belongs_to_many = 'pages';
	
}

?>