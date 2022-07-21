<?php

class InMap_Admin extends Joe_Admin {
	
	function __construct() {
		parent::__construct();

		//Admin only
		if(! is_admin()) {
			return;
		}
		
		//Actions
		add_action('admin_init', array($this, 'load_assets'));
	}
	
	function load_assets() {
		parent::load_assets();
/*
		//Leaflet CSS
		Joe_Assets::css_enqueue(Joe_Helper::plugin_url('assets/css/leaflet.css'));	

		//Leaflet JS
		Joe_Assets::js_enqueue([
			'id' => 'leaflet_js',
			'url' => Joe_Helper::plugin_url('assets/js/leaflet.js'),
			'deps' => [],
			'data' => [
// 				'lang' => []						
			]
		]);	
*/		
	}
}	
new InMap_Admin;