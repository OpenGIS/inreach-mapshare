<?php

class InMap_Inreach extends Joe_Class {

	private $request_endpoint = 'https://explore.garmin.com/feed/share/';
	
	private $request_data = [];

	private $cache_id = '';	
	private $cache_response = [];
	
	private $request_string = '';
	private $response_string = '';

	private $KML = null;
	private $point_count = 0;
	private $FeatureCollection = [];
	
	function __construct($params_in = null) {
		//Set parameters
		$this->parameters = [
			'mapshare_identifier' => null,
			'mapshare_password' => null,
			'mapshare_date_start' => null,
			'mapshare_date_end' => null,									
		];
					
		parent::__construct($params_in);

		Joe_Log::reset();
		foreach([
			'setup_request',
			'execute_request',
			'process_kml',
			'build_geojson',
		] as $call) {
			//Stop if error
			if($log = Joe_Log::in_error()) {
				Joe_Log::render();

				return;
			}

			$this->$call();			
		}
	}

	function execute_request() {
		//Request is setup
		if($this->cache_id) {
			//Cached response																			 			 ** GET STALE!
			$this->cache_response = Joe_Cache::get_item($this->cache_id, true);
			
			//Fresh
			if($this->cache_response && $this->cache_response['status'] == 'fresh') {
				Joe_Log::add('Response retrieved from Cache.', 'info', 'cache_fresh');

	 			$this->response_string = $this->cache_response['value'];			
			//Nothing fresh...
			} else {
				//Setup call
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $this->request_string);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

				if($auth_password = $this->get_parameter('mapshare_password')) {
					curl_setopt($ch, CURLOPT_USERPWD, ":" . $auth_password);	//No username			
				}

				//Run it
				curl_exec($ch);

				//cURL success?
				if(! curl_errno($ch)) {
					$response_info = curl_getinfo($ch);

					switch(true) {
						//Success
						case strpos($response_info['http_code'], '2') === 0 :
							$response_string = curl_multi_getcontent($ch);
							
							//Content has length
							if(! empty($response_string)) {
								//MUST BE VALID KML RESPONSE
								if(is_string($response_string) && @simplexml_load_string($response_string)) {								
						 			$this->response_string = $response_string;			

									//Insert into cache
									Joe_Cache::set_item($this->cache_id, $response_string);

									Joe_Log::add('Garmin provided a valid KML response, which has been added to Cache.', 'info', 'response_cached');									
								} else {
									Joe_Log::add('Received invalid KML response from Garmin. Check your MapShare Settings', 'error', 'invalid_kml');
								}				
							//Invalid identifier
							} else {
								Joe_Log::add('Garmin does not recognise this MapShare Identifier.', 'error', 'identifier');
							}
					
							break;
						//Fail
						case $response_info['http_code'] == '401' :
							Joe_Log::add('There was a problem with your MapShare Password.', 'error', 'error_password');

							break;
						//Other
						default :
							Joe_Log::add('Garmin returned a ' . $response_info['http_code'] . ' error.', 'error', 'error_' . $response_info['http_code']);

							break;
					}
			
					curl_close($ch);
				}
			}	
		}
		
