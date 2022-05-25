<?php

class Inreach_Mapshare_Inreach_Front {
	
	function __construct() {
		if(! is_admin()) {
			add_shortcode(Inreach_Mapshare_Config::get_item('shortcode'), array($this, 'shortcode_handler'));
		}

		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
	}
	
	function enqueue_scripts() {
		$plugin_base = plugin_dir_url('') . Inreach_Mapshare_Config::get_item('plugin_slug');
		
		//CSS
		$ol_css_url = $plugin_base . '/assets/openlayers/ol.css';
	  wp_enqueue_style(Inreach_Mapshare_Config::get_item('plugin_slug') . '-ol', $ol_css_url);
	  
	  //JS
		$ol_js_url = $plugin_base . '/assets/openlayers/ol.js';
	  wp_enqueue_script(Inreach_Mapshare_Config::get_item('plugin_slug') . '-ol', $ol_js_url);
	}
	
	function shortcode_handler($attributes) {
		$out = '';
		
		require_once('Inreach_Mapshare_Inreach.php');

		//Get data
		$attributes = shortcode_atts(array(
			'mapshare_identifier' => false,
			'mapshare_password' => false,
			'mapshare_date_start' => false,
			'mapshare_date_end' => false
		), $attributes, Inreach_Mapshare_Config::get_item('shortcode'));
		
		//Required	
		if($attributes['mapshare_identifier']) {					
			$map_id = 'inreach-mapshare-map-' . md5(json_encode($attributes));
			
			//Make request
			$Inreach_Mapshare_Inreach = new Inreach_Mapshare_Inreach($attributes);		
			$response_geojson_string = $Inreach_Mapshare_Inreach->response_geojson();

			$out .= '<div id="' . $map_id . '" class="inreach-mapshare-map" style="height:300px"></div>' . "\n";
	    $out .= '<script type="text/javascript">

const marker = new CircleStyle({
  radius: 5,
  fill: null,
  stroke: new Stroke({color: "red", width: 1}),
});

const styles = {
  "Point": new Style({
    image: image,
  }),
  "LineString": new Style({
    stroke: new Stroke({
      color: "red",
      width: 1,
    }),
  }),
};

const styleFunction = function (feature) {
  return styles[feature.getGeometry().getType()];
};

const geojsonObject = ' . $response_geojson_string . ';

const vectorSource = new VectorSource({
  features: new GeoJSON().readFeatures(geojsonObject),
});

const vectorLayer = new VectorLayer({
  source: vectorSource,
  style: styleFunction,
});

const map = new Map({
  layers: [
    new TileLayer({
      source: new OSM(),
    }),
    vectorLayer,
  ],
  target: "' . $map_id . '",
  view: new View({
    center: [0, 0],
    zoom: 2,
  }),
});
			</script>' . "\n";
		
// 			Waymark_JS::add_call("
// 				setTimeout(function() {
// 					var waymark_container = jQuery('.waymark-map').first();
// 
// 					if(typeof waymark_container === 'object') {			
// 						var Waymark_Instance = waymark_container.data('Waymark');
// 
// 						if(typeof Waymark_Instance === 'object') {
// 							Waymark_Instance.load_json(" . $response_geojson_string . ", true);
// 						}		
// 					}	
// 				}, 250);
// 			");
		}
	
		return $out;
	}
}
new Inreach_Mapshare_Inreach_Front;