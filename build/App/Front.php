<?php

class InMap_Front extends Joe_v1_3_Front {
	function __construct() {
		parent::__construct();

		//Front only
		if(is_admin()) {
			return;
		}

		new InMap_Shortcode;	

		add_action('wp_head', array($this, 'wp_head'));			
	}

	function wp_head() {
		echo '<meta name="' . Joe_v1_3_Config::get_name(true, true) . ' Version" content="' . Joe_v1_3_Config::get_version() . '" />' . "\n";	
	}
}