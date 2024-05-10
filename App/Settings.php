<?php

class InMap_Settings extends Joe_Settings {
	
	private $shortcode = '';
	private $shortcode_output = '';	
	
	public function __construct() {
		if(! parent::__construct()) {
			return;
		}
		
		$this->do_shortcode();
		
		$this->settings_nav = [
			'joe-settings-tab-shortcode' => '-- ' . esc_html__('Shortcodes', Joe_Config::get_item('plugin_text_domain')),
			'joe-settings-tab-appearance' => '-- ' . esc_html__('Appearance', Joe_Config::get_item('plugin_text_domain')),
			'joe-settings-tab-joe' => '-- ' . esc_html__('Advanced', Joe_Config::get_item('plugin_text_domain'))
		];

		//Switch tabs
		if(Joe_Config::get_setting('mapshare', 'defaults', 'mapshare_identifier')) {	
			Joe_Config::set_item('settings_default_tab', 'joe-settings-tab-shortcode');
		}
		
		//Text
		add_filter('joe_admin_before_form', [ $this, 'joe_admin_before_form' ] );

		//Build shortcode
		add_filter('joe_admin_after_form', [ $this, 'joe_admin_after_form' ] );

		
		$this->tabs['shortcode'] = [
			'sections' => [
				'build' => [	
					'title' => esc_html__('Shortcodes', Joe_Config::get_item('plugin_text_domain')),
					'fields' => [
						'mapshare_identifier' => [
							'required' => 'demo',
							'id' => 'mapshare_identifier',
							'title' => esc_html__('MapShare Identifier', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('This is found in the Social tab of your Garmin Explore acount.', Joe_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://explore.garmin.com/Social',
							'prepend' => 'share.garmin.com/',
							//Remove all non-alphanemeric
							'input_processing' => [
								'strip_special'
							]
						],
						'mapshare_password' => [
							'title' => esc_html__('MapShare Password', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('It is recommended that you protect your MapShare page from public access by setting a password. This plugin requires that password request your MapShare data, ***HOWEVER*** it does not protect it from public access.', Joe_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://explore.garmin.com/Social'
						],
						'mapshare_date_start' => [
							'type' => 'datetime-local',
							'title' => esc_html__('Start Date', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_html__('Display data starting from this date and time (UTC time yyyy-mm-ddThh:mm, e.g. 2022-12-31T00:00). Leave both Start and End date/time blank to only display your most recent MapShare location.', Joe_Config::get_item('plugin_text_domain')),
						],						
						'mapshare_date_end' => [
							'id' => 'mapshare_date_end',
							'type' => 'datetime-local',
							'title' => esc_html__('End Date', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_html__('Strongly recommended! Display data until this date and time (UTC time yyyy-mm-ddThh:mm, e.g. 2022-12-31T23:59). Be careful when creating Shortcodes with no end date, all future MapShare data will be displayed!', Joe_Config::get_item('plugin_text_domain')),							
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
					'fields' => [
						'basemap_url' => [
							'required' => Joe_Config::get_fallback('appearance', 'map', 'basemap_url'),
							'title' => esc_html__('Basemap URL', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_html__('The URL to a "slippy map" tile service, this needs to contain the characters {z},{x} and {y}. OpenStreetMap is used by default.', Joe_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://leaflet-extras.github.io/leaflet-providers/preview/'
						],
						'basemap_attribution' => [
							'required' => Joe_Config::get_fallback('appearance', 'map', 'basemap_attribution'),
							'title' => esc_html__('Basemap Attribution', Joe_Config::get_item('plugin_text_domain')),
							'tip' => esc_html__('Mapping services often have the requirement that attribution is displayed by the map. Text and HTML links are supported.', Joe_Config::get_item('plugin_text_domain')),
							'input_processing' => array(
								'encode_special'
							),
							'output_processing' => array(
								'encode_special'
							)																						
						],
						'location_precision' => [
							'type' => 'select',
							'default' => '10cm',
							'options' => [
								'6' => '10cm',
								'5' => '1m',
								'4' => '10m',
								'3' => '100m',
								'2' => '1km',
								'1' => '10km',
 							],
							'title' => esc_html__('Location Precision', Joe_Config::get_item('plugin_text_domain')),  
							'tip' => esc_html__('Select the precision level of coordinates used to build the map. Garmin provides values to a precision of 10cm. Choose a larger value if you want the displayed coordinates to be less precise.', Joe_Config::get_item('plugin_text_domain')),  
						]
					]
				],
				'colours' => [		
					'title' => esc_html__('Colours', Joe_Config::get_item('plugin_text_domain')),
					'fields' => [						
						'tracking_colour' => [
							'type' => 'text',
							'class' => 'color joe-colour-picker',
							'required' => Joe_Config::get_fallback('appearance', 'colours', 'tracking_colour'),
							'title' => esc_html__('Tracking Colour', Joe_Config::get_item('plugin_text_domain')),
 							'tip' => esc_attr__('This is the primary colour used. Customise further by adding custom CSS rules.', Joe_Config::get_item('plugin_text_domain')),
 							'tip_link' => 'https://wordpress.org/support/article/css/#custom-css-in-wordpress'
						]																																									
					]											
				],
				'icons' => [		
					'title' => esc_html__('Icons', Joe_Config::get_item('plugin_text_domain')),
					'fields' => [						
						'tracking_icon' => [
							'required' => Joe_Config::get_fallback('appearance', 'icons', 'tracking_icon'),						
							'title' => esc_html__('Tracking Icon', Joe_Config::get_item('plugin_text_domain')),
 							'tip' => esc_attr__('The URL to a SVG image file to use as an icon for tracking points.', Joe_Config::get_item('plugin_text_domain')),
 							'tip_link' => 'https://www.svgrepo.com/vectors/location/'							
						],
						'message_icon' => [
							'required' => Joe_Config::get_fallback('appearance', 'icons', 'message_icon'),
							'title' => esc_html__('Message Icon', Joe_Config::get_item('plugin_text_domain')),
 							'tip' => esc_attr__('The URL to a SVG image file to use as an icon for message points.', Joe_Config::get_item('plugin_text_domain')),
 							'tip_link' => 'https://www.svgrepo.com/vectors/envelope/'							
						]																																										
					]											
				]
			]
		];											
	}

	function do_shortcode()  {
		Joe_Log::reset();
		Joe_Log::set_output_type('notice');
	
		$this->shortcode = '[';
		$this->shortcode .= Joe_Config::get_item('plugin_shortcode');
		foreach([
			'mapshare_identifier',
			'mapshare_password',
			'mapshare_date_start',
			'mapshare_date_end'
		] as $key) {
			$value = Joe_Config::get_setting('shortcode', 'build', $key);
		
			if(! empty($value)) {
				$this->shortcode .= ' ' . $key . '="' . Joe_Config::get_setting('shortcode', 'build', $key) . '"';
			}
		}
		$this->shortcode .= ']';
	
		//Execute Shortcode (and Garmin request)
		$this->shortcode_output = do_shortcode($this->shortcode);

		if(Joe_Log::has('do_demo')) {
			$this->shortcode = '[' . Joe_Config::get_item('plugin_shortcode') . ' mapshare_identifier="demo"]';
		}

		Joe_Log::render();
	}

	function joe_admin_after_form($out) {
		//Success
		if(! Joe_Log::in_error()) {		
			//Shortcode output
			$out .= '<p class="joe-lead">' . __('Add wherever Shortcodes are supported.', Joe_Config::get_item('plugin_text_domain')) . '</p>';
			$out .= '<div class="joe-shortcode">' . $this->shortcode . '</div>';				

			//Actual output
			$out .= $this->shortcode_output;			
		}			
		
		return $out;	
	}	

	function joe_admin_before_form($out) {
		//Demo
		if(Joe_Log::has('do_demo')) {
			$out .= '<p class="joe-lead">' . sprintf(__('Configure MapShare in the <a href="%s">Social</a> tab of your Garmin Explore Account.', Joe_Config::get_item('plugin_text_domain')), 'https://explore.garmin.com/Social') . '</p>';

			$out .= '<p>' . sprintf(__('<strong>Important!</strong> Even if you have a MapShare password set, <em>this plugin</em> simply uses it to request your data; it <strong>does not</strong> protect it from being viewed. You are responsible for <a href="%s">protecting access</a> if needed.', Joe_Config::get_item('plugin_text_domain')), 'https://wordpress.org/support/article/using-password-protection/') . '</p>';
		}
		
		return $out;
	}	
}
