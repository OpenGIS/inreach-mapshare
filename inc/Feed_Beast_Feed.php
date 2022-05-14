<?php

require_once('Feed_Beast_Object.php');
	
class Feed_Beast_Feed extends Feed_Beast_Object {
	
	public $post_type = 'feed_beast_feed';

	public $request_endpoint = '';
	public $request_data = [];
	public $cache_id = '';	

	public $request_string = '';
	public $response_string = '';
	
	function __construct($post_id = null) {
		parent::__construct($post_id);

		$this->setup_request();
		$this->execute_request();		
	}
	
	function execute_request() {
		//Cached response	
		$this->response_string = Feed_Beast_Cache::get_item($this->cache_id);
		
		if($this->response_string === false) {
			//Setup call
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->request_string);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			if($auth_password = $this->get_data_item('feed_auth_password')) {
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
					Feed_Beast_Cache::set_item($this->cache_id, $this->response_string, 15);	//Minutes
				}

				curl_close($ch);
			}
		}	
	}
}