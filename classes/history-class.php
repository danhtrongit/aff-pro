<?php

class AFF_History
    {

    static $table = 'vuacode_history';

    //Type 0: Down, 1: Up
    // $data = [
    //     'user_id' => '',
    //     'amount' => '',
    //     'description' => '',
    //     'end_balance' => '',
    //     'begin_balance' => '',
    //     'date' => '',
    // ];
    static function create($data)
        {
        $data['date'] = current_time( 'mysql' );
        return MH_Query::init( null, self::$table )->insert( $data );
        }

    static function getBalanceHistory($filters, $page = 1, $per_page = 15)
        {
        $query = MH_Query::init( null, self::$table );
        $query = self::buildQuery( $query, $filters )->order_by( 'id', 'DESC' );
        $data  = $query->page( $page, $per_page )->get( ARRAY_A, '', true );
        return [ 'data' => $data ? $data : [], 'pagination' => $pagination = $query->rows_found( $page, $per_page ) ];
        }


    static function buildQuery($query, $filters)
        {

        if ( isset($filters['user_id']) && $filters['user_id'] ) {
            $query = $query->where( 'user_id', $filters['user_id'] );
            }
        if ( isset($filters['date_range']) && $filters['date_range'][0] ) {
            $date_range = array_unique( $filters['date_range'] );
            if ( sizeof( $date_range ) == 1 )
                $query = $query->where( 'DATE(date)', $date_range[0] );
            else
                $query = $query->whereRaw( "(date BETWEEN '$date_range[0]' AND '$date_range[1]')" );
            }

        if ( isset($filters['search']) && $filters['search'] ) {
            $query = $query->search( $filters['search'], [ 'user_id', 'user_login' ], 'OR' );
            }

        return $query;
        }



    }

// debug( AFF_History::getBalanceHistory([]));
?>
