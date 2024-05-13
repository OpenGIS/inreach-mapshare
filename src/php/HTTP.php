<?php

class InMap_HTTP {

	protected $response = [];
	protected $request = [];

	public function __construct() {
		$this->request = $_REQUEST;
		$this->response = [
			'http_code' => '200',
			'content_type' => 'html',
			'cache_seconds' => HOUR_IN_SECONDS,
		];

		add_filter('query_vars', array($this, 'query_vars'));
		add_action('template_redirect', array($this, 'template_redirect'));

		//Setup AJAX
		InMap_Assets::js_inline('//HTTP' . "\n");
		InMap_Assets::js_inline('var inmap_http_endpoint = "' . InMap_Helper::http_url() . '";');
	}

	public function do_404() {
		$this->response = [
			'http_code' => '404',
			'content_type' => 'html',
			'content' => '',
		];

		$this->send_response();
	}

	public function query_vars($vars) {
		$vars[] = 'inmap_http';

		return $vars;
	}

	public function template_redirect() {
		//If not HTTP request
		if (!get_query_var('inmap_http')) {
			//WP loads normally
			return;
		}

		//Action
		if (array_key_exists('inmap_action', $_REQUEST)) {
			$this->execute_action();
		}

		if ($this->response) {
			$this->send_response();
		}
	}

	public function send_response() {
		//No content set
		if (!isset($this->response['content'])) {
			$this->do_404();
		}

		//HTTP status code?
		if (isset($this->response['http_code'])) {
			http_response_code($this->response['http_code']);
		}

		//Gzip supported?
		if (isset($this->response['gzip']) && $this->response['gzip']) {
			if (function_exists('gzcompress') && !in_array('ob_gzhandler', ob_list_handlers())) {
				ob_start("ob_gzhandler");
			} else {
				ob_start();
			}
		}

		//Cache
		if (isset($this->response['cache_seconds'])) {
			header('Cache-control: public,max-age=' . $this->response['cache_seconds']);
		}

		//Content Type
		switch ($this->response['content_type']) {
		case 'html':
		default:
			header('Content-Type: text/html');

			break;
		}

		//Content
		echo $this->response['content'];

		die;
	}
}