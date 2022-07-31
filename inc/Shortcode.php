<?php

class InMap_Shortcode extends Joe_Shortcode {

	function __construct() {
		parent::__construct();

		$this->load_assets();
	}
	
	function load_assets() {
		//Leaflet CSS
		Joe_Assets::css_enqueue(Joe_Helper::plugin_url('assets/css/leaflet.css'));	

		//InMap CSS
		$primary_colour = Joe_Config::get_setting('appearance', 'colours', 'tracking_colour');
		if($primary_colour) {
			Joe_Assets::css_inline('
				.inmap-wrap .inmap-info .inmap-info-item.inmap-last,
				.inmap-wrap .inmap-info .inmap-info-item.inmap-active .inmap-icon,
				.inmap-wrap .inmap-map .inmap-marker.inmap-icon-message .inmap-icon,
				.inmap-wrap .inmap-map .inmap-marker.inmap-hover .inmap-icon,
				.inmap-wrap .inmap-map .inmap-marker.inmap-active .inmap-icon,
				.inmap-wrap .inmap-map .inmap-marker,
				.inmap-wrap .inmap-icon {
					background-color: ' . $primary_colour . ';
				}
				.inmap-wrap .inmap-info .inmap-info-item.inmap-active .inmap-info-desc .inmap-info-title,
				.inmap-wrap .inmap-info .inmap-info-item.inmap-active .inmap-info-title,
				.inmap-wrap .inmap-info .inmap-info-item .inmap-info-desc,
				.inmap-wrap .inmap-map .inmap-marker.inmap-icon-message,
				.inmap-wrap .inmap-map .inmap-marker.inmap-hover,
				.inmap-wrap .inmap-map .inmap-marker.inmap-active {
					border-color: ' . $primary_colour . ';
				}
				.inmap-wrap .inmap-info .inmap-info-item.inmap-active.inmap-hide-extended .inmap-info-desc .inmap-info-expand {
					color: ' . $primary_colour . ';
				}
			');		
		}
		Joe_Assets::css_enqueue(Joe_Helper::plugin_url('assets/css/shortcode.min.css'));	

		//Leaflet JS
		Joe_Assets::js_enqueue([
			'id' => 'leaflet_js',
			'url' => Joe_Helper::plugin_url('assets/js/leaflet.js'),
			'deps' => [ 'jquery' ]
		]);

		//InMap JS
		Joe_Assets::js_enqueue([
			'id' => 'inmap_shortcode_js',
			'url' => Joe_Helper::plugin_url('assets/js/shortcode.min.js'),
			'deps' => [ 'leaflet_js' ],
			'data' => [
				'basemap_url' => Joe_Config::get_setting('appearance', 'map', 'basemap_url'),
				'basemap_attribution' => Joe_Config::get_setting('appearance', 'map', 'basemap_attribution')			
			]
		]);		
	}

	public function handle_shortcode($shortcode_data, $content = null) {
		Joe_Log::reset();
		
		$out = "\n" . '<!-- START ' . Joe_Config::get_name() . ' Shortcode -->' . "\n";
		$out .= '<div class="inmap-wrap">';
	
		$shortcode_data = shortcode_atts(array(
			'mapshare_identifier' => 'demo',
			'mapshare_password' => false,
			'mapshare_date_start' => false,
			'mapshare_date_end' => false
		), $shortcode_data, Joe_Config::get_item('plugin_shortcode'));
	
		if($shortcode_data['mapshare_identifier']) {					
			
			$Inreach_Mapshare = new InMap_Inreach($shortcode_data);		
			
			//Error?
			if($error = Joe_Log::in_error()) {
				Joe_Log::render_item($error, 'console');			
			//Proceed
			} else {
				//Create *unqiue* Hash used to target Div
				$hash = Joe_Helper::make_hash(
					array_merge(
						$Inreach_Mapshare->get_parameters(),
						//Salty count
						[
							'count' => Joe_Log::get_data('shortcode_count')						
						]
					)
				);
				$map_div_id = 'inmap-' . $hash;
				Joe_Log::add('Rendering Map in Div #' . $map_div_id, 'info', 'map_hash');				
				
				
				$geojson = $Inreach_Mapshare->get_geojson();

				if(is_string($geojson) && ! empty($geojson)) {		
					Joe_Log::add(sprintf('Displaying %s MapShare Points.', $Inreach_Mapshare->get_point_count()), 'success', 'rendering_points');
						
					//JS
					Joe_Assets::js_onready('
						inmap_create_map(
							"' . $hash . '",
							' . $geojson . '
						);
					');
			
					$out .= '	<div id="' . $map_div_id . '" class="inmap-map"></div>';
					$out .= '	<div class="inmap-info"></div>';
					
					//Increment call counter
					$shortcode_count = (int)Joe_Log::get_data('shortcode_count');
					$shortcode_count++;
					Joe_Log::set_data('shortcode_count', $shortcode_count);				
				} else {
					Joe_Log::add('GeoJSON contains no Points.', 'error', 'empty_geojson');				
				}
			}
		}	else {
			Joe_Log::add('MapShare Identifier not provided.', 'error', 'missing_identifier');
		}

		$out .= '</div>';
		$out .= '<!-- END ' . Joe_Config::get_name() . ' Shortcode -->' . "\n\n";
		
		//Log?
		
		//Display Full log to admin
		if(current_user_can('administrator')) {
			Joe_Log::render();
		//Error?
		} elseif($error = Joe_Log::in_error()) {
			Joe_Log::render_item($error);
		}

		return $out;
	}	
}