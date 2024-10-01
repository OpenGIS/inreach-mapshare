<?php

class InMap_Inreach extends InMap_Class {

	// private $request_endpoint = 'https://explore.garmin.com/feed/share/';
	private $request_endpoint = 'https://share.garmin.com/feed/share/';

	private $request_data = [];

	private $cache_id = '';
	private $cache_response = [];

	private $request_string = '';
	private $response_string = '';

	private $KML = null;
	private $Placemarks = [];
	private $FeatureCollection = [];

	public $point_count = 0;

	function __construct($params_in = null) {
		//Set parameters
		$this->parameters = [
			'mapshare_identifier' => null,
			'mapshare_password' => null,
			'mapshare_date_start' => null,
			'mapshare_date_end' => null,
		];

		parent::__construct($params_in);

		InMap_Log::reset();
		foreach ([
			'setup_request',
			'execute_request',
			'process_kml',
			'build_geojson',
		] as $call) {
			//Stop if error
			if ($log = InMap_Log::in_error()) {
				InMap_Log::render();

				return;
			}

			$this->$call();
		}
	}

	function execute_request() {
		//Request is setup
		if ($this->cache_id) {
			//Cached response																			 			 ** GET STALE!
			$this->cache_response = InMap_Cache::get_item($this->cache_id, true);

			//Fresh
			if ($this->cache_response && $this->cache_response['status'] == 'fresh') {
				InMap_Log::add(__('Response retrieved from Cache.', InMap_Config::get_item('plugin_text_domain')), 'info', 'cache_fresh');

				$this->response_string = $this->cache_response['value'];
				//Nothing fresh...
			} else {
				//Setup call
				$request_data = [];

				if ($auth_password = $this->get_parameter('mapshare_password')) {
					$request_data = [
						'headers' => [
							//Password only
							'Authorization' => 'Basic ' . base64_encode(':' . $auth_password),
						],
					];
				}

				//https://developer.wordpress.org/plugins/http-api/
				$response = wp_remote_get($this->request_string, $request_data);

				//Request success?
				if (!is_wp_error($response)) {
					$response_code = wp_remote_retrieve_response_code($response);

					switch (true) {
					//Success
					case strpos($response_code, '2') === 0:
						$response_string = wp_remote_retrieve_body($response);

						//Content has length
						if (!empty($response_string)) {
							//MUST BE VALID KML RESPONSE
							if (is_string($response_string) && @simplexml_load_string($response_string)) {
								$this->response_string = $response_string;

								//Insert into cache
								InMap_Cache::set_item($this->cache_id, $response_string);

								InMap_Log::add(__('Garmin provided a valid KML response, which has been added to Cache.', InMap_Config::get_item('plugin_text_domain')), 'info', 'response_cached');
							} else {
								InMap_Log::add(__('Received invalid KML response from Garmin. Check your MapShare Settings', InMap_Config::get_item('plugin_text_domain')), 'error', 'invalid_kml');
							}
							//Invalid identifier
						} else {
							InMap_Log::add(__('Garmin does not recognise this MapShare Identifier.', InMap_Config::get_item('plugin_text_domain')), 'error', 'identifier');
						}

						break;
					//Fail
					case $response_code == '401':
						InMap_Log::add(__('There was a problem with your MapShare Password.', InMap_Config::get_item('plugin_text_domain')), 'error', 'error_password');

						break;
					//Other
					default:
						InMap_Log::add(sprintf(__('Garmin returned a %s error.', InMap_Config::get_item('plugin_text_domain')), $response_code), 'error', 'error_' . $response_code);

						break;
					}
				}
			}
		}

		//We have no response
		if (!$this->response_string) {
			//Check for stale cache
			if ($this->cache_response && $this->cache_response['status'] == 'stale') {
				InMap_Log::add(sprintf(__('Unable to get updated KML from Garmin. Last update: %s minutes ago.', InMap_Config::get_item('plugin_text_domain')), round($this->cache_response['minutes'])), 'warning', 'cache_stale');

				//Better than nothing
				$this->response_string = $this->cache_response['value'];
				//No cache either
			} else {
				InMap_Log::add(__('Garmin provided an empty response. Check your MapShare Settings.', InMap_Config::get_item('plugin_text_domain')), 'error', 'empty_response');
			}
		}
	}

