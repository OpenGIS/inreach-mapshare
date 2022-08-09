<?php

class Joe_v1_0_Admin {

	protected $current_screen;

	function __construct() {
		//Admin only
		if(! is_admin()) {
			return;
		}

		add_action('admin_init', array($this, 'load_assets'));
		add_action('current_screen', array($this, 'get_current_screen'));	
		add_action('admin_head', array($this, 'admin_head'));			
	}
	
	function load_assets() {
 		Joe_v1_0_Assets::js_onready('jQuery("body").addClass("joe-admin");');						 		
 		
		//Enqueue
		Joe_v1_0_Assets::css_enqueue([
			'url' => Joe_v1_0_Helper::plugin_url('assets/css/joe-admin.min.css'),
			'deps' => [
// 				'jquery-ui-datepicker'			
			]
		]);			

		Joe_v1_0_Assets::js_enqueue([
			'id' => 'joe_admin_js',
			'url' => Joe_v1_0_Helper::plugin_url('assets/js/joe-admin.min.js'),
			'deps' => [ 
				'jquery',
				'jquery-ui-sortable',
				'jquery-effects-core',
 				'wp-color-picker'
			],
			'data' => [
				'multi_value_seperator' => Joe_v1_0_Config::get_item('multi_value_seperator'),			
				'lang' => [
					//Editor
					'repeatable_delete_title' => esc_attr__('Remove!', Joe_v1_0_Config::get_item('plugin_text_domain')),
					'error_message_prefix' => esc_attr__('Error', Joe_v1_0_Config::get_item('plugin_text_domain')),	
					'info_message_prefix' => esc_attr__('Info', Joe_v1_0_Config::get_item('plugin_text_domain')),
					'success_message_prefix' => esc_attr__('Success', Joe_v1_0_Config::get_item('plugin_text_domain')),
					'warning_message_prefix' => esc_attr__('Warning', Joe_v1_0_Config::get_item('plugin_text_domain'))
				]						
			]
		]);				
	}	
	
	function get_current_screen() {
		$this->current_screen = get_current_screen();
	}

	function admin_head() {
		echo '<meta name="' . Joe_v1_0_Config::get_name(true, true) . ' Version" content="' . Joe_v1_0_Config::get_version() . '" />' . "\n";	
	}
}