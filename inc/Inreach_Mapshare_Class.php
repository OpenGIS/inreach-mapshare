<?php 

class Inreach_Mapshare_Class {

	public $data = array();
	public $parameters = array();

	function __construct($params_in = []) {
		//Set passed params
		foreach($params_in as $key => $value) {
			//Only accept valid keys
			//and String values (set in Child class)
			if(array_key_exists($key, $this->parameters) && is_string($value)) {
				$this->set_data($key, $value);			
			}
		}
	}	

	function get_data($key = null) {
		if(! $key) {
			return $this->data;
		}
		
		if(array_key_exists($key, $this->data)) {
			return $this->data[$key];
		} else {
			return null;
		}
	}
	
	function set_data($key, $value) {
		$this->data[$key] = $value;		
	}	
}