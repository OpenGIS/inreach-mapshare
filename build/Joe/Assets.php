<?php

class Joe_v1_0_Assets {

	static private $head = [
		'css' => [
			'inline' => [],
			'enqueue' => []
		],
		'js' => [
			'inline' => [],
			'enqueue' => []
		]		
	];

	static private $foot = [
		'js' => [
			'inline' => [],
			'onready' => [],
			'enqueue' => []
		]		
	];
	
	static function init() {
		//Front
		add_action( 'wp_enqueue_scripts', [ get_called_class(), 'enqueue_styles' ] );		
		add_action( 'wp_enqueue_scripts', [ get_called_class(), 'enqueue_scripts' ] );		
		add_action( 'wp_head', [ get_called_class(), 'head' ] );		
		add_action( 'wp_footer', [ get_called_class(), 'footer' ] );		
		
		//Admin
		add_action( 'admin_enqueue_scripts', [ get_called_class(), 'enqueue_styles' ] );								
		add_action( 'admin_enqueue_scripts', [ get_called_class(), 'enqueue_scripts' ] );								
		add_action( 'admin_head', [ get_called_class(), 'head' ] );		
		add_action( 'admin_footer', [ get_called_class(), 'footer' ] );		
	}
	
	// CSS
	
	static function css_inline($css = '') {	
		if($css) {
			static::$head['css']['inline'][] = $css . "\n";
		}
	}

	static function css_enqueue($url = '') {	
		if($url) {
			static::$head['css']['enqueue'][] = $url;
		}
	}
	
	// JS

	static function js_inline($js = '') {	
		if($js) {
			if((! in_array($js[strlen($js)-1], array(';', "\n")) && (strpos($js, '//') === false))) {
				$js .= ';';
			}
			static::$foot['js']['inline'][] = $js;
		}
	}

	static function js_onready($js = '') {	
		if($js) {
			static::$foot['js']['onready'][] = $js;
		}
	}
	
	static function js_enqueue($enqueue = []) {	
		if($enqueue) {
			//Default
			if(! isset($enqueue['in_footer'])) {
				$enqueue['in_footer'] = true;
			}

			if($enqueue['in_footer']) {
				static::$foot['js']['enqueue'][] = $enqueue;			
			} else {
				static::$head['js']['enqueue'][] = $enqueue;			
			}
		}
	}

	static function head() {
		if(! sizeof(static::$head['css']['inline']) && ! sizeof(static::$head['js']['inline'])) {
			return;
		}
	
		echo "\n" . '<!-- START ' . Joe_v1_0_Config::get_name(true, true) . ' Head CSS -->' . "\n";
		echo '<style type="text/css">' . "\n";

		echo '/* ' . Joe_v1_0_Config::get_name(true, true) . ' v' . Joe_v1_0_Config::get_version() . ' */' . "\n";

		foreach(static::$head['css']['inline'] as $css) {
			 echo $css;
		}

		echo '</style>' . "\n";
		echo '<!-- END ' . Joe_v1_0_Config::get_name(true, true) . ' Head CSS -->' . "\n\n";				
	}
	
	static function footer() {
		//Something to output?
		if(! (sizeof(static::$foot['js']['inline']) + sizeof(static::$foot['js']['onready']))) {
			return;
		}
			
		echo "\n" . '<!-- START ' . Joe_v1_0_Config::get_name(true, true) . ' Footer JS -->' . "\n";
		echo '<script type="text/javascript">' . "\n";

		echo '	//' . Joe_v1_0_Config::get_name(true, true) . ' v' . Joe_v1_0_Config::get_version() . "\n";

		//Inline
		foreach(static::$foot['js']['inline'] as $js) {
			 echo $js;
		}
		
		//Calls
		if(sizeof(static::$foot['js']['onready'])) {
			echo "\n" . 'jQuery(document).ready(function() {' . "\n";
			foreach(static::$foot['js']['onready'] as $js) {
				echo "	" . $js . ";\n";
			}		
			echo '});' . "\n";
		}
		echo '</script>' . "\n";
		echo '<!-- END ' . Joe_v1_0_Config::get_name(true, true) . ' Footer JS -->' . "\n\n";			
	}	
	
	static function enqueue_styles() {
		if(! sizeof(static::$head['css']['enqueue'])) {
			return;
		}
		
		$count = 1;
		foreach(static::$head['css']['enqueue'] as $enqueue) {
			$deps = [];
			
			//URL
			if(is_string($enqueue)) {
				$url = $enqueue;
			//Data
			} elseif(is_array($enqueue) && isset($enqueue['url'])) {
				$url = $enqueue['url'];
				
				//Deps
				if(isset($enqueue['deps']) && sizeof($enqueue['deps'])) {
					foreach($enqueue['deps'] as $dep) {
						switch($dep) {
							default :
							
								$deps[] = $dep;
							
								break;
						}						
					}
				}
			}
			
			$id = Joe_v1_0_Helper::slug_prefix($count);
			
			wp_register_style($id, $url, $deps, Joe_v1_0_Config::get_version());
			wp_enqueue_style($id);
			
			$count++;			
		}
	}

	static function enqueue_scripts() {

		$enqueues = array_merge(
			static::$foot['js']['enqueue'],
			static::$head['js']['enqueue']
		);
		
		if(! sizeof($enqueues)) {
			return;
		}

		$count = 1;
		foreach($enqueues as $enqueue) {
			//URL
			if(! isset($enqueue['url'])) {
				continue;
			}
			
			//Don't cache when debugging
			if(Joe_v1_0_Helper::do_debug()) {
				$enqueue['url'] = add_query_arg('no_cache', rand(0,99999999), $enqueue['url']);			
			}
			
			//ID
			if(! isset($enqueue['id']) || ! $enqueue['id']) {
				$enqueue['id'] = Joe_v1_0_Helper::slug_prefix($count);			
			}
			
			//Deps
			if(! isset($enqueue['deps']) || ! sizeof($enqueue['deps'])) {
				$enqueue['deps'] = [];			
			}			

			//Footer
			if(! isset($enqueue['in_footer'])) {
				$enqueue['in_footer'] = true;
			}	
			
			//Register
			wp_register_script($enqueue['id'], $enqueue['url'], $enqueue['deps'], Joe_v1_0_Config::get_version(), $enqueue['in_footer']);		
			
			//Localize
			if(isset($enqueue['data']) && sizeof($enqueue['data'])) {
				wp_localize_script($enqueue['id'], $enqueue['id'], $enqueue['data']);
			}
			
			//Enqueue
			wp_enqueue_script($enqueue['id']);								

			$count++;			
		}
	}	
}
Joe_v1_0_Assets::init();