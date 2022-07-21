<?php
	
/*
Plugin Name: inReach MapShare
Plugin URI: https://www.morehawes.co.uk/
Description: Display inReach MapShare data on your WordPress site.
Version: 1.0
Text Domain: inreach-mapshare
Author: Joe Hawes
Author URI: https://www.morehawes.co.uk/
*/

spl_autoload_register(function($class_name) {
	if(strpos($class_name, 'InMap_') === 0) {
		require 'inc/' . str_replace('InMap_', '', $class_name . '.php') ;	
	}
});

//Joe
if(! class_exists('Joe_Config')) {
	require_once('Joe/Joe.php');
	

	//Helpers
	require_once('App/Classes/InMap_Inreach.php');
	
	//Core	
	require_once('App/Config.php');
	require_once('App/Extend/Settings.php');		
	require_once('App/Extend/Shortcode.php');	
 	require_once('App/Extend/Admin.php');
 	require_once('App/Extend/Front.php');
}

add_action('init', function() {
	new InMap_Settings;
});