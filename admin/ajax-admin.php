<?php
class AFF_Ajax_Admin
	{


	public function __construct()
		{

		//check Permission


		$this->ajax_admin();
		$this->ajax_no_priv();
		}


	public function ajax_admin()
		{
		$ajaxs = [
			//Admin User
			'aff_update_user'             => 'aff_update_user',
			'aff_get_user_tree'           => 'aff_get_user_tree',
			'aff_get_user_tree_2'         => 'aff_get_user_tree_2',
			'aff_get_user_profile'        => 'aff_get_user_profile',
			'aff_update_user_profile'     => 'aff_update_user_profile',
			'aff_user_logout'             => 'aff_user_logout',
			'aff_register_user'           => 'aff_register_user',
			'aff_init_user_relationship'  => 'aff_init_user_relationship',
			'aff_user_register_2'         => 'aff_user_register_2',
			'aff_user_change_password'    => 'aff_user_change_password',
			'aff_set_user_relationship'   => 'aff_set_user_relationship',


			//Config
			'aff_update_configs'          => 'aff_update_configs',
			'aff_set_configs'             => 'aff_set_configs',
			'aff_get_users'               => 'aff_get_users',
			'aff_get_user'                => 'aff_get_user',

			//Hitory
			'aff_get_balance_history'     => 'aff_get_balance_history',
			'aff_get_user_order_history'  => 'aff_get_user_order_history',


			//Payment
			'aff_get_payments'            => 'aff_get_payments',
			'aff_approve_payments'        => 'aff_approve_payments',
			'aff_create_payment_request'  => 'aff_create_payment_request',
			'aff_get_user_bank_info'      => 'aff_get_user_bank_info',


			//Dashboard
			'aff_get_dashboard_info'      => 'aff_get_dashboard_info',
			'aff_get_dashboard_info_2'    => 'aff_get_dashboard_info_2',

			//Commission settings
			'aff_get_products'            => 'aff_get_products',
			'aff_get_commission_setting'  => 'aff_get_commission_setting',
			'aff_save_commission_setting' => 'aff_save_commission_setting',
			'aff_get_product_by_id'       => 'aff_get_product_by_id',
			'aff_get_product_category'    => 'aff_get_product_category',




			//Banner 
			'aff_add_banner'              => 'aff_add_banner',
			'aff_get_banners'             => 'aff_get_banners',
			'aff_remove_banner'           => 'aff_remove_banner',

			//Remove all data
			'aff_remove_all_data'         => 'aff_remove_all_data',
			'aff_re_commission'           => 'aff_re_commission',

			'aff_select2_ajax_user_login' => 'aff_select2_ajax_user_login',
			'aff_assign_commission'       => 'aff_assign_commission',
			'aff_list_order_comments'     => 'aff_list_order_comments',
			'aff_get_user_order_income'   => 'aff_get_user_order_income',
			'aff_export_excel_payment'    => 'aff_export_excel_payment',
			'aff_export_excel_income'     => 'aff_export_excel_income',





		];

		if ( $ajaxs ) {
			foreach ( $ajaxs as $k => $v ) {
				add_action( 'wp_ajax_' . $v, [ $this, $v ] );
				}

			}
		}


	public function ajax_no_priv()
		{

		$ajaxs = [
			'aff_get_configs'       => 'aff_get_configs',

			'aff_user_login'        => 'aff_user_login',
			'aff_user_register_2'   => 'aff_user_register_2',
			'aff_lost_password'     => 'aff_lost_password',
			//Traffic
			'aff_set_traffic'       => 'aff_set_traffic',
			'aff_get_bank_json'     => 'aff_get_bank_json',
			'aff_reset_level_month' => 'aff_reset_level_month',
		];

		if ( $ajaxs ) {
			foreach ( $ajaxs as $k => $v ) {
				add_action( 'wp_ajax_' . $v, [ $this, $v ] );
				add_action( 'wp_ajax_nopriv_' . $v, [ $this, $v ] );
				}

			}

		}

	public function aff_export_excel_payment()
		{
		include AFF_PATH . 'helpers/excel/payment.php';
		die();
		}

	public function aff_export_excel_income()
		{
		include AFF_PATH . 'helpers/excel/income.php';
		die();
		}

	private function checkPermissionAdmin()
		{
		if ( !current_user_can( 'administrator' ) )
			MH_Response( false, 'Không có quyền thực hiện thao tác này.', [] );
		}

	private function checkPermissionOwner($user_id)
		{
		if ( $user_id != get_current_user_id() && !current_user_can( 'administrator' ) )
			MH_Response( false, 'Không có quyền thực hiện thao tác này.', [] );

		}

	//ADMIN

	public function aff_remove_all_data()
		{
		$this->checkPermissionAdmin();
		global $wpdb;
		$tables = [
			'affpro_user_order',
			'affpro_traffics',
			'affpro_payments',
			'affpro_history',
			'affpro_coupons',
			'affpro_commission_settings',
		];

		foreach ( $tables as $key => $table ) {
			MH_Query::init( null, $table )->delete();
			}
		$wpdb->query( "UPDATE {$wpdb->prefix}users SET balance = 0, income = 0, level = 0" );
		$wpdb->query( "UPDATE {$wpdb->prefix}affpro_user_relationships SET ancestor_level = 0" );
		MH_Response( true, 'Đã xóa toàn bộ dữ liệu', [] );

		}

	public function aff_reset_level_month()
		{
		if ( AFF_Config::getConfig( 'aff_reset_level_month' ) == 'true' ) {
			global $wpdb;
			$wpdb->query( "UPDATE {$wpdb->prefix}users SET income = 0, level = 0" );
			}
		MH_Response( true, 'Cập nhật thành công', [] );

		}

	public function aff_get_user_order_income()
		{
		$filters  = isset($_POST['filters']) ? $_POST['filters'] : '';
		$page     = isset($_POST['page']) ? $_POST['page'] : 1;
		$per_page = isset($_POST['per_page']) ? $_POST['per_page'] : 15;

		$result = AFF_User_Order::getUserIncome( $filters, $page, $per_page );
		MH_Response( true, '', $result, true, 1 );
		}

	public function aff_get_bank_json()
		{
		$banks = file_get_contents( AFF_PATH . 'helpers/banks.json' );
		$banks = json_decode( $banks, true );
		MH_Response( true, '', $banks['data'] );

		}

	public function aff_list_order_comments()
		{
		$id = isset($_POST['id']) ? sanitize_text_field( $_POST['id'] ) : 0;
		if ( $id ) {
			$res  = MH_Query::init( null, 'comments' )->where( 'comment_post_ID', $id )->where( 'comment_type', 'order_note' )->order_by( 'comment_date', 'DESC' )->get();
			$data = $res ? $res : [];
			MH_Response( true, '', $data );
			}
		MH_Response( false, 'Có lỗi xảy ra' );
		}

	public function aff_select2_ajax_user_login()
		{
		$this->checkPermissionAdmin();
		global $wpdb;
		$search  = $_GET['search'];
		$results = $wpdb->get_results( "
			   SELECT user_login
			   FROM {$wpdb->users}
			   WHERE user_login LIKE '%$search%'
			" );

		$data = array();
		foreach ( $results as $result ) {
			$data[] = array(
				'id'   => $result->user_login,
				'text' => $result->user_login
			);
			}

		wp_send_json( $data );
		}

	public function aff_update_user_role()
		{
		$this->checkPermissionAdmin();
		$user_id   = isset($_POST['user_id']) ? sanitize_text_field( $_POST['user_id'] ) : 0;
		$role_slug = isset($_POST['role']) ? sanitize_text_field( $_POST['role'] ) : 0;
		if ( $user_id && $role_slug ) {
			$user           = get_user_by( 'ID', $user_id );
			$role_whitelist = [ 'si', 'ctv', 'subscriber', 'contributor' ];

			if ( !in_array( $user->roles[0], $role_whitelist ) )
				MH_Response( false, 'Xin lỗi bạn chỉ có thể thay đổi với tài khoản thường' );


			foreach ( get_editable_roles() as $key => $role_info ) {
				$role_name = sanitize_title( $role_info['name'] );
				$user->remove_role( $role_name );
				}

			$user->add_role( $role_slug );

			wp_update_user( array( 'ID' => $user_id, 'role' => $role_slug ) );
			MH_Response( true, 'Cập nhật thành công' );

			}
		MH_Response( false, 'Có lỗi xảy ra' );

		}

	public function aff_assign_commission()
		{
		$this->checkPermissionAdmin();
		$user       = isset($_POST['user']) ? sanitize_text_field( $_POST['user'] ) : 0;
		$id         = isset($_POST['id']) ? sanitize_text_field( $_POST['id'] ) : 0;
		$user_order = MH_Query::init( null, 'affpro_user_order' )->where( 'order_id', $id )->first();
		if ( $user_order )
			MH_Response( false, 'Có lỗi xảy ra' );

		if ( $user && $id ) {
			$user  = get_user_by( 'login', $user );
			$order = wc_get_order( $id );
			AFF_User_Order::create( $user, $order, '', '', NULL );
			MH_Response( true, 'Cập nhật thành công' );

			}

		MH_Response( false, 'Có lỗi xảy ra' );

		}

	public function aff_re_commission($order_id = '')
		{
		$this->checkPermissionAdmin();
		$id = isset($_POST['id']) ? $_POST['id'] : 0;
		if ( $order_id )
			$id = $order_id;
		if ( $id ) {
			$user_order = MH_Query::init( null, 'affpro_user_order' )->where( 'order_id', $id )->where( 'status', 0 )->where( 'level', 0 )->first();
			if ( !$user_order )
				MH_Response( false, 'Không tìm thấy dữ liệu', [] );

			$user  = get_user_by( 'login', $user_order['user_ref'] );
			$order = wc_get_order( $id );
			MH_Query::init( null, 'affpro_user_order' )->where( 'order_id', $id )->delete();
			AFF_User_Order::create( $user, $order, $user_order['ref_path'], $user_order['ref_product'], NULL );
			MH_Response( true, 'Cập nhật thành công', [] );

			}

		}

	public function aff_get_user()
		{
		$this->checkPermissionAdmin();
		$type  = isset($_POST['type']) ? $_POST['type'] : '';
		$value = isset($_POST['value']) ? $_POST['value'] : '';
		if ( $type && $value ) {
			$result = MH_Query::init( null, 'users' )->whereRaw( "$type = '$value'" )->first();
			if ( $result )
				MH_Response( true, $result['display_name'] . ' ' . $result['user_phone'], $result );

			}
		MH_Response( false, 'Không tìm thấy tài khoản tương ứng' );

		}

	public function aff_set_user_relationship()
		{
		$this->checkPermissionAdmin();
		$descendant_id = isset($_POST['descendant_id']) ? $_POST['descendant_id'] : '';
		$ancestor_id   = isset($_POST['ancestor_id']) ? $_POST['ancestor_id'] : '';

		// $descendant = AFF_User::getUserBy(['column' => 'ID', 'value' => $descendant_id]);
		// if($descendant['parent_id'])
		// 	MH_Response(false, 'Xin lỗi tài khoản này đã là cấp dưới của tài khoản khác');


		$res = AFF_User_Relationship::setRelationship2( $descendant_id, $ancestor_id );

		MH_Query::init( null, 'users' )->where( 'ID', $descendant_id )->update( [ 'parent_id' => $ancestor_id ] );
		MH_Response( $res['success'], $res['msg'], [ 'a' => $ancestor_id, 'd' => $descendant_id ] );


		}

	public function aff_user_change_password()
		{
		$old_password   = isset($_POST['old_password']) ? sanitize_text_field( $_POST['old_password'] ) : '';
		$new_password   = isset($_POST['new_password']) ? sanitize_text_field( $_POST['new_password'] ) : '';
		$renew_password = isset($_POST['renew_password']) ? sanitize_text_field( $_POST['renew_password'] ) : '';
		$user_id        = get_current_user_id();
		$user           = AFF_User::getUserBy( [ 'column' => 'ID', 'value' => $user_id ], true );
		if ( $old_password && $new_password && $renew_password && $user ) {

			if ( !wp_check_password( $old_password, $user['user_pass'], $user['ID'] ) )
				MH_Response( false, 'Mật khẩu không chính xác' );

			if ( $new_password == $renew_password ) {
				wp_set_password( $new_password, $user_id );
				MH_Response( true, 'Cập nhật thành công' );
				} else
				MH_Response( false, 'Mật khẩu mới không khớp' );

			}

		}

	public function aff_user_login()
		{

		// if(isset($_POST['g-recaptcha-response'])){
		// 	$gcaptcha=$_POST['g-recaptcha-response'];
		// }
		// if(!$gcaptcha){
		// 	echo json_encode(['code' => 0, 'msg' => 'Hãy xác thực mã Captcha']);
		// 	exit;
		// }
		// $result = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LcG29UaAAAAAM7a9JXaY7dqct8_av93hMvmxbOw&response=".$gcaptcha."&remoteip=".$_SERVER['REMOTE_ADDR']);
		// $result = json_decode($result, true);
		// if(!$result['success'])
		// {
		// 	echo json_encode(['code' => 0, 'msg' => 'Hãy xác thực mã Captcha']);
		// 	exit;
		// }

		$info                  = array();
		$info['user_login']    = $_POST['username'];
		$info['user_password'] = $_POST['password'];
		$info['remember']      = false;


		$user_signon = wp_signon( $info, false );

		if ( is_wp_error( $user_signon ) )
			MH_Response( false, 'Thông tin đăng nhập không chính xác' );
		else {
			$user = get_user_by( 'login', $info['user_login'] );
			$user = $user ? $user : get_user_by( 'email', $info['user_login'] );
			MH_Response( true, 'Đăng nhập thành công', $user );
			}




		}

	public function aff_user_logout()
		{

		wp_logout();
		MH_Response( true, 'Đăng xuất thành công' );

		}


	public function aff_get_user_profile($id = '')
		{
		if ( $id == '' )
			$id = get_current_user_id();

		$user = AFF_User::getUserBy( [ 'column' => 'ID', 'value' => $id ] );
		MH_Response( true, '', $user );

		}

	public function aff_update_user_profile()
		{
		$user          = wp_get_current_user();
		$user_email    = isset($_POST['user_email']) ? sanitize_text_field( $_POST['user_email'] ) : '';
		$display_name  = isset($_POST['display_name']) ? sanitize_text_field( $_POST['display_name'] ) : '';
		$user_phone    = isset($_POST['user_phone']) ? sanitize_text_field( $_POST['user_phone'] ) : '';
		$avatar_base64 = isset($_POST['avatar_base64']) ? $_POST['avatar_base64'] : '';

		if ( $user ) {

			if ( $avatar_base64 ) {
				if ( !check_base64_image( $avatar_base64 ) ) {
					MH_Response( false, 'Hình ảnh không hợp lệ' );
					}

				$bytes     = strlen( base64_decode( $avatar_base64 ) );
				$roughsize = (((int) $bytes) / 1024.0) * 0.67;
				$filesize  = round( $roughsize, 2 );
				if ( $filesize > 1000 )
					MH_Response( false, 'Hình đại diện vượt kích cỡ cho phép < 1MB' );

				$avatar = MH_Save_Image_1( $avatar_base64, 'avatar-' . $user->ID );

				}

			if ( $user_email && $user_email != $user->user_email ) {
				$exist = MH_Query::init( null, 'users' )->where( 'user_email', "'$user_email'" )->where( 'user_email', '!=', "'$user->user_email'" )->first();
				if ( $exist )
					MH_Response( false, 'Email này đã tồn tại trên hệ thống' );

				}
			if ( $user_phone && $user_phone != $user->user_phone ) {
				$exist = MH_Query::init( null, 'users' )->where( 'user_phone', "$user_phone" )->where( 'user_phone', '!=', "$user->user_phone" )->first();
				if ( $exist )
					MH_Response( false, 'Số điện thoại này này đã tồn tại trên hệ thống' );

				}
			// $data_json =  isset($_POST['data_json']) ? $_POST['data_json'] : '';
			$data = [
				'user_email'   => $user_email,
				'user_phone'   => $user_phone,
				'display_name' => $display_name,

			];
			if ( isset($avatar) )
				$data['avatar'] = $avatar;
			AFF_User::update( [ 'ID' => $user->ID ] + $data );
			MH_Response( true, 'Cập nhật thành công' );
			}

		}

	public function aff_create_payment_request()
		{
		$data = isset($_POST['data']) ? $_POST['data'] : '';
		$id   = get_current_user_id();
		if ( !$data || !$id )
			return;
		$result = AFF_Payment::create( $id, $data );
		MH_Response( $result['success'], $result['msg'] );

		}

	public function aff_get_user_bank_info()
		{
		$id     = get_current_user_id();
		$result = AFF_Payment::getUserBanks( $id );
		MH_Response( true, '', $result );

		}

	public function aff_approve_payments()
		{
		$this->checkPermissionAdmin();
		$id          = isset($_POST['id']) ? $_POST['id'] : 1;
		$status      = isset($_POST['status']) ? $_POST['status'] : 15;
		$description = isset($_POST['description']) ? $_POST['description'] : '';

		$result = AFF_Payment::approve( $id, $status, $description );
		MH_Response( $result['success'], $result['msg'] );

		}

	public function aff_get_balance_history()
		{
		$filters  = isset($_POST['filters']) ? $_POST['filters'] : '';
		$page     = isset($_POST['page']) ? $_POST['page'] : 1;
		$per_page = isset($_POST['per_page']) ? $_POST['per_page'] : 15;

		$result = AFF_History::getBalanceHistory( $filters, $page, $per_page );
		MH_Response( true, '', $result, true, 1 );

		}

	public function aff_get_user_order_history()
		{
		$this->checkPermissionAdmin();

		$filters  = isset($_POST['filters']) ? $_POST['filters'] : '';
		$page     = isset($_POST['page']) ? $_POST['page'] : 1;
		$per_page = isset($_POST['per_page']) ? $_POST['per_page'] : 15;
		$result   = AFF_User_Order::getUserOrderHistory( $filters, $page, $per_page );
		MH_Response( true, '', $result, true, 1 );
		}

	public function aff_get_payments()
		{
		$filters  = isset($_POST['filters']) ? $_POST['filters'] : '';
		$page     = isset($_POST['page']) ? $_POST['page'] : 1;
		$per_page = isset($_POST['per_page']) ? $_POST['per_page'] : 15;

		// Check Permission
		$user_id = isset($filters['user_id']) ? $filters['user_id'] : 0;
		$this->checkPermissionOwner( $user_id );

		$result = AFF_Payment::getPayments( $filters, $page, $per_page );
		MH_Response( true, '', $result, true, 1 );

		}

	public function aff_update_user()
		{
		$this->checkPermissionAdmin();
		$data = isset($_POST['data']) ? $_POST['data'] : '';
		if ( isset($data['ID']) ) {

			$user = MH_Query::init( null, 'users' )->where( 'ID', $data['ID'] )->first();
			if ( !$user )
				MH_Response( false, 'Không tìm thấy tài khoản' );

			$description_balance_change = isset($data['description_balance_change']) ? '. Lý do' . $data['description_balance_change'] : '';
			if ( isset($data['data_json']) )
				unset( $data['data_json'] );
			if ( isset($data['description_balance_change']) )
				unset( $data['description_balance_change'] );
			if ( isset($data['parent_login']) )
				unset( $data['parent_login'] );

			if ( $data['commission_percent'] === '' )
				$data['commission_percent'] = NULL;
			$result = MH_Query::init( null, 'users' )->where( 'ID', $data['ID'] )->update( $data );
			if ( !$result )
				MH_Response( true, 'Cập nhật thành công' );

			//Send an Email when Active AFF
			if ( isset($data['aff_active']) && $data['aff_active'] == 1 && $user['aff_active'] != $data['aff_active'] && AFF_Config::getConfig( 'aff_email' ) == 'true' ) {

				AFF_User::bonusRegister( $data['ID'] );
				$mail_content = AFF_Config::getConfig( 'noti_email_user_actived' );

				if ( $mail_content ) {
					AFF_SendMail( $user['user_email'], 'Tài khoản cộng tác viên đã được kích hoạt trên hệ thống ' . get_site_url(), $mail_content, [
						'[user_name]' => $user['display_name'],
					] );
					}
				}

			//Check Balance
			if ( isset($data['balance']) && $user['balance'] != $data['balance'] ) {

				$neg    = $data['balance'] - $user['balance'];
				$amount = abs( $neg );

				$note = [
					'user_id'       => $data['ID'],
					'user_login'    => $data['user_login'],
					'amount'        => $amount,
					'type'          => $neg > 0 ? 1 : 0,
					'begin_balance' => $user['balance'],
					'end_balance'   => $data['balance'],
					'description'   => 'Hệ thống cập nhật số dư ' . ($neg > 0 ? '+' : '-') . number_format( $amount ) . $description_balance_change,
				];
				AFF_History::create( $note );
				}



			$user = MH_Query::init( null, 'users' )->where( 'ID', $data['ID'] )->first();
			MH_Response( true, 'Cập nhật thành công', $user );

			}
		MH_Response( false, 'Có lỗi xảy ra' );

		}

	public function aff_get_users()
		{
		$filters  = isset($_POST['filters']) ? $_POST['filters'] : '';
		$page     = isset($_POST['page']) ? $_POST['page'] : 1;
		$per_page = isset($_POST['per_page']) ? $_POST['per_page'] : 15;

		$result = AFF_User::getList( $filters, $page, $per_page );
		MH_Response( true, '', $result, true, 1 );

		}

	public function aff_get_user_tree()
		{
		$filters  = isset($_POST['filters']) ? $_POST['filters'] : '';
		$page     = isset($_POST['page']) ? $_POST['page'] : 1;
		$per_page = isset($_POST['per_page']) ? $_POST['per_page'] : 15;

		$result = AFF_User::getUserTree( $filters, $page, $per_page );
		MH_Response( true, '', $result, true, 1 );
		}

	public function aff_get_user_tree_2()
		{
		$filters  = isset($_POST['filters']) ? $_POST['filters'] : '';
		$page     = isset($_POST['page']) ? $_POST['page'] : 1;
		$per_page = isset($_POST['per_page']) ? $_POST['per_page'] : 15;

		$result = AFF_User::getUserTree2( $filters, $page, $per_page );
		MH_Response( true, '', $result );
		}


	public function aff_update_configs()
		{
		$this->checkPermissionAdmin();
		$configs = isset($_POST['configs']) ? $_POST['configs'] : '';
		if ( $configs )
			AFF_Config::updateConfigs();

		die();
		}

	public function aff_set_configs()
		{
		$this->checkPermissionAdmin();
		$configs = isset($_POST['data']) ? $_POST['data'] : '';
		if ( $configs ) {
			foreach ( $configs as $key => $config ) {
				if ( is_array( $config ) ) {
					$config = json_encode( $config );
					}
				AFF_Config::setConfig( $key, $config );

				}
			}
		MH_Response( true, 'Cập nhật thành công', AFF_Config::getConfigs() );

		}

	public function aff_get_configs()
		{
		$data = [];
		$rows = AFF_Config::getConfigs();
		if ( $rows ) {
			$data = $rows;
			}
		echo json_encode( [ 'data' => $data ] );
		die();
		}

	public function aff_get_dashboard_info()
		{
		//Get General Info
		$info = [
			'orders'              => 0,
			'completed_orders'    => 0,
			'views'               => 0,
			'income'              => 0,
			'approved_income'     => 0,
			'commission'          => 0,
			'approved_commission' => 0,
			'conversion_rate'     => 0,

		];

		$front      = isset($_POST['f']) ? $_POST['f'] : '';
		$date_range = isset($_POST['date_range']) ? $_POST['date_range'] : '';
		$user_id    = isset($_POST['user_id']) ? $_POST['user_id'] : '';
		$filters    = [ 'date_range' => $date_range, 'user_id' => $user_id, 'level' => 0 ];
		$info       = AFF_User_Order::getOrderStats( $filters ) +
			AFF_User_Order::getIncomeStats( $filters ) +
			AFF_User_Order::getCommissionStats( $filters ) +
			[ 'views' => AFF_Traffic::getViewsTotal( $filters ) ];

		$info['conversion_rate'] = 0;

		if ( $info['views'] > 0 ) {
			$info['conversion_rate'] = number_format( $info['orders'] / $info['views'] * 100, 2 );
			}


		//Get Chart Data
		$chart = AFF_User_Order::getOrderStatByDay( $filters );

		//Get Order
		$orders = AFF_User_Order::getUserOrder( $filters + [ 'level' => 0 ] );
		$orders = $orders ? $orders : [];

		//Get Traffix box
		$traffic = AFF_Traffic::getTrafficBox( $filters );

		MH_Response( true, '', [ 'info' => $info, 'chart' => $chart, 'orders' => $orders, 'traffic' => $traffic ] );

		}

	public function aff_get_dashboard_info_2()
		{

		//Get General Info
		$info        = [
			'orders'              => 0,
			'completed_orders'    => 0,
			'income'              => 0,
			'approved_income'     => 0,
			'commission'          => 0,
			'approved_commission' => 0,

		];
		$front       = isset($_POST['f']) ? $_POST['f'] : '';
		$date_range  = isset($_POST['date_range']) ? $_POST['date_range'] : '';
		$view_mode   = isset($_POST['view_mode']) ? $_POST['view_mode'] : 'all';
		$user_id_tmp = isset($_POST['user_id']) ? $_POST['user_id'] : '';

		$user_id = get_current_user_id();

		if ( current_user_can( 'administrator' ) && $user_id_tmp ) {
			$user_id = $user_id_tmp;
			}

		$filters = [ 'date_range' => $date_range, 'user_id' => $user_id ];
		//  'level' => ['>', 0]
		if ( $view_mode == 'only_me' )
			$filters['level'] = 0;
		if ( $view_mode == 'collaborators' )
			$filters['level'] = [ '>', 0 ];


		$info = AFF_User_Order::getOrderStats( $filters ) +
			AFF_User_Order::getIncomeStats( $filters ) +
			AFF_User_Order::getCommissionStats( $filters );


		$orders = AFF_User_Order::getUserOrder( $filters );
		$orders = $orders ? $orders : [];


		MH_Response( true, '', [ 'info' => $info, 'orders' => $orders ] );

		}



	public function aff_get_products()
		{
		$filters = isset($_POST['data']) ? $_POST['data'] : '';
		if ( $filters ) {
			$result = AFF_Commission_Settings::searchWooProducts( $filters );
			MH_Response( true, '', $result, true, 1 );

			}
		}


	public function aff_get_commission_setting()
		{
		$type = isset($_POST['type']) ? $_POST['type'] : '';
		$data = AFF_Commission_Settings::get( $type );
		echo json_encode( [ 'data' => $data ] );
		die();
		}

	public function aff_save_commission_setting()
		{
		$this->checkPermissionAdmin();

		$type   = isset($_POST['type']) ? $_POST['type'] : '';
		$data   = isset($_POST['data']) ? $_POST['data'] : '';
		$result = AFF_Commission_Settings::save( $data, $type );
		MH_Response( true, 'Cập nhật thành công' );

		}

	public function aff_get_product_by_id()
		{
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		if ( $id ) {
			$result = AFF_Commission_Settings::getWooProductById( $id );
			if ( $result )
				MH_Response( true, '', $result );
			else
				MH_Response( false, 'Không tìm thấy sản phẩm này' );

			}
		die();
		}

	public function aff_get_product_category()
		{
		$type = isset($_POST['type']) ? $_POST['type'] : '';

		$args       = array(
			'taxonomy' => $type,
			'get'      => 'all'
		);
		$categories = get_categories( $args );
		$cat        = MH_BuildTreeCat( $categories );
		echo json_encode( [ 'data' => $cat ] );
		die();
		}


	public function aff_set_traffic()
		{
		$user_login = isset($_POST['user_login']) ? $_POST['user_login'] : '';
		$path       = isset($_POST['path']) ? $_POST['path'] : '';
		if ( !$user_login && !$path )
			return;
		$ref_value_is_id = AFF_Config::getConfig( 'ref_value_is_id' );

		if ( $ref_value_is_id == 'true' )
			$user = get_user_by( 'id', $user_login );
		else
			$user = get_user_by( 'login', $user_login );

		$date = date( 'Y-m-d' );
		if ( $user )
			AFF_Traffic::setView( $user->ID, $path, $date );
		die();
		}

	public function aff_add_banner()
		{
		$this->checkPermissionAdmin();
		$data = isset($_POST['data']) ? $_POST['data'] : '';
		MH_Response( true, 'Thêm mới banner thành công', AFF_Banner::add( $data ) );
		}

	public function aff_remove_banner()
		{
		$this->checkPermissionAdmin();
		$id = isset($_POST['id']) ? $_POST['id'] : '';
		MH_Response( true, 'Xóa banner thành công', AFF_Banner::remove( $id ) );
		}

	public function aff_get_banners()
		{
		MH_Response( true, '', AFF_Banner::get() );
		}

	public function aff_init_user_relationship()
		{
		AFF_User_Relationship::initRelationShip();
		MH_Response( true, 'Khởi tạo thành công' );

		}

	public function aff_register_user()
		{

		global $wpdb;
		// Verify nonce
		if ( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'vb_new_user' ) )
			die('Ooops, something went wrong, please try again later.');

		// Post values
		$username = isset($_POST['user']) ? sanitize_text_field( $_POST['user'] ) : '';
		$password = isset($_POST['pass']) ? sanitize_text_field( $_POST['pass'] ) : '';
		$email    = isset($_POST['mail']) ? sanitize_text_field( $_POST['mail'] ) : '';
		$name     = isset($_POST['name']) ? sanitize_text_field( $_POST['name'] ) : '';
		$phone    = isset($_POST['phone']) ? sanitize_text_field( $_POST['phone'] ) : '';
		$parent   = isset($_POST['parent']) ? sanitize_text_field( $_POST['parent'] ) : '';
		if ( !$username || !$password || !$email )
			die();

		$userdata = array(
			'user_login' => $username,
			'user_pass'  => $password,
			'user_email' => $email,
			'first_name' => $name,
			'nickname'   => $name,
			'role'       => 'subscriber'
		);


		$user_id = wp_insert_user( $userdata );
		add_user_meta( $user_id, 'phone', $phone, true );

		// Return
		if ( !is_wp_error( $user_id ) ) {

			$sql = "UPDATE " . $wpdb->prefix . "users SET user_phone = '$phone' WHERE id = $user_id";
			$wpdb->query( $sql );

			if ( $parent ) {
				$sql = "SELECT * from  " . $wpdb->prefix . "users WHERE user_login = '$parent'";

				$row = $wpdb->get_row( $sql );
				if ( $row ) {

					$sql = "UPDATE " . $wpdb->prefix . "users SET parent_id = $row->ID WHERE id = $user_id";
					$wpdb->query( $sql );
					AFF_User_Relationship::setRelationship( $user_id, $row->ID );
					}

				}
			MH_Response( true, 'Đăng kí tài khoản thành công' );

			} else
			MH_Response( false, $user_id->get_error_message() );
		die();


		}

	public function aff_lost_password()
		{
		$user_email = isset($_POST['user_email']) ? sanitize_text_field( $_POST['user_email'] ) : '';
		$user       = get_user_by( 'email', $user_email );
		if ( !$user )
			MH_Response( false, 'Không tìm thấy email này trên hệ thống' );

		retrieve_password( $user->user_login );
		MH_Response( true, 'Thành công, hãy kiểm tra lại Email của bạn.' );

		}

	public function aff_user_register_2()
		{

		$user_login   = isset($_POST['user_login']) ? sanitize_text_field( $_POST['user_login'] ) : '';
		$display_name = isset($_POST['display_name']) ? sanitize_text_field( $_POST['display_name'] ) : '';
		$user_phone   = isset($_POST['user_phone']) ? sanitize_text_field( $_POST['user_phone'] ) : '';
		$user_email   = isset($_POST['user_email']) ? sanitize_text_field( $_POST['user_email'] ) : '';
		$password     = isset($_POST['password']) ? sanitize_text_field( $_POST['password'] ) : '';
		$ref          = isset($_POST['ref']) ? sanitize_text_field( $_POST['ref'] ) : '';

		if ( !$user_login || !$display_name || !$user_phone || !$password )
			MH_Response( false, 'Vui lòng điền đầy đủ thông tin đăng kí' );

		$checkExist = MH_Query::init( null, 'users' )->where( 'user_phone', $user_phone )->first();
		if ( $checkExist )
			MH_Response( false, 'Số điện thoại này đã tồn tại trên hệ thống' );
		$checkExist = MH_Query::init( null, 'users' )->where( 'user_login', $user_login )->first();
		if ( $checkExist )
			MH_Response( false, 'Tên tài khoản đã tồn tại trên hệ thống' );
		$checkExist = MH_Query::init( null, 'users' )->where( 'user_email', "'$user_email'" )->first();
		if ( $checkExist )
			MH_Response( false, 'Email này đã tồn tại trên hệ thống' );

		$userdata = array(
			'user_login'   => $user_login,
			'user_pass'    => $password,
			'user_email'   => $user_email,
			'display_name' => $display_name,
			'role'         => 'subscriber'
		);
		$user_id  = wp_insert_user( $userdata );
		if ( !is_wp_error( $user_id ) ) {

			if ( AFF_Config::getConfig( 'aff_email' ) == 'true' )
				AFF_SendMail( get_option( 'admin_email' ), "Đăng kí cộng tác viên tài khoản {$user_login} - " . get_site_url(), "Thông báo tài khoản cộng tác viên vừa được tạo trên Website của bạn \n Tên đăng nhập: {$user_login} \n Tên hiển thị: {$display_name} \n Email: {$user_email}", [ 'replace' => 'replace' ] );

			MH_Query::init( null, 'users' )->where( 'ID', $user_id )->update( [ 'user_phone' => $user_phone ] );
			if ( $ref ) {

				$column   = AFF_Config::getConfig( 'ref_value_is_id' ) == 'true' ? 'ID' : 'user_login';
				$checkRef = MH_Query::init( null, 'users' )->where( $column, $ref )->first();

				if ( $checkRef ) {
					MH_Query::init( null, 'users' )->where( 'ID', $user_id )->update( [ 'parent_id' => $checkRef['ID'] ] );
					AFF_User_Relationship::setRelationship( $user_id, $checkRef['ID'] );
					}
				}

			MH_Response( true, 'Đăng kí tài khoản thành công' );
			} else
			MH_Response( false, $user_id->get_error_message() );
		}


	}