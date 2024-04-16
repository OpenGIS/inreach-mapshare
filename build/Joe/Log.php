<?php

class Joe_v1_2_Log {

	private static $log = [];
	private static $by_type = [];
	private static $by_code = [];
	
	private static $data = [];
	
	private static $count = 0;
	private static $latest = null;
	
	private static $in_error = false;
	private static $in_success = false;
	
	private static $output_type = 'console';
	
	//Resets the $log. $data persists.
	public static function reset() {
		static::$log = [];
		static::$by_type = [];
		static::$by_code = [];
		static::$count = 0;
		static::$in_error = false;		
		static::$in_success = false;		
	}	
	
	public static function out($content = '') {
		switch(static::$output_type) {
			case 'notice' :
				$latest = Joe_v1_2_Log::latest();
				$type = isset($latest['type']) ? $latest['type'] : '';
				if(static::$in_success && static::latest_warning()) {
					$type = 'warning';
				}
				
				Joe_v1_2_Assets::js_onready('joe_admin_message("' . $content . '", "' . $type . '")');
		
				break;

			default :
			case 'console' :
				Joe_v1_2_Assets::js_inline('console.log("' . strip_tags($content) . '");');
		
				break;

		}
	}	

	public static function set_data(string $key, $value) {
		static::$data[$key] = $value;
	}

	public static function get_data(string $key = '') {
		if($key && array_key_exists($key, static::$data)) {
			return static::$data[$key];
		}
		
		return null;
	}

	public static function set_output_type($type) {
		static::$output_type = $type;
	}

	public static function in_error() {
		if(static::$in_error === true) {
			return static::latest();
		}
		
		return false;
	}

	public static function in_success() {
		if(static::$in_success === true) {
			return static::latest();
		}
		
		return false;
	}
	
	public static function latest_warning() {
		if(isset(static::$by_type['warning']) && is_array(static::$by_type['warning']) && sizeof(static::$by_type['warning'])) {
			return static::$by_type['warning'][sizeof(static::$by_type['warning'])-1];
		}
		
		return false;
	}	
	
	public static function latest($type = null) {
		$out = [];
		
		if(! $type) {
			$out = static::$log[sizeof(static::$log)-1];
		} elseif(is_array(static::$by_type[$type]) && sizeof(static::$by_type[$type])) {
			$out = static::$by_type[$type][sizeof(static::$by_type[$type])-1];
		}
		
		return $out;
	}	
	
	public static function add($message = '', $type = 'log', $code = 'info') {	
		if(! is_string($message)) {
			$message = htmlspecialchars(json_encode($message));
			$message = preg_replace('/\s+/', ' ', $message);
			// $message = '<pre>' . $message . '</pre>';
		}

		//Flags
		if($type == 'success') {
			static::$in_success = true;
			static::$in_error = false;			
		} elseif($type == 'error') {
			static::$in_error = true;
			static::$in_success = false;
		}
		
		$item = [
			'microtime' => time(),
			'type' => $type,
			'code' => $code,
			'message' => $message
		];

		static::$log[] = $item;
		static::$by_type[$type][] = $item;
		static::$by_code[$code][] = $item;
		
		static::$latest = $item;
					
		static::$count++;
	}

	public static function size() {
		return static::$count;
	}

	public static function has(string $code) {
		if(array_key_exists($code, static::$by_code)) {
			return static::$by_code[$code];
		}
		
		return false;
	}
	
	public static function render() {
		if(! sizeof(static::$log) || ! current_user_can('administrator')) {
			return;
		}
	
		$log_content = '';

		//Not debugging
		if(! Joe_v1_2_Helper::do_debug() && $latest = Joe_v1_2_Log::latest()) {
			//Success with warningS
			if(static::$in_success && static::latest_warning()) {
 				for($i = 0; $i < sizeof(static::$by_type['warning']); $i++) {
 					$log_content .= static::draw_item(static::$by_type['warning'][$i]);
					$log_content .= '<br />';
 				}
			}
		
			if(in_array($latest['type'], [ 'success', 'error' ])) {
				$log_content .= static::draw_item($latest);
			}		
		//Everything
		} else {
			for($i = 0; $i < sizeof(static::$log); $i++) {
				$log_content .= static::draw_item(static::$log[$i]);
				
				if($i < sizeof(static::$log)) {
					$log_content .= '<br />';
				}
			}		
		}
		
		if($log_content) {
			static::out($log_content);		
		}
	}

	public static function render_item(array $item) {
		if($item_content = static::draw_item($item)) {
			static::out($item_content);
		}
	}

	public static function draw_item(array $item) {
		if(empty($item) || ! isset($item['message']) || ! isset($item['type'])) {
			return false;
		}
		
		$code = isset($item['code']) ? $item['code'] : '';

		switch(static::$output_type) {
			case 'html' :
			case 'notice' :
				$out = '<b>[' . ucwords($item['type']) . ']</b> ' . $item['message'];
				
				if(Joe_v1_2_Helper::do_debug()) {
					$out .= ' (' . $code . ')';
				}
				
				return $out;
				
			default :
			case 'console' :
				if($code) {
					$code = '=' . $code;
				}
				return '[' . Joe_v1_2_Config::get_name() . ' ' . ucwords($item['type']) . $code . '] ' . $item['message'] . '\n';
		}
		
		return false;
	}	
}