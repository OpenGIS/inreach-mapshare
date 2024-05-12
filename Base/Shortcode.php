<?php

class Joe_Shortcode {
	function __construct() {
		if ($shortcode = Joe_Config::get_item('plugin_shortcode')) {
			add_shortcode($shortcode, [$this, 'handle_shortcode']);
		}
	}

	public function handle_shortcode($shortcode_data, $content = null) {}
}