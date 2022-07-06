<?php

class Waymark_Admin extends Joe_Admin {
	
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
/*
		//Load Assets
		if(Waymark_Helper::is_debug()) {
			Joe_Assets::css_enqueue(Joe_Helper::asset_url('css/admin.css'));	
		} else {
			Joe_Assets::css_enqueue(Joe_Helper::asset_url('css/admin.min.css'));			
		}

 		//Debug Mode
 		if(Joe_Config::get_setting('misc', 'advanced', 'debug_mode')) {
	 		Joe_Assets::js_onready('jQuery("body").addClass("waymark-debug");');						 		
 		}		
 		
		//Enqueue
		$admin_js_url = Joe_Helper::asset_url('js/admin.min.js');

		Joe_Assets::js_enqueue([
			'id' => 'waymark_admin_js',
			'url' => $admin_js_url,
			'deps' => [ 
				'jquery',
				'jquery-ui-sortable',
				'jquery-effects-core',
				'wp-color-picker'
			],
			'data' => [
				'ajaxurl' => admin_url('admin-ajax.php'),
				'multi_value_seperator' => Joe_Config::get_item('multi_value_seperator'),			
				'waymark_security' => wp_create_nonce(Joe_Config::get_item('nonce_string')),				
				'waymark_settings' => Waymark_Helper::get_settings_js(),
				'lang' => [
					//Editor
					'repeatable_delete_title' => esc_attr__('Remove!', 'waymark'),
					'marker_icon_icon_label' => esc_attr__('Name', 'waymark'),
					'marker_icon_text_label' => esc_attr__('Text', 'waymark'),
					'marker_icon_html_label' => esc_attr__('HTML', 'waymark'),	
					'error_message_prefix' => Joe_Config::get_name() . ' ' . esc_attr__('Error', 'waymark'),	
					'info_message_prefix' => Joe_Config::get_name() . ' ' . esc_attr__('Info', 'waymark'),
					'success_message_prefix' => Joe_Config::get_name() . ' ' . esc_attr__('Success', 'waymark'),
					'warning_message_prefix' => Joe_Config::get_name() . ' ' . esc_attr__('Warning', 'waymark')
				]						
			]
		]);
*/
		
	}
}	
new Waymark_Admin;
