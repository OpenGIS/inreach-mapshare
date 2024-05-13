<?php

class InMap_Settings {

	private $default_slug = 'options-general.php';
	private $submenu_slug;
	private $submit_button_text = 'Update';
	private $slug;
	private $shortcode = '';
	private $shortcode_output = '';

	protected $current_settings = [];

	public $tabs = [];
	public $settings_nav = [];

	public function __construct() {

		global $pagenow;

		//Not front
		if (!is_admin()) {
			return false;
		}

		//Determine page slug
		if (!$this->slug = InMap_Config::get_item('settings_menu_slug')) {
			$this->slug = $this->default_slug;
		}
		$this->submenu_slug = InMap_Helper::slug_prefix('settings', '-');

		//Add Menu link
		add_action('admin_menu', [$this, 'admin_menu']);
		add_action('admin_init', [$this, 'register_settings']);

		//Only continue if we are on *OUR* Settings page

		//Get
		if (!sizeof($_POST)) {
			if ($pagenow != $this->slug) {
				return false;
			}

			//Check URL
			if (!isset($_GET['page']) || $_GET['page'] != $this->submenu_slug) {
				return false;
			}
			//Post
		} else {
			//Check submission
			if (!isset($_POST['option_page']) || $_POST['option_page'] != InMap_Config::get_item('settings_id')) {
				return false;
			}
		}

		//Joe Plugin
		$this->add_setting_tab('inmap', [
			'sections' => [
				'cache' => [
					'title' => esc_html__('Cache', InMap_Config::get_item('plugin_text_domain')),
					'fields' => [
						'minutes' => [
							'required' => InMap_Config::get_fallback('inmap', 'cache', 'minutes'),
							'class' => 'inmap-short-input',
							'title' => esc_html__('Minutes', InMap_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('How often the Cache updates.', InMap_Config::get_item('plugin_text_domain')),
						],
					],
				],
				'debug' => [
					'title' => esc_html__('Debug', InMap_Config::get_item('plugin_text_domain')),
					'fields' => [
						'enabled' => [
							'required' => InMap_Config::get_fallback('inmap', 'debug', 'enabled'),
							'type' => 'boolean',
							'title' => esc_html__('Enabled', InMap_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('Display useful infomation to administrators (admin notices and browser console logging).', InMap_Config::get_item('plugin_text_domain')),
						],
					],
				],
			],
		]);

		//Get current settings from DB
		$current_settings = get_option(InMap_Config::get_item('settings_id'));
		if (is_array($current_settings) && sizeof($current_settings)) {
			$this->current_settings = $current_settings;
		}

		$this->do_shortcode();

		$this->settings_nav = [
			'inmap-settings-tab-shortcode' => '-- ' . esc_html__('Shortcodes', InMap_Config::get_item('plugin_text_domain')),
			'inmap-settings-tab-appearance' => '-- ' . esc_html__('Appearance', InMap_Config::get_item('plugin_text_domain')),
			'inmap-settings-tab-inmap' => '-- ' . esc_html__('Advanced', InMap_Config::get_item('plugin_text_domain')),
		];

		//Switch tabs
		if (InMap_Config::get_setting('mapshare', 'defaults', 'mapshare_identifier')) {
			InMap_Config::set_item('settings_default_tab', 'inmap-settings-tab-shortcode');
		}

		//Text
		add_filter('joe_admin_before_form', [$this, 'joe_admin_before_form']);

		//Build shortcode
		add_filter('joe_admin_after_form', [$this, 'joe_admin_after_form']);

		$this->tabs['shortcode'] = [
			'sections' => [
				'build' => [
					'title' => esc_html__('Shortcodes', InMap_Config::get_item('plugin_text_domain')),
					'fields' => [
						'mapshare_identifier' => [
							'required' => 'demo',
							'id' => 'mapshare_identifier',
							'title' => esc_html__('MapShare Identifier', InMap_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('This is found in the Social tab of your Garmin Explore acount.', InMap_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://explore.garmin.com/Social',
							'prepend' => 'share.garmin.com/',
							//Remove all non-alphanemeric
							'input_processing' => [
								'strip_special',
							],
						],
						'mapshare_password' => [
							'title' => esc_html__('MapShare Password', InMap_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('It is recommended that you protect your MapShare page from public access by setting a password. This plugin requires that password request your MapShare data, ***HOWEVER*** it does not protect it from public access.', InMap_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://explore.garmin.com/Social',
						],
						'mapshare_date_start' => [
							'type' => 'datetime-local',
							'title' => esc_html__('Start Date', InMap_Config::get_item('plugin_text_domain')),
							'tip' => esc_html__('Display data starting from this date and time (UTC time yyyy-mm-ddThh:mm, e.g. 2022-12-31T00:00). Leave both Start and End date/time blank to only display your most recent MapShare location.', InMap_Config::get_item('plugin_text_domain')),
						],
						'mapshare_date_end' => [
							'id' => 'mapshare_date_end',
							'type' => 'datetime-local',
							'title' => esc_html__('End Date', InMap_Config::get_item('plugin_text_domain')),
							'tip' => esc_html__('Strongly recommended! Display data until this date and time (UTC time yyyy-mm-ddThh:mm, e.g. 2022-12-31T23:59). Be careful when creating Shortcodes with no end date, all future MapShare data will be displayed!', InMap_Config::get_item('plugin_text_domain')),
						],
					],
				],
			],
		];

		//Map
		$this->tabs['appearance'] = [
			'sections' => [
				'map' => [
					'title' => esc_html__('Map', InMap_Config::get_item('plugin_text_domain')),
					'fields' => [
						'basemap_url' => [
							'required' => InMap_Config::get_fallback('appearance', 'map', 'basemap_url'),
							'title' => esc_html__('Basemap URL', InMap_Config::get_item('plugin_text_domain')),
							'tip' => esc_html__('The URL to a "slippy map" tile service, this needs to contain the characters {z},{x} and {y}. OpenStreetMap is used by default.', InMap_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://leaflet-extras.github.io/leaflet-providers/preview/',
						],
						'basemap_attribution' => [
							'required' => InMap_Config::get_fallback('appearance', 'map', 'basemap_attribution'),
							'title' => esc_html__('Basemap Attribution', InMap_Config::get_item('plugin_text_domain')),
							'tip' => esc_html__('Mapping services often have the requirement that attribution is displayed by the map. Text and HTML links are supported.', InMap_Config::get_item('plugin_text_domain')),
							'input_processing' => array(
								'encode_special',
							),
							'output_processing' => array(
								'encode_special',
							),
						],
					],
				],
				'colours' => [
					'title' => esc_html__('Colours', InMap_Config::get_item('plugin_text_domain')),
					'fields' => [
						'tracking_colour' => [
							'type' => 'text',
							'class' => 'color inmap-colour-picker',
							'required' => InMap_Config::get_fallback('appearance', 'colours', 'tracking_colour'),
							'title' => esc_html__('Tracking Colour', InMap_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('This is the primary colour used. Customise further by adding custom CSS rules.', InMap_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://wordpress.org/support/article/css/#custom-css-in-wordpress',
						],
					],
				],
				'icons' => [
					'title' => esc_html__('Icons', InMap_Config::get_item('plugin_text_domain')),
					'fields' => [
						'tracking_icon' => [
							'required' => InMap_Config::get_fallback('appearance', 'icons', 'tracking_icon'),
							'title' => esc_html__('Tracking Icon', InMap_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('The URL to a SVG image file to use as an icon for tracking points.', InMap_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://www.svgrepo.com/vectors/location/',
						],
						'message_icon' => [
							'required' => InMap_Config::get_fallback('appearance', 'icons', 'message_icon'),
							'title' => esc_html__('Message Icon', InMap_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('The URL to a SVG image file to use as an icon for message points.', InMap_Config::get_item('plugin_text_domain')),
							'tip_link' => 'https://www.svgrepo.com/vectors/envelope/',
						],
					],
				],
			],
		];
	}

	function do_shortcode() {
		InMap_Log::reset();
		InMap_Log::set_output_type('notice');

		$this->shortcode = '[';
		$this->shortcode .= InMap_Config::get_item('plugin_shortcode');
		foreach ([
			'mapshare_identifier',
			'mapshare_password',
			'mapshare_date_start',
			'mapshare_date_end',
		] as $key) {
			$value = InMap_Config::get_setting('shortcode', 'build', $key);

			if (!empty($value)) {
				$this->shortcode .= ' ' . $key . '="' . InMap_Config::get_setting('shortcode', 'build', $key) . '"';
			}
		}
		$this->shortcode .= ']';

		//Execute Shortcode (and Garmin request)
		$this->shortcode_output = do_shortcode($this->shortcode);

		if (InMap_Log::has('do_demo')) {
			$this->shortcode = '[' . InMap_Config::get_item('plugin_shortcode') . ' mapshare_identifier="demo"]';
		}

		InMap_Log::render();
	}

	function joe_admin_after_form($out) {
		//Success
		if (!InMap_Log::in_error()) {
			//Shortcode output
			$out .= '<p class="inmap-lead">' . __('Add wherever Shortcodes are supported.', InMap_Config::get_item('plugin_text_domain')) . '</p>';
			$out .= '<div class="inmap-shortcode">' . $this->shortcode . '</div>';

			//Actual output
			$out .= $this->shortcode_output;
		}

		return $out;
	}

	function joe_admin_before_form($out) {
		//Demo
		if (InMap_Log::has('do_demo')) {
			$out .= '<p class="inmap-lead">' . sprintf(__('Configure MapShare in the <a href="%s">Social</a> tab of your Garmin Explore Account.', InMap_Config::get_item('plugin_text_domain')), 'https://explore.garmin.com/Social') . '</p>';

			$out .= '<p>' . sprintf(__('<strong>Important!</strong> Even if you have a MapShare password set, <em>this plugin</em> simply uses it to request your data; it <strong>does not</strong> protect it from being viewed. You are responsible for <a href="%s">protecting access</a> if needed.', InMap_Config::get_item('plugin_text_domain')), 'https://wordpress.org/support/article/using-password-protection/') . '</p>';
		}

		return $out;
	}

	private function add_setting_tab(string $tab_key, array $tab_data) {
		if (!$tab_key || !$tab_data) {
			return false;
		}

		//Create
		if (!array_key_exists($tab_key, $this->tabs)) {
			$this->tabs[$tab_key] = $tab_data;
			//Merge
		} else {
			$this->tabs[$tab_key] = array_merge($this->tabs[$tab_key], $tab_data);
		}
	}

	//Menu link
	public function admin_menu() {
		if ($this->slug != $this->default_slug) {
			$text = esc_html__('Settings', InMap_Config::get_item('plugin_text_domain'));
		} else {
			$text = InMap_Helper::plugin_name();
		}

		add_submenu_page($this->slug, $text, $text, 'manage_options', $this->submenu_slug, array($this, 'content_admin_page'));
	}

	public function get_settings() {
		return $this->current_settings;
	}

	public function register_settings() {
		register_setting(InMap_Config::get_item('settings_id'), InMap_Config::get_item('settings_id'), [$this, 'sanitize_callback']);

		//For each tab
		foreach ($this->tabs as $tab_key => $tab_data) {
			//For each section
			foreach ($tab_data['sections'] as $section_key => $section_data) {
				//Set if blank if unset
				$section_data['title'] = (isset($section_data['title'])) ? $section_data['title'] : '';

				//Create section
				add_settings_section($section_key, $section_data['title'], [$this, 'section_text'], InMap_Config::get_item('settings_id'));

				//For each field in section
				if (isset($section_data['fields']) && is_array($section_data['fields']) && sizeof($section_data['fields'])) {
					foreach ($section_data['fields'] as $field_id => $field) {
						//Use index as default ID
						if (!isset($field['id'])) {
							$field['id'] = $field_id;
						}

						//Use ID for Name (if absent)
						if (!array_key_exists('name', $field)) {
							$field['name'] = $field['id'];
						}

						//Get set_value
						if (array_key_exists($tab_key, $this->current_settings) && array_key_exists($section_key, $this->current_settings[$tab_key])) {
							if (array_key_exists($field['name'], $this->current_settings[$tab_key][$section_key])) {
								$setting_val = $this->current_settings[$tab_key][$section_key][$field['name']];

								//Check for empty
								if (empty($setting_val) && isset($field['required']) && $field['required']) {
									//Use fallback
									$field['set_value'] = InMap_Config::get_setting($tab_key, $section_key, $field['name'], true);
								} else {
									$field['set_value'] = $setting_val;
								}
							}
						}

						//Modify name for multi-dimensional array
						$field['name'] = InMap_Config::get_item('settings_id') . '[' . $tab_key . '][' . $section_key . '][' . $field['name'] . ']';

						//Repeatable section
						if (isset($section_data['repeatable']) && $section_data['repeatable']) {
							//Get count
							$repeatable_count = InMap_Helper::get_section_repeatable_count($section_data);

							//Must be an array
							if (!is_array($field['default'])) {
								//Make array
								$field['default'] = InMap_Helper::convert_single_value_to_array($field['default']);
							}

							//Array size must match
							if (sizeof($field['default']) < $repeatable_count) {
								//Pad
								$field['default'] = array_pad($field['default'], $repeatable_count, $field['default'][0]);
							}
						}

						add_settings_field($field['name'], $field['title'], [$this, 'create_input'], InMap_Config::get_item('settings_id'), $section_key, $field);
					}
				}
			}
		}
	}

	public function content_admin_page() {
		echo '<div id="' . InMap_Helper::css_prefix() . 'admin-container">' . "\n";

		echo InMap_Helper::plugin_about();

		echo '	<div class="card">' . "\n";

		//Tabs
		$active_content = (isset($_GET['content'])) ? $_GET['content'] : InMap_Config::get_item('settings_default_tab');
		$this->settings_nav($active_content);

		//Prepend?
		echo apply_filters('joe_admin_before_form', '');

		//Open form
		echo '		<form action="' . admin_url('options.php') . '" method="post">' . "\n";
		settings_fields(InMap_Config::get_item('settings_id'));

		//For each tab
		foreach ($this->tabs as $tab_key => $tab_data) {
			echo '	<div class="' . InMap_Helper::css_prefix() . 'settings-tab ' . InMap_Helper::css_prefix() . 'settings-tab-' . esc_attr($tab_key) . '">' . "\n";

			//Tab title?
			if (array_key_exists('name', $tab_data)) {
				echo '	<h2 class="' . InMap_Helper::css_prefix() . 'settings-tab-title">' . $tab_data['name'] . '</h2>' . "\n";
			}

			//Tab description?
			if (array_key_exists('description', $tab_data)) {
				$tab_description = $tab_data['description'];

				echo '	<div class="' . InMap_Helper::css_prefix() . 'settings-tab-description">' . $tab_description . '</div>' . "\n";
			}

			//For each section
			foreach ($tab_data['sections'] as $section_key => $section_data) {
				$class = (isset($section_data['class'])) ? ' ' . $section_data['class'] : '';
				echo '		<div class="' . InMap_Helper::css_prefix('settings-section') . ' ' . InMap_Helper::css_prefix('settings-section-' . $section_key . $class) . '">' . "\n";

				//Help
				if (array_key_exists('help', $section_data) && isset($section_data['help']['url'])) {
					$help_text = (isset($section_data['help']['text'])) ? $section_data['help']['text'] : 'View Help &raquo;';

					echo '		<a class="' . InMap_Helper::css_prefix('docs-link button') . '" href="' . esc_url_raw($section_data['help']['url']) . '" target="_blank">' . $help_text . '</a>' . "\n";
				}

				//Title
				if (isset($section_data['title'])) {
					echo '		<h2>' . $section_data['title'] . '</h2>' . "\n";
				}

				//Description
				if (array_key_exists('description', $section_data)) {
					echo '		<div class="' . InMap_Helper::css_prefix() . 'settings-section-description">' . $section_data['description'] . '</div>' . "\n";
				}

				//Repeatable?
				if (array_key_exists('repeatable', $section_data) && $section_data['repeatable']) {
					echo '<div class="' . InMap_Helper::css_prefix() . 'repeatable" data-count="0">' . "\n";
				}

				echo '		<table class="form-table">' . "\n";
				do_settings_fields(InMap_Config::get_item('settings_id'), $section_key);
				echo '		</table>' . "\n";

				//Repeatable?
				if (array_key_exists('repeatable', $section_data) && $section_data['repeatable']) {
					echo '</div>' . "\n";
				}

				//Footer
				if (array_key_exists('footer', $section_data)) {
					echo '	<div class="' . InMap_Helper::css_prefix() . 'settings-section-footer">' . $section_data['footer'] . '</div>' . "\n";
				}

				echo '</div>' . "\n";
			}

			echo '	</div>' . "\n";
		}

		submit_button($this->submit_button_text, 'primary large');
		echo '		</form>' . "\n";

		//Append?
		echo apply_filters('joe_admin_after_form', '');

		echo '	</div>' . "\n";
		echo '</div>' . "\n";
	}

	public function create_input($field) {
		//Set value
		if (array_key_exists('set_value', $field)) {
			$set_value = $field['set_value'];
		} else {
			$set_value = null;
		}

		echo InMap_Input::create_field($field, $set_value, false);
	}

	public function section_text($args) {
		//Unused
	}

	public function sanitize_callback($input_data) {
		//For each tab
		foreach ($this->tabs as $tab_key => $tab_data) {
			//If we have sections
			if (array_key_exists('sections', $tab_data)) {
				//Iterate over each section
				foreach ($tab_data['sections'] as $section_key => $section_data) {
					//If section has fields
					if (array_key_exists('fields', $section_data)) {
						//For each field
						foreach ($section_data['fields'] as $field_key => $field_definition) {
							//Name passed?
							if (!isset($field_definition['name'])) {
								$field_definition['name'] = $field_key;
							}

							//If this field was submitted
							if (isset($input_data[$tab_key][$section_key][$field_definition['name']])) {
								$value = $input_data[$tab_key][$section_key][$field_definition['name']];

								//If no input processing specified
								if (!array_key_exists('input_processing', $field_definition)) {
									//Make safe by default
									$field_definition['input_processing'][] = 'encode_special';
								}

								//Process the input
								$input_data[$tab_key][$section_key][$field_definition['name']] = InMap_Input::process_input($field_definition, $value);
							}
						}
					}
				}
			}
		}

		return $input_data;
	}

	public function settings_nav($current = '') {
		if (!sizeof($this->settings_nav)) {
			return;
		}

		echo '<div id="' . InMap_Helper::css_prefix() . 'settings-nav" data-init_tab_key="' . esc_attr($current) . '">' . "\n";
		echo '	<select>' . "\n";

		foreach ($this->settings_nav as $content_id => $content_title) {
			if (strpos($content_id, 'label') === 0) {
				echo '	<option disabled="disabled">' . esc_html($content_title) . '</option>' . "\n";
			} else {
				echo '	<option value="' . esc_attr($content_id) . '"' . (($current == $content_id) ? ' selected="selected"' : '') . '>' . esc_html($content_title) . '</option>' . "\n";
			}
		}

		echo '	</select>' . "\n";
		echo '</div>' . "\n";
	}

}