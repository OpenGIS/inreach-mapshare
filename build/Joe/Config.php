<?php

class Joe_v1_2_Config {
	//Set defaults
	protected static $fallback = [];
	protected static $default = [];
	protected static $data = [
		'plugin_slug' => 'joe',
		'plugin_name' => 'Joe',
		'plugin_name_short' => 'Joe',	
		'plugin_version' => '1.0',
		'plugin_text_domain' => 'joe',
		'css_prefix' => 'joe-',
		'plugin_about' => '<img alt="Joe\'s mug" src="//www.josephhawes.co.uk/assets/images/Joe1BW.jpg" /><p class="joe-first"><b>Joe</b></p>',
 		'multi_value_seperator' => '__multi__',
 		'joe' => [
 			'debug' => [
 				'enabled' => '0'
 			],
 			'cache' => [
 				'minutes' => '15'
 			] 			
 		]
	];

	public static function init($data_in = []) {
		//Merge data provided
		static::$data = array_merge(static::$data, $data_in);

		//Keep copies of the original values
		static::$fallback = static::$data;
		static::$default = static::$data;

		//Read config options from DB
		$settings_data = get_option(static::get_item('settings_id'));

		//Add settings to config data
		if(is_array($settings_data)) {
			foreach($settings_data as $tab_key => $tab_data) {
				foreach($tab_data as $section_key => $section_data) {
					foreach($section_data as $parameter_key => $parameter_value) {
						static::$data[$tab_key][$section_key][$parameter_key] = $parameter_value;
					}
				}
			}	
		}
	}

	public static function get_data() {	
		return static::$data;
	}

	public static function set_item($key = null, $value) {
		if(array_key_exists($key, static::$data)) {
			static::$data[$key] = $value;
		}
	}

	public static function get_item($key, $key_2 = null, $is_repeatable = false) {	
		if(array_key_exists($key, static::$data)) {
			if(is_array(static::$data[$key]) && array_key_exists($key_2, static::$data[$key])) {
				//Single value
				if(! $is_repeatable) {
					return static::$data[$key][$key_2];
				//Multi-value
				} else {
					//Convert
					$values = static::$data[$key][$key_2];
					
					//Pad if necessary
					$max_size = null;
					foreach($values as $key => &$value) {
						//Must be an array
						if(! is_array($value)) {
							continue;
						}
						
						if($max_size !== null && sizeof($value) != $max_size) {
							$value = array_pad(array(), $max_size, $value);
						} else {
							$max_size = sizeof($value);
						}
					}
					
					$values = Joe_v1_2_Helper::convert_values_to_single_value($values);
					$values = Joe_v1_2_Helper::convert_single_value_to_array($values);				
			
					return $values;
				}
			} else {
				if(! $is_repeatable) {
					return static::$data[$key];
				} else {
					return [];
				}
			}			
		} else {
			return null;
		}			
	}

	public static function get_default($tab, $group, $key) {	
		if(array_key_exists($tab, static::$default) && array_key_exists($group, static::$default[$tab]) && array_key_exists($key, static::$default[$tab][$group])) {
			return static::$default[$tab][$group][$key];
		} else {
			return false;
		}	
	}

	public static function get_fallback($tab, $group, $key) {
		if(array_key_exists($tab, static::$fallback) && array_key_exists($group, static::$fallback[$tab]) && array_key_exists($key, static::$fallback[$tab][$group])) {			
			return static::$fallback[$tab][$group][$key];
		} else {
			return false;
		}	
	}

	public static function get_setting($tab, $group, $key, $fallback_if_empty = true) {
		if(array_key_exists($tab, static::$data) && array_key_exists($group, static::$data[$tab]) && array_key_exists($key, static::$data[$tab][$group])) {			
			$value = static::$data[$tab][$group][$key];
			
			if($fallback_if_empty && empty($value) && isset(static::$fallback[$tab][$group][$key])) {
				return static::$fallback[$tab][$group][$key];
			}
			
			return $value;
		} else {
			return false;
		}	
	}

	//Helpers
	public static function get_name($short = false, $really_short = false) {
		if(! $short) {
			return static::get_item('plugin_name');				
		} else {
			if(! $really_short) {
				return static::get_item('plugin_name_short');															
			} else {
				return strip_tags(static::get_item('plugin_name_short'));															
			}
		}		
	}	

	public static function get_version() {
		return static::get_item('plugin_version');	
	}	
	
	public static function get_settings_parameters($tab_id = null, $group_id = null) {
		$settings = array();
		
		//If only getting a secific section
		if(array_key_exists($tab_id, static::$parameters) && array_key_exists($group_id, static::$parameters[$tab_id])) {
			$group_data = static::$parameters[$tab_id][$group_id];
			//Iterate over each parameter
			foreach($group_data as $parameter_data) {
				if(array_key_exists('setting', $parameter_data) && $parameter_data['setting']) {
					$settings[] = $parameter_data;
				}
			}								
		}
	
		return $settings;		
	}
}