<?php

class Joe_v1_0_Helper {

	static public function make_hash($data, $length = 6) {
		if(! is_string($data)) {
			$data = json_encode($data);
		}
		
		return substr(md5($data), 0, $length);
	}

	static public function site_url($url_path = '') {
		return Joe_v1_0_Config::get_item('site_url') . $url_path;
	}

	static public function plugin_url($file_path = '', $plugin_slug = '') {	
		if(! $plugin_slug) {
			$plugin_slug = Joe_v1_0_Config::get_item('plugin_slug');
		}
		
		return plugin_dir_url('') . $plugin_slug . '/' . $file_path;
	}

	static public function plugin_file_path($file_path = '', $plugin_slug = '') {	
		if(! $file_path) {
			$file_path = Joe_v1_0_Config::get_item('plugin_slug') . '.php';
		}

		if(! $plugin_slug) {
			$plugin_slug = Joe_v1_0_Config::get_item('plugin_slug');
		}
		
		return $plugin_slug . '/' . $file_path;
	}

	static public function asset_url($file_path = '', $plugin_slug = '') {	
		if(! $plugin_slug) {
			$plugin_slug = Joe_v1_0_Config::get_item('plugin_slug');
		}

		return plugin_dir_url('') . $plugin_slug . '/assets/' . $file_path;
	}
	
	static public function plugin_name($short = false) {
		if(! $short) {
			return Joe_v1_0_Config::get_item('plugin_name');
		} else {
			return Joe_v1_0_Config::get_item('plugin_name_short');
		}
	}

	static public function plugin_about() {
		$out = '	<div id="' . Joe_v1_0_Helper::css_prefix('about') . '">' . "\n";		
		$out .= Joe_v1_0_Config::get_item('plugin_about');
		$out .= '	</div>' . "\n";		
		
		return $out;
	}	

	static public function do_debug() {
		return Joe_v1_0_Config::get_setting('joe', 'debug', 'enabled')	 == 1;
	}

	static public function debug($thing, $die = false) {
		if(!	self::do_debug()) {
			return;
		}
		
		if(! $die) {			
			echo '<textarea onclick="jQuery(this).hide()" style="background:rgba(255,255,255,.8);position:absolute;top:30px;right:0;width:400px;height:400px;padding:15px;z-index:+10000000"><pre>';
		}

		print_r($thing);

		if(! $die) {			
			echo '</pre></textarea>';
		} else {
			die;
		}
	}

	static public function make_key($str, $prefix = '', $use_underscores = true) {
		$str = str_replace(' ', '_', $str);

		if($prefix) {
			$str = $prefix . '_' . $str;	
		}
		
		//Like in JS
		if(! $use_underscores) {
			$str = str_replace('_', '', $str);		
		}
		
		$str = strtolower($str);
		$str = preg_replace('/[^a-z0-9+_]+/i', '', $str);
		
		return $str;
	}

	static public function anchor_urls(string $text) {
		//Thanks! https://stackoverflow.com/a/580163
		return preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', $text);
	}

	public static function convert_values_to_single_value($array_in) {
		$array_out = array();
		
		if(! is_array($array_in)) {
			return $array_out;
		}
					
		foreach($array_in as $key => $value) {
			//Single value
			if(! is_array($value)) {
				//Use that
				$array_out[$key] = $value;
			//Multiple values
			} else {
				//Single value, use that
				$array_out[$key] = implode(Joe_v1_0_Config::get_item('multi_value_seperator'), $value);
			}
		}	
		
		return $array_out;
	}
	
	public static function convert_single_value_to_array($value_in) {
		//Array
		if(is_array($value_in)) {
			$array_out = array();
		
			foreach($value_in as $key => $value) {
				$multi = explode(Joe_v1_0_Config::get_item('multi_value_seperator'), $value);			

				$count = 0;
				foreach($multi as $m) {
					$array_out[$count][$key] = $m;

					$count++;
				}			
			}	
		
			return $array_out;		
		//String
		} else {
			return explode(Joe_v1_0_Config::get_item('multi_value_seperator'), $value_in);			
		}
	}		

	static public function get_section_repeatable_count($section_data) {
		$first_field = $section_data['fields'][array_keys($section_data['fields'])[0]];
		
		if(is_array($first_field['default'])) {
			return sizeof($first_field['default']);
		}

		return false;	
	}	

	public static function css_prefix($text = '')	{
		return Joe_v1_0_Config::get_item('css_prefix') . $text;
	}

