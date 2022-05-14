<?php

class Beast_Inreach_Front {
	
	function __construct() {
		add_shortcode('Feed_Beast', array($this, 'shortcode_handler'));
	}
	
	function shortcode_handler($attributes) {
		require_once('Beast_Inreach.php');

		$attributes = shortcode_atts(array(
			'mapshare_identifier' => false,
			'mapshare_password' => false,
		), $attributes, 'feed_beast');
	
		if($attributes['mapshare_identifier']) {					
			echo do_shortcode('[Waymark]');
		
			$Beast_Inreach = new Beast_Inreach($attributes);
			$response_geojson_string = $Beast_Inreach->response_geojson();
		
			Waymark_JS::add_call("
				setTimeout(function() {
					var waymark_container = jQuery('.waymark-map').first();

					if(typeof waymark_container === 'object') {			
						var Waymark_Instance = waymark_container.data('Waymark');

						if(typeof Waymark_Instance === 'object') {
							Waymark_Instance.load_json(" . $response_geojson_string . ", true);
						}		
					}	
				}, 250);
			");
		}
	}
}
new Beast_Inreach_Front;