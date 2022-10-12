<?php
	
/*
Plugin Name: inReach MapShare
Plugin URI: https://github.com/morehawes/inreach-mapshare
Description: Display inReach MapShare data on your WordPress site. Visit the <a href="options-general.php?page=inreach-mapshare-settings">Settings</a> page to create and customise Shortcodes.
Version: 1.0.2
Text Domain: inreach-mapshare
Author: Joe Hawes
Author URI: https://www.morehawes.co.uk/
*/

//Joe App
spl_autoload_register(function($class_name) {
	$file_name = substr($class_name, strripos($class_name, '_')+1);
	$file_name .= '.php';

	switch(true) {
		//Joe Build
		case strpos($class_name, 'Joe_v') === 0 :
			require 'Joe/' . $file_name;	
			
			break;
		
		//Joe Development
		case strpos($class_name, 'Joe_') === 0 :
			require 'Joe/inc/' . $file_name;	
			
			break;
		
		//App
		case strpos($class_name, 'InMap_') === 0 :
			require 'App/' . $file_name;	
			
			break;
	}
});

add_action('init', function() {
	$plugin_slug = 'inreach-mapshare';
	$plugin_name = 'inReach MapShare';
	
	$colour_primary = '#e524ab';
	
	//Icon URLs	
	$message_icon = Joe_v1_0_Helper::asset_url('img/message.svg', $plugin_slug);
	$tracking_icon = Joe_v1_0_Helper::asset_url('img/location-gps.svg', $plugin_slug);

	$config = [
		'plugin_slug' => $plugin_slug,
		'plugin_text_domain' => $plugin_slug,
		'plugin_name' => $plugin_name,
		'plugin_version' => '1.0.2',
		'settings_id' => 'inmap_settings',
		'settings_default_tab' => 'joe-settings-tab-mapshare',
		'site_url' => 'https://github.com/morehawes/inreach-mapshare/',
// 		'directory_url' => 'https://wordpress.org/plugins/' . $plugin_slug . '/',
		'github_url' => 'https://github.com/morehawes/inreach-mapshare/',
		'plugin_shortcode' => $plugin_slug,
		'plugin_about' => '
			<p class="joe-lead">' . sprintf(__('Display your live <a href="%s">MapShare</a> data with a simple Shortcode.', Joe_v1_0_Config::get_item('plugin_text_domain')), 'https://support.garmin.com/?faq=p2lncMOzqh71P06VifrQE7') . '</p>
						
			<p>' . sprintf(__('Enable and configure MapShare in the <a href="%s">Social</a> tab of your Garmin Explore Account.', Joe_v1_0_Config::get_item('plugin_text_domain')), 'https://explore.garmin.com/Social') . '</p>
		',
	
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

	Joe_v1_0_Config::init($config);
	
	new InMap_Admin;
	new InMap_Front;	
});