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
		
		Joe_Assets::js_onready('
// 			var form = jQuery("body.settings_page_inreach-mapshare-settings form");
// 			form.on("submit", function(e) {
// 				var identifier = jQuery(".joe-input-mapshare_identifier");
// 				if(! identifier.val()) {
// 					e.preventDefault();
// 
// 					identifier.addClass("joe-error");			
// 
// 					return false;
// 				}				
// 			});
		');
	}
}