<?php

class AFF_Traffic extends AFF_App
    {

    static $table = 'vuacode_traffics';


    static function setView($userId, $url, $date)
        {
        $db     = MH_Query::init( null, self::$table )
            ->where( "user_id", $userId )
            ->whereRaw( "url = '$url'" )
            ->where( "date", $date );
        $record = $db->first();

        if ( $record ) {
            $data = [
                'total' => ++$record['total']
            ];
            $db->where( 'id', $record['id'] );
            $db->update( $data );

            } else {
            $product_slug = explode( '/', $url );
            $product      = self::get_product_by_slug( $product_slug[count( $product_slug ) - 2] );
            $data         = [
                'user_id' => $userId,
                'url'     => $url,
                'date'    => $date,
                'product' => $product->ID
            ];
            $db->insert( $data );
            }
        }

    static function get_product_by_slug($page_slug, $output = OBJECT)
        {
        global $wpdb;
        $product = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type= %s", $page_slug, 'product' ) );
        if ( $product )
            return get_post( $product, $output );

        return null;
        }


    static function getViewsTotal($filters)
        {
        $view = 0;

        $query  = MH_Query::init( null, self::$table )->select( 'sum(total) as total' );
        $query  = self::buildQuery( $query, $filters );
        $result = $query->first();
        if ( $result ) {
            $view = intval( $result['total'] );
            }
        return $view;
        }

    static function getTrafficBox($filters)
        {
        $data  = [];
        $query = MH_Query::init( null, self::$table )->select( 'url, sum(total) as total' )->whereRaw( 'product IS NULL' );
        $query = self::buildQuery( $query, $filters )->order_by( 'total' )->group_by( 'url' );
        $url   = $query->get();
        if ( $url )
            foreach ( $url as $key => $r ) {
                $data[] = [
                    'url'           => $r['url'],
                    'total'         => $r['total'],
                    'product_id'    => '',
                    'product_name'  => '',
                    'product_image' => '',
                ];

                }


        $query   = MH_Query::init( null, self::$table )->select( 'url, product, sum(total) as total' )->whereRaw( 'product IS NOT NULL' );
        $query   = self::buildQuery( $query, $filters )->order_by( 'total' )->group_by( 'product' );
        $product = $query->get();

        if ( $product )
            foreach ( $product as $key => $r ) {
                $img    = wp_get_attachment_image_src( get_post_thumbnail_id( $r['product'] ) );
                $data[] = [
                    'url'           => $r['url'],
                    'total'         => $r['total'],
                    'product_id'    => $r['product'],
                    'product_name'  => get_the_title( $r['product'] ),
                    'product_image' => isset($img[0]) ? $img[0] : '',
                ];

                }

        return $data;

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

        return $query;
        }



    }

?>
