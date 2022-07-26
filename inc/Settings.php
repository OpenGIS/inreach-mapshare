<?php

class InMap_Settings extends Joe_Settings {
	public function __construct() {
		parent::__construct();

		$this->settings_nav = [
			'joe-settings-tab-shortcode' => '-- ' . esc_html__('Shortcode', Joe_Config::get_item('plugin_text_domain')),
			'joe-settings-tab-mapshare' => '-- ' . esc_html__('Mapshare', Joe_Config::get_item('plugin_text_domain')),
			'joe-settings-tab-appearance' => '-- ' . esc_html__('Appearance', Joe_Config::get_item('plugin_text_domain')),
		];

		//Switch tabs
		if(Joe_Config::get_setting('mapshare', 'defaults', 'mapshare_identifier')) {	
			Joe_Config::set_item('settings_default_tab', 'joe-settings-tab-shortcode');
		}
		
		//Build shortcode
		$shortcode = '[';
		$shortcode .= Joe_Config::get_item('plugin_shortcode');
		foreach([
			'mapshare_identifier',
			'mapshare_password',
			'mapshare_date_start',
			'mapshare_date_end'
		] as $key) {
			$value = Joe_Config::get_setting('shortcode', 'build', $key);
			
			if(! empty($value)) {
				$shortcode .= ' ' . $key . '="' . Joe_Config::get_setting('shortcode', 'build', $key) . '"';
			}
		}
		$shortcode .= ']';
		
		$description = do_shortcode($shortcode);
		$description .= '<pre class="joe-shortcode"><code>' . $shortcode . '</code></pre>';
		$description .= '<p class="joe-lead">' . __('Add wherever Shortcodes are supported.', Joe_Config::get_item('plugin_text_domain')) . '</p>';

		//Defaults
		$this->tabs['shortcode'] = [
			'sections' => [
				'build' => [	
					'title' => esc_html__('Build a Shortcode', Joe_Config::get_item('plugin_text_domain')),
 					'description' => $description,
					'fields' => [
						'mapshare_identifier' => [
							'id' => 'mapshare_identifier',
							'default' => Joe_Config::get_setting('mapshare', 'defaults', 'mapshare_identifier'),
							'title' => esc_html__('MapShare Identifier', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('!!!', Joe_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://developer.mozilla.org/en-US/docs/Tools/Browser_Console'
						],
						'mapshare_password' => [
							'title' => esc_html__('MapShare Password', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('The (optional) password for your MapShare page, set in MapShare Settings. This is *not* any kind of account password.', Joe_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://explore.garmin.com/Social'
						],
						'mapshare_date_start' => [
// 							'required' => Joe_Config::get_fallback('mapshare', 'defaults', 'mapshare_date_start'),
							'type' => 'datetime-local',
							'title' => esc_html__('Start Date', Joe_Config::get_item('plugin_text_domain'))
						],						
						'mapshare_date_end' => [
							'id' => 'mapshare_date_end',
							'type' => 'datetime-local',
							'title' => esc_html__('End Date', Joe_Config::get_item('plugin_text_domain'))
						]																																										
					]											
				]
			]
		];
		
		//Defaults
		$this->tabs['mapshare'] = [
			'sections' => [
				'defaults' => [		
					'title' => esc_html__('Defaults', Joe_Config::get_item('plugin_text_domain')),
 					'description' => __('Reduce keyboard wear.', Joe_Config::get_item('plugin_text_domain')),
					'fields' => [
						'mapshare_identifier' => [
 							'id' => 'mapshare_identifier',
							'title' => esc_html__('MapShare Identifier', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('!!!', Joe_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://developer.mozilla.org/en-US/docs/Tools/Browser_Console'
						],
						'mapshare_password' => [
							'title' => esc_html__('MapShare Password', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('The (optional) password for your MapShare page, set in MapShare Settings. This is *not* any kind of account password.', Joe_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://explore.garmin.com/Social'
						],
						'mapshare_date_start' => [
							'type' => 'datetime-local',
							'title' => esc_html__('Start Date', Joe_Config::get_item('plugin_text_domain'))
						]// ,						
// 						'mapshare_date_end' => [
// 							'id' => 'mapshare_date_end',
// 							'type' => 'datetime-local',
// 							'title' => esc_html__('End Date', Joe_Config::get_item('plugin_text_domain'))
// 						]																																									
					]											
				],
				'advanced' => [		
					'title' => esc_html__('Cache', Joe_Config::get_item('plugin_text_domain')),
// 					'description' => '',
					'fields' => [
						'cache_minutes' => [
							'required' => Joe_Config::get_fallback('mapshare', 'advanced', 'cache_minutes'),
							'class' => 'joe-short-input',
							'title' => esc_html__('Cache Minutes', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('How often the feed is updated.', Joe_Config::get_item('plugin_text_domain'))
						]	
					]
				]
			]
		];
		
		//Map
		$this->tabs['appearance'] = [
			'sections' => [
				'map' => [		
					'title' => esc_html__('Map', Joe_Config::get_item('plugin_text_domain')),
					'description' => '',
					'fields' => [
						'basemap_url' => [
							'required' => Joe_Config::get_fallback('appearance', 'map', 'basemap_url'),
							'title' => esc_html__('Basemap URL', Joe_Config::get_item('plugin_text_domain')),
						],
						'basemap_attribution' => [
							'required' => Joe_Config::get_fallback('appearance', 'map', 'basemap_attribution'),
							'title' => esc_html__('Basemap Attribution', Joe_Config::get_item('plugin_text_domain')),
							'input_processing' => array(
								'(! strpos($param_value, "&")) ? htmlspecialchars($param_value) : $param_value'
							)															
						]						
					]
				],
				'colours' => [		
					'title' => esc_html__('Colours', Joe_Config::get_item('plugin_text_domain')),
					'description' => '',
					'fields' => [						
						'tracking_colour' => [
							'type' => 'color',
							'required' => Joe_Config::get_fallback('appearance', 'colours', 'tracking_colour'),
							'title' => esc_html__('Tracking Colour', Joe_Config::get_item('plugin_text_domain')),
 							'tip' => esc_attr__('!!!', Joe_Config::get_item('plugin_text_domain')),
						]																																									
					]											
				],
				'icons' => [		
					'title' => esc_html__('Icons', Joe_Config::get_item('plugin_text_domain')),
					'description' => '',
					'fields' => [						
						'tracking_icon' => [
							'required' => Joe_Config::get_fallback('appearance', 'icons', 'tracking_icon'),						
							'title' => esc_html__('Tracking Icon', Joe_Config::get_item('plugin_text_domain')),
						],
						'message_icon' => [
							'required' => Joe_Config::get_fallback('appearance', 'icons', 'message_icon'),
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