<?php

/*
 *	Reusable and easy to maintain components for wordpress themes
 *  author: Mario Helbing, CSGIS	
 *
 *	@changelog
 *		2017-08-29	add_action changed from init to after_setup_theme which fires earlier
 *		2016-09-04	exclude components that start with underscore
 *		2016-09-03	added handling of component.init.php for action hooks
 *		2016-08-23	changed unset method to action-hook
 *		2016-08-22	first commit
 *
 */
 
// unset session variable
function unsetSessionComponents(){
	unset($_SESSION['components']);
}
add_action('after_setup_theme', 'unsetSessionComponents', 1);

// call the component and include their php file
function component($name, $args = false){
	$phpFile = dirname(__FILE__).'/'.$name.'/'.$name.'.php';
	$jsFile = dirname(__FILE__).'/'.$name.'/'.$name.'.js';
	
	if(file_exists($jsFile)){
		$_SESSION['components'][$name]['jsFile'] = $jsFile;
	} else {
		//echo componentLog('component js file does not exists: '.$jsFile, 'error');
	}		
	
	if(file_exists($phpFile)){
		$_SESSION['components'][$name]['phpFile'] = $phpFile;						
		$return = include($phpFile);			
		if($return) return $return;
	} else {
		echo componentLog('component php file does not exists: '.$phpFile, 'error');		
	}
}

// log helper
function componentLog($string, $type = 'log' ){
	if($type == 'log'){
		return $string;
	} elseif($type == 'error') {
		return '<br /><span style="background-color: red; padding: 0.2em;">ERROR: '.$string.'</span><br />';
	}
}

// included automatisch alle component.init.php files in der functions.php
function load_components_init_php_files($filename) {
	$initScripts = glob(dirname(__FILE__).'/[!_]*/*.init.php');
	//print_r2($initScripts, '$initScripts');
	
	if(is_array($initScripts)){
		foreach($initScripts as $initScript){
			include($initScript);
		}
	}
}

add_action( 'after_setup_theme', 'load_components_init_php_files');

function load_components_auto_js_files($filename) {
	$autoJsFiles = glob(dirname(__FILE__).'/[!_]*/*.auto.js');
	$componentsDirUri = get_template_directory_uri().'/components';
	
	if(is_array($autoJsFiles)){
		foreach($autoJsFiles as $autoJsFile){
			$autoJsFile = str_replace(dirname(__FILE__), $componentsDirUri, $autoJsFile);								
			if($autoJsFile) echo '<script src="'.$autoJsFile.'"></script>';	
		}
	}
}
add_action( 'wp_footer', 'load_components_auto_js_files');

// in case the component has been used with component() before
// we load all component.js files in footer	
function load_loaded_components_js_files($filename) {
	$components = $_SESSION['components'];
	$componentsDirUri = get_template_directory_uri().'/components';
		
	if(is_array($components)){
		foreach($components as $componentName => $componentVars){
			
			$jsFile = $componentVars['jsFile'];
			$jsFile = str_replace(dirname(__FILE__), $componentsDirUri, $jsFile);								
			if($jsFile) echo '<script src="'.$jsFile.'"></script>';	
			
		}
	}

}
add_action( 'wp_footer', 'load_loaded_components_js_files');	


