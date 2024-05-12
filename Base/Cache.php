<?php

class Joe_Cache {

	static function set_item($cache_id, string $cache_content, int $cache_minutes = 0) {
		if (!$cache_minutes) {
			$cache_minutes = (int) Joe_Config::get_setting('joe', 'cache', 'minutes');
		}
		$cache_seconds = $cache_minutes * 60;

		set_transient(Joe_Helper::slug_prefix($cache_id, '_', false), $cache_content, $cache_seconds);
	}

	static function get_item($cache_id, $check_stale = false) {
		//Straight WP caching baby...
		if (!$check_stale) {
			return get_transient(Joe_Helper::slug_prefix($cache_id, '_', false));
			//Joe implementation
		} else {
			global $wpdb;

			$results = $wpdb->get_results(
				$wpdb->prepare("
					SELECT option_name, option_value
					FROM $wpdb->options
					WHERE option_name LIKE '%s'
				", '_transient_%' . Joe_Helper::slug_prefix($cache_id, '_', false)
				)
				, ARRAY_A);

			//Valid response
			if (sizeof($results) == 2) {
				//Get our values
				foreach ($results as $result) {
					if ($result['option_name'] == '_transient_timeout_' . Joe_Helper::slug_prefix($cache_id, '_', false)) {
						$timeout = $result['option_value'];
					} elseif ($result['option_name'] == '_transient_' . Joe_Helper::slug_prefix($cache_id, '_', false)) {
						$value = $result['option_value'];
					}
				}

				//Both are required
				if (isset($timeout) && isset($value)) {
					$time_to_expire = $timeout - time();

					//Fresh
					if ($time_to_expire > 0) {
						return [
							'status' => 'fresh',
							'minutes' => ($time_to_expire / 60),
							'value' => $value,
						];
						//Stale
					} else {
						return [
							'status' => 'stale',
							'minutes' => ((0 - $time_to_expire) / 60), //Negate
							'value' => $value,
						];
					}
				}
			}
		}

		return false;
	}

	static function do_hash($string) {
		return md5($string);
	}

// 	static function flush() {
// 		global $wpdb;
//
// 		$wpdb->query("DELETE FROM " . $wpdb->options . " WHERE option_name LIKE '_transient_%" . self::$cache_prefix . "%'");
// 	}
}