<?php

class InMap_Admin extends Joe_Admin {
	
	function __construct() {
		parent::__construct();

		//Admin only
// 		if(! $this->is_admin()) {
		if(! is_admin()) {
			return;
		}
		
		//Actions
		add_action('admin_init', array($this, 'load_assets'));
	}

// 	function is_admin() {
// 		global $pagenow;
// 
// 		switch($pagenow) {
// 			case 'admin-ajax.php' :
// 				return isset($_GET['waymark_security']);
// 			case 'post.php' :
// 				return true;
// 			case 'term.php' :
// 			case 'edit-tags.php' :
// 			case 'edit.php' :
// 				return isset($_GET['post_type']) && $_GET['post_type'] == 'waymark_map';				
// 			default:
// 				return false;
// 		}
// 	}
	
	function load_assets() {
		parent::load_assets();
/*
		//Leaflet CSS
		Joe_Assets::css_enqueue(Joe_Helper::plugin_url('App/assets/css/leaflet.css'));	

		//Leaflet JS
		Joe_Assets::js_enqueue([
			'id' => 'leaflet_js',
			'url' => Joe_Helper::plugin_url('App/assets/js/leaflet.js'),
			'deps' => [],
			'data' => [
// 				'lang' => []						
			]
		]);	
*/		
	}
}	
new InMap_Admin;