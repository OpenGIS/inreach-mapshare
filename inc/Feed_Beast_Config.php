<?php
class Feed_Beast_Config {
	//Set defaults
	private static $data = array();
	private static $default = array();
	
	public static function init() {
		$slug = 'feed-beast';
	
		self::$data = array(
			'plugin_slug' => $slug,
			'plugin_name' => 'Feed Beast',
			'plugin_name_short' => 'Feed Beast',		
			'custom_types' => array(),
			'plugin_version' => '0.1',
			'nonce_string' => 'Feed_Beast_Nonce',
			'site_url' => 'https://wordpress.org/support/plugin/' . $slug . '/',
			'directory_url' => 'https://wordpress.org/support/plugin/' . $slug . '/',
			'multi_value_seperator' => '__multi__',
			'shortcode' => 'Feed_Beast',
			
			/**
			 * ===========================================
			 * ================= MISC ====================
			 * ===========================================
			 */
			'misc' => array(
				'advanced' => array(
					'debug_mode' => true
				)
			)
		);
		
		//Keep a copy of the original values
		self::$default = self::$data;
	
		//Read config options from DB
		$settings_data = get_option('Feed_Beast_Settings');

		//Waymark_Helper::debug($settings_data);
		
		//Add settings to config data
		if(is_array($settings_data)) {
			foreach($settings_data as $tab_key => $tab_data) {
				foreach($tab_data as $section_key => $section_data) {
					foreach($section_data as $parameter_key => $parameter_value) {
						self::$data[$tab_key][$section_key][$parameter_key] = $parameter_value;
					}
				}
			}
		}
	}	

	public static function set_item($key = null, $value) {
		if(array_key_exists($key, self::$data)) {
			self::$data[$key] = $value;
		}
	}

	public static function get_item($key, $key_2 = null, $is_repeatable = false) {	
		//Waymark_Helper::debug(self::$data);

		if(array_key_exists($key, self::$data)) {
			if(is_array(self::$data[$key]) && array_key_exists($key_2, self::$data[$key])) {
				//Single value
				if(! $is_repeatable) {
					return self::$data[$key][$key_2];
				//Multi-value
				} else {
					//Convert
					$values = self::$data[$key][$key_2];
					
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
					
					$values = Waymark_Helper::convert_values_to_single_value($values);
					$values = Waymark_Helper::convert_single_value_to_array($values);				
			
					return $values;
				}
			} else {
				if(! $is_repeatable) {
					return self::$data[$key];
				} else {
					return [];
				}
			}			
		} else {
			return null;
		}			
	}

	public static function get_data() {	
		return self::$data;
	}	

	public static function get_default($tab, $group, $key) {	
		if(array_key_exists($tab, self::$default) && array_key_exists($group, self::$default[$tab]) && array_key_exists($key, self::$default[$tab][$group])) {
			return self::$default[$tab][$group][$key];
		} else {
			return false;
		}	
	}

	public static function get_setting($tab, $group, $key) {
		if(array_key_exists($tab, self::$data) && array_key_exists($group, self::$data[$tab]) && array_key_exists($key, self::$data[$tab][$group])) {			
			return self::$data[$tab][$group][$key];
		} else {
			return false;
		}	
	}

	//Helpers
	public static function get_name($short = false, $really_short = false) {
		if(! $short) {
			return self::get_item('plugin_name');				
		} else {
			if(! $really_short) {
				return self::get_item('plugin_name_short');															
			} else {
				return strip_tags(self::get_item('plugin_name_short'));															
			}
		}		
	}	

	public static function get_version() {
		return self::get_item('plugin_version');	
	}	
	
	public static function get_settings_parameters($tab_id = null, $group_id = null) {
		$settings = array();
		
		//If only getting a secific section
		if(array_key_exists($tab_id, self::$parameters) && array_key_exists($group_id, self::$parameters[$tab_id])) {
			$group_data = self::$parameters[$tab_id][$group_id];
			//Iterate over each parameter
			foreach($group_data as $parameter_data) {
				if(array_key_exists('setting', $parameter_data) && $parameter_data['setting']) {
					$settings[] = $parameter_data;
				}
			}								
		}
	
		return $settings;		
	}

	public static function is_custom_type($type = null) {
		//Get from post
		if($type == null) {
			global $post;
			
			//If it exists			
			if(isset($post->post_type)) {
				$type = $post->post_type;			
			}
		}
		
		return in_array($type, self::$data['custom_types']) || in_array($type, self::$data['custom_types']);
	}
}

Feed_Beast_Config::init();
