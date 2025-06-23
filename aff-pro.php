<?php

/**
 * File khởi tạo chính của plugin
 *
 * File này được WordPress đọc để tạo thông tin plugin trong khu vực quản trị.
 * File này cũng bao gồm tất cả các dependencies được sử dụng bởi plugin,
 * đăng ký các hàm kích hoạt và vô hiệu hóa, và định nghĩa hàm khởi động plugin.
 *
 * @link              https://affpro.dev
 * @since             1.0.0
 * @package           AFF_Pro
 *
 * @wordpress-plugin
 * Plugin URI:        aff-pro
 * Plugin Name:       AFF Pro
 * Description:       Plugin chuyên nghiệp giúp bạn xây dựng hệ thống affiliate marketing hoàn chỉnh cho WooCommerce.
 * Version:           1.0.0
 * Author:            AFF Pro Team
 * Author URI:        https://affpro.dev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       aff-pro
 * Domain Path:       /languages
 */


// Ngăn chặn truy cập trực tiếp
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Định nghĩa các hằng số cơ bản của plugin
if ( ! defined( 'AFF_PRO_VERSION' ) ) {
	define( 'AFF_PRO_VERSION', '1.0.0' );
}

if ( ! defined( 'AFF_PRO_PLUGIN_FILE' ) ) {
	define( 'AFF_PRO_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'AFF_PRO_PLUGIN_BASENAME' ) ) {
	define( 'AFF_PRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'AFF_PRO_URL' ) ) {
	define( 'AFF_PRO_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'AFF_PRO_PATH' ) ) {
	define( 'AFF_PRO_PATH', plugin_dir_path( __FILE__ ) );
}

// Giữ lại hằng số cũ để tương thích ngược
if ( ! defined( 'AFF_URL' ) ) {
	define( 'AFF_URL', AFF_PRO_URL );
}

if ( ! defined( 'AFF_PATH' ) ) {
	define( 'AFF_PATH', AFF_PRO_PATH );
}

/**
 * Hàm debug được cải thiện với bảo mật
 * 
 * @since 1.0.0
 * @param mixed $data Dữ liệu cần debug
 * @param bool $die Có dừng thực thi sau khi debug không
 * @param bool $force Có force hiển thị không (bỏ qua check debug mode)
 */
if ( ! function_exists( 'aff_pro_debug' ) ) {
	function aff_pro_debug( $data, $die = false, $force = false ) {
		// Chỉ hiển thị debug khi ở debug mode hoặc force
		if ( ! $force && ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) ) {
			return;
		}

		// Chỉ admin mới được xem debug info
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<pre style="background: #f1f1f1; padding: 10px; margin: 10px; border: 1px solid #ccc; font-size: 12px;">';
		echo '<strong>AFF Pro Debug:</strong><br>';
		print_r( $data );
		echo '</pre>';

		if ( $die ) {
			wp_die( 'Debug stopped execution.' );
		}
	}
}

// Backward compatibility
if ( ! function_exists( 'debug' ) ) {
	function debug( $v, $die = true ) {
		aff_pro_debug( $v, $die, true );
	}
}

/**
 * Mã được thực thi khi kích hoạt plugin
 * Chi tiết được ghi trong includes/class-aff-pro-activator.php
 */
function activate_AFF_Pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-aff-pro-activator.php';
	AFF_Pro_Activator::activate();
}

/**
 * Mã được thực thi khi vô hiệu hóa plugin
 * Chi tiết được ghi trong includes/class-aff-pro-deactivator.php
 */
function deactivate_AFF_Pro() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-aff-pro-deactivator.php';
	AFF_Pro_Deactivator::deactivate();
}

// Đăng ký các hook kích hoạt và vô hiệu hóa
register_activation_hook( __FILE__, 'activate_AFF_Pro' );
register_deactivation_hook( __FILE__, 'deactivate_AFF_Pro' );

/**
 * Tải các file cần thiết cho plugin
 * Bao gồm các class core, helpers và components
 */

// Tải các helper functions
include_once "helpers/functions.php";
include_once "helpers/load-template.php";

// Tải class query cơ bản
include_once plugin_dir_path( __FILE__ ) . 'includes/class-query.php';

// Tải AJAX handlers cho admin
include_once "admin/ajax-admin.php";

// Tải các class chức năng chính
include_once "classes/config-class.php";        // Quản lý cấu hình
include_once "classes/app-class.php";           // Class ứng dụng chính
include_once "classes/history-class.php";       // Lịch sử giao dịch
include_once "classes/traffic-class.php";       // Theo dõi traffic
include_once "classes/user-class.php";          // Quản lý user affiliate
include_once "classes/commission-settings-class.php"; // Cài đặt hoa hồng
include_once "classes/user-order-class.php";    // Đơn hàng của user
include_once "classes/user-relationship-class.php"; // Mối quan hệ user
include_once "classes/payment-class.php";       // Thanh toán
include_once "classes/banner-class.php";        // Quản lý banner

// Tải class chính dựa trên phiên bản PHP
if ( version_compare( PHP_VERSION, '8.1', '>=' ) ) {
	// PHP 8.1+ - sử dụng version mới
	require plugin_dir_path( __FILE__ ) . 'includes/class-momo-mh-en8.php';
	require plugin_dir_path( __FILE__ ) . 'includes/class-aff-pro8.php';
} else {
	// PHP < 8.1 - sử dụng version cũ
	require plugin_dir_path( __FILE__ ) . 'includes/class-momo-mh-en.php';
	require plugin_dir_path( __FILE__ ) . 'includes/class-aff-pro.php';
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
function run_AFF_Pro() {
	$plugin = new AFF_Pro();
	$plugin->run();
}

// Khởi chạy plugin
run_AFF_Pro();



// Cấu hình SQL mode để tương thích với các phiên bản MySQL khác nhau
// SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));

/**
 * Thiết lập rewrite rules cho trang affiliate
 * Tạo URL thân thiện cho trang dashboard của affiliate
 */
add_action( 'init', 'aff_pro_custom_rewrite_rules' );
function aff_pro_custom_rewrite_rules() {
	// Tên base cho trang affiliate dashboard
	$aff_user_basename = "trang-cong-tac-vien-version-2";

	// Thêm rewrite rule
	add_rewrite_rule(
		'^' . $aff_user_basename . '\/(.*)\/?',
		'index.php?pagename=' . $aff_user_basename,
		'top'
	);
}