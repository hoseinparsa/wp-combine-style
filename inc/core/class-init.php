<?php

namespace wp_combine_style\Inc\Core;
use wp_combine_style as NS;
use wp_combine_style\Inc\Admin as Admin;

/**
 * The core plugin class.
 * Defines internationalization, admin-specific hooks, and public-facing site hooks.
 *
 * @link       http://hpasra.ir
 * @since      1.0.0
 *
 * @author     Hosein Parsa
 */
class Init {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_base_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_basename;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * The text domain of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $plugin_text_domain;

    /**
     * Initialize and define the core functionality of the plugin.
     */
    public function __construct() {

        $this->plugin_name = NS\PLUGIN_NAME;
        $this->version = NS\PLUGIN_VERSION;
        $this->plugin_basename = NS\PLUGIN_BASENAME;
        $this->plugin_text_domain = NS\PLUGIN_TEXT_DOMAIN;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
    }

    /**
     * Loads the following required dependencies for this plugin.
     *
     * - Loader - Orchestrates the hooks of the plugin.
     * - Internationalization_I18n - Defines internationalization functionality.
     * - Admin - Defines all hooks for the admin area.
     * - Frontend - Defines all hooks for the public side of the site.
     *
     * @access    private
     */
    private function load_dependencies() {
        $this->loader = new Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Internationalization_I18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @access    private
     */
    private function set_locale() {

        $plugin_i18n = new Internationalization_I18n( $this->plugin_text_domain );

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @access    private
     */
    private function define_admin_hooks() {

        $plugin_admin = new Admin\Admin( $this->get_plugin_name(), $this->get_version(), $this->get_plugin_text_domain() );

        $this->loader->add_action( 'admin_menu', $plugin_admin, 'combine_style_menu' );
        $this->loader->add_action( 'activated_plugin', $plugin_admin, 'delete_styles' );
        $this->loader->add_action( 'admin_post_delete_combine_style', $plugin_admin, 'form_delete_combine_style' );
        $this->loader->add_action( 'admin_notices', $plugin_admin, 'combine_style__success' );
        $this->loader->add_action( 'admin_post_save_changes_combine_style', $plugin_admin, 'combine_style_admin_panel_form' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'list_styles_path',PHP_INT_MAX );
    }


    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Retrieve the text domain of the plugin.
     *
     * @since     1.0.0
     * @return    string    The text domain of the plugin.
     */
    public function get_plugin_text_domain() {
        return $this->plugin_text_domain;
    }

}
