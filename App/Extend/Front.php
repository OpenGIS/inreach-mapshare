<?php

class Waymark_Front extends Joe_Front {
	function __construct() {
		parent::__construct();

		//Admin only
		if(is_admin()) {
			return;
		}

		//Load Assets
		add_action('init', array($this, 'load_assets'));
	}
	
	function load_assets() {
/*
		//CSS
		Joe_Assets::css_enqueue(Joe_Helper::asset_url('css/front.min.css'));	

		//Elevation
		Joe_Assets::css_inline('
div.waymark-container .waymark-map .elevation-polyline { stroke: ' . Joe_Config::get_setting('misc', 'elevation_options', 'elevation_colour') . '; }
div.waymark-container .waymark-elevation .elevation-control.elevation .area { fill: ' . Joe_Config::get_setting('misc', 'elevation_options', 'elevation_colour') . ';	}
		');	


		//JS
 		Joe_Assets::js_inline("\n" . 'var waymark_user_config = ' . json_encode(Waymark_Helper::get_map_config()));						

		//Enqueue
		if(Waymark_Helper::is_debug()) {
			$waymark_css_url = Joe_Helper::asset_url('dist/waymark-js/css/waymark-js.css');
			$waymark_js_url = Joe_Helper::asset_url('dist/waymark-js/js/waymark-js.js');
		} else {
			$waymark_css_url = Joe_Helper::asset_url('dist/waymark-js/css/waymark-js.min.css');
			$waymark_js_url = Joe_Helper::asset_url('dist/waymark-js/js/waymark-js.min.js');
		}

		Joe_Assets::css_enqueue($waymark_css_url);

		$front_js_url = Joe_Helper::asset_url('js/front.min.js');
		Joe_Assets::js_enqueue([
			'url' => $front_js_url,
			'deps' => [ 'jquery' ]
		]);
		
		Joe_Assets::js_enqueue([
			'id' => 'waymark_js',
			'url' => $waymark_js_url,
			'deps' => [ 'jquery' ],
			'data' => [
				//AJAX
				'ajaxurl' => admin_url('admin-ajax.php'),
				'waymark_security' => wp_create_nonce(Joe_Config::get_item('nonce_string')),				
				'waymark_settings' => Waymark_Helper::get_settings_js(),
				'lang' => [
					//Viewer
					'action_fullscreen_activate' => esc_attr__('View Fullscreen', 'waymark'),		
					'action_fullscreen_deactivate' => esc_attr__('Exit Fullscreen', 'waymark'),		
					'action_locate_activate' => esc_attr__('Show me where I am', 'waymark'),		
					'action_zoom_in' => esc_attr__('Zoom in', 'waymark'),		
					'action_zoom_out' => esc_attr__('Zoom out', 'waymark'),
					'label_total_length' => esc_attr__('Total Length: ', 'waymark'),
					'label_max_elevation' => esc_attr__('Max. Elevation: ', 'waymark'),
					'label_min_elevation' => esc_attr__('Min. Elevation: ', 'waymark')
				]
			]
		]);		
*/	
	}		
}	
new Waymark_Front;