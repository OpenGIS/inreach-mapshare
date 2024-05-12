<?php

class InMap_Admin extends Joe_Admin {

	function __construct() {
		parent::__construct();

		//Admin only
		if (!is_admin()) {
			return;
		}

		new InMap_Shortcode;
		new InMap_Settings;

		//Actions
		add_action('admin_init', array($this, 'load_assets'));
		add_filter('plugin_action_links_' . Joe_Helper::plugin_file_path(), array($this, 'add_action_links'));
	}

	function add_action_links($links) {
		$links_before = array();

		$links_after = array(
			'<a href="' . admin_url('options-general.php?page=' . Joe_Helper::slug_prefix('settings', '-')) . '">' . esc_html__('Settings', Joe_Config::get_item('plugin_text_domain')) . '</a>',
		);

		return array_merge($links_before, $links, $links_after);
	}

	function load_assets() {
		parent::load_assets();

		//Joe CSS
// 		Joe_Assets::css_enqueue(Joe_Helper::asset_url('css/admin.min.css'));
	}
}