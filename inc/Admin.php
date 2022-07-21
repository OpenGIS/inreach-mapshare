<?php

class InMap_Admin extends Joe_Admin {
	
	function __construct() {
		parent::__construct();

		//Admin only
		if(! is_admin()) {
			return;
		}

		new InMap_Shortcode;	
		new InMap_Settings;
			
		//Actions
		add_action('admin_init', array($this, 'load_assets'));
	}
	
	function load_assets() {
		parent::load_assets();
	}
}