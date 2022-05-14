<?php

class Beast_Inreach extends Beast_Feed {
	function __construct($post_id = null) {
		//Set groups
		$this->parameter_groups = [
			'request' => [
				'group_title' => __('Request', 'feed-beast')
			],
			'auth' => [
				'group_title' => __('Authorisation', 'feed-beast')
			]
		];
		
		//Set parameters
		$this->parameters['feed_url_endpoint'] = array(
			'id' => 'feed_url_endpoint',
			'type' => 'text',				
			'group' => 'request',
			'title' => __('Endpoint', 'feed-beast')
		);

		$this->parameters['feed_url_identifier'] = array(
			'id' => 'feed_url_identifier',
			'type' => 'text',				
			'group' => 'request',
			'title' => __('Identifier', 'feed-beast')
		);

		$this->parameters['feed_auth_password'] = array(
			'id' => 'feed_auth_password',
			'type' => 'text',				
			'group' => 'auth',
			'title' => __('Password', 'feed-beast')
		);

// 		$this->inputs['feed_data_start'] = array(
// 			'title' => 'Mapshare Start Date',
// 			'id' => 'feed_data_start',
// 			'type' => 'text',				
// 			'default' => null,
// 		);
// 
// 		$this->inputs['feed_data_end'] = array(
// 			'title' => 'Mapshare End Date',
// 			'id' => 'feed_data_end',
// 			'type' => 'text',				
// 			'default' => null,
// 		);
					
		parent::__construct($post_id);
	}
	
	function setup_request() {
		//Required
		$url_endpoint = $this->get_data_item('feed_url_endpoint');
		$url_identifier = $this->get_data_item('feed_url_identifier');
				
		if(! $url_endpoint || ! $url_identifier) {
			return;		
		}
		
		$this->request_string = $url_endpoint . $url_identifier;

		//Start date
		if($data_start = $this->get_data_item('feed_data_start')) {
			$this->request_data['d1'] = $this->get_data_item('feed_data_start');
		}

		//End date
		if($data_end = $this->get_data_item('feed_data_end')) {
			$this->request_data['d2'] = $this->get_data_item('feed_data_end');
		}
		
		//Append data
		if(sizeof($this->request_data)) {
			$this->request_string .= '?';
			$this->request_string .= http_build_query($request_data);
		}	
		
		//Determine cache ID
		$this->cache_id = Beast_Config::get_item('plugin_slug') . '_inreach_feed_' . md5($this->request_string);
	}	
}