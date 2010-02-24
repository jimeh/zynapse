<?php
/*

   Zynapse Generator - generate controllers, models, and more


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


class FixPermissions extends ShellScript {
	
	
	function run () {
		
		// preference folder and all preference files
		$list[] = Znap::$tmp_path;
		$list = array_merge($list, glob(Znap::$tmp_path.'/cache.prefs.php'));
		$list[] = Znap::$cache_path;
		$list[] = Znap::$prefs_path;
		$list = array_merge($list, glob(Znap::$prefs_path.'/*.prefs.php'));
		$list[] = Znap::$log_path;
		$list = array_merge($list, glob(Znap::$log_path.'/*.log'));
		
		foreach( $list as $item ) {
			if ( !$this->check($item) ) {
				if ( $this->change($item) ) {
					$this->echo_changed($item);
				} else {
					$this->echo_change_error($item);
				}
			} else {
				$this->echo_ok($item);
			}
		}
		
	}
	
	function check ($item) {
		if ( is_file($item) && substr(sprintf('%o', fileperms($item)), -4) == '0666' ) {
			return true;
		} elseif ( is_dir($item) && substr(sprintf('%o', fileperms($item)), -4) == '0777' ) {
			return true;
		}
		return false;
	}
	
	function change ($item) {
		if ( is_file($item) ) {
			$this->chmod($item, '0666');
		} elseif ( is_dir($item) ) {
			$this->chmod($item, '0777');
		}
		clearstatcache();
		return $this->check($item);
	}
	
	function chmod ($item, $mode) {
		exec(Znap::$chmod_cmd.' '.$mode.' "'.$item.'"');
	}
	
	function echo_changed ($item, $mode = null) {
		if ( $mode == null ) {
			if ( is_file($item) ) {
				$mode = '0666';
			} elseif ( is_dir($item) ) {
				$mode = '0777';
				$item .= '/';
			}
		}
		echo '   '.str_replace(ZNAP_ROOT.'/', '', $item).' changed to '.$mode.".\n";
	}
	
	function echo_change_error ($item, $mode = null) {
		echo '   '.str_replace(ZNAP_ROOT.'/', '', $item).' is '.substr(sprintf('%o', fileperms($item)), -4)." and could not be changed to 0666.\n";
	}
	
	function echo_ok ($item, $mode = null) {
		if ( $mode == null ) {
			if ( is_file($item) ) {
				$mode = '0666';
			} elseif ( is_dir($item) ) {
				$mode = '0777';
				$item .= '/';
			}
		}
		echo '   '.str_replace(ZNAP_ROOT.'/', '', $item).' is '.$mode.".\n";
	}
	
}

?>