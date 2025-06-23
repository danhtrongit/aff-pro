<?php

/**
 * Class xử lý khi vô hiệu hóa plugin
 *
 * @link       https://affpro.dev
 * @since      1.0.0
 *
 * @package    AFF_Pro
 * @subpackage AFF_Pro/includes
 */

/**
 * Class được thực thi khi vô hiệu hóa plugin
 *
 * Class này định nghĩa tất cả code cần thiết để chạy khi plugin bị vô hiệu hóa.
 * Thường được sử dụng để dọn dẹp temporary data, clear cache, v.v.
 *
 * @since      1.0.0
 * @package    AFF_Pro
 * @subpackage AFF_Pro/includes
 * @author     AFF Pro Team
 */
class AFF_Pro_Deactivator {

	/**
	 * Thực hiện vô hiệu hóa plugin
	 *
	 * Dọn dẹp dữ liệu tạm thời, clear cache và các tác vụ cleanup khác
	 * khi plugin bị vô hiệu hóa.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Clear any cached data
		wp_cache_flush();
		
		// Clear rewrite rules
		flush_rewrite_rules();
		
		// Có thể thêm các tác vụ cleanup khác ở đây
		// Ví dụ: xóa scheduled events, clear transients, v.v.
	}
}
