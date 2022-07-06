<?php

class InMap_Shortcode extends Joe_Shortcode {

	public function handle_shortcode($shortcode_data, $content = null) {
		Joe_Helper::debug($shortcode_data);
	}	
}
new InMap_Shortcode;