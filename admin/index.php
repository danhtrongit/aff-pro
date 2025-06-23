<?php
global $wpdb;
$sql = "SELECT * FROM {$wpdb->prefix}vuacode_configs";
if ( !$wpdb->get_results( $sql ) ) {
	$site_url       = get_site_url();
	$aff_db_configs = [
		"ALTER TABLE  {$wpdb->prefix}users CHANGE `user_registered` `user_registered` DATETIME NULL DEFAULT NULL;",
		"ALTER TABLE  {$wpdb->prefix}users ADD  `user_phone` varchar(25)  DEFAULT NULL;",
		"ALTER TABLE  {$wpdb->prefix}users ADD  `level` tinyint(4) NOT NULL DEFAULT '0';",
		"ALTER TABLE  {$wpdb->prefix}users ADD  `balance` int(255) NOT NULL DEFAULT '0';",
		"ALTER TABLE  {$wpdb->prefix}users ADD  `income` int(255) NOT NULL DEFAULT '0';",
		"ALTER TABLE  {$wpdb->prefix}users ADD  `commission_percent` int(11) DEFAULT NULL;",
		"ALTER TABLE  {$wpdb->prefix}users ADD  `aff_active` tinyint(4) NOT NULL DEFAULT '0';",
		"ALTER TABLE  {$wpdb->prefix}users ADD  `parent_id` int(11) DEFAULT 0;",
		"ALTER TABLE  {$wpdb->prefix}users ADD  `data_json` TEXT NULL AFTER `parent_id`;",


		"INSERT INTO `{$wpdb->prefix}vuacode_configs` (`id`, `config_name`, `config_value`, `description`, `field_name`, `field_attributes`, `autoload`) VALUES
		(10, 'warning_days_duplicate_order', '10', NULL, NULL, NULL, 0),
		(11, 'commission_user_levels', '[{\"name\":\"Cấp Đồng\",\"income\":\"0\",\"commission\":\"0\"},{\"name\":\"Cấp Bạc\",\"income\":\"2000000\",\"commission\":\"1\"},{\"name\":\"Cấp Vàng\",\"income\":\"3000000\",\"commission\":\"2\"},{\"name\":\"Cấp Platinum\",\"income\":\"4000000\",\"commission\":\"3\"},{\"name\":\"Kim Cương\",\"income\":\"5000000\",\"commission\":\"5\"},{\"name\":\"\",\"income\":\"\",\"commission\":\"\"},{\"name\":\"\",\"income\":\"\",\"commission\":\"\"}]', NULL, NULL, NULL, 0),
		(12, 'user_level', '5', NULL, NULL, NULL, 0),
		(13, 'commission_relationship_levels', '[{\"name\":\"Cấp 1\",\"income\":\"1\",\"commission\":\"7\"},{\"name\":\"Cấp 2\",\"income\":\"2000000\",\"commission\":\"6\"},{\"name\":\"Cấp 3\",\"income\":\"3000000\",\"commission\":\"5\"},{\"name\":\"Cấp 4\",\"income\":\"4000000\",\"commission\":\"4\"},{\"name\":\"Cấp 5\",\"income\":\"5000000\",\"commission\":\"3\"},{\"name\":\"Cấp 6\",\"income\":\"\",\"commission\":\"2\"},{\"name\":\"Cấp 7\",\"income\":\"\",\"commission\":\"1\"}]', NULL, NULL, NULL, 0),
		(14, 'relationship_level', '0', NULL, NULL, NULL, 0),
		(15, 'aff_mode', 'order_mode', NULL, NULL, NULL, 0),
		(16, 'commission_percent_default', '10', NULL, NULL, NULL, 0),
		(17, 'aff_commission_include_order_shipping', 'true', NULL, NULL, NULL, 0),
		(18, 'aff_cookie_once', 'true', NULL, NULL, NULL, 0),
		(19, 'aff_cookie_time', '21', NULL, NULL, NULL, 0),
		(20, 'aff_min_request', '100000', NULL, NULL, NULL, 0),
		(21, 'aff_auto_active', 'true', NULL, NULL, NULL, 0),
		(23, 'auto_active_aff', 'false', NULL, NULL, NULL, 0),
		(25, 'noti_email_user_actived', 'Chúc mừng tài khoản [user_name] đã được kích hoạt tính năng Cộng tác viên trên website {$site_url}', NULL, NULL, NULL, 0),
		(26, 'noti_not_active', '<div><br></div>Tài khoản của bạn chưa được kích hoạt tính năng Affiliate, vui lòng liên hệ Admin để được kích hoạt để có thể trở thành cộng tác viên.<div><br></div>', NULL, NULL, NULL, 0),
		(27, 'noti_email_creat_payment_request', 'Thông báo bạn vừa tạo yêu cầu rút tiền cho tài khoản [user_name] trên hệ thống {$site_url}\n- Số tiền: [total]\n- Thông tin ngân hàng: [bank_information]\nNếu có thắc mắc xin vui lòng liên hệ, chúng tôi sẽ duyệt lệnh thanh toán của bạn trong thời gian sớm nhất.', NULL, NULL, NULL, 0),
		(28, 'noti_email_payment_request_completed', 'Chúc mừng bạn vừa được thanh toán thành công cho lệnh rút tiền tài khoản [user_name] trên hệ thống {$site_url}\n- Số tiền: [total]\n- Thông tin ngân hàng: [bank_infomation]\nHãy kiểm tra lại tài khoản ngân hàng, Nếu có thắc mắc xin vui lòng liên hệ chúng tôi.', NULL, NULL, NULL, 0),
		(29, 'noti_email_order_completed', 'Bạn vừa được duyệt hoa hồng trên hệ thống {$site_url}\nMã đơn hàng:   #[order_id]\nTổng giá trị đơn hàng: [order_total] đ\nHoa hồng mà bạn nhận được: [commision]', NULL, NULL, NULL, 0),
		(30, 'commission_relationship_mode', 'commission', NULL, NULL, NULL, 0),
		(31, 'aff_email', 'false', NULL, NULL, NULL, 0),
		(32, 'cookie_traffic_mode', 'true', NULL, NULL, NULL, 0),
		(33, 'aff_user_page', '{$site_url}/cong-tac-vien/', NULL, NULL, NULL, 0),
		(34, 'income_include_child', 'false', NULL, NULL, NULL, 0),
		(35, 'version', 'pro', NULL, NULL, NULL, 0),
		(36, 'aff_refund_commission', 'false', NULL, NULL, NULL, 0),
		(37, 'allow_order_self', 'false', NULL, NULL, NULL, 0),
		(38, 'logo', 'https://magiamgiashopee.vn/wp-content/uploads/2018/08/logoshopee.png', NULL, NULL, NULL, 0),
		(39, 'favicon', '', NULL, NULL, NULL, 0),
		(40, 'noti_all', '', NULL, NULL, NULL, 0),
		(41, 'ref_name', 'ref', NULL, NULL, NULL, 0),
		(42, 'ref_value_is_id', 'false', NULL, NULL, NULL, 0),
		(43, 'noti_new_order', 'Chúc mừng Cộng tác viên [user_name] giới thiệu được đơn hàng mới trên website <br>ID đơn hàng: [order_id] <br>Tổng giá trị đơn hàng: [order_total]<br>Hoa hồng chờ duyệt: [commission]', NULL, NULL, NULL, 0)
		"
	];

	foreach ( $aff_db_configs as $q ) {
		$wpdb->query( $q );
		}
	}
