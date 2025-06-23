<?php

class AFF_User extends AFF_App
    {

    static $table = 'users';



    static function getList($filters = [], $page = 1, $per_page = 15)
        {
        $query = MH_Query::init( null, self::$table );


        if ( isset($filters['search']) && $filters['search'] )
            $query = $query->search( $filters['search'], [ 'ID', 'user_login', 'user_email', 'user_phone' ] );
        if ( isset($filters['aff_active']) && $filters['aff_active'] !== '' )
            $query = $query->where( 'aff_active', $filters['aff_active'] );

        $data       = $query->page( $page, $per_page )->get( ARRAY_A, '', true );
        $pagination = $query->rows_found( $page, $per_page );
        if ( $data ) {
            foreach ( $data as $key => &$d ) {
                $parent            = self::getUserBy( [ 'column' => 'ID', 'value' => $d['parent_id'] ] );
                $d['parent_login'] = $parent ? $parent['user_login'] : '';

                }
            }
        return [ 'data' => $data ? $data : [], 'pagination' => $pagination ];
        }

    // static function getUserTree($filters = [], $page = 1, $per_page = 15){
    //     $settings = self::getSettings();
    //     $relationship_level = $settings['relationship_level'];
    //     $descendants = AFF_User_Relationship::getDescendants($filters['user_id'], $relationship_level);

    //     unset($filters['user_id']);
    //     if(sizeof($descendants) == 1 || sizeof($descendants) == 0)
    //     return [];
    //     array_shift($descendants);

    //     $descendants_ids = [];
    //     $level_map = [];
    //     foreach ($descendants as $key => $d) {
    //         $descendants_ids[] = $d['descendant_id'];
    //         $level_map[$d['descendant_id']] = $d['distance']; 
    //     }

    //     $filters = [
    //         'id_in' => $descendants_ids
    //     ];
    // 	$query = MH_Query::init(null, self::$table);
    //     $query = self::buildQuery($query, $filters);
    //     $data = $query->page($page, $per_page)->get(ARRAY_A, '', true);

    //     if($data){
    //         foreach ($data as $key => &$d) {
    //             $d['level_2'] = $level_map[$d['ID']];
    //         }
    //     }
    //     return ['data' => $data ? $data : [], 'pagination' => $pagination = $query->rows_found($page, $per_page)];
    // }


    static function getUserTree($filters = [], $page = 1, $per_page = 15)
        {
        $settings           = self::getSettings();
        $relationship_level = $settings['relationship_level'];
        $query              = MH_Query::init( null, self::$table . " as t1" )->innerJoin( 'affpro_user_relationships as t2', 't1.ID', '=', 't2.descendant_id' );
        $query              = $query->where( 't2.ancestor_id', $filters['user_id'] )->where( 't2.distance', '>', 0 )->where( 't2.distance', '<=', $relationship_level );

        if ( isset($filters['search']) && $filters['search'] ) {
            $query = $query->search( $filters['search'], [ 't1.user_login', 't1.user_email' ] );
            }
        $query = $query->order_by( 't2.distance' );
        $data  = $query->page( $page, $per_page )->get( ARRAY_A, '', true );
        if ( $data ) {
            foreach ( $data as &$d ) {
                unset( $d['user_pass'] );
                }
            }

        return [ 'data' => $data ? $data : [], 'pagination' => $pagination = $query->rows_found( $page, $per_page ) ];
        }




    static function getUserTree2($filters = [])
        {
        $settings           = self::getSettings();
        $relationship_level = $settings['relationship_level'];
        $query              = MH_Query::init( null, self::$table . " as t1" )->select( 't1.user_login as label, t1.parent_id, t1.ID, t1.user_login, t2.distance' )->innerJoin( 'affpro_user_relationships as t2', 't1.ID', '=', 't2.descendant_id' );
        // $query = $query->where('t2.ancestor_id', $filters['user_id'])->where('t2.distance', '>', 0)->where('t2.distance', '<=', $relationship_level);
        $query = $query->where( 't2.ancestor_id', $filters['user_id'] )->where( 't2.distance', '<=', $relationship_level );
        $query = $query->order_by( 't2.distance' );
        $data  = $query->get();
        foreach ( $data as $key => &$d ) {
            $d['expand'] = true;
            }
        $user      = self::getUserBy( [ 'column' => 'ID', 'value' => $filters['user_id'] ] );
        $parent_id = $user['parent_id'] ? $user['parent_id'] : 0;

        $data = MH_BuildTree( $data, $parent_id, 'parent_id', 'ID' );
        return $data;

        }

    static function getUserBy($w, $all_fields = false)
        {
        $fields = $all_fields ? '*' : 'ID, user_login, user_nicename, user_email, user_registered, display_name, user_phone, level, balance, income, commission_percent, aff_active, parent_id';
        $db     = MH_Query::init( null, self::$table )->select( $fields );
        return $db->where( $w['column'], $w['value'] )->first();
        }


    static function update($data)
        {
        if ( isset($data['ID']) ) {

            $user = MH_Query::init( null, 'users' )->where( 'ID', $data['ID'] )->first();
            if ( !$user )
                return false;

            $result = MH_Query::init( null, 'users' )->where( 'ID', $data['ID'] )->update( $data );
            if ( !$result )
                return false;

            return true;
            }
        }

    static function changeBalance($user_id, $amount, $type, $income = 0, $description = '', $order_id = '')
        {
        $user = self::getUserBy( [ 'column' => 'ID', 'value' => $user_id ] );
        if ( $user ) {
            $begin_balance = $user['balance'];
            $end_balance   = $type == 1 ? $begin_balance + $amount : $begin_balance - $amount;
            $data          = [
                'balance' => $end_balance,
                'income'  => $user['income'] + $income,
            ];
            $result        = MH_Query::init( null, self::$table )->where( 'ID', $user_id )->update( $data );
            if ( $result && $amount ) {

                $note = [
                    'user_id'       => $user_id,
                    'user_login'    => $user['user_login'],
                    'amount'        => $amount,
                    'type'          => $type,
                    'end_balance'   => $end_balance,
                    'begin_balance' => $begin_balance,
                    'description'   => $description,
                    'order_id'      => $order_id
                ];
                AFF_History::create( $note );
                return true;
                }

            }
        return false;
        }

    static function bonusRegister($user_id)
        {
        $bonus_register = AFF_Config::getConfig( 'bonus_register' );
        $dates          = AFF_Config::getConfig( 'bonus_register_date_range' );
        if ( $bonus_register ) {
            if ( $dates ) {
                $dates              = json_decode( $dates );
                $startDateTimestamp = strtotime( $dates[0] );
                $endDateTimestamp   = strtotime( $dates[1] );
                $todayTimestamp     = strtotime( 'today' );

                if ( $todayTimestamp >= $startDateTimestamp && $todayTimestamp <= $endDateTimestamp ) {

                    } else
                    return;
                }
            $flag = get_user_meta( $user_id, 'bonus_register', true );
            if ( !$flag ) {
                self::changeBalance( $user_id, $bonus_register, 1, 0, 'Thưởng đăng kí tài khoản cộng tác viên' );
                add_user_meta( $user_id, 'bonus_register', 1, true );
                }
            }
        }

    public function activeUser($id, $aff_active)
        {
        // $db = MH_Query::init(null, self::$table);
        // $data = [
        //     'aff_active' => $aff_active
        // ];
        // if($aff_active == 1)
        // {   
        //     $userInfo = get_user_by('id', $id);
        //     // $configModel = new ConfigModel();
        //     // $mail_content = $configModel->getConfig('aff_email_user_actived');

        //     // sendMail_( $userInfo['user_email'], 'Tài khoản đã được kích hoạt', $mail_content, [
        //     //     '{{user_name}}' => $userInfo[display_name],

        //     // ]);
        // }
        // $db->where ('ID', $id);

        // return $db->update ($this->prefix .'_users', $data);

        }

    static function buildQuery($query, $filters)
        {

        if ( isset($filters['ID']) && $filters['ID'] ) {
            $query = $query->where( 'ID', $filters['ID'] );
            }

        if ( isset($filters['id_in']) && $filters['id_in'] ) {
            $query = $query->whereIn( 'ID', $filters['id_in'] );
            }

        if ( isset($filters['search']) && $filters['search'] ) {
            $query = $query->search( $filters['search'], [ 'user_id', 'user_login', 'user_email', 'user_phone' ], 'OR' );
            }

        return $query;
        }




    }

?>
