<?php

require_once('Feed_Beast_Object.php');
	
class Feed_Beast_Feed extends Feed_Beast_Object {
	
	public $post_type = 'feed_beast_feed';
	
	function __construct($post_id = null) {
		//Set groups
		$this->parameter_groups = [];

		//Map Data
		$this->parameters['feed_enpoint'] = array(
			'id' => 'feed_enpoint',
			'type' => 'text',				
			'group' => '',
			'title' => 'Endpoint',
// 			'class' => 'waymark-hidden'
		);
					
		parent::__construct($post_id);
	}		
}