<?php

/**
 * Script migration dữ liệu từ VuaCode AFF sang AFF Pro
 *
 * @package AFF_Pro
 * @since 1.0.0
 */

// Ngăn chặn truy cập trực tiếp
if ( !defined( 'WPINC' ) ) {
	die;
}

/**
 * Class xử lý migration dữ liệu
 */
class AFF_Pro_Migration {

	/**
	 * Mapping các bảng cũ sang bảng mới
	 * @var array
	 */
	private static $table_mapping = [
		'vuacode_banners' => 'affpro_banners',
		'vuacode_commission_settings' => 'affpro_commission_settings',
		'vuacode_configs' => 'affpro_configs',
		'vuacode_coupons' => 'affpro_coupons',
		'vuacode_history' => 'affpro_history',
		'vuacode_payments' => 'affpro_payments',
		'vuacode_traffics' => 'affpro_traffics',
		'vuacode_user_order' => 'affpro_user_order',
		'vuacode_user_relationships' => 'affpro_user_relationships',
	];

	/**
	 * Thực hiện migration dữ liệu
	 *
	 * @return array Kết quả migration
	 */
	public static function migrate() {
		global $wpdb;
		
		$results = [
			'success' => true,
			'migrated_tables' => [],
			'errors' => [],
			'total_records' => 0
		];

		foreach ( self::$table_mapping as $old_table => $new_table ) {
			$old_table_name = $wpdb->prefix . $old_table;
			$new_table_name = $wpdb->prefix . $new_table;

			// Kiểm tra xem bảng cũ có tồn tại không
			if ( !self::table_exists( $old_table_name ) ) {
				continue;
			}

			// Kiểm tra xem bảng mới có tồn tại không
			if ( !self::table_exists( $new_table_name ) ) {
				$results['errors'][] = "Bảng mới {$new_table_name} không tồn tại";
				continue;
			}

			// Đếm số records trong bảng cũ
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$old_table_name}" );
			
			if ( $count > 0 ) {
				// Kiểm tra xem bảng mới đã có dữ liệu chưa
				$new_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$new_table_name}" );
				
				if ( $new_count == 0 ) {
					// Copy dữ liệu từ bảng cũ sang bảng mới
					$result = $wpdb->query( "INSERT INTO {$new_table_name} SELECT * FROM {$old_table_name}" );
					
					if ( $result !== false ) {
						$results['migrated_tables'][] = [
							'old_table' => $old_table,
							'new_table' => $new_table,
							'records' => $count
						];
						$results['total_records'] += $count;
					} else {
						$results['errors'][] = "Lỗi khi copy dữ liệu từ {$old_table} sang {$new_table}: " . $wpdb->last_error;
						$results['success'] = false;
					}
				} else {
					$results['errors'][] = "Bảng {$new_table} đã có dữ liệu, bỏ qua migration";
				}
			}
		}

		// Migration các cấu hình trong wp_options
		self::migrate_options();

		return $results;
	}

	/**
	 * Migration các options từ vuacode sang affpro
	 */
	private static function migrate_options() {
		global $wpdb;

		// Lấy tất cả options có prefix vuacode
		$options = $wpdb->get_results( 
			"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'vuacode_%'",
			ARRAY_A 
		);

		foreach ( $options as $option ) {
			$old_name = $option['option_name'];
			$new_name = str_replace( 'vuacode_', 'affpro_', $old_name );
			
			// Kiểm tra xem option mới đã tồn tại chưa
			if ( !get_option( $new_name ) ) {
				update_option( $new_name, $option['option_value'] );
			}
		}
	}

	/**
	 * Kiểm tra xem bảng có tồn tại không
	 *
	 * @param string $table_name Tên bảng
	 * @return bool
	 */
	private static function table_exists( $table_name ) {
		global $wpdb;
		
		$result = $wpdb->get_var( 
			$wpdb->prepare( 
				"SHOW TABLES LIKE %s", 
				$table_name 
			) 
		);
		
		return $result === $table_name;
	}

	/**
	 * Rollback migration (khôi phục dữ liệu)
	 *
	 * @return array Kết quả rollback
	 */
	public static function rollback() {
		global $wpdb;
		
		$results = [
			'success' => true,
			'cleared_tables' => [],
			'errors' => []
		];

		foreach ( self::$table_mapping as $old_table => $new_table ) {
			$new_table_name = $wpdb->prefix . $new_table;

			if ( self::table_exists( $new_table_name ) ) {
				$result = $wpdb->query( "TRUNCATE TABLE {$new_table_name}" );
				
				if ( $result !== false ) {
					$results['cleared_tables'][] = $new_table;
				} else {
					$results['errors'][] = "Lỗi khi xóa dữ liệu bảng {$new_table}: " . $wpdb->last_error;
					$results['success'] = false;
				}
			}
		}

		return $results;
	}

	/**
	 * Tạo backup dữ liệu trước khi migration
	 *
	 * @return string|false Đường dẫn file backup hoặc false nếu lỗi
	 */
	public static function create_backup() {
		global $wpdb;
		
		$backup_dir = wp_upload_dir()['basedir'] . '/aff-pro-backups/';
		
		// Tạo thư mục backup nếu chưa có
		if ( !file_exists( $backup_dir ) ) {
			wp_mkdir_p( $backup_dir );
		}

		$backup_file = $backup_dir . 'vuacode-aff-backup-' . date( 'Y-m-d-H-i-s' ) . '.sql';
		$backup_content = '';

		foreach ( array_keys( self::$table_mapping ) as $old_table ) {
			$table_name = $wpdb->prefix . $old_table;
			
			if ( self::table_exists( $table_name ) ) {
				// Export structure
				$create_table = $wpdb->get_row( "SHOW CREATE TABLE {$table_name}", ARRAY_N );
				$backup_content .= $create_table[1] . ";\n\n";

				// Export data
				$rows = $wpdb->get_results( "SELECT * FROM {$table_name}", ARRAY_A );
				
				foreach ( $rows as $row ) {
					$values = array_map( function( $value ) {
						return is_null( $value ) ? 'NULL' : "'" . esc_sql( $value ) . "'";
					}, array_values( $row ) );
					
					$backup_content .= "INSERT INTO {$table_name} VALUES (" . implode( ', ', $values ) . ");\n";
				}
				
				$backup_content .= "\n";
			}
		}

		if ( file_put_contents( $backup_file, $backup_content ) ) {
			return $backup_file;
		}

		return false;
	}
}