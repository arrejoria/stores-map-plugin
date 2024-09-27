<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.linkedin.com/in/arr-dev/
 * @since      1.0.0
 *
 * @package    Spsm_Plugin
 * @subpackage Spsm_Plugin/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Spsm_Plugin
 * @subpackage Spsm_Plugin/admin
 * @author     Arrejoria Lucas <arrejoria.work@gmail.com>
 */
class Spsm_Plugin_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Spsm_Plugin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Spsm_Plugin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style('openlayers-style', 'https://cdn.jsdelivr.net/npm/ol@v10.1.0/ol.css', array(), null, 'all');
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/spsm-plugin-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		// Encolar el script principal de Leaflet

		wp_enqueue_script('ol-js', plugin_dir_url(__FILE__) . 'js/ol.js', array(), null, false);
		wp_enqueue_script('map-js', plugin_dir_url(__FILE__) . 'js/map.js', array(), null, false);
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/spsm-plugin-admin.js', array('jquery', 'leaflet-js'), $this->version, true);
	}


	public function spsm_admin_menu()
	{

		add_menu_page(
			"SPSM Dashboard",
			"spsm config",
			"manage_options",
			"spsm-options",
			array($this, 'spsm_option_page'),
			"dashicons-location",
		);
	}

	 // Función que registra el custom post type "SPSM Stores"
	 public function spsm_register_stores_post_type() {

        // Etiquetas para el Custom Post Type
        $labels = array(
            'name'                  => _x('SPSM Stores', 'Post Type General Name', 'spsm-plugin'),
            'singular_name'         => _x('Store', 'Post Type Singular Name', 'spsm-plugin'),
            'menu_name'             => __('SPSM Stores', 'spsm-plugin'),
            'name_admin_bar'        => __('SPSM Store', 'spsm-plugin'),
            'archives'              => __('Store Archives', 'spsm-plugin'),
            'attributes'            => __('Store Attributes', 'spsm-plugin'),
            'parent_item_colon'     => __('Parent Store:', 'spsm-plugin'),
            'all_items'             => __('All Stores', 'spsm-plugin'),
            'add_new_item'          => __('Add New Store', 'spsm-plugin'),
            'add_new'               => __('Add New', 'spsm-plugin'),
            'new_item'              => __('New Store', 'spsm-plugin'),
            'edit_item'             => __('Edit Store', 'spsm-plugin'),
            'update_item'           => __('Update Store', 'spsm-plugin'),
            'view_item'             => __('View Store', 'spsm-plugin'),
            'view_items'            => __('View Stores', 'spsm-plugin'),
            'search_items'          => __('Search Store', 'spsm-plugin'),
            'not_found'             => __('Not found', 'spsm-plugin'),
            'not_found_in_trash'    => __('Not found in Trash', 'spsm-plugin'),
            'featured_image'        => __('Featured Image', 'spsm-plugin'),
            'set_featured_image'    => __('Set featured image', 'spsm-plugin'),
            'remove_featured_image' => __('Remove featured image', 'spsm-plugin'),
            'use_featured_image'    => __('Use as featured image', 'spsm-plugin'),
            'insert_into_item'      => __('Insert into store', 'spsm-plugin'),
            'uploaded_to_this_item' => __('Uploaded to this store', 'spsm-plugin'),
            'items_list'            => __('Stores list', 'spsm-plugin'),
            'items_list_navigation' => __('Stores list navigation', 'spsm-plugin'),
            'filter_items_list'     => __('Filter stores list', 'spsm-plugin'),
        );

        // Argumentos del Custom Post Type
        $args = array(
            'label'                 => __('SPSM Stores', 'spsm-plugin'),
            'description'           => __('Post type for storing information about SPSM stores.', 'spsm-plugin'),
            'labels'                => $labels,
            'supports'              => array('title', 'thumbnail'), // Lo que soportará
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-store', // Icono en el menú del admin
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
        );

        // Registrar el Custom Post Type
        register_post_type('spsm_store', $args);
    }

}
