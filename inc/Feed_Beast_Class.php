<?php 

class Feed_Beast_Class {

	function __construct($params_in = []) {
		//Set passed params
		foreach($params_in as $key => $value) {
			//Only accept valid keys
			//and String values (set in Child class)
			if(array_key_exists($key, $this->parameters) && is_string($value)) {
				$this->set_parameter($key, $value);			
			}
		}
	}	

	function get_parameter($key = null) {
		if(! $key) {
			return $this->get_parameters();
		}
		
		if(array_key_exists($key, $this->parameters)) {
			return $this->parameters[$key];
		} else {
			return false;
		}
	}	

	function get_parameters() {
		$out = [];
		
		foreach($this->parameters as $key => $value) {
			$out[$key] = $value;
		}
		
		return $out;
	}
	
	function set_parameter($key, $value) {
		$this->parameters[$key] = $value;		
	}	
}