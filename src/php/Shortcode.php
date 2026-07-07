<?php

class InMap_Shortcode {

	function __construct() {
		if ($shortcode = InMap_Config::get_item('plugin_shortcode')) {
			add_shortcode($shortcode, [$this, 'handle_shortcode']);
		}

		$this->load_assets();
	}

	function load_assets() {
		//Waymark CSS (MapLibre base styles, controls, popups)
		InMap_Assets::css_enqueue(InMap_Helper::asset_url('waymark.css'));

		//InMap CSS
		InMap_Assets::css_enqueue(InMap_Helper::asset_url('inreach-mapshare.css'));

		//Minimal map container styling
		InMap_Assets::css_inline('
			.inmap-wrap .inmap-map {
				min-height: 400px;
				width: 100%;
			}
		');
	}

	public function handle_shortcode($shortcode_data, $content = null) {
		InMap_Log::reset();

		$out = "\n" . '<!-- START ' . InMap_Config::get_name() . ' Shortcode -->' . "\n";
		$out .= '<div class="inmap-wrap">';

		$shortcode_data = shortcode_atts([
			'mapshare_identifier' => 'demo',
			'mapshare_password' => false,
			'mapshare_date_start' => false,
			'mapshare_date_end' => false,
			'mapshare_route_url' => false,
		], $shortcode_data, InMap_Config::get_item('plugin_shortcode'));

		if ($shortcode_data['mapshare_identifier']) {

			$Inreach_Mapshare = new InMap_Inreach($shortcode_data);

			//Error?
			if ($error = InMap_Log::in_error()) {
				InMap_Log::render_item($error, 'console');
				//Proceed
			} else {
				//Create *unqiue* Hash used to target Div
				$hash = InMap_Helper::make_hash(
					array_merge(
						$Inreach_Mapshare->get_parameters(),
						//Salty count
						[
							'count' => InMap_Log::get_data('shortcode_count'),
						]
					)
				);
				$map_div_id = 'inmap-' . $hash;
				InMap_Log::add(__('Rendering Map', InMap_Config::get_item('plugin_text_domain')) . ' (in Div #' . $map_div_id . ')', 'info', 'map_hash');

				$geojson = $Inreach_Mapshare->get_geojson();
				$has_tracking = (is_string($geojson) && ! empty($geojson));

				//Route?
				$route_geojson = null;
				$route_valid = false;
				$route_url = filter_var($shortcode_data['mapshare_route_url'], FILTER_VALIDATE_URL);
				if ($route_url && $route_geojson_raw = file_get_contents($route_url)) {

					//Valid
					if (json_decode($route_geojson_raw)) {
						$route_geojson = $route_geojson_raw;
						$route_valid = true;
						InMap_Log::add(__('Displaying route JSON.', InMap_Config::get_item('plugin_text_domain')), 'success', 'route_valid');
					} else {
						InMap_Log::add(__('Invalid route JSON.', InMap_Config::get_item('plugin_text_domain')), 'error', 'route_invalid');
					}
				}

				if ($has_tracking || $route_valid) {
					if ($has_tracking) {
						$point_count = $Inreach_Mapshare->point_count;
						$point_text = ($point_count == 1) ? __('Point', InMap_Config::get_item('plugin_text_domain')) : __('Points', InMap_Config::get_item('plugin_text_domain'));

						InMap_Log::add(sprintf(__('Displaying %s MapShare', InMap_Config::get_item('plugin_text_domain')), $point_count) . ' ' . $point_text, 'success', 'rendering_points');
					}

					if (! $has_tracking && $route_valid) {
						InMap_Log::add(__('Displaying route only — no tracking data.', InMap_Config::get_item('plugin_text_domain')), 'info', 'route_only');
					}

					$out .= '	<div id="' . $map_div_id . '" class="inmap-map"></div>';

				//Inline module — no jQuery dependency, no window globals
				$out .= '<script type="module">' . "\n";
				$out .= 'import { createMapInstance } from ' . json_encode(InMap_Helper::asset_url('create-map-instance.js')) . ";\n";
				$out .= 'await createMapInstance({' . "\n";
				$out .= '  hash: ' . json_encode($hash) . ",\n";
				$out .= '  geojson: ' . ($geojson ?: 'null') . ",\n";
				$out .= '  routeGeojson: ' . ($route_geojson ?: 'null') . ",\n";
				$out .= '  waymarkUrl: ' . json_encode(InMap_Helper::asset_url('waymark.js')) . ",\n";
				$out .= '  messageColour: ' . json_encode(InMap_Config::get_setting('appearance', 'colours', 'message_colour')) . ",\n";
				$out .= '  trackingColour: ' . json_encode(InMap_Config::get_setting('appearance', 'colours', 'tracking_colour')) . ",\n";
				$out .= '  routeColour: ' . json_encode(InMap_Config::get_setting('appearance', 'colours', 'route_colour')) . ",\n";
				$out .= '  basemapUrl: ' . json_encode(InMap_Config::get_setting('appearance', 'map', 'basemap_url')) . ",\n";
				$out .= '  basemapAttribution: ' . json_encode(html_entity_decode(InMap_Config::get_setting('appearance', 'map', 'basemap_attribution'), ENT_QUOTES | ENT_HTML5)) . ",\n";
				$out .= '  basemapTitle: ' . json_encode(InMap_Config::get_setting('appearance', 'map', 'basemap_title')) . ",\n";
				$out .= '  basemapOpacity: ' . json_encode(InMap_Config::get_setting('appearance', 'map', 'basemap_opacity')) . ",\n";
				$out .= '  basemapMaxzoom: ' . json_encode(InMap_Config::get_setting('appearance', 'map', 'basemap_maxzoom')) . ",\n";
				$out .= '});' . "\n";
				$out .= '</script>';

					//Increment call counter
					$shortcode_count = (int) InMap_Log::get_data('shortcode_count');
					$shortcode_count++;
					InMap_Log::set_data('shortcode_count', $shortcode_count);
				} else {
					InMap_Log::add(__('No tracking or route data to display.', InMap_Config::get_item('plugin_text_domain')), 'error', 'empty_data');
				}
			}
		} else {
			InMap_Log::add(__('MapShare Identifier not provided.', InMap_Config::get_item('plugin_text_domain')), 'error', 'missing_identifier');
		}

		$out .= '</div>';
		$out .= '<!-- END ' . InMap_Config::get_name() . ' Shortcode -->' . "\n\n";

		//Log?

		//Display Full log to admin
		if (InMap_Helper::do_debug() && current_user_can('administrator')) {
			InMap_Log::render();
			//Error?
		} elseif ($error = InMap_Log::in_error()) {
			InMap_Log::render_item($error);
		}

		return $out;
	}
}