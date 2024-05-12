<?php

class InMap_Front {

	function __construct() {
		//Front only
		if (is_admin()) {
			return;
		}

		new InMap_Shortcode;

		add_action('wp_head', array($this, 'wp_head'));
	}

	function wp_head() {
		echo '<meta name="' . InMap_Config::get_name(true, true) . ' Version" content="' . InMap_Config::get_version() . '" />' . "\n";
	}
}