<?php

class AFF_Payment extends AFF_App
    {

    static $table = 'vuacode_payments';


    static function create($user_id, $data)
        {
        $settings = self::getSettings();
        $user     = AFF_User::getUserBy( [ 'column' => 'ID', 'value' => $user_id ], true );

        $data['amount'] = str_replace( ',', '', $data['amount'] );

        if ( !$user )
            return [ 'success' => false, 'msg' => 'Không tồn tại tài khoản người dùng' ];

        $aff_min_request = $settings['aff_min_request'];

        $data['amount'] = abs( $data['amount'] );
        if ( $data['amount'] < $aff_min_request )
            return [ 'success' => false, 'msg' => 'Số tiền rút tối thiểu phải lớn hơn' . number_format( $aff_min_request ) . 'đ' ];
        if ( $data['amount'] > $user['balance'] )
            return [ 'success' => false, 'msg' => 'Số tiền rút vượt quá số dư tài khoản' ];
        if ( !wp_check_password( $data['password'], $user['user_pass'], $user['ID'] ) )
            return [ 'success' => false, 'msg' => 'Mật khẩu không chính xác' ];

        if ( !$data['bank_info']['bank_name'] || !$data['bank_info']['bank_number'] || !$data['bank_info']['bank_owner'] ) {
            return [ 'success' => false, 'msg' => 'Yêu cầu điền đầy đủ thông tin ngân hàng' ];

            }

        $amount_format = number_format( $data['amount'] );
        $result        = AFF_User::changeBalance( $user['ID'], $data['amount'], 0, 0, "- $amount_format cho yêu cầu rút tiền của $user[user_login]" );

        if ( $result ) {
            $record = [
                'user_id'     => $user['ID'],
                'user_login'  => $user['user_login'],
                'amount'      => $data['amount'],
                'date'        => current_time( 'mysql' ),
                'bank_info'   => json_encode( $data['bank_info'] ),
                'description' => ''
            ];


            $result = MH_Query::init( null, self::$table )->insert( $record );
            if ( $result ) {
                if ( AFF_Config::getConfig( 'aff_email' ) == 'true' ) {
                    $mail_content = AFF_Config::getConfig( 'noti_email_creat_payment_request' );
                    $bank_info    = $data['bank_info']['bank_name'] . ' - ' . $data['bank_info']['bank_owner'] . ' - ' . $data['bank_info']['bank_number'];
                    AFF_SendMail( get_option( 'admin_email' ), 'Yêu cầu thanh toán từ ' . $user['user_login'], $mail_content, [
                        '[user_name]'       => $user['user_login'],
                        '[total]'           => $amount_format,
                        '[bank_infomation]' => $bank_info,
                    ] );
                    }
                return [ 'success' => true, 'msg' => 'Tạo lệnh rút tiền thành công' ];
                }

            }
        return [ 'success' => false, 'msg' => 'Có lỗi xảy ra' ];


        }

    static function getPayments($filters, $page = 1, $per_page = 15)
        {
        $query = MH_Query::init( null, self::$table );
        $query = self::buildQuery( $query, $filters )->order_by( 'id', 'DESC' );
        $data  = $query->page( $page, $per_page )->get( ARRAY_A, '', true );
        return [ 'data' => $data ? $data : [], 'pagination' => $pagination = $query->rows_found( $page, $per_page ) ];
        }



    static function approve($id, $status, $description = '')
        {
        $db = MH_Query::init( null, self::$table );
        $db->where( 'id', $id );
        $paymentRequest = $db->first();
        $data           = [
            'status'      => $status,
            'description' => $description
        ];
        $userInfo       = MH_Query::init( null, 'users' )->where( 'ID', $paymentRequest['user_id'] )->first();
        if ( $id && $paymentRequest && $userInfo ) {

            if ( $status == 1 ) {


                $result = MH_Query::init( null, self::$table )->where( 'id', $id )->update( $data );
                if ( $result ) {
                    if ( AFF_Config::getConfig( 'aff_email' ) == 'true' ) {
                        $mail_content      = AFF_Config::getConfig( 'noti_email_payment_request_completed' );
                        $data['bank_info'] = json_decode( $paymentRequest['bank_info'], true );
                        $bank_info         = $data['bank_info']['bank_name'] . ' - ' . $data['bank_info']['bank_owner'] . ' - ' . $data['bank_info']['bank_number'];

                        AFF_SendMail( $userInfo['user_email'], 'Rút tiền thành công ' . $userInfo['user_login'], $mail_content, [
                            '[user_name]'        => $userInfo['user_login'],
                            '[total]'            => number_format( $paymentRequest['amount'] ),
                            '[bank_information]' => $bank_info,
                        ] );
                        }
                    return [ 'success' => true, 'msg' => 'Cập nhật thành công' ];
                    }

                } elseif ( $status = 2 ) {


                if ( $userInfo ) {
                    $userData = [
                        'balance' => $userInfo['balance'] + $paymentRequest['amount']
                    ];
                    $result   = MH_Query::init( null, 'users' )->where( 'ID', $paymentRequest['user_id'] )->update( $userData );
                    if ( $result ) {
                        $amount_format = number_format( $paymentRequest['amount'] );
                        $note          = [
                            'user_id'       => $paymentRequest['user_id'],
                            'user_login'    => $paymentRequest['user_login'],
                            'amount'        => $paymentRequest['amount'],
                            'type'          => 1,
                            'begin_balance' => $userInfo['balance'],
                            'end_balance'   => $userData['balance'],
                            'description'   => "+ $amount_format hoàn trả - hủy lệnh thanh toán #$paymentRequest[id] của $userInfo[user_login]",
                        ];
                        AFF_History::create( $note );

                        }

                    }
                $result = MH_Query::init( null, self::$table )->where( 'id', $id )->update( $data );
                if ( $result )
                    return [ 'success' => true, 'msg' => 'Cập nhật thành công' ];
                } else {
                // status = 0
                $result = MH_Query::init( null, self::$table )->where( 'id', $id )->update( $data );
                if ( $result )
                    return [ 'success' => true, 'msg' => 'Cập nhật thành công' ];
                }

            }
        return false;
        }


    static function getUserBanks($user_id)
        {
        $result = MH_Query::init( 'null', self::$table )->where( 'user_id', $user_id )->group_by( 'bank_info' )->pluckOneColumnArray( 'bank_info' );
        if ( $result ) {
            foreach ( $result as &$r ) {
                $r = json_decode( $r, true );
                }
            }
        return $result;

        }

    static function buildQuery($query, $filters)
        {

        if ( isset($filters['user_id']) && $filters['user_id'] ) {
            $query = $query->where( 'user_id', $filters['user_id'] );
            }

        if ( isset($filters['status']) && $filters['status'] !== '' ) {
            $query = $query->where( 'status', $filters['status'] );
            }

        if ( isset($filters['search']) && $filters['search'] ) {
            $query = $query->search( $filters['search'], [ 'user_id', 'user_login' ], 'OR' );
            }

        return $query;
        }


    }

?>
