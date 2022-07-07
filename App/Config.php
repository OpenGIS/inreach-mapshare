<?php

$joe_plugin_slug = 'inreach-mapshare';
	
$config = [
	'plugin_slug' => $joe_plugin_slug,
	'plugin_text_domain' => 'joe',
	'plugin_name' => 'Inreach Mapshare',
	'plugin_name_short' => 'Inreach Mapshare',		
	'plugin_version' => '1.0',
	'site_url' => 'https://wordpress.org/support/plugin/' . $joe_plugin_slug . '/',
	'directory_url' => 'https://wordpress.org/support/plugin/' . $joe_plugin_slug . '/',
	'shortcode' => $joe_plugin_slug,
	'css_prefix' => 'inmap-',
	'plugin_about' => '<img alt="Joe\'s mug" src="https://www.morehawes.co.uk/assets/images/Joe1BW.jpg" />',
	
	/**
	 * ===========================================
	 * ================= MISC ====================
	 * ===========================================
	 */
	'misc' => array(
		'advanced' => array(
			'debug_mode' => true
		)
	)
];

Joe_Config::init($config);