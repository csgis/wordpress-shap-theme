<?php

	// ----------------------------------------------------
	// Debug Functions
	// ----------------------------------------------------	
	function mh_startTimer( $name ){ // debug function: get execution time start
	
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$GLOBALS[execution_timer][$name] = $mtime;	
	
	}
	function mh_endTimer( $name ){ // debug function: get execution time end
		
		$mtime = microtime();
		$mtime = explode(" ",$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		$totaltime = round($endtime - $GLOBALS[execution_timer][$name], 4);	
		
		return $totaltime;
		
	}
	function print_r2( $var, $text = false){ // debug function: print_r with pre 	
		echo '<pre style="margin-bottom: 1.2em; font-family: monospace; text-align: left;">';
		if( $text ) echo '<strong style="background-color: yellow; color: black;">'.$text.':</strong>&nbsp;&nbsp;';
		print_r( $var );
		echo '</pre>';
	}

?>
