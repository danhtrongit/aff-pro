<?php

/**
 * Class xử lý khi kích hoạt plugin
 *
 * @link       https://affpro.dev
 * @since      1.0.0
 *
 * @package    AFF_Pro
 * @subpackage AFF_Pro/includes
 */

/**
 * Class được thực thi khi kích hoạt plugin
 *
 * Class này định nghĩa tất cả code cần thiết để chạy khi plugin được kích hoạt.
 * Bao gồm tạo bảng database, thiết lập dữ liệu mặc định, v.v.
 *
 * @since      1.0.0
 * @package    AFF_Pro
 * @subpackage AFF_Pro/includes
 * @author     AFF Pro Team
 */
class AFF_Pro_Activator {

	/**
	 * Thực hiện kích hoạt plugin
	 *
	 * Tạo các bảng database cần thiết và thiết lập dữ liệu mặc định
	 * cho hệ thống affiliate marketing.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// Định nghĩa các câu lệnh SQL để tạo bảng
		$sqls = [
			// Bảng quản lý banner quảng cáo
			"CREATE TABLE `{$wpdb->prefix}affpro_banners` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`name` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
				`dimension` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
				`url` char(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
				`link` char(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
				`created_at` timestamp NULL DEFAULT NULL,
				`updated_at` timestamp NULL DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8",
			
			// Bảng cài đặt hoa hồng theo sản phẩm/danh mục
			"CREATE TABLE `{$wpdb->prefix}affpro_commission_settings` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`type` varchar(20) NOT NULL,
				`object_id` int(11) NOT NULL,
				`commission` int(11) NOT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8",
			
			// Bảng cấu hình hệ thống
			"CREATE TABLE `{$wpdb->prefix}affpro_configs` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`config_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
				`config_value` text NOT NULL,
				`description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
				`field_name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
				`field_attributes` int(11) DEFAULT NULL,
				`autoload` tinyint(4) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8",
			
			// Bảng quản lý mã giảm giá của affiliate
			"CREATE TABLE `{$wpdb->prefix}affpro_coupons` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`user_id` int(11) NOT NULL,
				`user_login` varchar(100) DEFAULT NULL,
				`coupon` varchar(255) NOT NULL,
				`value` float NOT NULL,
				`apply_total` int(11) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8",
			
			// Bảng lịch sử giao dịch và hoa hồng
			"CREATE TABLE `{$wpdb->prefix}affpro_history` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`user_id` int(11) NOT NULL,
				`user_login` varchar(100) DEFAULT NULL,
				`order_id` varchar(100) DEFAULT NULL,
				`amount` varchar(255) NOT NULL,
				`description` varchar(255) NOT NULL,
				`begin_balance` int(11) NOT NULL,
				`end_balance` int(11) NOT NULL,
				`date` datetime NOT NULL,
				`type` tinyint(4) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`),
				KEY `user_login` (`user_login`),
				KEY `user_id` (`user_id`),
				KEY `date` (`date`)
			) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8",
			
			// Bảng quản lý thanh toán
			"CREATE TABLE `{$wpdb->prefix}affpro_payments` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`user_id` int(11) NOT NULL,
				`user_login` varchar(100) DEFAULT NULL,
				`amount` int(11) NOT NULL,
				`status` int(11) NOT NULL DEFAULT '0',
				`date` datetime NOT NULL,
				`bank_info` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
				`description` varchar(255) DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8",
			
			// Bảng theo dõi traffic và clicks
			"CREATE TABLE `{$wpdb->prefix}affpro_traffics` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`user_id` int(11) NOT NULL,
				`url` varchar(255) NOT NULL,
				`product` int(11) DEFAULT NULL,
				`total` int(11) NOT NULL DEFAULT '1',
				`date` date DEFAULT NULL,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8",
			
			// Bảng đơn hàng của affiliate
			"CREATE TABLE `{$wpdb->prefix}affpro_user_order` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`user_id` int(11) NOT NULL,
				`user_login` varchar(100) DEFAULT NULL,
				`user_ref` varchar(100) DEFAULT NULL,
				`order_id` int(11) NOT NULL,
				`order_status` varchar(20) DEFAULT NULL,
				`ref_path` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
				`ref_product` int(11) DEFAULT NULL,
				`ref_coupon` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
				`customer_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
				`customer_phone` varchar(15) DEFAULT NULL,
				`date` datetime NOT NULL,
				`status` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
				`total` int(11) NOT NULL,
				`commission` int(11) DEFAULT NULL,
				`is_paid` int(255) DEFAULT '0',
				`level` int(11) NOT NULL DEFAULT '0',
				`description` varchar(255) DEFAULT NULL,
				`payment_date` date DEFAULT NULL,
				`order_json` text,
				PRIMARY KEY (`id`),
				KEY `user_id` (`user_id`),
				KEY `user_login` (`user_login`),
				KEY `order_id` (`order_id`),
				KEY `date` (`date`)
			) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=utf8",
			
			// Bảng mối quan hệ phân cấp affiliate
			"CREATE TABLE `{$wpdb->prefix}affpro_user_relationships` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`ancestor_id` int(11) NOT NULL,
				`descendant_id` int(11) NOT NULL,
				`distance` int(11) NOT NULL,
				`ancestor_level` int(11) NOT NULL,
				PRIMARY KEY (`id`),
				KEY `ancestor_id` (`ancestor_id`)
			) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8",

			// Các câu lệnh ALTER TABLE để thêm cột vào bảng wp_users
			// Được comment để tránh lỗi khi đã có cột
			// "ALTER TABLE {$wpdb->users} ADD `user_phone` varchar(25) DEFAULT NULL;",
			// "ALTER TABLE {$wpdb->users} ADD `level` tinyint(4) NOT NULL DEFAULT '0';",
			// "ALTER TABLE {$wpdb->users} ADD `balance` int(255) NOT NULL DEFAULT '0';",
			// "ALTER TABLE {$wpdb->users} ADD `income` int(255) NOT NULL DEFAULT '0';",
			// "ALTER TABLE {$wpdb->users} ADD `commission_percent` int(11) DEFAULT NULL;",
			// "ALTER TABLE {$wpdb->users} ADD `aff_active` tinyint(4) NOT NULL DEFAULT '0';",
			// "ALTER TABLE {$wpdb->users} ADD `parent_id` int(11) DEFAULT 0;",
			// "ALTER TABLE {$wpdb->users} ADD `data_json` TEXT NULL AFTER `parent_id`;",
		];

		// Thực thi các câu lệnh SQL
		foreach ( $sqls as $sql ) {
			dbDelta( $sql );
		}
		
		// Thiết lập dữ liệu mặc định nếu cần
		self::setup_default_data();
	}

	/**
	 * Thiết lập dữ liệu mặc định cho hệ thống
	 *
	 * @since 1.0.0
	 */
	private static function setup_default_data() {
		// Có thể thêm dữ liệu mặc định ở đây nếu cần
		// Ví dụ: cấu hình mặc định, banner mẫu, v.v.
	}
}