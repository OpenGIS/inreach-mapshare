<?php

class Waymark_GeoJSON {

	static public function string_to_feature_collection($string = '') {			
		if(is_string($string)) {
			return json_decode($string, true, 512,  JSON_OBJECT_AS_ARRAY);		
		}

		return false;	
	}	

	static public function get_feature_count($FeatureCollection = []) {			
		if(is_string($FeatureCollection)) {
			$FeatureCollection = self::string_to_feature_collection($FeatureCollection);		
		}
		
		if($FeatureCollection && isset($FeatureCollection['features']) && is_array($FeatureCollection['features'])) {			

			return sizeof($FeatureCollection['features']);
		}		

		return false;	
	}	

	static public function update_feature_property($FeatureCollection = [], $property_key = null, $property_value = null) {		
		if(is_string($FeatureCollection)) {
			$FeatureCollection = self::string_to_feature_collection($FeatureCollection);		
		}

		//Feature Collection
		if($FeatureCollection && isset($FeatureCollection['features'])) {	

			//Each Feature
			foreach($FeatureCollection['features'] as &$Feature) {
				if(! isset($Feature['properties']) || ! is_array($Feature['properties'])) {
					$Feature['properties'] = [];
				}
				
				$Feature['properties'][$property_key] = $property_value;
			}
		}		

		return $FeatureCollection;	
	}	

	//Thanks! https://stackoverflow.com/a/24365425/569788
	static public function stringify_numbers($obj) {
		//Bad data
		if(! $obj) {
			return $obj;
		}
		
		foreach($obj as &$item) {
			if(is_object($item) || is_array($item)) {
				$item = self::stringify_numbers($item); // recurse!
			}
	
			if(is_numeric($item)) {
				$item = (string) $item;
			}
		}				
		
		return $obj;
	}	

	static public function remove_unwanted_data_properties($data_in, $wanted = [ 'radius' ]) {		
		$FeatureCollection = json_decode($data_in);
		
		$FeatureCollection = Waymark_GeoJSON::stringify_numbers($FeatureCollection);
				
		if($FeatureCollection && sizeof($FeatureCollection->features)) {	
			foreach($FeatureCollection->features as &$feature) {
				//No existing properties
				if(! property_exists($feature, 'properties')) {
					return json_encode($FeatureCollection);	
				}
				
				$properties_out = new stdClass();
				foreach($wanted as $key) {
					if(property_exists($feature->properties, $key)) {
						$properties_out->{$key} = (string)$feature->properties->{$key};
					}					
				}
				//Update
				$feature->properties = $properties_out;
			}
		}		
		
		$data_out = json_encode($FeatureCollection);
		
		return $data_out;	
	}

	static public function set_features_property($map_data, $key = false, $value = false, $append = false) {
		$FeatureCollection = json_decode($map_data);
		
		//Ensure valid data		
		if($FeatureCollection && sizeof($FeatureCollection->features)) {	
			foreach($FeatureCollection->features as &$feature) {
				//No existing properties
				if(! property_exists($feature, 'properties')) {
					$feature->properties = new stdClass();
				}
				
				//Set
				if(! property_exists($feature->properties, $key)) {
					$feature->properties->{$key} = $value;								
				//Update
				} else {
					if($append) {
						$feature->properties->{$key} .= $value;				
					} else {
						$feature->properties->{$key} = $value;				
					}				
				}
			}
		//Invalid data
		} else {
			return $map_data;
		}
		
		return json_encode($FeatureCollection);
	}		
}