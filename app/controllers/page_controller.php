<?php


class PageController extends ApplicationController {
	
	
	
	function index ($vars) {
		// page view count
		if ( isset($_REQUEST['kill']) ) {
			unset($_SESSION['views']);
			$this->redirect_to('back');
		}
		
		@$_SESSION['views']++;
		$this->views = $_SESSION['views'];
		
		// new Page object experiments
		// $newpage = new Page();
		// $newpage->id = 8;
		// $newpage->title = 'anything else';
		// $newpage->body = '...maybe not...';
		// $newpage->get_association('person');
		// $newpage->error_title = 'error message'; // define errors
		// print_r($newpage);
		// $newpage->save();
		// echo $newpage->build_update_query($newpage)."\n<br />";
		
		// $newpage->delete_all('`id` > 11'); 
		

		// get page list
		$page = new Page();
		$this->columns = $page->_columns;
		$this->pages = $page->find_all();
		
		
		// App::$prefs->yay = 'hello';
		// unset(App::$prefs->yay);
		// App::$prefs->save();
		// print_r(Znap::$prefs);
		
		// print_r($this->page);
		// print_r(Page::$table_info);
		// print_r($this->page->_columns);
	}
	
	
	function view () {
		if ( isset($_REQUEST['id']) ) {
			$page = new Page();
			$this->columns = $page->_columns;
			$this->page = $page->find($_REQUEST['id']);
		}
	}
	
	
	function edit () {
		
	}
	
	
}

?>