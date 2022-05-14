<?php

class Beast_Cache {
	static function set_item($cache_id, $cache_content, $cache_minutes = 0) {
		$cache_seconds = $cache_minutes * 60;
		
		set_transient(Beast_Config::get_item('plugin_slug') . $cache_id, $cache_content, $cache_seconds);						
	}
	
	static function get_item($cache_id) {
		return get_transient(Beast_Config::get_item('plugin_slug') . $cache_id);
	}

	static function do_hash($string) {	
		return md5($string);
	}
	
	static function flush() {
		global $wpdb;
		
		$wpdb->query("DELETE FROM " . $wpdb->options . " WHERE option_name LIKE '_transient_%" . Beast_Config::get_item('plugin_slug') . "%'");
	}
}