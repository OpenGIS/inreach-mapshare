<?php

class InMap_Settings extends Joe_Settings {
	public function __construct() {
		parent::__construct();

		//Advanced
		$this->tabs['misc'] = [
			'name' => esc_html__('Misc.', Joe_Config::get_item('plugin_text_domain')),
			'description' => '',
			'sections' => [
				'advanced' => [		
					'title' => esc_html__('Debug', Joe_Config::get_item('plugin_text_domain')),
					'description' => '',
					'fields' => [
						'debug_mode' => [
							'name' => 'debug_mode',
							'id' => 'debug_mode',
							'type' => 'boolean',
							'title' => esc_html__('Debug Mode', Joe_Config::get_item('plugin_text_domain')),
							'default' => Joe_Config::get_setting('misc', 'advanced', 'debug_mode'),
							'tip' => esc_attr__('With debug mode enabled, the plugin will output Map and Settings data in Admin Dashboard. This may come in handy if you need to report a bug. Pro Tip! Check the browser console for output when signed in as an administrator.', Joe_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://developer.mozilla.org/en-US/docs/Tools/Browser_Console',
							'options' => [
								'0' => esc_html__('Disable', Joe_Config::get_item('plugin_text_domain')),
								'1' => esc_html__('Enable', Joe_Config::get_item('plugin_text_domain'))								
							]
						]												
					]											
				]
			]
		];											
	}
}

new InMap_Settings;