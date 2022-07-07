<?php

class InMap_Shortcode extends Joe_Shortcode {

	function __construct() {
		//Front only
		if(is_admin()) {
			return;
		}
		
		parent::__construct();
	}

	public function handle_shortcode($shortcode_data, $content = null) {
		$shortcode_data = shortcode_atts(array(
			'mapshare_identifier' => false,
			'mapshare_password' => false,
			'mapshare_date_start' => false,
			'mapshare_date_end' => false
		), $shortcode_data, Joe_Config::get_item('shortcode'));
	
		if($shortcode_data['mapshare_identifier']) {					
			$Inreach_Mapshare_Inreach = new InMap_Inreach($shortcode_data);		

			$hash = Joe_Helper::make_hash($Inreach_Mapshare_Inreach->get_parameters());
			$geojson = $Inreach_Mapshare_Inreach->response_geojson();
			
			//JS
			Joe_Assets::js_onready('
				inmap_create_map(
					"' . $hash . '",
					' . $geojson . '
				);
			');
			
			//HTML
			return '<div id="inmap-' . $hash . '" class="inmap-map"></div>';
		}	
	}	
}
new InMap_Shortcode;