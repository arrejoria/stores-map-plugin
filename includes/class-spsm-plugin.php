<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.linkedin.com/in/arr-dev/
 * @since      1.0.0
 *
 * @package    Spsm_Plugin
 * @subpackage Spsm_Plugin/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Spsm_Plugin
 * @subpackage Spsm_Plugin/includes
 * @author     Arrejoria Lucas <arrejoria.work@gmail.com>
 */
class Spsm_Plugin
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Spsm_Plugin_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('SPSM_PLUGIN_VERSION')) {
			$this->version = SPSM_PLUGIN_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'spsm-plugin';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_ajax_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Spsm_Plugin_Loader. Orchestrates the hooks of the plugin.
	 * - Spsm_Plugin_i18n. Defines internationalization functionality.
	 * - Spsm_Plugin_Admin. Defines all hooks for the admin area.
	 * - Spsm_Plugin_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-spsm-plugin-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-spsm-plugin-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-spsm-plugin-admin.php';

		/**
		 * Clase responsable de la comunicacion entre el servidor y el cliente
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/inc/class-spsm-ajax.php';

		/**
		 * Clase responsable de manejar todas las consultas del administrador y la base de datos
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/inc/class-spsm-queries.php';


		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-spsm-plugin-public.php';


		$this->loader = new Spsm_Plugin_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Spsm_Plugin_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Spsm_Plugin_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Spsm_Plugin_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_action('admin_menu', $plugin_admin, 'spsm_admin_menu');
		// $this->loader->add_action('admin_head', $plugin_admin, 'add_stores_data_to_head');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'spsm_stores_localize_scripts');


		// $this->loader->add_action( 'admin_head', $plugin_admin, 'spsm_head_scripts');

		// $this->loader->add_action( 'init', $plugin_admin, 'spsm_register_stores_post_type');
	}

	private function define_ajax_hooks()
	{
		$plugin_ajax = new SPSM_Ajax;
		$this->loader->add_action('admin_enqueue_scripts', $plugin_ajax, 'spsm_enqueue_ajax_scripts');
		$this->loader->add_action('wp_ajax_spsm_handle_ajax', $plugin_ajax, 'spsm_handle_ajax_callback');
		$this->loader->add_action('wp_ajax_nopriv_spsm_handle_ajax', $plugin_ajax, 'spsm_handle_ajax_callback');
		$this->loader->add_action('wp_ajax_spsm_get_store', $plugin_ajax, 'spsm_get_store_info');
		$this->loader->add_action('wp_ajax_spsm_update_store', $plugin_ajax, 'spsm_update_store_info');
		$this->loader->add_action('wp_ajax_spsm_delete_store', $plugin_ajax, 'spsm_delete_store_handle');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Spsm_Plugin_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Spsm_Plugin_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
