<?php
	
/*
Plugin Name: Feed Beast
Plugin URI: https://www.morehawes.co.uk/
Description: Got to feed the beast!
Version: 1.0
Text Domain: feed-beast
Author: Joe Hawes
Author URI: https://www.morehawes.co.uk/
*/

require_once('inc/Helpers/Beast_Helper.php');
require_once('inc/Helpers/Beast_Cache.php');
require_once('inc/Helpers/Beast_Input.php');

require_once('inc/Beast_Config.php');
require_once('inc/Beast_Class.php');
require_once('inc/Beast_Types.php');
require_once('inc/Beast_Object.php');
require_once('inc/Beast_Feed.php');

// require_once('inc/Inreach/Beast_Inreach.php');

//Inreach
require_once('inc/Inreach/Front.php');
require_once('inc/Inreach/Admin.php');