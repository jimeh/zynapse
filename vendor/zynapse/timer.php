<?php
/*

   Zynapse Timer - script speedometer


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


class Timer {

	public static 	
		
		# number of decimals to calculate
		$digits = 3,
		
		# number of decimals to calulate requests per second to
		$req_digits = 2,
	
		# output pattern used by end()
		#  %s = seconds since start
		#  %r = requests per second
		$pattern = '%s sec — %r reqs/sec',
	
	
		# start time
		$start = null,
		
		# end time
		$end = null,
	
		# execution time
		$time = null,
		
		# requests per second at current execution time
		$requests = null,
	
		# saved output from end()
		$output = null,
		
		# timer is started? true or false
		$started = false;
	
	
	/**
	 * Start
	 * @param   digits   number of decimals to calculate
	 * @return  nothing
	 */
	function start ($digits = null) {
		if ( !empty($digits) ) self::$digits = $digits;
		self::$start = microtime(true);
		self::$started = true;
	}
	
	/**
	 * Calculate time since start and return using defined pattern
	 * @param   digits    number of decimals to calculate
	 * @param   pattern   output pattern ("%s" is replaced by time)
	 * @return  time with microseconds since start, formatted using pattern
	 */
	function end ($digits = null, $pattern = null) {
		self::$end = microtime(true);
		if ( !preg_match("/[0-9]{1,3}/", $digits) ) $digits = self::$digits;
		if ( strpos($pattern, '%s') === false ) $pattern = self::$pattern;
		self::$time = number_format( (self::$end - self::$start), $digits);
		self::$output = str_replace('%s', self::$time, $pattern);
		if ( strpos($pattern, '%r') !== false ) {
			self::$requests = number_format( (1 / self::$time), self::$req_digits);
			self::$output = str_replace('%r', self::$requests, self::$output);
		}
		return self::$output;
	}
	
	/**
	 * Calculate time since start and return plain time
	 * @param   digits    number of decimals to calculate
	 * @return  time with microseconds since start
	 */
	function term ($digits = null) {
		self::$end = microtime(true);
		if ( !preg_match("/[0-9]{1,3}/", $digits) ) $digits = self::$digits;
		self::$time = number_format( (self::$end - self::$start), $digits);
		self::$requests = number_format( (1 / self::$time), self::$req_digits);
		return self::$time;
	}
	
	/**
	 * Output detailed timing info for debugging
	 * @return  detailed execution timing info
	 */
	function debug () {
		$lf = "\n";
		
		$start  = 'Start time: ';
		$start .= (self::$started) ? self::$start.' ('.self::prettify_time(self::$start).')' : 'Not started.' ;
		
		$end  = 'End time:   ';
		$end .= (self::$end !== null) ? self::$end.' ('.self::prettify_time(self::$end).')' : 'Not ended.' ;
		
		$time = 'Script execution: ';
		$reqs = 'Requests/second: ';
		
		if ( self::$started && self::$end !== null ) {
			$time .= $t = number_format( (self::$end - self::$start), 5);
			$reqs .= number_format( (1 / $t), self::$req_digits);
		} else {
			$time .= 'Unknown';
			$reqs .= 'Unknown';
		}
		
		return $time.$lf.$reqs.$lf.$start.$lf.$end;
	}
	
	function prettify_time ($input, $format = 'd-M-Y H:i:s') {
		if ( strpos($input, '.') !== false ) {
			$time = explode('.', $input);
			return date($format, $time[0]).'.'.$time[1];
		} else {
			return date($format, $time[0]);
		}
	}
}

?>