<?php

class AFF_User_Order extends AFF_App
    {

    static $table = 'vuacode_user_order';


    static function approveCommission($order_id, $status)
        {
        $settings = self::getSettings();
        $rows     = MH_Query::init( null, self::$table )->where( 'order_id', $order_id )->where( 'status', 0 )->get();
        if ( !$rows )
            return;

        foreach ( $rows as $key => $row ) {
            $user = AFF_User::getUserBy( [ 'column' => 'ID', 'value' => $row['user_id'] ] );
            if ( !$user )
                return;

            // if($user['user_phone']){
            //     $data = [
            //      'phone' => $user['user_phone'],
            //      'template_id' => '248856',
            //      'data__order_code' => '#'. $row['order_id'],
            //      'data__hoahong' => $row['commission'],
            //      'data__giatridonhang' => $row['total'],
            //      'data__customer_name' => $user['display_name'],
            //      'data__ghichuhoahong' => $row['description'],

            //     ];
            //     ZNSZALO::sendMessage($data);
            // }

            //SETTING INCLUDE COMMISSION OF CHILDREN TO PARENT
            if ( $row['level'] == 0 ) {

                //Increase Level when user reach income
                $new_income             = $user['income'] + $row['total'];
                $commission_user_levels = $settings['commission_user_levels'];
                $user_level             = $settings['user_level'];

                $level_index = null;
                foreach ( $commission_user_levels as $key => $level ) {
                    if ( $level['income'] == '' )
                        continue;

                    if ( $key + 1 > $user_level )
                        break;

                    if ( $new_income >= $level['income'] )
                        $level_index = $key;
                    }

                if ( $level_index != null && $level_index > $user['level'] ) {
                    $data = [
                        'level' => $level_index
                    ];
                    MH_Query::init( null, 'users' )->where( 'ID', $user['ID'] )->update( $data );
                    }




                $result = AFF_User::changeBalance( $user['ID'], $row['commission'], 1, $row['total'], $row['description'], $row['order_id'] );
                } else if ( $settings['income_include_child'] )
                $result = AFF_User::changeBalance( $user['ID'], $row['commission'], 1, $row['total'], $row['description'], $row['order_id'] );
            else
                $result = AFF_User::changeBalance( $user['ID'], $row['commission'], 1, 0, $row['description'], $row['order_id'] );



            if ( $row['level'] == 0 && AFF_Config::getConfig( 'aff_email' ) == 'true' ) {
                $mail_content = AFF_Config::getConfig( 'noti_email_order_completed' );
                AFF_SendMail( $user['user_email'], 'Đơn hàng thành công ' . $user['user_login'], $mail_content, [
                    '[user_name]'   => $user['user_login'],
                    '[order_id]'    => $order_id,
                    '[order_total]' => number_format( $row['total'] ),
                    '[commission]'  => number_format( $row['commission'] ),
                ] );
                }

            MH_Query::init( null, self::$table )->where( 'id', $row['id'] )->update( [ 'status' => 1 ] );
            }
        return;
        }

    static function refundCommission($order_id, $status)
        {
        $settings = self::getSettings();
        $rows     = MH_Query::init( null, self::$table )->where( 'order_id', $order_id )->where( 'status', 1 )->get();
        if ( !$rows || (isset($settings['aff_refund_commission']) && !$settings['aff_refund_commission']) )
            return;
        foreach ( $rows as $key => $row ) {
            $user = AFF_User::getUserBy( [ 'column' => 'ID', 'value' => $row['user_id'] ] );
            if ( !$user )
                return;
            if ( $row['status'] == 1 ) {
                if ( $row['level'] == 0 )
                    $result = AFF_User::changeBalance( $user['ID'], $row['commission'], 0, (-1 * $row['total']), 'Trừ tiền cho đơn đơn hàng hoàn #' . $row['order_id'] );
                else {
                    if ( $settings['income_include_child'] == 'true' )
                        $result = AFF_User::changeBalance( $user['ID'], $row['commission'], 0, (-1 * $row['total']), 'Trừ tiền cho đơn đơn hàng hoàn #' . $row['order_id'] );
                    else
                        $result = AFF_User::changeBalance( $user['ID'], $row['commission'], 0, 0, 'Trừ tiền cho đơn đơn hàng hoàn #' . $row['order_id'] );


                    }

                }


            }
        MH_Query::init( null, self::$table )->where( 'order_id', $order_id )->delete();
        }

    static function create($user, $order, $ref_path, $ref_product, $ref_coupon)
        {
        $settings = self::getSettings();

        if ( $settings['aff_mode'] == 'order_mode' )
            $data = self::setCommissionOrderMode( $user, $order, $ref_coupon );

        if ( $settings['aff_mode'] == 'product_mode' )
            $data = self::setCommissionProductMode( $user, $order, $ref_coupon );


        $order_id = $order->get_id();
        $products = [];
        $items    = $order->get_items();
        foreach ( $items as $item ) {
            $id    = $item->get_product_id();
            $image = get_the_post_thumbnail_url( $id, 'thumbnail' );

            $products[] = [
                'name'                 => $item->get_name(),
                'product_id'           => $id,
                'product_variation_id' => $item->get_variation_id(),
                'quantity'             => $item->get_quantity(),
                'total'                => $item->get_total(),
                'image'                => $image ? $image : 'https://thumbs.dreamstime.com/b/no-image-available-icon-vector-illustration-flat-design-140476186.jpg',
                'link'                 => get_permalink( $id ),
            ];
            }

        $order_json = [
            'order_id'         => $order_id,
            'total'            => $order->get_total(),
            'customer_name'    => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'customer_phone'   => $order->get_billing_phone(),
            'customer_address' => $order->get_billing_address_1(),
            'products'         => $products,
            'shipping_fee'     => intval( $order->get_shipping_total() ),
            'sub_total'        => $order->get_subtotal(),
        ];


        $record = [
            'user_id'        => $user->ID,
            'user_ref'       => $user->user_login,
            'user_login'     => $user->user_login,
            'order_id'       => $order_id,
            'order_status'   => $order->get_status(),
            'ref_path'       => $ref_path,
            'ref_product'    => $ref_product ? $ref_product : NULL,
            'ref_coupon'     => isset($coupon) ? $ref_coupon . '-' . $coupon['value'] : NULL,
            'status'         => 0,
            'date'           => current_time( 'mysql' ),
            'total'          => $order->get_total(),
            'commission'     => $data['commission'],
            'customer_name'  => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'customer_phone' => $order->get_billing_phone(),
            'description'    => '+' . number_format( $data['commission'] ) . ' Hoa hồng của ID đơn hàng ' . $order_id . ' cho cộng tác viên ' . $user->user_login,
            'order_json'     => json_encode( $order_json )

        ];

        $exist = MH_Query::init( null, self::$table )->where( 'user_id', $user->ID )->where( 'order_id', $order_id )->first();

        if ( !$exist ) {
            MH_Query::init( null, self::$table )->insert( $record );
            self::setCommissionAncestors( $user, $order, $data['commission'], $order_json );

            if ( AFF_Config::getConfig( 'aff_email' ) == 'true' ) {
                $mail_content = AFF_Config::getConfig( 'noti_new_order' );
                AFF_SendMail( $user->user_email, 'Thông báo bạn vừa giới thiệu được đơn hàng mới.', $mail_content, [
                    '[user_name]'   => $user->user_login,
                    '[order_id]'    => $order_id,
                    '[order_total]' => wc_price( $order->get_total() ),
                    '[commission]'  => wc_price( $data['commission'] ),
                ] );
                }
            }

        }


    static function setCommissionProductMode($user, $order, $ref_coupon)
        {
        $settings         = self::getSettings();
        $commission       = 0;
        $commission_level = 0;

        $commission_percent_default = $settings['commission_percent_default'];
        $user_level                 = $settings['user_level'];
        if ( $user_level > 0 ) {
            $commission_user_levels = $settings['commission_user_levels'];
            $commission_level       = $commission_user_levels[$user->level]['commission'] > 0 ? $commission_user_levels[$user->level]['commission'] : 0;

            }

        // Apply Coupon
        if ( $ref_coupon )
            $coupon = MH_Query::init( null, 'vuacode_coupons' )->where( 'coupon', $ref_coupon )->first();

        $fees              = $order->get_fees();
        $fee_total         = 0;
        $fee_sub_per_items = 0;

        if ( $fees ) {
            foreach ( $fees as $key => $fee ) {
                $fee_total += $fee->get_total();
                }
            $allquantity = 0;
            foreach ( $order->get_items() as $item_key => $item_values ) {
                $allquantity += $item_values->get_quantity();
                }
            if ( $fee_total < 0 )
                $fee_sub_per_items = floor( abs( $fee_total ) / $allquantity );
            }




        foreach ( $order->get_items() as $item_key => $item_values ) {
            $quantity   = $item_values->get_quantity();
            $item_data  = $item_values->get_data();
            $line_total = $item_data['total'];

            //Get Commission Setting By Product ID
            $id                 = isset($item_data['variation_id']) && $item_data['variation_id'] ? $item_data['variation_id'] : $item_data['product_id'];
            $commission_setting = AFF_Commission_Settings::getCommissionSettingById( $id );
            if ( $commission_setting )
                $commission_percent = $commission_setting + $commission_level;
            else
                $commission_percent = $commission_percent_default + $commission_level;

            if ( $user->commission_percent && $user->commission_percent > 0 )
                $commission_percent = $user->commission_percent;

            if ( $commission_setting === '0' )
                $commission_percent = 0;

            if ( !$commission_setting && $commission_percent_default == 0 )
                $commission_percent = 0;

            if ( isset($coupon) ) {
                $line_total         = $line_total * 100 / (100 - $coupon['value']);
                $commission_percent = $commission_percent - $coupon['value'];
                $commission_percent = $commission_percent < 0 ? 0 : $commission_percent;
                }



            if ( $fee_sub_per_items )
                $line_total = $line_total - $quantity * $fee_sub_per_items;


            $commission += ceil( ($line_total / 100) * $commission_percent );

            }


        return [
            'commission'         => $commission,
            'commission_percent' => $commission_percent,
        ];

        }

    static function setCommissionOrderMode($user, $order, $ref_coupon)
        {
        $settings = self::getSettings();
        if ( $user->commission_percent )
            $commission_percent = $user->commission_percent; // If User have certain commission percent then Omit all.
        else {
            $commission_percent_default = $settings['commission_percent_default'];
            $commission_level           = 0;

            //User Level
            $user_level = $settings['user_level'];
            if ( $user_level > 0 ) {
                $commission_user_levels = $settings['commission_user_levels'];
                $commission_level       = $commission_user_levels[$user->level]['commission'] > 0 ? $commission_user_levels[$user->level]['commission'] : 0;


                }
            }
        // Order Mode - Include Shipping Fee And Tax
        $total = $order->get_total();
        if ( $settings['aff_commission_include_order_shipping'] == 'false' )
            $total = $order->get_total() - $order->get_total_tax() - $order->get_total_shipping() - $order->get_shipping_tax();


        //Final Commission    
        $commission_percent = $commission_percent_default + $commission_level;

        //Apply Coupon
        if ( $ref_coupon ) {
            $coupon = MH_Query::init( null, 'vuacode_coupons' )->where( 'coupon', $ref_coupon )->first();
            if ( $coupon ) {
                $total              = $total * 100 / (100 - $coupon['value']);
                $commission_percent = $commission_percent - $coupon['value'];
                $commission_percent = $commission_percent < 0 ? 0 : $commission_percent;

                }
            }

        if ( $user->commission_percent && $user->commission_percent > 0 )
            $commission_percent = $user->commission_percent;

        $commission = ceil( ($total / 100) * $commission_percent );

        return [
            'commission'         => $commission,
            'commission_percent' => $commission_percent,
        ];

        }



    static function setCommissionAncestors($user, $order, $commission, $order_json)
        {
        $settings                       = self::getSettings();
        $commission_relationship_levels = $settings['commission_relationship_levels'];

        if ( $settings['relationship_level'] ) {
            $ancestors = AFF_User_Relationship::getAncestor( $user->ID, $settings['relationship_level'] );
            if ( $ancestors ) {

                $order_id       = $order->get_id();
                $order_total    = $order->get_total();
                $cutomer_name   = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                $customer_phone = $order->get_billing_phone();

                // debug($commission_relationship_levels);
                foreach ( $ancestors as $key => $an ) {
                    if ( $an['distance'] == 0 )
                        continue;

                    // if(self::getIncomeInMonth($an['ancestor_id']) < 1000000){
                    //     continue;
                    // }

                    if ( isset($commission_relationship_levels[$an['distance'] - 1]['commission']) ) {

                        $commission_percent = floatval( $commission_relationship_levels[$an['distance'] - 1]['commission'] );


                        //Mode
                        if ( $settings['commission_relationship_mode'] == 'commission' )
                            $commission_level = $commission / 100 * $commission_percent;
                        else {
                            $total = $order->get_total();
                            if ( $settings['aff_commission_include_order_shipping'] == 'false' )
                                $total = $order->get_total() - $order->get_total_tax() - $order->get_total_shipping() - $order->get_shipping_tax();

                            $commission_level = $total / 100 * $commission_percent;
                            }


                        $parent_user = get_user_by( 'id', $an['ancestor_id'] );

                        if ( $parent_user && $parent_user->is_vip == 0 ) {
                            $data = [
                                'user_id'        => $an['ancestor_id'],
                                'user_ref'       => $user->user_login,
                                'user_login'     => $parent_user->user_login,
                                'order_id'       => $order->get_id(),
                                'order_status'   => $order->get_status(),
                                'ref_path'       => NULL,
                                'ref_product'    => NULL,
                                'ref_coupon'     => NULL,
                                'status'         => 0,
                                'date'           => current_time( 'mysql' ),
                                'total'          => $order_total,
                                'level'          => $an['distance'],
                                'commission'     => $commission_level,
                                'customer_name'  => $cutomer_name,
                                'customer_phone' => $customer_phone,
                                'order_json'     => json_encode( $order_json ),
                                'description'    => '+' . number_format( $commission_level ) . ' Hoa hồng của ID đơn hàng ' . $order_id . ' cho tài khoản ' . $parent_user->user_login . ' từ cộng tác viên cấp ' . $an['distance'] . ' ' . $user->user_login


                            ];
                            MH_Query::init( null, self::$table )->insert( $data );
                            }

                        }
                    }
                }



            // Thiết lập % hoa hồng đa cấp user_vip
            $ancestors = MH_Query::init( null, 'vuacode_user_relationships' )->select( 'ancestor_id, distance' )->where( 'descendant_id', $user->ID )->where( 'distance', '>=', 1 )->where( 'distance', '<=', 999 )->order_by( 'distance', 'ASC' )->get();
            if ( $ancestors ) {
                foreach ( $ancestors as $key => $an ) {
                    if ( $an['distance'] == 0 )
                        continue;
                    $parent_user = get_user_by( 'id', $an['ancestor_id'] );
                    if ( $parent_user->is_vip == 1 ) {
                        $data = [
                            'user_id'        => $an['ancestor_id'],
                            'user_ref'       => $user->user_login,
                            'user_login'     => $parent_user->user_login,
                            'order_id'       => $order->get_id(),
                            'order_status'   => $order->get_status(),
                            'ref_path'       => NULL,
                            'ref_product'    => NULL,
                            'ref_coupon'     => NULL,
                            'status'         => 0,
                            'date'           => current_time( 'mysql' ),
                            'total'          => $order_total,
                            'level'          => $an['distance'],
                            'commission'     => 0,
                            'customer_name'  => $cutomer_name,
                            'customer_phone' => $customer_phone,
                            'order_json'     => json_encode( $order_json ),
                            'description'    => '+' . number_format( $commission_level ) . ' Hoa hồng của ID đơn hàng ' . $order_id . ' cho tài khoản ' . $parent_user->user_login . ' từ cộng tác viên cấp ' . $an['distance'] . ' ' . $user->user_login


                        ];
                        MH_Query::init( null, self::$table )->insert( $data );
                        break;
                        }


                    }
                }


            }
        }


    static function getOrderStats($filters)
        {
        $data = [
            'orders'           => 0,
            'completed_orders' => 0,
        ];

        $query  = MH_Query::init( null, self::$table )->select( 'status, count(id) as total' );
        $query  = self::buildQuery( $query, $filters );
        $result = $query->group_by( 'status' )->get();
        if ( $result ) {
            foreach ( $result as $r ) {
                if ( $r['status'] == 1 )
                    $data['completed_orders'] = intval( $r['total'] );

                $data['orders'] += $r['total'];

                }
            }
        return $data;
        }

    static function getIncomeStats($filters)
        {
        $data = [
            'income'          => 0,
            'approved_income' => 0,
        ];

        $query = MH_Query::init( null, self::$table )->select( 'status, total, order_status' );
        $query = self::buildQuery( $query, $filters );

        $result = $query->get();
        if ( $result ) {
            foreach ( $result as $r ) {
                if ( $r['status'] == 1 )
                    $data['approved_income'] += intval( $r['total'] );
                if ( $r['order_status'] != 'refunded' && $r['order_status'] != 'failed' && $r['order_status'] != 'cancelled' )
                    $data['income'] += intval( $r['total'] );
                }
            }
        return $data;
        }

    static function getCommissionStats($filters)
        {
        $data = [
            'commission'          => 0,
            'approved_commission' => 0,
        ];

        $query  = MH_Query::init( null, self::$table )->select( 'status, commission, order_status' );
        $query  = self::buildQuery( $query, $filters );
        $result = $query->get();
        if ( $result ) {
            foreach ( $result as $r ) {
                if ( $r['status'] == 1 )
                    $data['approved_commission'] += intval( $r['commission'] );

                if ( $r['order_status'] != 'refunded' && $r['order_status'] != 'failed' && $r['order_status'] != 'cancelled' )
                    $data['commission'] += $r['commission'];

                }
            }
        return $data;
        }

    static function getUserOrder($filters)
        {
        $query = MH_Query::init( null, self::$table );
        $query = self::buildQuery( $query, $filters );
        return $query->order_by( 'id', 'DESC' )->get();
        }

    static function getUserOrderHistory($filters, $page = 1, $per_page = 15)
        {
        $query = MH_Query::init( null, self::$table );
        $query = self::buildQuery( $query, $filters )->order_by( 'id', 'DESC' );
        $data  = $query->page( $page, $per_page )->get( ARRAY_A, '', true );
        return [ 'data' => $data ? $data : [], 'pagination' => $pagination = $query->rows_found( $page, $per_page ) ];
        }



    static function getOrderStatByDay($filters)
        {
        $date_range = MH_Date_Array( $filters['date_range'][0], $filters['date_range'][1] );


        $query         = MH_Query::init( null, self::$table )->select( 'DATE_FORMAT(date, "%d-%m-%Y")  as date, count(id) as total' )->where( 'level', 0 );
        $query         = self::buildQuery( $query, $filters );
        $result_orders = $query->group_by( 'DATE_FORMAT(date, "%d-%m-%Y")' )->order_by( 'date' )->get();

        $query                   = MH_Query::init( null, self::$table )->select( 'DATE_FORMAT(date, "%d-%m-%Y")  as date, count(id) as total' )->where( 'status', 1 )->where( 'level', 0 );
        $query                   = self::buildQuery( $query, $filters );
        $result_completed_orders = $query->group_by( 'DATE_FORMAT(date, "%d-%m-%Y")' )->order_by( 'date' )->get();

        $query = MH_Query::init( null, 'vuacode_traffics' )->select( 'DATE_FORMAT(date, "%d-%m-%Y") as date,sum(total) as total' );
        unset( $filters['level'] );
        $query = self::buildQuery( $query, $filters );

        $result_views = $query->group_by( 'date' )->order_by( 'date' )->get();

        $chart_adapter = [
            'orders'           => &$result_orders,
            'completed_orders' => &$result_completed_orders,
            'views'            => &$result_views
        ];
        $chart_stats   = [];
        $data          = [];

        foreach ( $date_range as $k1 => $date ) {
            foreach ( $chart_adapter as $k2 => &$c ) {
                $chart_stats[$k2][$date] = 0;
                }
            }
        foreach ( $chart_adapter as $k2 => &$c ) {
            foreach ( $c as $k3 => $t ) {
                $formatDate                    = explode( '-', $t['date'] );
                $formatDate                    = $formatDate[0] . '-' . $formatDate[1];
                $chart_stats[$k2][$formatDate] = intval( $t['total'] );
                }
            }
        // return $chart_stats;


        foreach ( $chart_stats as $k1 => &$c ) {
            foreach ( $c as $key => $t ) {
                $data[$k1][] = $t;
                }
            }


        $data['date_range'] = $date_range;
        return $data;




        }

    static function getUserIncome($filters, $page = 1, $per_page = 15)
        {
        $query = MH_Query::init( null, self::$table );

        if ( isset($filters['date_range']) && $filters['date_range'][0] ) {
            $date_range = array_unique( $filters['date_range'] );
            if ( sizeof( $date_range ) == 1 )
                $query = $query->where( 'DATE(date)', $date_range[0] );
            else
                $query = $query->whereRaw( "(date BETWEEN '$date_range[0]' AND '$date_range[1] 23:59:59')" );
            }

        $data = $query->select( 'user_id, user_login, sum(total) as total, SUM(CASE WHEN level = 0 THEN total ELSE 0 END) as total_directly, SUM(CASE WHEN level > 0 THEN total ELSE 0 END) as total_descendants, sum(commission) as commission' )->group_by( 'user_id' )->page( $page, $per_page )->get( ARRAY_A, '', true );

        if ( $data ) {
            foreach ( $data as $key => &$d ) {
                $d['user'] = AFF_User::getUserBy( [ 'column' => 'ID', 'value' => $d['user_id'] ] );
                }
            }

        return [ 'data' => $data ? $data : [], 'pagination' => $pagination = $query->rows_found( $page, $per_page ) ];
        }


    static function updateOrderStatus($order_id, $status)
        {
        MH_Query::init( null, self::$table )->where( 'order_id', $order_id )->update( [ 'order_status' => $status ] );
        }

    static function buildQuery($query, $filters)
        {
        if ( isset($filters['status']) ) {
            $query = $query->where( 'status', $filters['status'] );
            }
        if ( isset($filters['user_id']) && $filters['user_id'] ) {
            $query = $query->where( 'user_id', $filters['user_id'] );
            }
        if ( isset($filters['level']) ) {
            if ( is_array( $filters['level'] ) )
                $query = $query->where( 'level', $filters['level'][0], $filters['level'][1] );
            else
                $query = $query->where( 'level', $filters['level'] );
            }
        if ( isset($filters['date_range']) && $filters['date_range'][0] ) {
            $date_range = array_unique( $filters['date_range'] );
            if ( sizeof( $date_range ) == 1 )
                $query = $query->where( 'DATE(date)', $date_range[0] );
            else
                $query = $query->whereRaw( "(date BETWEEN '$date_range[0]' AND '$date_range[1] 23:59:59')" );
            }

        if ( isset($filters['search']) && $filters['search'] ) {
            $query = $query->search( $filters['search'], [ 'order_id', 'user_login' ], 'OR' );
            }

        return $query;
        }


    static function getRankOrder($atts)
        {
        $atts['range'] = strtoupper( $atts['range'] );
        $select        = $atts['type'] == 'order' ? 'user_id, count(id) as total, user_login' : 'user_id, sum(total) as total, user_login';
        $result        = MH_Query::init( null, self::$table )->select( $select )->where( 'level', 0 )->where( 'status', 1 )->where( 'order_status', 'completed' )->whereRaw( "{$atts['range']}(date) = {$atts['range']}(CURRENT_DATE())" )->group_by( 'user_id' )->order_by( 'total', 'DESC' )->limit( $atts['limit'] )->get();
        return $result;
        }


    static function moveOrderToTrash($order_id)
        {
        $order = MH_Query::init( null, self::$table )->where( 'order_id', $order_id )->where( 'order_status', '!=', 'completed' )->first();
        if ( $order )
            MH_Query::init( null, self::$table )->where( 'order_id', $order_id )->update( [ 'order_status' => 'cancelled' ] );

        }


    static function getIncomeInMonth($user_id)
        {
        $data = MH_Query::init( null, self::$table )->select( 'SUM(total) as total' )->where( 'user_id', $user_id )->where( 'level', 0 )->where( 'status', 1 )->where( 'order_status', 'completed' )->whereRaw( "MONTH(date) = MONTH(CURRENT_DATE())" )->value();
        return $data ? $data : 0;
        }




    }
// AFF_User_Order::moveOrderToTrash(506);

?>
