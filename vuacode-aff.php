<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://vuacode.io
 * @since             1.0.1
 * @package           WP_VuaCode_AFF
 *
 * @wordpress-plugin
 * Plugin URI:        vuacode-aff
 * Plugin Name:       VuaCode AFF
 * Description:       Plugin giúp bạn xây dựng hệ thống cộng tác viên bán hàng cho Woocommerce.
 * Version:           1.0.1
 * Author:            Đỗ Minh Hải
 * Author URI:        https://vuacode.io
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       vuacode-aff
 * Domain Path:       /languages
 */


if ( !defined( 'AFF_URL' ) ) {
	define( 'AFF_URL', plugin_dir_url( __FILE__ ) );
	}
if ( !defined( 'AFF_PATH' ) ) {
	define( 'AFF_PATH', plugin_dir_path( __FILE__ ) );
	}

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
	die;
	}

if ( !function_exists( 'debug' ) ) {
	function debug($v, $die = true)
		{
		echo "<pre>";
		print_r( $v );
		echo "</pre>";
		if ( $die )
			die();

		}
	}


// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
	die;
	}

/**
 * Currently plugin version.
 * Start at version 1.0.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WP_VuaCode_AFF_VERSION', '1.0.5' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-vuacode-aff-activator.php
 */
function activate_WP_VuaCode_AFF()
	{
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vuacode-aff-activator.php';
	WP_VuaCode_AFF_Activator::activate();
	}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-vuacode-aff-deactivator.php
 */
function deactivate_WP_VuaCode_AFF()
	{
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-vuacode-aff-deactivator.php';
	WP_VuaCode_AFF_Deactivator::deactivate();
	}

register_activation_hook( __FILE__, 'activate_WP_VuaCode_AFF' );
register_deactivation_hook( __FILE__, 'deactivate_WP_VuaCode_AFF' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */

include_once "helpers/functions.php";
include_once "helpers/load-template.php";
include_once plugin_dir_path( __FILE__ ) . 'includes/class-query.php';
include_once "admin/ajax-admin.php";

include_once "classes/config-class.php";
include_once "classes/history-class.php";
include_once "classes/app-class.php";
include_once "classes/traffic-class.php";
include_once "classes/user-class.php";
include_once "classes/commission-settings-class.php";
include_once "classes/history-class.php";
include_once "classes/user-order-class.php";
include_once "classes/user-relationship-class.php";
include_once "classes/payment-class.php";
include_once "classes/banner-class.php";

if ( version_compare( PHP_VERSION, '8.1', '>=' ) ) {
	require plugin_dir_path( __FILE__ ) . 'includes/class-momo-mh-en8.php';
	require plugin_dir_path( __FILE__ ) . 'includes/class-vuacode-aff8.php';
	} else {
	require plugin_dir_path( __FILE__ ) . 'includes/class-momo-mh-en.php';
	require plugin_dir_path( __FILE__ ) . 'includes/class-vuacode-aff.php';
	}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_WP_VuaCode_AFF()
	{

	$plugin = new WP_VuaCode_AFF();
	$plugin->run();

	}
run_WP_VuaCode_AFF();



//SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));







add_action( 'init', 'custom_rewrite_basic_angularApp' );
function custom_rewrite_basic_angularApp()
	{
	$aff_user_basename = "trang-cong-tac-vien-version-2";

	add_rewrite_rule(
		'^' . $aff_user_basename . '\/(.*)\/?',
		'index.php?pagename=' . $aff_user_basename,
		'top'
	);
	}