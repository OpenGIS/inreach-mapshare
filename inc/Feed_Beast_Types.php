<?php
	
class Feed_Beast_Types {
	private $types;
	
	function __construct() {
		$this->types = array(
	 
		 'feed_beast_feed' => array(
				'label'                 => esc_html__('Feed', 'feed_beast'),
				'description'           => '',
				'labels'                => array(
					'name'                  => esc_html__('Feeds', 'feed_beast'),
					'singular_name'         => esc_html__('Feed', 'feed_beast'),
					'menu_name'             => esc_html__('Feeds', 'feed_beast'),
					'name_admin_bar'        => esc_html__('Feed', 'feed_beast'),
					'archives'              => esc_html__('Feed Archives', 'feed_beast'),
					'attributes'            => esc_html__('Feed Attributes', 'feed_beast'),
					'parent_item_colon'     => esc_html__('Parent Feed:', 'feed_beast'),
					'all_items'             => esc_html__('All Feeds', 'feed_beast'),
					'add_new_item'          => esc_html__('Add New Feed', 'feed_beast'),
					'add_new'               => esc_html__('Add New', 'feed_beast'),
					'new_item'              => esc_html__('New Feed', 'feed_beast'),
					'edit_item'             => esc_html__('Edit Feed', 'feed_beast'),
					'update_item'           => esc_html__('Update Feed', 'feed_beast'),
					'view_item'             => esc_html__('View Feed', 'feed_beast'),
					'view_items'            => esc_html__('View Feeds', 'feed_beast'),
					'search_items'          => esc_html__('Search Feed', 'feed_beast'),
					'not_found'             => esc_html__('Not found', 'feed_beast'),
					'not_found_in_trash'    => esc_html__('Not found in Trash', 'feed_beast'),
					'featured_image'        => esc_html__('Featured Image', 'feed_beast'),
					'set_featured_image'    => esc_html__('Set featured image', 'feed_beast'),
					'remove_featured_image' => esc_html__('Remove featured image', 'feed_beast'),
					'use_featured_image'    => esc_html__('Use as featured image', 'feed_beast'),
					'insert_into_item'      => esc_html__('Insert into Feed', 'feed_beast'),
					'uploaded_to_this_item' => esc_html__('Uploaded to this Feed', 'feed_beast'),
					'items_list'            => esc_html__('Feed list', 'feed_beast'),
					'items_list_navigation' => esc_html__('Feeds list navigation', 'feed_beast'),
					'filter_items_list'     => esc_html__('Filter Feed list', 'feed_beast'),
				),
				'supports'              => array('title', 'author', 'revisions', 'thumbnail'),
				'hierarchical'          => false,
				'public'                => true,
				'show_ui'               => true,
				'show_in_menu'          => true,
				'menu_position'         => 5,
				'show_in_admin_bar'     => true,
				'show_in_nav_menus'     => true,
				'can_export'            => true,
				'has_archive'           => false,
				'exclude_from_search'   => false,
				'publicly_queryable'    => true,
				'rewrite'               => array('slug' => 'feed', 'with_front' => false),
				'capability_type'       => 'post'
			)
		);

		//Show if debug
		if(Feed_Beast_Config::get_setting('misc', 'advanced', 'debug_mode') == true) {
			$this->types['feed_beast_feed']['supports'][] = 'custom-fields';
		}

		//Add Featured Image Support
		//add_theme_support('post-thumbnails', array('feed_beast_feed'));
	
		add_action('init', array($this, 'register_types'), 0);			
		
		//Admin
		if(is_admin()) {
			add_action('current_screen', array($this, 'current_screen'));			
			add_action('post_updated', array($this, 'save_feed_form'), 10, 2);			
		}
	}	

	function register_types() {
		$types = array();
		
		foreach($this->types as $type_id => $type_data) {
			$types[] = $type_id;
						
			register_post_type($type_id, $type_data);			
		}

		Feed_Beast_Config::set_item('custom_types', $types);			
	}

	function delete_posts() {
		//For each custom type
		foreach($this->types as $type_id => $type_data) {
			//Get posts
			$posts = get_posts(array(
				'post_type' => $type_id
			));
			
			//For each post
			foreach($posts as $post) {
				//Force delete post
				wp_delete_post($post->ID, true);
			}
		}
	}

	/**
	 * ===========================================
	 * =============== FEED EDITOR ===============
	 * ===========================================
	 */	
	
	function current_screen() {
		if(! is_admin()) {
			return;
		}
			
		if(function_exists('get_current_screen')) {  
			$current_screen = get_current_screen();
			
			$plugin_types = Feed_Beast_Config::get_item('custom_types');
			
			if(in_array($current_screen->post_type, $plugin_types)) {
				switch($current_screen->post_type) {
					//Map
					case 'feed_beast_feed' :									
						add_meta_box('feed_beast_feed_meta', esc_html__('Feed Editor', 'waymark'), array($this, 'display_feed_form'), $current_screen->post_type, 'normal', 'high');			
					
						break;
				}	
			}
		}		
	}

	function display_feed_form($post) {	
		if(! is_admin()) {
			return;
		}
			
		$data = Feed_Beast_Helper::flatten_meta(get_post_meta($post->ID));
								
		//Create Feed meta input
		$Feed = new Feed_Beast_Feed($post->ID);		
		$Feed->set_data($data);
		echo $Feed->create_form();		
	}	

	function save_feed_form() {
		global $post;
		
		if(is_object($post) && ! (wp_is_post_revision($post->ID) || wp_is_post_autosave($post->ID))) {
			switch($post->post_type) {
				case 'feed_beast_feed' :									
					$Feed = new Feed_Beast_Feed($post->ID);		
					$Feed->set_data($_POST);	
					
// 					Feed_Beast_Helper::debug($_POST);
							
					$Feed->save_meta($post->ID);
										
					break;			
			}			
		}
	}	
}
new Feed_Beast_Types;	
