<?php

class InMap_Front extends Joe_Front {
	function __construct() {
		parent::__construct();

		//Front only
		if(is_admin()) {
			return;
		}

		//Load Assets
		add_action('init', array($this, 'load_assets'));
	}
	
	function load_assets() {
		//Joe CSS
		Joe_Assets::css_enqueue(Joe_Helper::plugin_url('Joe/Assets/css/front.min.css'));	

		//Leaflet CSS
		Joe_Assets::css_enqueue(Joe_Helper::plugin_url('App/Assets/css/leaflet.css'));	

		//Leaflet JS
		Joe_Assets::js_enqueue([
			'id' => 'leaflet_js',
			'url' => Joe_Helper::plugin_url('App/Assets/js/leaflet.js'),
			'deps' => [ 'jquery' ],
			'data' => [
// 				'lang' => []						
			]
		]);

		//InMap CSS
		Joe_Assets::css_inline('
			.inmap-map .inmap-marker-icon {
				background: ' . Joe_Config::get_setting('map', 'styles', 'tracking_colour') . ';
			}
		');
		Joe_Assets::css_enqueue(Joe_Helper::plugin_url('App/Assets/css/front.min.css'));	
		
		//InMap JS
		Joe_Assets::js_enqueue([
			'id' => 'inmap_js',
			'url' => Joe_Helper::plugin_url('App/Assets/js/front.min.js'),
			'deps' => [ 'leaflet_js' ],
			'data' => [
// 				'lang' => []						
			]
		]);
	}		
}	
new InMap_Front;