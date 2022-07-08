<?php

class InMap_Settings extends Joe_Settings {
	public function __construct() {
		parent::__construct();


// 			'mapshare_identifier' => null,
// 			'mapshare_password' => null

		//Defaults
		$this->tabs['defaults'] = [
			'name' => '',
			'description' => '',
			'sections' => [
				'defaults' => [		
					'title' => esc_html__('Defaults', Joe_Config::get_item('plugin_text_domain')),
					'description' => '',
					'fields' => [
						'mapshare_identifier' => [
							'id' => 'mapshare_identifier',
							'title' => esc_html__('MapShare Identifier', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('!!!', Joe_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://developer.mozilla.org/en-US/docs/Tools/Browser_Console'
						],
						'mapshare_password' => [
							'id' => 'mapshare_password',
							'title' => esc_html__('MapShare Password', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('The (optional) password for your MapShare page, set in MapShare Settings. This is *not* any kind of account password.', Joe_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://explore.garmin.com/Social'
						]																		
					]											
				]
			]
		];

		//Map
		$this->tabs['map'] = [
			'name' => '',
			'description' => '',
			'sections' => [
				'basemap' => [		
					'title' => esc_html__('Basemap', Joe_Config::get_item('plugin_text_domain')),
					'description' => '',
					'fields' => [
						'basemap_url' => [
							'id' => 'mapshare_identifier',
							'title' => esc_html__('Basemap URL', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('!!!.', Joe_Config::get_item('plugin_text_domain')),
// !!!
// 							'tip_link' => ''
						],
					]
				],
				'styles' => [		
					'title' => esc_html__('Styles', Joe_Config::get_item('plugin_text_domain')),
					'description' => '',
					'fields' => [						
						'tracking_colour' => [
							'id' => 'tracking_colour',
							'default' => Joe_Config::get_setting('map', 'styles', 'tracking_colour'),
							'class' => 'joe-short-input joe-colour-picker',										
							'title' => esc_html__('Tracking Colour', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('!!!.', Joe_Config::get_item('plugin_text_domain')),
// !!!
// 							'tip_link' => ''
						]																														
					]											
				]
			]
		];

		//Advanced
		$this->tabs['misc'] = [
			'name' => '',
			'description' => '',
			'sections' => [
				'advanced' => [		
					'title' => esc_html__('Advanced', Joe_Config::get_item('plugin_text_domain')),
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
						],
						'cache_minutes' => [
							'id' => 'cache_minutes',
							'class' => 'joe-short-input',
							'title' => esc_html__('Cache Minutes', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('How often the feed is updated.', Joe_Config::get_item('plugin_text_domain'))
						]																			
					]											
				]
			]
		];											
	}
}

new InMap_Settings;