<?php

class InMap_Admin extends Joe_Admin {
	
	function __construct() {
		parent::__construct();

		//Admin only
		if(! is_admin()) {
			return;
		}
		
		//Actions
		add_action('admin_init', array($this, 'load_assets'));
	}
	
	function load_assets() {
		parent::load_assets();
		
		InMap_Helper::enqueue_shortcode_assets([
			'context' => 'admin'
		]);
	}
}