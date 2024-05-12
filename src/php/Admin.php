<?php

class InMap_Admin {

	protected $current_screen;

	function __construct() {
		//Admin only
		if (!is_admin()) {
			return;
		}

		add_action('admin_init', array($this, 'load_assets'));
		add_action('current_screen', array($this, 'get_current_screen'));
		add_action('admin_head', array($this, 'admin_head'));

		new InMap_Shortcode;
		new InMap_Settings;

		//Actions
		add_action('admin_init', array($this, 'load_assets'));
		add_filter('plugin_action_links_' . InMap_Helper::plugin_file_path(), array($this, 'add_action_links'));
	}

	function add_action_links($links) {
		$links_before = array();

		$links_after = array(
			'<a href="' . admin_url('options-general.php?page=' . InMap_Helper::slug_prefix('settings', '-')) . '">' . esc_html__('Settings', InMap_Config::get_item('plugin_text_domain')) . '</a>',
		);

		return array_merge($links_before, $links, $links_after);
	}

	function load_assets() {
		InMap_Assets::js_onready('jQuery("body").addClass("joe-admin");');

		//Enqueue
		InMap_Assets::css_enqueue([
			'url' => InMap_Helper::plugin_url('dist/inreach-mapshare.css'),
		]);

		InMap_Assets::js_enqueue([
			'id' => 'joe_admin_js',
			'url' => InMap_Helper::plugin_url('dist/inreach-mapshare.js'),

			'deps' => [
				'jquery',
				'jquery-ui-sortable',
				'jquery-effects-core',
				'wp-color-picker',
			],
			'data' => [
				'multi_value_seperator' => InMap_Config::get_item('multi_value_seperator'),
				'lang' => [
					//Editor
					'repeatable_delete_title' => esc_attr__('Remove!', InMap_Config::get_item('plugin_text_domain')),
					'error_message_prefix' => esc_attr__('Error', InMap_Config::get_item('plugin_text_domain')),
					'info_message_prefix' => esc_attr__('Info', InMap_Config::get_item('plugin_text_domain')),
					'success_message_prefix' => esc_attr__('Success', InMap_Config::get_item('plugin_text_domain')),
					'warning_message_prefix' => esc_attr__('Warning', InMap_Config::get_item('plugin_text_domain')),
				],
			],
		]);
	}

	function get_current_screen() {
		$this->current_screen = get_current_screen();
	}

	function admin_head() {
		echo '<meta name="' . InMap_Config::get_name(true, true) . ' Version" content="' . InMap_Config::get_version() . '" />' . "\n";
	}
}