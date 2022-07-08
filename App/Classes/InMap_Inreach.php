<?php

class InMap_Inreach extends InMap_Feed {
	function __construct($params_in = []) {
		$this->request_endpoint = 'https://explore.garmin.com/feed/share/';	

		//Set parameters
		$this->parameters = [
			'mapshare_identifier' => null,
			'mapshare_password' => null,
			'mapshare_date_start' => null,
			'mapshare_date_end' => null,									
		];
					
		parent::__construct($params_in);		
	}
	
	function setup_request() {
		$this->request_string = $this->request_endpoint;	

		//Required
		$url_identifier = $this->get_parameter('mapshare_identifier');
				
		if(! $url_identifier) {
			return;		
		}
		
		$this->request_string .= $url_identifier;

		//Start date
		if($data_start = $this->get_parameter('mapshare_date_start')) {
			$this->request_data['d1'] = $this->get_parameter('mapshare_date_start');
		}

		//End date
		if($data_end = $this->get_parameter('mapshare_date_end')) {
			$this->request_data['d2'] = $this->get_parameter('mapshare_date_end');
		}
		
		//Append data
		if(sizeof($this->request_data)) {
			$this->request_string .= '?';
			$this->request_string .= http_build_query($this->request_data, "", null,  PHP_QUERY_RFC3986);
		}	
		
		//Determine cache ID
		$this->cache_id = Joe_Helper::slug_prefix(md5($this->request_string));
	}	

	function response_geojson($response_type = 'string') {
		$FeatureCollection = null;

		//Do we have a response?
		if($this->response_string) {
			$FeatureCollection = [
				'type' => 'FeatureCollection',
				'features' => []
			];
		
			$KML = simplexml_load_string($this->response_string);

			//We have Placemarks
			if(isset($KML->Document->Folder->Placemark) && sizeof($KML->Document->Folder->Placemark)) {
				//Each Placemark
				for($i = 0; $i < sizeof($KML->Document->Folder->Placemark); $i++) {
					$Placemark = $KML->Document->Folder->Placemark[$i];
					
					//Create Feature
					$Feature = [
						'type' => 'Feature',
						'properties' => [],
						'geometry' => []
					];
					
					//Extended Data?
					if(isset($Placemark->ExtendedData)) {
						if(sizeof($Placemark->ExtendedData->Data)) {
							$extended_data = [];
							
							//Each
							for($j = 0; $j < sizeof($Placemark->ExtendedData->Data); $j++) {
								$key = (string)$Placemark->ExtendedData->Data[$j]->attributes()->name;
								$value = (string)$Placemark->ExtendedData->Data[$j]->value;
								
								//Store
								$extended_data[$key] = $value;								
							}
							
							if(sizeof($extended_data)) {
								$Feature['properties']['description'] = Joe_Helper::assoc_array_table($extended_data);
							}
						}
					}
										
					// =========== Point ===========
					
					if($Placemark->Point->coordinates) {
						$coordinates = explode(',', (String)$Placemark->Point->coordinates);													
						
						//Invalid
						if(sizeof($coordinates) < 2 || sizeof($coordinates) > 3) {
							continue;						
						}
						
						$Feature['geometry']['type'] = 'Point';
						$Feature['geometry']['coordinates'] = $coordinates;

						//Style
						$Feature['properties']['icon'] = [
							'className' => 'inmap-marker-icon',
							'iconSize' => [ 7, 7 ],
							'html' => '<span></span>'
						];						
/*
						if(isset($extended_data['Event'])) {
							switch($extended_data['Event']) {

// Quick Text to MapShare received
// Tracking turned on from device.
// Tracking message received.
// Tracking turned off from device.
// 
// 
								case 'Quick Text to MapShare received';
									break;
							}										
						}
*/						
						//Description
						if(isset($Placemark->description) && (string)$Placemark->description) {
							$Feature['properties']['icon']['html'] = Joe_Config::get_setting('map', 'styles', 'message_icon');
							$Feature['properties']['icon']['className'] .= ' inmap-icon-message';
						
							//Prepend
							$Feature['properties']['description'] = '<p>' . (String)$Placemark->description . '</p>' . $Feature['properties']['description'];
						}
						
						//When
						if(isset($Placemark->TimeStamp->when)) {
							$title = (String)$Placemark->TimeStamp->when;
							$title = str_replace([
								'T',
								'Z'
							],
							[
								' ',
								' (UTC) [#' . $i . ']'
							], $title);
							
							$Feature['properties']['title'] = $title;
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
					$Feature['properties']['style']['color'] = Joe_Config::get_setting('map', 'styles', 'tracking_colour');
					
					$FeatureCollection['features'][] = $Feature;
				}
			}
		}
		
		//Response type
		if($response_type == 'string') {
			$FeatureCollection = json_encode($FeatureCollection);
		}
		
		return $FeatureCollection;	
	}
}