		//We have no response
		if(! $this->response_string) {
			//Check for stale cache
			if($this->cache_response && $this->cache_response['status'] == 'stale') {
				Joe_Log::add(sprintf('Unable to get updated KML from Garmin. Last update: %s minutes ago.', round($this->cache_response['minutes'])), 'warning', 'cache_stale');

				//Better than nothing
	 			$this->response_string = $this->cache_response['value'];			
			//No cache either
			} else {
				Joe_Log::add('Garmin provided an empty response. Check your MapShare Settings.', 'error', 'empty_response');			
			}
		}
	}

	function setup_request() {
		//Required
		$url_identifier = $this->get_parameter('mapshare_identifier');
		
		//Required
		if(! $url_identifier) {
			Joe_Log::add('No MapShare identifier provided.', 'error', 'missing_identifier');
		
			return false;		
		//Load Demo
		} elseif($url_identifier == 'demo') {
			$demo_kml = file_get_contents(Joe_Helper::asset_url('geo/demo.kml'));
			
			if($demo_kml) {
				$this->response_string = $demo_kml;
				Joe_Log::add('Demo mode enabled!', 'info', 'do_demo');
			} else {
				Joe_Log::add('Unable to read Demo KML.', 'warning', 'demo_kml_unreadable');			
			}
			
			return true;		
		}
		
		//No password warning
		if($this->get_parameter('mapshare_password')) {
			Joe_Log::add('Don\'t forget that you are responsible for <a href=\"https://wordpress.org/support/article/using-password-protection/\">protecting access</a> if needed!', 'warning', 'password_set');
		}		

		//Start building the request
		$this->request_string = $this->request_endpoint . $url_identifier;

		//Start date
		if($data_start = $this->get_parameter('mapshare_date_start')) {
			$this->request_data['d1'] = $this->get_parameter('mapshare_date_start');
		}

		//End date
		if($data_end = $this->get_parameter('mapshare_date_end')) {
			$this->request_data['d2'] = $this->get_parameter('mapshare_date_end');
		}
		
		//Open-ended request
		if($data_start && ! $data_end) {
			Joe_Log::add('Be careful when creating Shortcodes with no end date. <strong>All future MapShare data will be displayed!</strong>', 'warning', 'no_end_date');
		}
		
		//Append data
		if(sizeof($this->request_data)) {
			$this->request_string .= '?';
			$this->request_string .= http_build_query($this->request_data);
		}	

		//Determine cache ID
		$this->cache_id = md5(json_encode($this->get_parameters()));

		Joe_Log::add($this->request_string, 'info', 'request_ready');
		
		return true;
	}	

	function get_geojson($response_type = 'string') {
		if($response_type == 'string') {
			return json_encode($this->FeatureCollection);
		}
		
		return $this->FeatureCollection;		
	}

	function process_kml() {
		//Do we have a response?
		if(is_string($this->response_string) && simplexml_load_string($this->response_string)) {								
			$this->KML = simplexml_load_string($this->response_string);
			$this->get_point_count();
			
			if($this->point_count) {
				$point_text = ($this->point_count == 1) ? __('Point', Joe_Config::get_item('plugin_text_domain')) : __('Points', Joe_Config::get_item('plugin_text_domain'));
				
				Joe_Log::add('The KML response contains ' . $this->point_count . ' ' . $point_text . '.', 'info', 'has_points');			
			} else {
				Joe_Log::add('The KML response contains no Points.', 'error', 'no_points');			
			}
		} else {
			Joe_Log::add('The KML response is invalid.', 'error', 'empty_kml');
		}
	}
	
	function build_geojson() {
		$this->FeatureCollection = [
			'type' => 'FeatureCollection',
			'features' => []
		];
		
		//We have Points
		if($this->point_count) {
			//Each Placemark
			for($i = 0; $i < sizeof($this->KML->Document->Folder->Placemark); $i++) {
				$Placemark = $this->KML->Document->Folder->Placemark[$i];
				
				//Create Feature
				$Feature = [
					'type' => 'Feature',
					'properties' => [
						'className' => 'inmap-info-item'
					],
					'geometry' => []
				];

				// =========== Point ===========
				
				if($Placemark->Point->coordinates) {
					$class_append = [];
							
					if(! Joe_Log::has('do_demo')) {
						$time_ago = Joe_Helper::time_ago(strtotime($Placemark->TimeStamp->when));
					} else {				
						$class_append[] = 'inmap-demo';
						
						$time_ago = Joe_Helper::time_ago(strtotime($Placemark->TimeStamp->when), strtotime('5/21/2022 11:04:30 PM'));
					}
				
					//Coordinates
					$coordinates = explode(',', (String)$Placemark->Point->coordinates);																

					//Invalid
					if(sizeof($coordinates) < 2 || sizeof($coordinates) > 3) {
						continue;						
					}
					
					$Feature['geometry']['type'] = 'Point';
					$Feature['geometry']['coordinates'] = $coordinates;

					//Extended Data?
					if(isset($Placemark->ExtendedData)) {
						if(sizeof($Placemark->ExtendedData->Data)) {
							$extended_data = [];
						
							//Each
							for($j = 0; $j < sizeof($Placemark->ExtendedData->Data); $j++) {
								$key = (string)$Placemark->ExtendedData->Data[$j]->attributes()->name;
							
								//Must be a key we are interested in
								if(in_array($key, Joe_Config::get_item('kml_data_include'))) {
									$value = (string)$Placemark->ExtendedData->Data[$j]->value;

									//By Key
									switch($key) {
										case 'Id' :
											$Feature['properties']['id'] = $value;

											$extended_data[$key] = $value;																

											break;

										case 'Text' :
											//Skip empty text
											if(! empty($value)) {
												$extended_data[$key] = $value;																
											}

											break;
										default :
											$extended_data[$key] = $value;																

											break;
									}
								}								
							}						
						}
					}
					
					//Demo Time
					if(Joe_Log::has('do_demo')) {
						$key = esc_attr__('Demo', Joe_Config::get_item('plugin_text_domain'));
						$extended_data[$key] = esc_attr__('This is a demo!', Joe_Config::get_item('plugin_text_domain'));																					
					}					
					
					//Title
					$title = '[' . ($i + 1) . '/' . $this->point_count . ']';
					if(isset($Placemark->TimeStamp->when)) {
						$title .= $time_ago;						
					}
					$Feature['properties']['title'] = $title;
					
					//Classes
					//First (oldest)
					if($i === 0) {
						$class_append[] = 'inmap-first';

						//**Only**
						if($this->point_count === 1) {
							$class_append[] = 'inmap-last inmap-active inmap-only';
							$Feature['properties']['title'] = '[' . __('Latest', Joe_Config::get_item('plugin_text_domain')) . ']';
						//First
						} else {
							$Feature['properties']['title'] = '[' . __('First', Joe_Config::get_item('plugin_text_domain')) . ']';						
						}

						//Most recent
						$Feature['properties']['title'] .= $time_ago;	
					//Last - *LATEST*
					} elseif(
						//EOF array
						$i === sizeof($this->KML->Document->Folder->Placemark) - 2
					) {
						//Active
						$class_append[] = 'inmap-last inmap-active';

						//Most recent
						$Feature['properties']['title'] = '[' . __('Latest', Joe_Config::get_item('plugin_text_domain')) . ']';
						$Feature['properties']['title'] .= $time_ago;						
					}					

					//By event
					if(isset($extended_data['Event'])) {
						//Remove periods!
						$extended_data['Event'] = trim($extended_data['Event'], '.');

						switch($extended_data['Event']) {
							case 'Msg to shared map received' :
								$class_append[] = 'inmap-icon-message inmap-icon-custom';
			
								break;
							case 'Quick Text to MapShare received' :
								$class_append[] = 'inmap-icon-message inmap-icon-quick';
								
								break;
							case 'Tracking turned on from device' :
							case 'Tracking turned off from device' :
							case 'Tracking interval received' :
							case 'Tracking message received' :
 							default : 							

								//Valid GPS
								if(isset($extended_data['Valid GPS Fix']) && 'True' === $extended_data['Valid GPS Fix']) {
									$class_append[] = 'inmap-icon-gps';
								}

								break;
						}

						//Classes
						$icon_class = 'inmap-icon';
						foreach($class_append as $append) {
							$Feature['properties']['className'] .= ' ' . $append;
							$icon_class .= ' ' . $append;
						}

						//Icon
						$Feature['properties']['icon'] = [
							'className' => 'inmap-marker',
							'iconSize' => [ 15, 15 ],
							'html' => '<div class="' . $icon_class . '"></div>'
						];

						//Description
						$description = '<div class="inmap-info-desc">';
						$description .= '<div class="inmap-info-title">' . $Feature['properties']['title'] . '</div>';
					
						//We have data														
						if(sizeof($extended_data)) {
							$description .= Joe_Helper::assoc_array_table($extended_data);
	
							$description .= '<div class="inmap-info-expand">' . __('More detail +', Joe_Config::get_item('plugin_text_domain'))  . '</div>';
						}
						$description .= '</div>';

						$Feature['properties']['description'] = $description;																
					}

				// =========== LineString ===========
				
				} elseif($Placemark->LineString->coordinates) {
					$coordinates = (string)$Placemark->LineString->coordinates;
					$coordinates = preg_split('/\r\n|\r|\n/', $coordinates);
					
					//Valid array
					if(sizeof($coordinates)) {

						$Feature['geometry']['type'] = 'LineString';

						//Each Coordinate
						foreach($coordinates as $point) {
							$coords = explode(',', $point);													
					
							//Invalid
							if(sizeof($coords) < 2 || sizeof($coords) > 3) {
								continue;						
							}	

							$Feature['geometry']['coordinates'][] = $coords;
						}
					}
				}
				
				//Style
				$Feature['properties']['style']['weight'] = 2;
				$Feature['properties']['style']['color'] = Joe_Config::get_setting('appearance', 'colours', 'tracking_colour');
				
				$this->FeatureCollection['features'][] = $Feature;
			}
			
			//Reverse order (most recent first)
			$this->FeatureCollection['features'] = array_reverse($this->FeatureCollection['features']);				
		//No points in KML
		} else {
			Joe_Log::add('The KML response contains no Points.', 'error', 'no_points');			
		}
	}
	
	function get_point_count() {
		if($this->point_count) {
			return $this->point_count;
		}
		
		if(
 			is_object($this->KML)
 			&& isset($this->KML->Document->Folder->Placemark)
 			&& is_iterable($this->KML->Document->Folder->Placemark)	
		) {
			foreach($this->KML->Document->Folder->Placemark as $Placemark) {
				if($Placemark->Point->coordinates) {
					$this->point_count++;
				}			
			}		
		}
		
		return $this->point_count;
	}
}