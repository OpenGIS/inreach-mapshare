<?php

class inReach_KML_Feed extends Feed_Beast_Request {

	public $parameters = [
		'mapshare_identifier' => null,
		'mapshare_password' => null,
		'mapshare_date_start' => null,
		'mapshare_date_end' => null
	];
	public $intputs = [];
	public $input_groups = [];	
		
	private $request_string = 'https://explore.garmin.com/feed/share/';
	public $response_string = '';
		
	function __construct($params_in = []) {
		parent::__construct($params_in);
		
		$this->inputs['mapshare_identifier'] = array(
			'title' => 'Mapshare Identifier',
			'id' => 'mapshare_identifier',
			'type' => 'text',				
			'default' => null,
		);

		$this->inputs['mapshare_password'] = array(
			'title' => 'Mapshare Password',
			'id' => 'mapshare_password',
			'type' => 'text',				
			'default' => null,
		);

		$this->inputs['mapshare_date_start'] = array(
			'title' => 'Mapshare Start Date',
			'id' => 'mapshare_date_start',
			'type' => 'text',				
			'default' => null,
		);

		$this->inputs['mapshare_date_end'] = array(
			'title' => 'Mapshare End Date',
			'id' => 'mapshare_date_end',
			'type' => 'text',				
			'default' => null,
		);;
		
		//Set defaults
		foreach($this->parameters as $parameter_key => $parameter_value) {
			$this->inputs[$parameter_key]['default'] = $parameter_value;		
		}
		
		if($mapshare_identifier = $this->get_parameter('mapshare_identifier')) {
			$this->request_string .= $mapshare_identifier;

			$request_data = [];
		
			//Start date
			if($this->get_parameter('mapshare_date_start')) {
				$request_data['d1'] = $this->get_parameter('mapshare_date_start');
			}

			//End date
			if($this->get_parameter('mapshare_date_end')) {
				$request_data['d2'] = $this->get_parameter('mapshare_date_end');
			}
			
			//Append data
			if(sizeof($request_data)) {
				$this->request_string .= '?';
				$this->request_string .= http_build_query($request_data);
			}

// 			Waymark_Helper::debug($this->request_string);

			//Determine cache ID
			$cache_id = 'Feed_Beast_InReach_Feed_' . md5($this->request_string);

			//Cached response	
			$this->response_string = Waymark_Cache::get_item($cache_id);
			if($this->response_string === false) {
				//Setup call
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $this->request_string);
				curl_setopt($ch, CURLOPT_USERPWD, ":" . $this->get_parameter('mapshare_password'));	//No username
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				//Run it
				curl_exec($ch);

				//cURL success?
				if(! curl_errno($ch)) {
					$this->response_string = curl_multi_getcontent($ch);
					
					//MUST BE VALID KML to go into Cache
					if(is_string($this->response_string) && simplexml_load_string($this->response_string)) {
						//Insert into cache
						Waymark_Cache::set_item($cache_id, $this->response_string, 15);	//Minutes
					}

					curl_close($ch);
				}
			}
		}			
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
			if(isset($KML->Document->Folder->Placemark)) {
				//Each
				foreach($KML->Document->Folder->Placemark as $Placemark) {
					if($Placemark->Point->coordinates) {
						$coordinates = explode(',', (String)$Placemark->Point->coordinates);													
						
						//Coordinates
						$Feature = [
							'type' => 'Feature',
							'properties' => [],
							'geometry' => [
								'type' => 'Point',
								'coordinates' => [
									$coordinates[0],
									$coordinates[1]
								]
							]
						];
					
						//Description
						if(isset($Placemark->description)) {
							$Feature['properties']['description'] = (String)$Placemark->description;
						}
						
						//When
						if(isset($Placemark->TimeStamp->when)) {
							$Feature['properties']['title'] = (String)$Placemark->TimeStamp->when;
						}
					}
					
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
	
// 	function get_output() {
// 		$out = '';
// 		
// 		//Do we have a response?
// 		if($this->response_string) {
// 			$KML = simplexml_load_string($this->response_string);
// 		
// 			//We have Placemarks
// 			if(isset($KML->Document->Folder->Placemark)) {
// 				//Each
// 				foreach($KML->Document->Folder->Placemark as $Placemark) {
// 					if($Placemark->Point->coordinates) {
// 						$coordinates = explode(',', $Placemark->Point->coordinates);													
// 						$waymark_shortcode = '[Waymark marker_centre="' . $coordinates[1] . ',' . $coordinates[0] . '"';
// 						
// 						//Description
// 						if(isset($Placemark->description)) {
// 							$waymark_shortcode .= ' marker_description="' . $Placemark->description;
// 							
// 							if(isset($Placemark->TimeStamp->when)) {
// 								$waymark_shortcode .= ' (Sent: ' . $Placemark->TimeStamp->when . ')';
// 							}
// 							
// 							$waymark_shortcode .= '"';
// 						}
// 
// 						$waymark_shortcode .= ']';
// 						
// 						$out .= do_shortcode($waymark_shortcode);
// 					}
// 				}
// 			}
// 		}
// 		
// 		return $out;
// 	}
	
	function create_form() {
		echo Waymark_Input::create_parameter_groups($this->inputs, $this->input_groups, $this->parameters);	
	}
}