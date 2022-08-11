<?php

class Joe_v1_0_Settings {

	private $default_slug = 'options-general.php';
	private $submenu_slug;
	private $submit_button_text = 'Update';
	
	protected $current_settings = [];
	
	public $tabs = [];	
	public $settings_nav = [];

	public function __construct() {
		global $pagenow;
		
		//Not front
		if(! is_admin()) {
			return false;
		}
		
		//Determine page slug
		if(! $this->slug = Joe_v1_0_Config::get_item( 'settings_menu_slug' ) ) {
			$this->slug = $this->default_slug;
		}
		$this->submenu_slug = Joe_v1_0_Helper::slug_prefix('settings', '-');		

		//Add Menu link
		add_action( 'admin_menu', [ $this, 'admin_menu'] );				
		add_action( 'admin_init', [ $this, 'register_settings'] );				

		//Only continue if we are on the Settings page
		if($pagenow != $this->slug || ! isset($_GET['page']) || $_GET['page'] != $this->submenu_slug) {
			return false;		
		}
		
		//Joe Plugin
		$this->add_setting_tab('joe', [
			'sections' => [
				'cache' => [		
					'title' => esc_html__('Cache', Joe_v1_0_Config::get_item('plugin_text_domain')),
					'fields' => [
						'minutes' => [
							'required' => Joe_v1_0_Config::get_fallback('joe', 'cache', 'minutes'),
							'class' => 'joe-short-input',
							'title' => esc_html__('Minutes', Joe_v1_0_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('How often the Cache updates.', Joe_v1_0_Config::get_item('plugin_text_domain'))
						]						
					],
				],
				'debug' => [		
					'title' => esc_html__('Debug', Joe_v1_0_Config::get_item('plugin_text_domain')),
					'fields' => [
						'enabled' => [
							'required' => Joe_v1_0_Config::get_fallback('joe', 'debug', 'enabled'),
							'type' => 'boolean',
							'title' => esc_html__('Enabled', Joe_v1_0_Config::get_item('plugin_text_domain')),
							'tip' => esc_attr__('Display useful infomation to administrators (admin notices and browser console logging).', Joe_v1_0_Config::get_item('plugin_text_domain'))
						]						
					]
				]				
			]
		]);
	
		//Get current settings from DB
		$current_settings = get_option(Joe_v1_0_Config::get_item('settings_id'));
		if(is_array($current_settings) && sizeof($current_settings)) {
			$this->current_settings = $current_settings;
		}	
		
    add_action( 'admin_notices', [ $this, 'admin_notices' ] );	
		
		return true;		
	}
	
	private function add_setting_tab(string $tab_key, array $tab_data) {
		if(! $tab_key || ! $tab_data) {
			return false;
		}
		
		//Create
		if(! array_key_exists($tab_key, $this->tabs)) {
			$this->tabs[$tab_key] = $tab_data;		
		//Merge
		} else {
			$this->tabs[$tab_key] = array_merge($this->tabs[$tab_key], $tab_data);		
		}
	}
	
	//Menu link
	public function admin_menu() {
		if($this->slug != $this->default_slug) {
			$text = esc_html__('Settings', Joe_v1_0_Config::get_item('plugin_text_domain'));		
		} else {
			$text = Joe_v1_0_Helper::plugin_name();						
		}
	
		add_submenu_page($this->slug, $text, $text, 'manage_options', $this->submenu_slug, array($this, 'content_admin_page'));					    
	}

	public function get_settings() {
		return $this->current_settings;
	}


	public function register_settings(){
		register_setting( Joe_v1_0_Config::get_item('settings_id'), Joe_v1_0_Config::get_item( 'settings_id' ), [ $this , 'sanitize_callback' ] );

		//For each tab		
		foreach($this->tabs as $tab_key => $tab_data) {		
			//For each section
			foreach($tab_data['sections'] as $section_key => $section_data) {		
				//Set if blank if unset		
				$section_data['title'] = (isset($section_data['title'])) ? $section_data['title'] : '';
				
				//Create section
				add_settings_section($section_key, $section_data['title'], [ $this, 'section_text' ] , Joe_v1_0_Config::get_item('settings_id'));		
				
				//For each field in section
				if(isset($section_data['fields']) && is_array($section_data['fields']) && sizeof($section_data['fields'])) {
					foreach($section_data['fields'] as $field_id => $field) {
						//Use index as default ID
						if(! isset($field['id'])) {
							$field['id'] = $field_id;
						}
						
						//Use ID for Name (if absent)
						if(! array_key_exists('name', $field)) {
							$field['name'] = $field['id'];
						}
		
						//Get set_value
						if(array_key_exists($tab_key, $this->current_settings) && array_key_exists($section_key, $this->current_settings[$tab_key])) {
							if(array_key_exists($field['name'], $this->current_settings[$tab_key][$section_key])) {
								$setting_val = $this->current_settings[$tab_key][$section_key][$field['name']];
								
								//Check for empty
								if(empty($setting_val) && isset($field['required']) && $field['required']) {	
									//Use fallback
									$field['set_value'] = Joe_v1_0_Config::get_setting($tab_key, $section_key, $field['name'], true);
								} else {
									$field['set_value'] = $setting_val;								
								}
							}
						}
						
						//Modify name for multi-dimensional array
						$field['name'] = Joe_v1_0_Config::get_item('settings_id') . '[' . $tab_key . '][' . $section_key . '][' . $field['name'] . ']';
						
						//Repeatable section
						if(isset($section_data['repeatable']) && $section_data['repeatable']) {
							//Get count
							$repeatable_count = Joe_v1_0_Helper::get_section_repeatable_count($section_data);
							
							//Must be an array
							if(! is_array($field['default']) ) {
								//Make array
								$field['default'] = Joe_v1_0_Helper::convert_single_value_to_array($field['default']);
							}
							
							//Array size must match
							if(sizeof($field['default']) < $repeatable_count) {
								//Pad
								$field['default'] = array_pad($field['default'], $repeatable_count, $field['default'][0]);	 							
							}							
						}	

						add_settings_field($field['name'], $field['title'], [ $this, 'create_input' ], Joe_v1_0_Config::get_item('settings_id'), $section_key, $field);														
					}						
				}			
			}			
		}
	}

	public function content_admin_page() {
		echo '<div id="' . Joe_v1_0_Helper::css_prefix() . 'admin-container">' . "\n";

		echo Joe_v1_0_Helper::plugin_about();

		echo '	<div class="card">' . "\n";	

		//Tabs
		$active_content = (isset($_GET['content'])) ? $_GET['content'] : Joe_v1_0_Config::get_item('settings_default_tab');
		$this->settings_nav($active_content);

		//Prepend?
		echo apply_filters('joe_admin_before_form', '');

		//Open form
		echo '		<form action="' . admin_url('options.php') . '" method="post">' . "\n";
		settings_fields(Joe_v1_0_Config::get_item('settings_id'));

		//For each tab		
		foreach($this->tabs as $tab_key => $tab_data) {
			$style = '';

			echo '	<div class="' . Joe_v1_0_Helper::css_prefix() . 'settings-tab ' . Joe_v1_0_Helper::css_prefix() . 'settings-tab-' . $tab_key . '"' . $style . '>' . "\n";

			//Tab title?
			if(array_key_exists('name', $tab_data)) {
				echo '	<h2 class="' . Joe_v1_0_Helper::css_prefix() . 'settings-tab-title">' . $tab_data['name'] . '</h2>' . "\n";
			}

			//Tab description?
			if(array_key_exists('description', $tab_data)) {
				$tab_description = $tab_data['description'];
				
				echo '	<div class="' . Joe_v1_0_Helper::css_prefix() . 'settings-tab-description">' . $tab_description . '</div>' . "\n";
			}

			//For each section
			foreach($tab_data['sections'] as $section_key => $section_data) {
				$class = (isset($section_data['class'])) ? ' ' . $section_data['class'] : '';
				echo '		<div class="' . Joe_v1_0_Helper::css_prefix() . 'settings-section ' . Joe_v1_0_Helper::css_prefix() . 'settings-section-' . $section_key . $class . '">' . "\n";
				
				//Help
				if(array_key_exists('help', $section_data) && isset($section_data['help']['url'])) {
					$help_text = (isset($section_data['help']['text'])) ? $section_data['help']['text'] : 'View Help &raquo;';

					echo '		<a class="' . Joe_v1_0_Helper::css_prefix() . 'docs-link button" href="' . $section_data['help']['url'] . '" target="_blank">' . $help_text . '</a>' . "\n";				
				}
				
				//Title
				if(isset($section_data['title'])) {
					echo '		<h2>' . $section_data['title'] . '</h2>' . "\n";
				}

				//Description
				if(array_key_exists('description', $section_data)) {
					echo '		<div class="' . Joe_v1_0_Helper::css_prefix() . 'settings-section-description">' . $section_data['description'] . '</div>' . "\n";
				}		
				
				//Repeatable?
				if(array_key_exists('repeatable', $section_data) && $section_data['repeatable']) {
					echo '<div class="' . Joe_v1_0_Helper::css_prefix() . 'repeatable" data-count="0">' . "\n";
				}
				
        echo '		<table class="form-table">' . "\n";
        do_settings_fields(Joe_v1_0_Config::get_item('settings_id'), $section_key);					
        echo '		</table>' . "\n";        

				//Repeatable?
				if(array_key_exists('repeatable', $section_data) && $section_data['repeatable']) {
					echo '</div>' . "\n";
				}

				//Footer
				if(array_key_exists('footer', $section_data)) {
					echo '	<div class="' . Joe_v1_0_Helper::css_prefix() . 'settings-section-footer">' . $section_data['footer'] . '</div>' . "\n";
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
		if(array_key_exists('set_value', $field)) {
			$set_value = $field['set_value'];
		} else {
			$set_value = null;
		}

		echo Joe_v1_0_Input::create_field($field, $set_value, false);
	}	

	public function section_text($args) {
		//Unused
	}
	
	public function sanitize_callback($input_data) {
		//For each tab
		foreach($this->tabs as $tab_key => $tab_data) {
			//If we have sections
			if(array_key_exists('sections', $tab_data)) {
				//Iterate over each section
				foreach($tab_data['sections'] as $section_key => $section_data) {
					//If section has fields
					if(array_key_exists('fields', $section_data)) {
						//For each field
						foreach($section_data['fields'] as $field_key => $field_definition) {
							//Name passed?
							if(! isset($field_definition['name'])) {
								$field_definition['name'] = $field_key;
							}
							
							//If this field was submitted
							if(isset($input_data[$tab_key][$section_key][$field_definition['name']])) {															
								$value = $input_data[$tab_key][$section_key][$field_definition['name']];
								
								//If no input processing specified
								if(! array_key_exists('input_processing', $field_definition)) {
									//Make safe by default
									$field_definition['input_processing'][] = 'htmlspecialchars($param_value)';								
								}
																						
								//Process the input
								$input_data[$tab_key][$section_key][$field_definition['name']] = Joe_v1_0_Input::process_input($field_definition, $value);
							}
						}					
					}
				}				
			}
		}
		
		return $input_data;
	}	

	public function settings_nav($current = 'tiles') {
		if(! sizeof($this->settings_nav)) {
			return;
		}
		
		echo '<div id="' . Joe_v1_0_Helper::css_prefix() . 'settings-nav" data-init_tab_key="' . $current . '">' . "\n";
		echo '	<select>' . "\n";

		foreach($this->settings_nav as $content_id => $content_title) {
			if(strpos($content_id, 'label') === 0) {
				echo '	<option disabled="disabled">' . $content_title . '</option>' . "\n";				
			} else {
				echo '	<option value="' . $content_id . '"' . (($current == $content_id) ? ' selected="selected"' : '') . '>' . $content_title . '</option>' . "\n";				
			}
		}

		echo '	</select>' . "\n";
		echo '</div>' . "\n";
	}
}