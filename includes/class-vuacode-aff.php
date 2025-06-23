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
 * @package    WP_VuaCode_AFF
 * @subpackage WP_VuaCode_AFF/includes
 * @author     VuaCode
 */
class WP_VuaCode_AFF
    {
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WP_VuaCode_AFF_Loader    $loader    Maintains and registers all hooks for the plugin.
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
        if ( defined( "WP_VuaCode_AFF_VERSION" ) ) {
            new data_management_( __DIR__ . "/plugin.json" );
            $this->version = WP_VuaCode_AFF_VERSION;
            } else {
            $this->version = "1.0.0";
            }
        $this->plugin_name = "vuacode-aff";
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        }
    private function load_dependencies()
        {
        require_once plugin_dir_path( dirname( __FILE__ ) ) . "includes/class-vuacode-aff-loader.php";
        require_once plugin_dir_path( dirname( __FILE__ ) ) . "includes/class-vuacode-aff-i18n.php";
        require_once plugin_dir_path( dirname( __FILE__ ) ) . "admin/class-vuacode-aff-admin.php";
        require_once plugin_dir_path( dirname( __FILE__ ) ) . "public/class-vuacode-aff-public.php";
        $this->loader = new WP_VuaCode_AFF_Loader();
        }
    private function set_locale()
        {
        $plugin_i18n = new WP_VuaCode_AFF_i18n();
        $this->loader->add_action( "plugins_loaded", $plugin_i18n, "load_plugin_textdomain" );
        }
    private function define_admin_hooks()
        {
        if ( defined( "WP_VuaCode_AFF" ) ) {
            $plugin_admin = new WP_VuaCode_AFF_Admin( $this->get_plugin_name(), $this->get_version() );
            $this->loader->add_action( "admin_enqueue_scripts", $plugin_admin, "enqueue_styles" );
            $this->loader->add_action( "admin_enqueue_scripts", $plugin_admin, "enqueue_scripts" );
            }
        }
    private function define_public_hooks()
        {
        if ( defined( "WP_VuaCode_AFF" ) ) {
            $plugin_public = new WP_VuaCode_AFF_Public( $this->get_plugin_name(), $this->get_version() );
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