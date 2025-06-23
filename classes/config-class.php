<?php

/**
 * Class quản lý cấu hình hệ thống AFF Pro
 *
 * Class này xử lý việc lưu trữ, truy xuất và cập nhật các cấu hình
 * của hệ thống affiliate marketing.
 *
 * @package AFF_Pro
 * @since 1.0.0
 */
class AFF_Config {

	/**
	 * Tên bảng lưu trữ cấu hình
	 * @var string
	 */
	static $table = 'affpro_configs';

	/**
	 * Lấy tất cả cấu hình hệ thống
	 *
	 * @return array Mảng chứa tất cả cấu hình với key là config_name
	 * @since 1.0.0
	 */
	static function getConfigs() {
		$db   = MH_Query::init( null, self::$table );
		$data = [];
		$rows = $db->get();
		
		// Chuyển đổi dữ liệu thành mảng key-value
		foreach ( $rows as $key => $row ) {
			$data[$row['config_name']] = MH_FormatConfigValue( $row['config_value'] );
		}
		
		return $data;
	}

	/**
	 * Lấy giá trị của một cấu hình cụ thể
	 *
	 * @param string $config_name Tên cấu hình cần lấy
	 * @return mixed|false Giá trị cấu hình hoặc false nếu không tìm thấy
	 * @since 1.0.0
	 */
	static function getConfig( $config_name ) {
		global $wpdb;
		
		// Chuẩn bị và thực thi câu truy vấn an toàn
		$sql = $wpdb->prepare( 
			"SELECT * FROM {$wpdb->prefix}affpro_configs WHERE config_name = %s", 
			$config_name 
		);
		$row = $wpdb->get_row( $sql, ARRAY_A );
		
		if ( $row ) {
			return $row['config_value'];
		}
		
		return false;
	}

	static function setConfig($config_name, $config_value)
		{

		if ( strpos( $config_name, 'noti_' ) !== false ) {
			// global $wpdb;
			// debug("UPDATE {$wpdb->prefix}affpro_configs SET config_value = '{$config_value}' WHERE config_name = '{$config_name}'");
			// $wpdb->query("UPDATE {$wpdb->prefix}affpro_configs SET config_value = '{$config_value}' WHERE config_name = '{$config_name}'");
			// return;
			$config_value = stripslashes( $config_value );
			}

		$db = MH_Query::init( null, self::$table );
		if ( !$config_name )
			return;
		$db->where( 'config_name', $config_name );
		$config = $db->first();

		$data = [
			'config_name'  => $config_name,
			'config_value' => $config_value,
			// 'config_value' => stripslashes_deep($config_value),
		];

		if ( $config ) {
			$db->where( 'config_name', $config_name );
			$db->update( $data );
			} else {
			$db->insert( $data );

			}

		}





	}


?>