	function setup_request() {
		//Required
		$url_identifier = $this->get_parameter('mapshare_identifier');

		//Required
		if (!$url_identifier) {
			InMap_Log::add(__('No MapShare identifier provided.', InMap_Config::get_item('plugin_text_domain')), 'error', 'missing_identifier');

			return false;
			//Load Demo
		} elseif ($url_identifier == 'demo') {
			$demo_kml = file_get_contents(InMap_Helper::asset_url('geo/demo.kml'));

			if ($demo_kml) {
				$this->response_string = $demo_kml;
				InMap_Log::add(__('Demo mode enabled!', InMap_Config::get_item('plugin_text_domain')), 'info', 'do_demo');
			} else {
				InMap_Log::add(__('Unable to read Demo KML.', InMap_Config::get_item('plugin_text_domain')), 'warning', 'demo_kml_unreadable');
			}

			return true;
		}

		//Password warning
		if ($this->get_parameter('mapshare_password')) {
			InMap_Log::add(sprintf(__('Remember that you are responsible for <a%s>protecting access</a> if needed!', InMap_Config::get_item('plugin_text_domain')), ' href=\"https://wordpress.org/support/article/using-password-protection/\"'), 'warning', 'password_set');
		}

		//Start building the request
		$this->request_string = $this->request_endpoint . $url_identifier;

		//Start date
		if ($data_start = $this->get_parameter('mapshare_date_start')) {
			$this->request_data['d1'] = $this->get_parameter('mapshare_date_start');
		}

		//End date
		if ($data_end = $this->get_parameter('mapshare_date_end')) {
			$this->request_data['d2'] = $this->get_parameter('mapshare_date_end');
		}

		//Open-ended request warning
		if ($data_start && !$data_end) {
			InMap_Log::add(__('Be careful when creating Shortcodes with no end date. <strong>All future MapShare data will be displayed!</strong>', InMap_Config::get_item('plugin_text_domain')), 'warning', 'no_end_date');
		}

		//Append data
		if (sizeof($this->request_data)) {
			$this->request_string .= '?';
			$this->request_string .= http_build_query($this->request_data);
		}

		//Determine cache ID
		$this->cache_id = md5(json_encode($this->get_parameters()));

		InMap_Log::add($this->request_string, 'info', 'request_ready');

		return true;
	}

	function get_geojson($response_type = 'string') {
		if ($response_type == 'string') {
			return json_encode($this->FeatureCollection);
		}

		return $this->FeatureCollection;
	}

	function process_kml() {
		//Do we have a response?
		if (is_string($this->response_string) && simplexml_load_string($this->response_string)) {
			$this->KML = simplexml_load_string($this->response_string);

			$this->process_points();

			if ($this->point_count) {
				$point_text = ($this->point_count == 1) ? __('Point', InMap_Config::get_item('plugin_text_domain')) : __('Points', InMap_Config::get_item('plugin_text_domain'));

				InMap_Log::add(__('The KML response contains ' . $this->point_count . ' ' . $point_text . '.', InMap_Config::get_item('plugin_text_domain')), 'info', 'has_points');
			} else {
				InMap_Log::add(__('The KML response contains no Points.', InMap_Config::get_item('plugin_text_domain')), 'error', 'no_points');
			}
		} else {
			InMap_Log::add(__('The KML response is invalid.', InMap_Config::get_item('plugin_text_domain')), 'error', 'empty_kml');
		}
	}

