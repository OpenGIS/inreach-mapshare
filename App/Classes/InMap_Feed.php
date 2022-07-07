<?php

class InMap_Feed extends Joe_Class {
	
	public $request_endpoint = '';
	public $request_data = [];
	public $cache_id = '';	

	public $request_string = '';
	public $response_string = '';
	
	function __construct($params_in = null) {
		parent::__construct($params_in);

		$this->setup_request();
		$this->execute_request();		
	}
	
	function execute_request() {
		//Request is setup
		if($this->cache_id) {
			//Cached response	
			$this->response_string = Joe_Cache::get_item($this->cache_id);

			if($this->response_string === false) {
				//Setup call
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $this->request_string);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				if($auth_password = $this->get_parameter('mapshare_password')) {
					curl_setopt($ch, CURLOPT_USERPWD, ":" . $auth_password);	//No username			
				}

				//Run it
				curl_exec($ch);

				//cURL success?
				if(! curl_errno($ch)) {
					$this->response_string = curl_multi_getcontent($ch);

					//MUST BE VALID KML to go into Cache
					if(is_string($this->response_string) && simplexml_load_string($this->response_string)) {
						//Insert into cache
						Joe_Cache::set_item($this->cache_id, $this->response_string, 15);	//Minutes
					}

					curl_close($ch);
				}
			}	
		}
	}
}