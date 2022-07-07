<?php

class Waymark_Front extends Joe_Front {
	function __construct() {
		parent::__construct();

		//Admin only
		if(is_admin()) {
			return;
		}

		//Load Assets
		add_action('init', array($this, 'load_assets'));
	}
	
	function load_assets() {
		//Leaflet CSS
		Joe_Assets::css_enqueue(Joe_Helper::plugin_url('App/assets/css/leaflet.css'));	

		//Leaflet JS
		Joe_Assets::js_enqueue([
			'id' => 'leaflet_js',
			'url' => Joe_Helper::plugin_url('App/assets/js/leaflet.js'),
			'deps' => [ 'jquery' ],
			'data' => [
// 				'lang' => []						
			]
		]);

		//InMap CSS
		Joe_Assets::css_enqueue(Joe_Helper::plugin_url('App/assets/css/inmap.css'));	
		
		//InMap JS
		Joe_Assets::js_enqueue([
			'id' => 'inmap_js',
			'url' => Joe_Helper::plugin_url('App/assets/js/inmap.js'),
			'deps' => [ 'leaflet_js' ],
			'data' => [
// 				'lang' => []						
			]
		]);
	}		
}	
new Waymark_Front;