<?php

$joe_plugin_slug = 'inreach-mapshare';
	
$config = [
	'plugin_slug' => $joe_plugin_slug,
	'plugin_text_domain' => $joe_plugin_slug,
	'plugin_name' => 'inReach MapShare',
	'plugin_version' => '1.0',
	'settings_id' => 'inreach_mapshare',
	'site_url' => 'https://wordpress.org/support/plugin/' . $joe_plugin_slug . '/',
	'directory_url' => 'https://wordpress.org/support/plugin/' . $joe_plugin_slug . '/',
	'shortcode' => $joe_plugin_slug,
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