<?php

class Beast_Inreach extends Beast_Feed {
	function __construct($params_in = []) {
		$this->request_endpoint = 'https://explore.garmin.com/feed/share/';	
	
		//Set parameters
		$this->parameters['mapshare_identifier'] = array(
			'id' => 'mapshare_identifier',
			'type' => 'text',				
			'title' => __('Identifier', 'feed-beast')
		);

		$this->parameters['mapshare_password'] = array(
			'id' => 'mapshare_password',
			'type' => 'text',				
			'title' => __('Password', 'feed-beast')
		);

		$this->parameters['mapshare_date_start'] = array(
			'id' => 'mapshare_date_start',
			'type' => 'date',				
		);
		
		$this->parameters['mapshare_date_end'] = array(
			'id' => 'mapshare_date_end',
			'type' => 'date',				
		);		
					
		parent::__construct($params_in);
	}
	
	function setup_request() {
		$this->request_string = $this->request_endpoint;	

		//Required
		$url_identifier = $this->get_data('mapshare_identifier');
				
		if(! $url_identifier) {
			return;		
		}
		
		$this->request_string .= $url_identifier;

		//Start date
		if($data_start = $this->get_data('mapshare_date_start')) {
			$this->request_data['d1'] = $this->get_data('mapshare_date_start');
		}

		//End date
		if($data_end = $this->get_data('mapshare_date_end')) {
			$this->request_data['d2'] = $this->get_data('mapshare_date_end');
		}
		
		//Append data
		if(sizeof($this->request_data)) {
			$this->request_string .= '?';
			$this->request_string .= http_build_query($this->request_data, "", null,  PHP_QUERY_RFC3986);
		}	
		
		//Determine cache ID
		$this->cache_id = Beast_Config::get_item('plugin_slug') . '_inreach_feed_' . md5($this->request_string);
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
//   				Beast_Helper::debug($KML->Document->Folder);

				//Each
				for($i = 0; $i < sizeof($KML->Document->Folder->Placemark); $i++) {
					$Placemark = $KML->Document->Folder->Placemark[$i];
					
					// =========== Point ===========
					
					if($Placemark->Point->coordinates) {
						$coordinates = explode(',', (String)$Placemark->Point->coordinates);													
						
						//Invalid
						if(sizeof($coordinates) < 2 || sizeof($coordinates) > 3) {
							continue;						
						}
						
						$Feature = [
							'type' => 'Feature',
							'properties' => [
								'type' => 'inreach_tracking'
							],
							'geometry' => [
								'type' => 'Point',
								'coordinates' => $coordinates
							]
						];

						//Description
						if(isset($Placemark->description) && $Placemark->description) {
							$Feature['properties']['description'] = (String)$Placemark->description;
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

							$Feature = [
								'type' => 'Feature',
									'properties' => [
										'type' => 'inreach_tracking'
									],
									'geometry' => [
									'type' => 'LineString'
								]
							];

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
					
					$FeatureCollection['features'][] = $Feature;
				}
				
//  				Beast_Helper::debug($FeatureCollection['features']);
			}
		}
		
		//Response type
		if($response_type == 'string') {
			$FeatureCollection = json_encode($FeatureCollection);
		}
		
		return $FeatureCollection;	
	}
}