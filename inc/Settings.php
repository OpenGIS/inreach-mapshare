<?php

class InMap_Settings extends Joe_Settings {
	public function __construct() {
		parent::__construct();

// 		$current_shortcode = '[';
// 		$current_shortcode .= Joe_Config::get_item('shortcode');
// 		foreach([
// 			'mapshare_identifier',
// 			'mapshare_password',
// 			'mapshare_date_start',
// 			'mapshare_date_end'
// 		] as $key) {
// 			$current_shortcode .= ' ' . $key . '="' . Joe_Config::get_setting('defaults', 'defaults', $key). '"';
// 		}
// 		$current_shortcode .= ']';

		//Defaults
		$this->tabs['defaults'] = [
			'name' => '',
			'description' => '',
			'sections' => [
				'defaults' => [		
					'title' => esc_html__('Defaults', Joe_Config::get_item('plugin_text_domain')),
// 					'description' => '<pre><code>' . $current_shortcode . '</code></pre>' . do_shortcode($current_shortcode),
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
						],
						'mapshare_date_start' => [
							'required' => Joe_Config::get_setting('defaults', 'defaults', 'mapshare_date_start'),
							'id' => 'mapshare_date_start',
							'type' => 'datetime-local',
							'title' => esc_html__('Start Date', Joe_Config::get_item('plugin_text_domain'))
						],
// 						'mapshare_date_end' => [
// 							'id' => 'mapshare_date_end',
// 							'type' => 'datetime-local',
// 							'title' => esc_html__('End Date', Joe_Config::get_item('plugin_text_domain'))
// 						],
						'cache_minutes' => [
							'required' => Joe_Config::get_setting('defaults', 'defaults', 'mapshare_date_start'),
							'id' => 'cache_minutes',
							'class' => 'joe-short-input',
							'title' => esc_html__('Cache Minutes', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('How often the feed is updated.', Joe_Config::get_item('plugin_text_domain'))
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
							'required' => Joe_Config::get_setting('map', 'basemap', 'basemap_url'),
							'id' => 'basemap_url',
							'title' => esc_html__('Basemap URL', Joe_Config::get_item('plugin_text_domain')),
						],
					]
				],
				'appearance' => [		
					'title' => esc_html__('Appearance', Joe_Config::get_item('plugin_text_domain')),
					'description' => '',
					'fields' => [						
						'tracking_colour' => [
							'type' => 'color',
							'required' => Joe_Config::get_setting('map', 'appearance', 'tracking_colour'),
							'id' => 'tracking_colour',
							'title' => esc_html__('Tracking Colour', Joe_Config::get_item('plugin_text_domain')),
 							'tip' => esc_attr__('!!!', Joe_Config::get_item('plugin_text_domain')),
						],
						'tracking_icon' => [
							'required' => Joe_Config::get_setting('map', 'appearance', 'tracking_icon'),						
							'id' => 'tracking_icon',
							'title' => esc_html__('Tracking Icon', Joe_Config::get_item('plugin_text_domain')),
						],
						'message_icon' => [
							'id' => 'message_icon',
							'required' => Joe_Config::get_setting('map', 'appearance', 'message_icon'),
							'title' => esc_html__('Message Icon', Joe_Config::get_item('plugin_text_domain')),
// 							'tip' => esc_attr__('!!!.', Joe_Config::get_item('plugin_text_domain')),
// !!!
// 							'tip_link' => ''
						]																																										
					]											
				]
			]
		];											
	}
}