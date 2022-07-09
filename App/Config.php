<?php

$inmap_plugin_slug = 'inreach-mapshare';
$inmap_colour_primary = '#dd9933';

$config = [
	'plugin_slug' => $inmap_plugin_slug,
	'plugin_text_domain' => $inmap_plugin_slug,
	'plugin_name' => 'inReach MapShare',
	'plugin_version' => '1.0',
	'settings_id' => 'inreach_mapshare',
	'site_url' => 'https://wordpress.org/support/plugin/' . $inmap_plugin_slug . '/',
	'directory_url' => 'https://wordpress.org/support/plugin/' . $inmap_plugin_slug . '/',
	'shortcode' => $inmap_plugin_slug,
	'plugin_about' => '<img alt="Joe\'s mug" src="https://www.morehawes.co.uk/assets/images/Joe1BW.jpg" />',
	
	//KML
	'kml_data_include' => [
// 		'Id',
		'Time UTC',
		'Time',
// 		'Name',
		'Map Display Name',
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
	
	'map' => [
		'styles' => [
			'tracking_colour' => $inmap_colour_primary,
			'message_icon' => '&#9993;'
		]
	],
	'misc' => [
		'advanced' => [
			'debug_mode' => true
		]
	]
];

Joe_Config::init($config);