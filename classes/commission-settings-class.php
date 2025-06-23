<?php

class AFF_Commission_Settings
    {

    static $table = 'vuacode_commission_settings';

    static function get($type = 'product')
        {

        $db = MH_Query::init( null, self::$table );
        if ( $type == 'product' ) {
            $db->where( 'type', 'product' );
            $data = $db->get();
            if ( $data ) {
                $ids      = [];
                $mapKV    = [];
                $products = [];
                foreach ( $data as $key => $d ) {
                    $ids[]                  = $d['object_id'];
                    $mapKV[$d['object_id']] = $d;
                    }

                $args = array(
                    'post__in'       => $ids,
                    'post_type'      => [ 'product', 'product_variation' ],
                    'posts_per_page' => 450
                );

                $the_query = new WP_Query( $args );

                if ( $the_query->have_posts() ) {
                    while ( $the_query->have_posts() ) {
                        $the_query->the_post();

                        $product_id = get_the_ID();

                        $_product = wc_get_product( $product_id );
                        if ( $_product ) {
                            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'thumbnail' );

                            $sale_price = $_product->get_regular_price();
                            $products[] = [
                                'commission'            => $mapKV[$product_id]['commission'],
                                'type'                  => $mapKV[$product_id]['type'],
                                'commission_setting_id' => $mapKV[$product_id]['id'],
                                'product_id'            => $product_id,
                                'name'                  => get_the_title(),
                                'sku'                   => $_product->get_sku(),
                                'regular_price'         => $_product->get_regular_price(),
                                'sale_price'            => $_product->get_sale_price(),
                                'price'                 => $_product->get_price(),
                                'image'                 => isset($image[0]) ? $image[0] : ''
                            ];
                            }
                        }

                    }
                return $products;


                }
            }
        if ( $type == 'product_cat' ) {
            $db->where( 'type', 'product_cat' );
            $data = $db->get();


            $categories = [];


            foreach ( $data as $key => $d ) {
                $term = get_term( $d['object_id'], 'product_cat' );
                if ( $term )
                    $categories[] = [
                        'commission_setting_id' => $d['id'],
                        'cat_ID'                => $d['object_id'],
                        'type'                  => $d['type'],
                        'object_id'             => $d['object_id'],
                        'commission'            => $d['commission'],
                        'cat_name'              => $term->name,
                    ];

                }

            return $categories;
            }
        if ( $type == 'product_tag' ) {
            $db->where( 'type', 'product_tag' );
            $data = $db->get();

            $categories = [];


            foreach ( $data as $key => $d ) {
                $term = get_term( $d['object_id'], 'product_tag' );
                if ( $term )
                    $categories[] = [
                        'commission_setting_id' => $d['id'],
                        'cat_ID'                => $d['object_id'],
                        'type'                  => $d['type'],
                        'object_id'             => $d['object_id'],
                        'commission'            => $d['commission'],
                        'cat_name'              => $term->name,
                    ];

                }

            return $categories;
            }


        }

    static function save($data, $type)
        {
        MH_Query::init( null, self::$table )->where( 'type', $type )->delete();
        if ( $data ) {
            if ( $type == 'product' ) {
                foreach ( $data as $key => $d ) {
                    MH_Query::init( null, self::$table )->insert( [
                        'type'       => $type,
                        'object_id'  => $d['product_id'],
                        'commission' => $d['commission'],
                    ] );
                    }
                } else if ( $type == 'product_cat' || $type == 'product_tag' ) {
                foreach ( $data as $key => $d ) {
                    MH_Query::init( null, self::$table )->insert( [
                        'type'       => $type,
                        'object_id'  => $d['cat_ID'],
                        'commission' => $d['commission'],
                    ] );
                    }
                }
            }
        }

    static function searchWooProducts($filters)
        {

        $db = MH_Query::init( null, 'posts' )->select( 'ID, post_parent' );
        if ( isset($filters['search']) && $filters['search'] ) {

            $filters['search'] = strtolower( $filters['search'] );


            $db->where( 'post_type', 'product' )
                ->where( 'post_parent', '0' )
                ->where( 'post_status', 'publish' )
                ->whereRaw( "LOWER(post_title) LIKE '%$filters[search]%'" );

            // $db->whereRaw("(post_type = 'product' or post_type = 'product_variation')")
            //    ->where('post_status', 'publish')
            //    ->whereRaw("LOWER(post_title) LIKE '%$filters[search]%'");
            }

        $data = $db->get( ARRAY_A, '', true );
        // debug($data);
        // return ['data' => $data ? $data : [], 'pagination' => $pagination = $query->rows_found($page, $per_page)];

        $products = [];
        foreach ( $data as $key => $row ) {
            $product = MH_Get_Product_Array( $row['ID'], true );
            if ( $product )
                $products[] = $product;
            }
        return [
            'data' => $products,
            // 'products' => $products,
            // 'pagination' => $pagination
        ];
        }

    static function getWooProductById($id)
        {
        global $wpdb;
        $posts_table = $wpdb->prefix . "posts";

        $sql    = "SELECT ID FROM $posts_table as p INNER JOIN $wpdb->postmeta as m ON p.ID = m.post_id  WHERE post_parent = 0 AND ((m.meta_key='_sku' AND m.meta_value='$id') OR p.ID = '$id') LIMIT 1";
        $result = $wpdb->get_row( $sql, ARRAY_A );
        if ( $result ) {
            $result = MH_Get_Product_Array( $result['ID'] );
            if ( $result )
                return $result;
            }

        return false;



        }

    static function getCommissionSettingById($object_id)
        {
        $db = MH_Query::init( null, self::$table );
        $db->where( 'type', 'product' );
        $db->where( 'object_id', $object_id );
        $record = $db->first();
        if ( $record ) {
            return $record['commission'];
            } else {
            $listCagories = [];

            $terms        = wp_get_post_terms( $object_id, 'product_cat', [ 'fields' => 'ids' ] );
            $listCagories = $terms;
            if ( $terms ) {
                foreach ( $terms as $key => $term ) {
                    $parent       = get_ancestors( $term, 'product_cat' );
                    $listCagories = array_merge( $listCagories, $parent );
                    }


                $listCagories = array_unique( $listCagories );

                foreach ( $listCagories as $key => $category ) {
                    $db = MH_Query::init( null, self::$table );
                    $db->where( 'type', 'product_cat' );
                    $db->where( 'object_id', $category );
                    $record = $db->first();
                    if ( $record )
                        return $record['commission'];
                    }



                }


            $listTags = [];

            $terms    = wp_get_post_terms( $object_id, 'product_tag', [ 'fields' => 'ids' ] );
            $listTags = $terms;
            if ( $terms ) {
                foreach ( $listTags as $key => $tag ) {
                    $db = MH_Query::init( null, self::$table );
                    $db->where( 'type', 'product_tag' );
                    $db->where( 'object_id', $tag );
                    $record = $db->first();
                    if ( $record )
                        return $record['commission'];
                    }

                }
            }

        return;

        }




    }


?>
