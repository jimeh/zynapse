<?php

class Comment extends ActiveRecord {
	
	public $belongs_to = 'page';
	public $counter_cache = true;
	
}

?>