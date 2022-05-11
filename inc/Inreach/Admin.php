<?php

class Feed_Beast_Inreach_Admin {
	
	function __construct() {
		add_action('admin_init', array($this, 'admin_init'));
		add_action('current_screen', array($this, 'current_screen'));	
		add_action('post_updated', array($this, 'post_updated'), 10, 2);			
	}

	function admin_init() {
		require_once('Inreach_KML_Request.php');
	}
	
	function current_screen() {

		if(function_exists('get_current_screen')) {  
			$current_screen = get_current_screen();
			
			switch($current_screen->post_type) {
				//Map
				case 'waymark_map' :									
 					add_meta_box('feed_beast_inreach_meta', esc_html__('Inreach KML Feed', 'feed-beast'), array($this, 'display_inreach_meta'), 'waymark_map', 'normal', 'high');			
							
					break;
			}		
		}
	}	

	/**
	 * ===========================================
	 * =============== SAVE POST =================
	 * ===========================================
	 */	
	function post_updated() {
		global $post;
		
		if(is_object($post) && ! (wp_is_post_revision($post->ID) || wp_is_post_autosave($post->ID))) {
			switch($post->post_type) {
				// ============ MAP ============
				case 'waymark_map' :													

					//Inreach
					if(isset($_POST['mapshare_identifier'])) {				
						$inReach_KML_Feed = new inReach_KML_Feed($_POST);
						$parameters_encoded = json_encode($inReach_KML_Feed->get_parameters());

						update_post_meta($post->ID, 'feed_beast_inreach_feed', $parameters_encoded);
					}
					
					break;			
			}			
		}
	}

	/**
	 * ===========================================
	 * ================== FORM ===================
	 * ===========================================
	 */	
	function display_inreach_meta($post) {	
 		$data = json_decode(get_post_meta($post->ID, 'feed_beast_inreach_feed', true));

		$inReach_KML_Feed = new inReach_KML_Feed($data);
		$response_geojson_string = $inReach_KML_Feed->response_geojson();
		
		Waymark_JS::add_call("
			setTimeout(function() {
				var waymark_container = jQuery('.waymark-map').first();

				if(typeof waymark_container === 'object') {			
	// 			waymark_container.addClass('map-first-sidebar-active');
					var Waymark_Instance = waymark_container.data('Waymark');

					if(typeof Waymark_Instance === 'object') {
// 						var kml_doc = (new DOMParser()).parseFromString('', 'text/xml');
// 						var geo_json = toGeoJSON.kml(kml_doc);

						Waymark_Instance.load_query_json(" . $response_geojson_string . ");
					}		
				}	
			}, 250);
		");
		
		echo $inReach_KML_Feed->create_form();
	}	
}
new Feed_Beast_Inreach_Admin;