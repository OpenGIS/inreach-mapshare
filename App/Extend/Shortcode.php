<?php

class InMap_Shortcode extends Joe_Shortcode {

	public function handle_shortcode($shortcode_data, $content = null) {
		$shortcode_data = shortcode_atts(array(
			'mapshare_identifier' => false,
			'mapshare_password' => false,
			'mapshare_date_start' => false,
			'mapshare_date_end' => false
		), $shortcode_data, Joe_Config::get_item('shortcode'));
	
		if($shortcode_data['mapshare_identifier']) {					
			$Inreach_Mapshare_Inreach = new InMap_Inreach($shortcode_data);		

			$response_geojson_string = $Inreach_Mapshare_Inreach->response_geojson();
		
			//Joe_Assets::js_onready('console.log(' . $response_geojson_string . ');');
			Joe_Helper::debug($response_geojson_string, false);
		}	
	}	
}
new InMap_Shortcode;