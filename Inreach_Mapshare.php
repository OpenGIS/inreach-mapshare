<?php
	
/*
Plugin Name: Inreach Mapshare
Plugin URI: https://www.morehawes.co.uk/
Description: Display Inreach Mapshare data on your WordPress site.
Version: 1.0
Text Domain: inreach-mapshare
Author: Joe Hawes
Author URI: https://www.morehawes.co.uk/
*/

require_once('inc/Helpers/Inreach_Mapshare_Helper.php');
require_once('inc/Helpers/Inreach_Mapshare_Cache.php');

require_once('inc/Inreach_Mapshare_Config.php');
require_once('inc/Inreach_Mapshare_Class.php');
require_once('inc/Inreach_Mapshare_Feed.php');

//Inreach
require_once('inc/Inreach/Front.php');