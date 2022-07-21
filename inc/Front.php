<?php

class InMap_Front extends Joe_Front {
	function __construct() {
		parent::__construct();

		//Front only
		if(is_admin()) {
			return;
		}

		//Load Assets
		add_action('init', array($this, 'load_assets'));
	}
	
	function load_assets() {
		//Joe CSS
		Joe_Assets::css_enqueue(Joe_Helper::plugin_url('Joe/Assets/css/front.min.css'));	

		InMap_Helper::enqueue_shortcode_assets([
			'context' => 'front'
		]);
	}		
}