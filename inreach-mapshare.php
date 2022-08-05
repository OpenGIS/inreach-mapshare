<?php
	
/*
Plugin Name: inReach MapShare
Plugin URI: https://www.morehawes.co.uk/
Description: Display inReach MapShare data on your WordPress site. Visit the <a href="options-general.php?page=inreach-mapshare-settings">Settings</a> page to create and customise Shortcodes.
Version: 1.0
Text Domain: inreach-mapshare
Author: Joe Hawes
Author URI: https://www.morehawes.co.uk/
*/

//Joe
if(! class_exists('Joe_Config')) {
	require_once('Joe/Joe.php');
}

spl_autoload_register(function($class_name) {
	if(strpos($class_name, 'InMap_') === 0) {
		require 'inc/' . str_replace('InMap_', '', $class_name . '.php') ;	
	}
});

add_action('init', function() {
	$plugin_slug = 'inreach-mapshare';
	$colour_primary = '#dd9933';
	
	//Icon URLs	
	$message_icon = Joe_Helper::asset_url('img/message.svg', $plugin_slug);
	$tracking_icon = Joe_Helper::asset_url('img/location-gps.svg', $plugin_slug);

	$config = [
		'plugin_slug' => $plugin_slug,
		'plugin_text_domain' => $plugin_slug,
		'plugin_name' => 'inReach MapShare',
		'plugin_version' => '1.0',
		'settings_id' => 'inmap_settings',
		'settings_default_tab' => 'joe-settings-tab-mapshare',
		'site_url' => 'https://wordpress.org/support/plugin/' . $plugin_slug . '/',
		'directory_url' => 'https://wordpress.org/support/plugin/' . $plugin_slug . '/',
		'plugin_shortcode' => $plugin_slug,
		'plugin_about' => '<img alt="Joe\'s mug" src="https://www.morehawes.co.uk/assets/images/Joe1BW.jpg" />',
	
		//KML
		'kml_data_include' => [
			'Id',
			'Time UTC',
			'Time',
	// 		'Name',
	//		'Map Display Name',
	// 		'Device Type',
	// 		'IMEI',
	// 		'Incident Id',
			'Latitude',
			'Longitude',
			'Elevation',
			'Velocity',
	// 		'Course',
			'Valid GPS Fix',
	// 		'In Emergency',
			'Text',
			'Event',
	// 		'Device Identifier',
	// 		'SpatialRefSystem'			
		],

		//Shortcode
		'shortcode' => [
			'build' => []
		],
	
		//MapShare
		'mapshare' => [
			'defaults' => [
				'mapshare_date_start' => '2020-10-02T16:20'
			]
		],
		
		//Appearance
		'appearance' => [
			'map' => [
				'basemap_url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
				'basemap_attribution' => '© &lt;a href=&quot;https://www.openstreetmap.org/copyright&quot;&gt;OpenStreetMap&lt;/a&gt; contributors'
			],
			'colours' => [
				'tracking_colour' => $colour_primary,
			],
			'icons' => [
				'message_icon' => $message_icon,
				'tracking_icon' => $tracking_icon
			]			
		]
	];

	Joe_Config::init($config);
	
	new InMap_Admin;
	new InMap_Front;	
});