	function process_points() {
		if ($this->point_count) {
			return $this->point_count;
		}

		if (
			is_object($this->KML)
			&& isset($this->KML->Document->Folder->Placemark)
			&& is_iterable($this->KML->Document->Folder->Placemark)
		) {
			foreach ($this->KML->Document->Folder->Placemark as $Placemark) {
				if ($Placemark->Point->coordinates) {
					// Coordinates (WSG84)
					$coords = $Placemark->Point->coordinates;
					$coords = explode(',', $coords);
					$coords = $this->fuzz_coordinates($coords);
					$Placemark->Point->coordinates = implode(',', $coords);

					// Extended Data
					for ($j = 0; $j < sizeof($Placemark->ExtendedData->Data); $j++) {
						$key = (string) $Placemark->ExtendedData->Data[$j]->attributes()->name;
						$value = (string) $Placemark->ExtendedData->Data[$j]->value;

						//By Key
						switch ($key) {
						case 'Latitude':
							$Placemark->ExtendedData->Data[$j]->value = $coords[1];

							break;
						case 'Longitude':
							$Placemark->ExtendedData->Data[$j]->value = $coords[0];
							break;

						}
					}

					// Store
					$this->Placemarks[] = $Placemark;

					// Count
					$this->point_count++;
				}
			}
		}

		return $this->point_count;
	}

	// WSG84
	function fuzz_coordinates($coords) {
		$precision = InMap_Config::get_setting('advanced', 'response', 'precision');

		// Default, no fuzz
		if ($precision == '6') {
			return $coords;
		}

		$coords[1] = round($coords[1], $precision);
		$coords[0] = round($coords[0], $precision);

		return $coords;
	}

