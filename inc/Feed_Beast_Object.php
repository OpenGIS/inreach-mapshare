<?php 

class Feed_Beast_Object extends Feed_Beast_Class {
	public $post_type = null;
	public $post_id = null;
	public $post_title = null;	

	public $data = array();

	protected $input_type = null;
	protected $input_name_format = null;

	protected $parameter_groups = array();
	protected $parameters = array();

	protected $meta_prefix = 'feed_beast_';
	
	function __construct($post_id = null) {
		//If post ID set
		if($post_id) {
			//Valid post ID
			if($post = get_post($post_id)) {
				//If it has been deleted
				if(in_array($post->post_status, array('trash'))) {
					$post_id = null;
				}
			//Invalid post ID
			} else {
				$post_id = null;
			}
			
			if($post_id != null) {
				$this->post_id = $post_id;
				$this->post_title = $post->post_title;
				
				$this->set_data(get_post_meta($post_id));							
			}
		}
	}
	
	function set_data($data_in = array()) {
		if(! sizeof($data_in) || ! is_array($data_in)) {
			return;
		}
		
		//For each of the incoming data
		foreach($data_in as $parameter_key => $parameter_value) {
			//Get correct value
			if(is_array($parameter_value)) {
				if(sizeof($parameter_value) == 1 && array_key_exists(0, $parameter_value)) {
					$parameter_value = $parameter_value[0];					
				} else {
					$parameter_value = json_encode($parameter_value);										
				}
			}
				
			//Always check to see if key is prefixed
			$parameter_key = $this->unprefix($parameter_key);
			
			//Is this an allowable parameter of this object?
			if(array_key_exists($parameter_key, $this->parameters)) {

				$this->data[$parameter_key] = $parameter_value;					
			}			
		}
	}	
	
	function prefix($str) {
		return $this->meta_prefix . $str;
	}

	function unprefix($str) {
		//Is the key prefixed?
		if(strpos($str, $this->meta_prefix) === 0) {
			$str = substr($str, strlen($this->meta_prefix));
		}
		
		return $str;
	}	
	
	function set_data_item($data_key, $data_value) {
		$this->data[$data_key] = $data_value;
	}

	function get_data_item($data_key) {
		if(array_key_exists($data_key, $this->data)) {
			return $this->data[$data_key];			
		} else {
			return null;
		}
	}
	
	function get_data() {
		return $this->data;
	}

	function get_post_type() {
		return $this->post_type;
	}	

	function set_input_type($input_type) {
		$this->input_type = $input_type;
	}	

	function set_input_name_format($input_name_format) {
		$this->input_name_format = $input_name_format;
	}	
	
	function get_parameters() {
		return $this->parameters;
	}

	function get_parameter($key = null) {
		if(array_key_exists($key, $this->parameters)) {
			return $this->parameters[$key];
		} else {
			return false;
		}
	}	
	
	function create_form() {
		return Feed_Beast_Input::create_parameter_groups($this->parameters, $this->parameter_groups, $this->data, $this->input_name_format, Feed_Beast_Config::get_item('plugin_slug') . '-parameters-' . $this->post_type);
	}	
	
	function save_meta($post_id = null) {
		if(! $post_id) {
			$post_id = $this->post_id;
		}
				
		//Iterate over each parameter
		foreach($this->parameters as $param_defition) {
									
			//Ensure value exists and is not blank EXCEPT where blank values are allowed
			if(isset($this->data[$param_defition['id']]) && (trim($this->data[$param_defition['id']]) !== '' || (array_key_exists('allow_blank', $param_defition) && $param_defition['allow_blank'] == true))) {
				$param_value = $this->data[$param_defition['id']];
				
				//Process input
				$param_value = Feed_Beast_Input::process_input($param_defition, $param_value);

				update_post_meta($post_id, $this->prefix($param_defition['id']), $param_value);
			//No value exists
			} else {
				delete_post_meta($post_id, $this->prefix($param_defition['id']));
			}
		}
	}
	
	function delete_all_meta() {
		global $wpdb;

		$wpdb->query("
			DELETE FROM " . $wpdb->postmeta . "
			WHERE `meta_key` LIKE '" . $this->meta_prefix . "%'
		");
	}
	
	function get_posts() {
		if(! $this->post_type) {
			return null;
		}
		
		$wp_query = new WP_Query(array(
	    'post_type' => $this->post_type,
	    'post_status' => 'publish',
			'posts_per_page' => -1,
	    'orderby' => 'ID',
	    'order'   => 'ASC',			
		));
		
		$posts = $wp_query->get_posts();
		
		wp_reset_query();		
		
		return $posts;
	}		
	
	function get_posts_by_meta($key, $value, $limit = -1) {
		$wp_query = new WP_Query(
			array(
		    'post_type' => $this->post_type,
				'meta_key'   => $key,
				'posts_per_page' => $limit,
				'meta_query' => array(
					array(
						'key'     => $key,
						'value'   => $value
					)
				)
			)			
		);
		
		$posts = $wp_query->get_posts();		
		
		wp_reset_query();		
		
		return $posts;		
	}

	function get_list() {
		$list = array();
		
		//Get posts
		$posts = $this->get_posts();
		
		//Iterate over each
		if(is_array($posts)) {
			foreach($posts as $p) {
				//Get depth
		    $parent_id = $p->post_parent;
		    
		    $depth = 0;
		    while($parent_id > 0){
	        $page = get_page($parent_id);
	        
	        $parent_id = $page->post_parent;
	        
	        $depth++;
		    }

				$list[$p->ID] = str_repeat('-', $depth) . ' ' . $p->post_title;
			}
		}
		
		return $list;
	}		
	
	function create_post($title, $extra_args = array()) {
		//Insert Post
		$args = array(
		  'post_type' => $this->post_type,
		  'post_title' => $title,
		  'post_status' => 'publish'
		);
		$this->post_id = wp_insert_post(array_merge($args, $extra_args));
		
		//Set meta
		$this->save_meta($this->post_id);
		
		return $this->post_id;
	}	
	
	function update_post_title($title = null) {
		if($title) {
		  wp_update_post(array(
	      'ID' => $this->post_id,
	      'post_title' => $title
		  ));					
		}
	}

	function duplicate_post() {
		global $wpdb;
		
		//Get the post
		$post = get_post($this->post_id);
		if(! is_object($post)) {		
			return false;	
		}
		
		//Get the post meta
		$post_meta = get_post_meta($this->post_id, '', true);
		
		//Parse
		foreach($post_meta as $meta_key => $meta_value) {
			//Delete WP specific post meta
			if(strpos($meta_key, '_') === 0) {
				unset($post_meta[$meta_key]);
			//Flatten sub-array
			} else {
				$post_meta[$meta_key] = $meta_value[0];
			}
		}

		//Create new post WITH data
		$args = array(
			'post_status'    => 'publish',
			'post_title'     => $post->post_title . ' Copy',
			'post_type'      => $post->post_type,
			'meta_input'		 => $post_meta
		);
			
		//Create post
		$new_post_id = wp_insert_post($args);
		
		return $new_post_id;
	}	
}