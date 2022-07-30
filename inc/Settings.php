<?php

class InMap_Settings extends Joe_Settings {
	public function __construct() {
		if(! parent::__construct()) {
			return;
		}
		
		Joe_Log::reset();

		$this->settings_nav = [
			'joe-settings-tab-shortcode' => '-- ' . esc_html__('Shortcodes', Joe_Config::get_item('plugin_text_domain')),
			'joe-settings-tab-appearance' => '-- ' . esc_html__('Appearance', Joe_Config::get_item('plugin_text_domain')),
			'joe-settings-tab-joe' => '-- ' . esc_html__('Advanced', Joe_Config::get_item('plugin_text_domain'))
		];

		//Switch tabs
		if(Joe_Config::get_setting('mapshare', 'defaults', 'mapshare_identifier')) {	
			Joe_Config::set_item('settings_default_tab', 'joe-settings-tab-shortcode');
		}
		
		//Build shortcode
		add_filter('joe_admin_after_form', function($out) {
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
		
			//Execute Shortcode (and Garmin request)
			$shortcode_output = do_shortcode($shortcode);

			Joe_Log::set_output_type('notice');
			Joe_Log::render();
		
			//Success
			if(! Joe_Log::in_error()) {		
				//Display Shortcode - Not for demo
				if(! Joe_Log::has('do_demo')) {
					$out .= '<p class="joe-lead">' . __('Add wherever Shortcodes are supported.', Joe_Config::get_item('plugin_text_domain')) . '</p>';
					$out .= '<pre class="joe-shortcode"><code>' . $shortcode . '</code></pre>';
				}
			
				//Actual output
				$out .= $shortcode_output;			
			}			
			
			return $out;
		});

		//Shortcode builder
		$this->tabs['shortcode'] = [
// 			'description' => $description,
			'sections' => [
				'build' => [	
					'title' => esc_html__('Build a Shortcode', Joe_Config::get_item('plugin_text_domain')),
					'fields' => [
						'mapshare_identifier' => [
							'required' => 'demo',
							'id' => 'mapshare_identifier',
							'title' => esc_html__('MapShare Address', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('This is found in the Social tab', Joe_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://explore.garmin.com/Social',
							'prepend' => 'share.garmin.com/',
							//Remove all non-alphanemeric
							'input_processing' => [
								'preg_replace("/[^\da-z]/i", "", $param_value);'
							]
						],
						'mapshare_password' => [
							'title' => esc_html__('MapShare Password', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('The (optional) password for your MapShare page, set in MapShare Settings. This is *not* any kind of account password.', Joe_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://explore.garmin.com/Social'
						],
						'mapshare_date_start' => [
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
							),
							'output_processing' => array(
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