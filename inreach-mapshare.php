<?php

/*
Plugin Name: inReach MapShare
Plugin URI: https://github.com/morehawes/inreach-mapshare
Description: Display inReach MapShare data on your WordPress site. Visit the <a href="options-general.php?page=inreach-mapshare-settings">Settings</a> page to create and customise Shortcodes.
Version: 3.0.4
Text Domain: inreach-mapshare
Author: Joe Hawes
Author URI: https://www.morehawes.ca/
 */

spl_autoload_register(function ($class_name) {
	$file_name = substr($class_name, strripos($class_name, '_') + 1);
	$file_name .= '.php';

	switch (true) {
	//App
	case strpos($class_name, 'InMap_') === 0:
		require 'src/php/' . $file_name;

		break;
	}
});

add_action('init', function () {
	$plugin_slug = 'inreach-mapshare';
	$plugin_name = 'inReach MapShare';

	$config = [
		'plugin_slug' => $plugin_slug,
		'plugin_text_domain' => $plugin_slug,
		'plugin_name' => $plugin_name,
		'plugin_name_short' => $plugin_name,
		'plugin_version' => '3.0.4',
		'settings_id' => 'inmap_settings',
		'settings_default_tab' => 'inmap-settings-tab-mapshare',
		'site_url' => 'https://github.com/morehawes/inreach-mapshare/',
		'github_url' => 'https://github.com/morehawes/inreach-mapshare/',
		'plugin_shortcode' => $plugin_slug,
		'plugin_about' => '
			<p class="inmap-lead">' . sprintf(__('Display your live <a href="%s">MapShare</a> data with a simple Shortcode.', InMap_Config::get_item('plugin_text_domain')), 'https://support.garmin.com/?faq=p2lncMOzqh71P06VifrQE7') . '</p>

			<p>' . sprintf(__('Enable and configure MapShare in the <a href="%s">Social</a> tab of your Garmin Explore Account.', InMap_Config::get_item('plugin_text_domain')), 'https://explore.garmin.com/Social') . '</p>
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
			'build' => [],
		],

		//MapShare
		'mapshare' => [
			'defaults' => [
				'mapshare_date_start' => '2020-10-02T16:20',
			],
		],

		//Appearance
		'appearance' => [
			'map' => [
				'basemap_url' => 'https://tile.opentopomap.org/{z}/{x}/{y}.png',
				'basemap_attribution' => '© &lt;a href=&quot;https://www.openstreetmap.org/copyright&quot;&gt;OpenStreetMap&lt;/a&gt; contributors, SRTM | © &lt;a href=&quot;https://opentopomap.org&quot;&gt;OpenTopoMap&lt;/a&gt; (&lt;a href=&quot;https://creativecommons.org/licenses/by-sa/3.0/&quot;&gt;CC-BY-SA&lt;/a&gt;)',
				'basemap_title' => 'OpenTopoMap',
				'basemap_opacity' => '0.7',
				'basemap_maxzoom' => '17',
				'detail_expanded' => '1',
			],
			'colours' => [
				'message_colour' => '#e524ab',
				'tracking_colour' => '#e524ab',
				'route_colour' => '#e29809',
			],

		],

		// Advanced
		'advanced' => [
			'request' => [
				'cache_minutes' => '15',
			],

			'response' => [
				'precision' => '3',
			],
		],

	];

	InMap_Config::init($config);

	new InMap_Admin;
	new InMap_Front;
});