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
 * Kiểm tra yêu cầu hệ thống trước khi kích hoạt
 * 
 * @since 1.0.0
 * @return bool|WP_Error
 */
function aff_pro_check_requirements() {
	$errors = array();

	// Kiểm tra phiên bản PHP
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		$errors[] = sprintf(
			__( 'AFF Pro requires PHP version 7.4 or higher. You are running version %s.', 'aff-pro' ),
			PHP_VERSION
		);
	}

	// Kiểm tra phiên bản WordPress
	if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
		$errors[] = sprintf(
			__( 'AFF Pro requires WordPress version 5.0 or higher. You are running version %s.', 'aff-pro' ),
			get_bloginfo( 'version' )
		);
	}

	// Kiểm tra WooCommerce
	if ( ! class_exists( 'WooCommerce' ) ) {
		$errors[] = __( 'AFF Pro requires WooCommerce to be installed and activated.', 'aff-pro' );
	} elseif ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '5.0', '<' ) ) {
		$errors[] = sprintf(
			__( 'AFF Pro requires WooCommerce version 5.0 or higher. You are running version %s.', 'aff-pro' ),
			WC_VERSION
		);
	}

	if ( ! empty( $errors ) ) {
		return new WP_Error( 'requirements_not_met', implode( '<br>', $errors ) );
	}

	return true;
}

/**
 * Mã được thực thi khi kích hoạt plugin
 * 
 * @since 1.0.0
 */
function activate_aff_pro() {
	// Kiểm tra yêu cầu hệ thống
	$requirements_check = aff_pro_check_requirements();
	if ( is_wp_error( $requirements_check ) ) {
		wp_die(
			$requirements_check->get_error_message(),
			__( 'Plugin Activation Error', 'aff-pro' ),
			array( 'back_link' => true )
		);
	}

	// Load activator class
	require_once AFF_PRO_PATH . 'includes/class-aff-pro-activator.php';
	AFF_Pro_Activator::activate();

	// Flush rewrite rules
	flush_rewrite_rules();
}

/**
 * Mã được thực thi khi vô hiệu hóa plugin
 * 
 * @since 1.0.0
 */
function deactivate_aff_pro() {
	// Load deactivator class
	require_once AFF_PRO_PATH . 'includes/class-aff-pro-deactivator.php';
	AFF_Pro_Deactivator::deactivate();

	// Flush rewrite rules
	flush_rewrite_rules();
}

// Đăng ký các hook kích hoạt và vô hiệu hóa
register_activation_hook( __FILE__, 'activate_aff_pro' );
register_deactivation_hook( __FILE__, 'deactivate_aff_pro' );

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

// Tải các class chính của plugin
require plugin_dir_path( __FILE__ ) . 'includes/class-aff-pro-license.php';
require plugin_dir_path( __FILE__ ) . 'includes/class-aff-pro.php';

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
	
	// Khởi tạo license manager sau khi plugin được khởi chạy
	if ( class_exists( 'AFF_Pro_License_Manager' ) ) {
		new AFF_Pro_License_Manager( plugin_dir_path( __FILE__ ) . 'includes/plugin.json' );
	}
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