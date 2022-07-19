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