	function build_geojson() {
		$this->FeatureCollection = [
			'type' => 'FeatureCollection',
			'features' => [],
		];

		//We have Points
		if ($this->point_count) {
			//Each Placemark
			for ($i = 0; $i < sizeof($this->KML->Document->Folder->Placemark); $i++) {
				$Placemark = $this->KML->Document->Folder->Placemark[$i];

				//Create Feature
				$Feature = [
					'type' => 'Feature',
					'properties' => [
						'className' => 'inmap-info-item',
					],
					'geometry' => [],
				];

				// =========== Point ===========

				if ($Placemark->Point->coordinates) {
					$class_append = [];

					//Demo!
					if (!InMap_Log::has('do_demo')) {
						$time_ago = InMap_Helper::time_ago(strtotime($Placemark->TimeStamp->when));
					} else {
						$class_append[] = 'inmap-demo';

						$time_ago = InMap_Helper::time_ago(strtotime($Placemark->TimeStamp->when), strtotime('5/21/2022 11:04:30 PM'));
					}

					//Coordinates
					$coordinates = explode(',', (String) $Placemark->Point->coordinates);

					//Invalid
					if (sizeof($coordinates) < 2 || sizeof($coordinates) > 3) {
						continue;
					}

					$Feature['geometry']['type'] = 'Point';
					$Feature['geometry']['coordinates'] = $coordinates;

					//Extended Data?
					if (isset($Placemark->ExtendedData)) {
						if (sizeof($Placemark->ExtendedData->Data)) {
							$extended_data = [];

							//Each
							for ($j = 0; $j < sizeof($Placemark->ExtendedData->Data); $j++) {
								$key = (string) $Placemark->ExtendedData->Data[$j]->attributes()->name;

								//Must be a key we are interested in
								if (in_array($key, InMap_Config::get_item('kml_data_include'))) {
									$value = (string) $Placemark->ExtendedData->Data[$j]->value;

									//By Key
									switch ($key) {
									case 'Id':
										$Feature['properties']['id'] = $value;

										$extended_data[$key] = $value;

										break;

									case 'Text':
										//Skip empty text
										if (!empty($value)) {
											$extended_data[$key] = $value;
										}

										break;
									default:
										$extended_data[$key] = $value;

										break;
									}
								}
							}
						}
					}

					//Demo Time
					if (InMap_Log::has('do_demo')) {
						$key = esc_attr__('Demo', InMap_Config::get_item('plugin_text_domain'));
						$extended_data[$key] = esc_attr__('This is a demo!', InMap_Config::get_item('plugin_text_domain'));
					}

					//Title
					$title = '[' . ($i + 1) . '/' . $this->point_count . ']';
					if (isset($Placemark->TimeStamp->when)) {
						$title .= $time_ago;
					}
					$Feature['properties']['title'] = $title;

					//Classes
					//First (oldest)
					if ($i === 0) {
						$class_append[] = 'inmap-first';

						//**Only**
						if ($this->point_count === 1) {
							$class_append[] = 'inmap-last inmap-active inmap-only';
							$Feature['properties']['title'] = '[' . __('Latest', InMap_Config::get_item('plugin_text_domain')) . ']';
							//First
						} else {
							$Feature['properties']['title'] = '[' . __('First', InMap_Config::get_item('plugin_text_domain')) . ']';
						}

						//Most recent
						$Feature['properties']['title'] .= $time_ago;
						//Last - *LATEST*
					} elseif (
						//EOF array
						$i === sizeof($this->KML->Document->Folder->Placemark) - 2
					) {
						//Active
						$class_append[] = 'inmap-last inmap-active';

						//Most recent
						$Feature['properties']['title'] = '[' . __('Latest', InMap_Config::get_item('plugin_text_domain')) . ']';
						$Feature['properties']['title'] .= $time_ago;
					}

					//By event
					if (isset($extended_data['Event'])) {
						//Remove periods!
						$extended_data['Event'] = trim($extended_data['Event'], '.');

						switch ($extended_data['Event']) {
						case 'Msg to shared map received':
							$class_append[] = 'inmap-icon-message inmap-icon-custom';

							break;
						case 'Quick Text to MapShare received':
							$class_append[] = 'inmap-icon-message inmap-icon-quick';

							break;
						case 'Tracking turned on from device':
						case 'Tracking turned off from device':
						case 'Tracking interval received':
						case 'Tracking message received':
						default:

							//Valid GPS
							if (isset($extended_data['Valid GPS Fix']) && 'True' === $extended_data['Valid GPS Fix']) {
								$class_append[] = 'inmap-icon-gps';
							}

							break;
						}

						//Classes
						$icon_class = 'inmap-icon';
						foreach ($class_append as $append) {
							$Feature['properties']['className'] .= ' ' . $append;
							$icon_class .= ' ' . $append;
						}

						//Icon
						$Feature['properties']['icon'] = [
							'className' => 'inmap-marker',
							'iconSize' => [15, 15],
							'html' => '<div class="' . $icon_class . '"></div>',
						];

						//Description
						$description = '<div class="inmap-info-desc">';
						$description .= '<div class="inmap-info-title">' . $Feature['properties']['title'] . '</div>';

						//We have data
						if (sizeof($extended_data)) {
							$description .= InMap_Helper::assoc_array_table($extended_data);

							$description .= '<div class="inmap-info-expand">' . __('More detail', InMap_Config::get_item('plugin_text_domain')) . ' +</div>';
						}
						$description .= '</div>';

						$Feature['properties']['description'] = $description;
					}

					// =========== LineString ===========

				} elseif ($Placemark->LineString->coordinates) {
					$coordinates = (string) $Placemark->LineString->coordinates;
					$coordinates = preg_split('/\r\n|\r|\n/', $coordinates);

					//Valid array
					if (sizeof($coordinates)) {

						$Feature['geometry']['type'] = 'LineString';

						//Each Coordinate
						foreach ($coordinates as $point) {
							$coords = explode(',', $point);

							//Invalid
							if (sizeof($coords) < 2 || sizeof($coords) > 3) {
								continue;
							}

							// Fuzz
							$coords = $this->fuzz_coordinates($coords);

							$Feature['geometry']['coordinates'][] = $coords;
						}
					}
				}

				//Style
				$Feature['properties']['style']['weight'] = 2;
				$Feature['properties']['style']['color'] = InMap_Config::get_setting('appearance', 'colours', 'tracking_colour');

				$this->FeatureCollection['features'][] = $Feature;
			}

			//Reverse order (most recent first)
			$this->FeatureCollection['features'] = array_reverse($this->FeatureCollection['features']);
			//No points in KML
		} else {
			InMap_Log::add(__('The KML response contains no Points.', InMap_Config::get_item('plugin_text_domain')), 'error', 'no_points');
		}
	}
}