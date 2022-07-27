<?php

class InMap_Shortcode extends Joe_Shortcode {

	function __construct() {
		parent::__construct();

		$this->load_assets();
	}
	
	function load_assets() {
		//Leaflet CSS
		Joe_Assets::css_enqueue(Joe_Helper::plugin_url('assets/css/leaflet.css'));	

		//Leaflet JS
		Joe_Assets::js_enqueue([
			'id' => 'leaflet_js',
			'url' => Joe_Helper::plugin_url('assets/js/leaflet.js'),
			'deps' => [ 'jquery' ]
		]);

		//InMap CSS
		Joe_Assets::css_inline('
			.inmap-map .inmap-point {
				background: ' . Joe_Config::get_setting('appearance', 'colours', 'tracking_colour') . ';
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

	public function handle_shortcode($shortcode_data, $content = null) {
		$shortcode_data = shortcode_atts(array(
			'mapshare_identifier' => false,
			'mapshare_password' => false,
			'mapshare_date_start' => false,
			'mapshare_date_end' => false
		), $shortcode_data, Joe_Config::get_item('plugin_shortcode'));
	
		if($shortcode_data['mapshare_identifier']) {					
			$Inreach_Mapshare_Inreach = new InMap_Inreach($shortcode_data);		

			$hash = Joe_Helper::make_hash($Inreach_Mapshare_Inreach->get_parameters());
			$geojson = $Inreach_Mapshare_Inreach->get_geojson();

			//JS
			Joe_Assets::js_onready('
				inmap_create_map(
					"' . $hash . '",
					' . $geojson . '
				);
			');
			
			//HTML
			$out = '<div class="inmap-wrap">';
			$out .= '	<div id="inmap-' . $hash . '" class="inmap-map"></div>';
			$out .= '	<div class="inmap-info"></div>';
			$out .= '</div>';

			return $out;
		}	else {
			return 'No params :(';
		}
	}	
}