<?php

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
 * @package    AFF_Pro
 * @subpackage AFF_Pro/includes
 * @author     AffPro
 */
class AFF_Pro
    {
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      AFF_Pro_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader = null;
    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name = null;
    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version = null;
    public function __construct()
        {
        if ( defined( "AFF_Pro_VERSION" ) ) {
            // License manager is now initialized in main plugin file
            $this->version = AFF_Pro_VERSION;
            } else {
            $this->version = "1.0.0";
            }
        $this->plugin_name = "aff-pro";
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        }
    private function load_dependencies()
        {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . "includes/class-aff-pro-loader.php";
        require_once plugin_dir_path( dirname( __FILE__ ) ) . "includes/class-aff-pro-i18n.php";
        require_once plugin_dir_path( dirname( __FILE__ ) ) . "admin/class-aff-pro-admin.php";
        require_once plugin_dir_path( dirname( __FILE__ ) ) . "public/class-aff-pro-public.php";
        $this->loader = new AFF_Pro_Loader();
        }
    private function set_locale()
        {
        $plugin_i18n = new AFF_Pro_i18n();
        $this->loader->add_action( "plugins_loaded", $plugin_i18n, "load_plugin_textdomain" );
        }
    private function define_admin_hooks()
        {
        if ( defined( "AFF_Pro" ) ) {
            $plugin_admin = new AFF_Pro_Admin( $this->get_plugin_name(), $this->get_version() );
            $this->loader->add_action( "admin_enqueue_scripts", $plugin_admin, "enqueue_styles" );
            $this->loader->add_action( "admin_enqueue_scripts", $plugin_admin, "enqueue_scripts" );
            }
        }
    private function define_public_hooks()
        {
        if ( defined( "AFF_Pro" ) ) {
            $plugin_public = new AFF_Pro_Public( $this->get_plugin_name(), $this->get_version() );
            $this->loader->add_action( "wp_enqueue_scripts", $plugin_public, "enqueue_styles" );
            $this->loader->add_action( "wp_enqueue_scripts", $plugin_public, "enqueue_scripts" );
            }
        }
    public function run()
        {
        $this->loader->run();
        }
    public function get_plugin_name()
        {
        return $this->plugin_name;
        }
    public function get_loader()
        {
        return $this->loader;
        }
    public function get_version()
        {
        return $this->version;
        }
    }