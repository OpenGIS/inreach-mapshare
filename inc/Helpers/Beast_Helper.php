<?php

class Beast_Helper {

	static public function require($path = '') {
		$path = plugin_dir_path(__DIR__) . $path;
		
		require_once($path);		
	}

	static public function make_hash($data, $length = 6) {
		if(! is_string($data)) {
			$data = json_encode($data);
		}
		
		return substr(md5($data), 0, $length);
	}
	
	static public function plugin_about() {
		$out = '';
		
		$out .= '	<div id="' . Beast_Config::get_item('plugin_slug') . '-about">' . "\n";	
		$out .= '		<img alt="Joe\'s mug" src="https://www.morehawes.co.uk/assets/images/Joe1BW.jpg" />' . "\n";		
		$out .= '		<p class="lead"><b>' . sprintf(esc_html__("Hi, I'm %s.", Beast_Config::get_item('plugin_slug')), "Joe") . '</b></p>' . "\n";		
		$out .= '	</div>' . "\n";		
		
		return $out;
	}				
	
	static public function logo($colour = 'dark', $width = '20', $height = '20', $title = false) {
		if(! $title) {
			$title = Beast_Config::get_name();
		}
		return '<img class="' . Beast_Config::get_item('plugin_slug') . '-logo" alt="' . Beast_Config::get_name() . '" src="' . self::asset_url('img/' . Beast_Config::get_item('plugin_slug') . '-icon-' . $colour . '.png') . '" width="' . $width . '" height="' . $height . '" />';
	}
	
	static public function site_url($url_path = '') {
		return Beast_Config::get_item('site_url') . $url_path;
	}

	static public function asset_url($file_path = '') {	
		return plugin_dir_url('') . Beast_Config::get_item('plugin_slug') . '/assets/' . $file_path;
	}

	static public function plugin_path($append = '') {
		return Beast_Config::get_item('site_url') . $url_path;
	}
	
	static public function http_url($data = array()) {
		return trim(add_query_arg(array_merge(array(Beast_Config::get_item('plugin_slug') . '_http' => '1'), $data), home_url('/')), '/');
	}
	
	static function array_random_assoc($arr, $num = 1) {
			$keys = array_keys($arr);
			shuffle($keys);
 
			$r = array();
			for ($i = 0; $i < $num; $i++) {
					$r[$keys[$i]] = $arr[$keys[$i]];
			}
			return $r;
	}	

	static public function is_debug() {
		return (true == Beast_Config::get_setting('misc', 'advanced', 'debug_mode'));
	}
	
	static public function debug($thing, $die = true) {
		if(! self::is_debug()) {
			return;	
		}

		//Clear other output
// 		if($die) {
// 			@ ob_end_clean();
// 		}
			
		echo '<pre>';
		print_r($thing);
		echo '</pre>';
		if($die) {
			die;
		}
	}

	//Thanks! https://stackoverflow.com/a/24365425/569788
	static public function stringify_numbers($obj) {
		//Bad data
		if(! $obj) {
			return $obj;
		}
		
		foreach($obj as &$item) {
			if(is_object($item) || is_array($item)) {
				$item = self::stringify_numbers($item); // recurse!
			}
	
			if(is_numeric($item)) {
				$item = (string) $item;
			}
		}				
		
		return $obj;
	}

	public static function convert_single_value_to_array($value_in) {
		//Array
		if(is_array($value_in)) {
			$array_out = array();
		
			foreach($value_in as $key => $value) {
				$multi = explode(Beast_Config::get_item('multi_value_seperator'), $value);			

				$count = 0;
				foreach($multi as $m) {
					$array_out[$count][$key] = $m;
				
					$count++;
				}			
			}	
		
			return $array_out;		
		//String
		} else {
			return explode(Beast_Config::get_item('multi_value_seperator'), $value_in);			
		}
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
				$array_out[$key] = implode(Beast_Config::get_item('multi_value_seperator'), $value);
			}
		}	
		
		return $array_out;
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

	static public function flatten_meta($data_in) {
		$data_out = array();		
		
		if(is_array($data_in)) {
			foreach($data_in as $data_key => $data_value) {
				$data_out[$data_key] = $data_value[0];
			}		
		}
		
		return $data_out;		
	}		
}