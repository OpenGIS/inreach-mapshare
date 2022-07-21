<?php
class InMap_Helper {
	//Data
	//'context' => 'front'
	static public function enqueue_shortcode_assets($data = []) {
		//Leaflet CSS
		Joe_Assets::css_enqueue(Joe_Helper::plugin_url('assets/css/leaflet.css'));	

		//Leaflet JS
		Joe_Assets::js_enqueue([
			'id' => 'leaflet_js',
			'url' => Joe_Helper::plugin_url('assets/js/leaflet.js'),
			'deps' => [ 'jquery' ],
			'data' => [
// 				'lang' => []						
			]
		]);

		//InMap CSS
		Joe_Assets::css_inline('
			.inmap-map .inmap-point {
				background: ' . Joe_Config::get_setting('map', 'appearance', 'tracking_colour') . ';
			}
		');
		Joe_Assets::css_enqueue(Joe_Helper::plugin_url('assets/css/front.min.css'));	
		
		//InMap JS
		Joe_Assets::js_enqueue([
			'id' => 'inmap_js',
			'url' => Joe_Helper::plugin_url('assets/js/front.min.js'),
			'deps' => [ 'leaflet_js' ],
			'data' => [
// 				'lang' => []						
			]
		]);	
	}
}