	public static function slug_prefix($text = '', $sep = '_', $hyphen = true)	{
		$out = Joe_v1_0_Config::get_item('plugin_slug') . $sep . $text;
		
		if(! $hyphen) {
			$out = str_replace('-', '_', $out);
		}
		
		return $out;
	}

	public static function array_string_to_array($string) {
		$string = str_replace(array('[',']','"','"'), array('','','',''), $string);
		
		return self::comma_string_to_array($string);
	}
	
	public static function comma_string_to_array($string) {
		//Process options
		$options_exploded = explode(',', $string);
		$options_array = array();
		foreach($options_exploded as $option) {
			$value = trim($option);
			$key = self::make_key($value);
		
			$options_array[$key] = $value;
		}
	
		return $options_array;
	}

	public static function multi_use_as_key($array_in, $as_key = false) {
		$array_out = array();
			
		$count = 0;
		foreach($array_in as $data) {
			if(is_array($data) && $as_key && array_key_exists($as_key, $data)) {
				$out_key = self::make_key($data[$as_key]);
			} else {
				$out_key = $count;
			}

			$array_out[$out_key] = $data;			

			$count++;						
		 }	
		
		return $array_out;
	}		

	static public function flatten_meta($data_in) {
		$data_out = array();		
		
		if(is_array($data_in)) {
			foreach($data_in as $data_key => $data_value) {
				$data_out[$data_key] = $data_value[0];
			}		
		}
		
		return $data_out;		
	}		

	static public function repeatable_setting_option_array($tab, $section, $key) {
		$options_array = array();
		$values = Joe_v1_0_Config::get_item($tab, $section, true);
		
		if(! is_array($values)) {
			return null;
		}
		
		foreach($values as $s) {
			//If exists
			if(array_key_exists($key, $s)) {
				//Add as option
				$options_array[Joe_v1_0_Helper::make_key($s[$key])] = $s[$key];
			}		
		}
		
		return $options_array;
	}	

	public static function assoc_array_table($assoc_array) {
		if(! is_array($assoc_array) || ! sizeof($assoc_array)) {
			return false;
		}

		$table = '<table class="' . Joe_v1_0_Helper::css_prefix('assoc_array') . '">';
					
		foreach($assoc_array as $key => $value) {
			$table .= '<tr class="' . Joe_v1_0_Helper::css_prefix('assoc_array-' . Joe_v1_0_Helper::make_key($key)) . '">';
			$table .= '<th>' . $key . '</th>';
			$table .= '<td>' . $value . '</td>';
			$table .= '</tr>';
		}
		
		$table .= '</table>';
	
		return $table;
	}	
	
	static function time_ago($time = '0', $comparison = false) {
		$periods_singular = [
			__('Second', Joe_v1_0_Config::get_item('plugin_text_domain')),
			__('Minute', Joe_v1_0_Config::get_item('plugin_text_domain')),
			__('Hour', Joe_v1_0_Config::get_item('plugin_text_domain')),
			__('Day', Joe_v1_0_Config::get_item('plugin_text_domain')),
			__('Week', Joe_v1_0_Config::get_item('plugin_text_domain')),
			__('Month', Joe_v1_0_Config::get_item('plugin_text_domain')),
			__('Year', Joe_v1_0_Config::get_item('plugin_text_domain'))
		];
		
		$periods_plural = [
			__('Seconds', Joe_v1_0_Config::get_item('plugin_text_domain')),
			__('Minutes', Joe_v1_0_Config::get_item('plugin_text_domain')),
			__('Hours', Joe_v1_0_Config::get_item('plugin_text_domain')),
			__('Days', Joe_v1_0_Config::get_item('plugin_text_domain')),
			__('Weeks', Joe_v1_0_Config::get_item('plugin_text_domain')),
			__('Months', Joe_v1_0_Config::get_item('plugin_text_domain')),
			__('Years', Joe_v1_0_Config::get_item('plugin_text_domain'))
		];
		
		$lengths = array("60","60","24","7","4.35","12", "365");

		$now = time();
		if($comparison && ($now >= $comparison)) {
			$difference = $comparison - $time;
		} else {
			$difference = $now - $time;
		}

		$tense = __('ago', Joe_v1_0_Config::get_item('plugin_text_domain'));
	
		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			 $difference /= $lengths[$j];
		}
	
		$difference = round($difference);
	
		if($difference == 1) {
			$period = $periods_singular[$j];
		} else {
			$period = $periods_plural[$j];	   
		}
	
		return "$difference $period $tense";
	}	
}