<?php

class InMap_Front extends Joe_Front {
	function __construct() {
		parent::__construct();

		//Front only
		if(is_admin()) {
			return;
		}

		new InMap_Shortcode;	

		//Load Assets
		add_action('init', array($this, 'load_assets'));
		add_action('wp_head', array($this, 'wp_head'));			
	}

	function wp_head() {
		echo '<meta name="' . Joe_Config::get_name(true, true) . ' Version" content="' . Joe_Config::get_version() . '" />' . "\n";	
	}
	
	function load_assets() {
		//Joe CSS
// 		Joe_Assets::css_enqueue(Joe_Helper::plugin_url('Joe/Assets/css/front.min.css'));	
	}		
}