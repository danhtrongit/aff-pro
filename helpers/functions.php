<?php

if ( !function_exists( 'debug' ) ) {
    function debug($var, $pre = true, $die = true)
        {
        if ( $pre )
            echo "<pre>";
        print_r( $var );
        if ( $pre )
            echo "</pre>";
        if ( $die )
            die();
        }
    }

//Format Config to Object JS
if ( !function_exists( 'MH_FormatConfigValue' ) ) {
    function MH_formatConfigValue($string)
        {
        $value = json_decode( $string, true );
        if ( json_last_error() === JSON_ERROR_NONE && !is_numeric( $string ) ) {
            return MH_formatConfigBoolean( $value );
            }
        return $string;
        }
    }

// Format String is True or False
if ( !function_exists( 'MH_formatConfigBoolean' ) ) {
    function MH_converter(&$value, $key)
        {
        if ( $value === 'false' )
            $value = false;
        else if ( $value === 'true' )
            $value = true;
        }
    function MH_formatConfigBoolean($data)
        {
        if ( is_array( $data ) )
            array_walk_recursive( $data, 'MH_converter' );
        return $data;
        }
    }

// Get current URL
if ( !function_exists( 'MH_CurrentUrl' ) ) {

    function MH_currentUrl($full = true)
        {
        if ( $full )
            return $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        else
            return $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        }
    }

// Get current URL
if ( !function_exists( 'MH_Response' ) ) {

    function MH_Response($flag = true, $msg = '', $data = '', $die = true, $type = 0)
        {
        if ( $type )
            echo json_encode( array_merge( [ 'success' => $flag, 'msg' => $msg ], $data ) );
        else
            echo json_encode( [ 'success' => $flag, 'msg' => $msg, 'data' => $data ] );
        if ( $die )
            die();
        }
    }

// Format String is True or False
if ( !function_exists( 'MH_HandlePagination' ) ) {

    function MH_HandlePagination($currentPage, $total_rows, $limit)
        {
        $url = MH_currentUrl();

        $parsed = parse_url( $url );

        if ( isset($parsed['query']) ) {
            $query = $parsed['query'];
            parse_str( $query, $params );
            unset( $params['paged'] );
            unset( $params['pagex'] );
            $url = explode( '?', $url )[0] . '?' . http_build_query( $params );
            }

        // echo $url;
        // $url = preg_replace('~(\?|&)'.$key.'=[^&]*~', '$1', $url);
        return [
            'current_page' => intval( $currentPage ) <= 1 ? 1 : intval( $currentPage ),
            'max_page'     => ceil( $total_rows / $limit ),
            'total'        => $total_rows,
            'limit'        => $limit,
            'url'          => $url
        ];
        }
    }


// Show DB Errors
if ( !function_exists( 'MH_Hanlde_Result_DB' ) ) {

    function MH_Hanlde_Result_DB($result, $db, $data = '')
        {

        if ( $result )
            if ( $data )
                return $data;
            else
                return $result;
        else {
            echo 'insert failed: ' . $db->getLastError();
            die();
            }
        }
    }

//Load View
if ( !function_exists( 'MH_Load_view' ) ) {
    function MH_Load_view($view, $data = array(), $echo = false, $is_admin = false, $path = '')
        {
        extract( $data );
        ob_start();
        if ( $is_admin )
            require $path . 'admin/partials/' . $view;
        else
            require $path . 'public/partials/' . $view;
        $content = ob_get_contents();
        echo $content;
        ob_end_clean();

        if ( $echo )
            echo $content;
        else
            return $content;
        }
    }


// Check Date is Between
if ( !function_exists( 'MH_DateIsBetween' ) ) {
    function MH_DateIsBetween($date, $type = 1)
        {
        // type:  1 - Mysql
        if ( $type == 1 ) {
            $begin   = strtotime( $date['begin'] );
            $between = strtotime( $date['between'] );
            $end     = strtotime( $date['end'] );
            // echo $begin;
            return $begin <= $between && $end >= $between;
            }

        }
    }


if ( !function_exists( 'MH_CreateDateArray' ) ) {
    function MH_CreateDateArray($from, $to, $value = NULL)
        {
        $begin    = new DateTime( $from );
        $end      = new DateTime( $to );
        $end      = $end->modify( '+1 day' );
        $interval = DateInterval::createFromDateString( '1 day' );
        $days     = new DatePeriod( $begin, $interval, $end );

        $baseArray = array();
        foreach ( $days as $day ) {
            $dateKey = $day->format( "d-m-Y" );
            if ( $value != NULL )
                $baseArray[$dateKey] = $value;
            else
                $baseArray[] = $dateKey;
            }

        return $baseArray;
        }

    }


if ( !function_exists( 'MH_Get_Product_Array' ) ) {
    function MH_Get_Product_Array($product_id, $include_child = true)
        {
        $product = wc_get_product( $product_id );

        if ( !$product )
            return;
        $image         = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'thumbnail' );
        $image_full    = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'large' );
        $regular_price = $product->get_regular_price();
        $sale_price    = $product->get_sale_price();
        $sale_price    = $sale_price ? $sale_price : $regular_price;
        $data          = [
            'product_id'     => $product_id,
            'name'           => $product->get_title(),
            'sku'            => $product->get_sku(),
            'regular_price'  => $regular_price,
            'sale_price'     => $sale_price,
            'sale_percent'   => '',
            'image'          => isset($image[0]) ? $image[0] : '',
            'post_thumbnail' => isset($image_full[0]) ? $image_full[0] : '',
            'children'       => [],
            'parent_id'      => 0
        ];
        if ( $include_child ) {
            if ( $product->has_child() ) {
                $data['regular_price'] = $product->get_variation_regular_price();
                $data['sale_price']    = $product->get_variation_sale_price();

                $children_ids = $product->get_children();
                foreach ( $children_ids as $key => $id ) {
                    $product       = wc_get_product( $id );
                    $regular_price = $product->get_regular_price();
                    $sale_price    = $product->get_sale_price();
                    $sale_price    = $sale_price ? $sale_price : $regular_price;

                    $child              = [
                        'product_id'    => $id,
                        'name'          => $product->get_name(),
                        'sku'           => $product->get_sku(),
                        'regular_price' => $regular_price,
                        'sale_price'    => $sale_price,
                        'sale_percent'  => '',
                        'image'         => isset($image[0]) ? $image[0] : '',
                        'parent_id'     => $product_id,
                    ];
                    $data['children'][] = $child;
                    }
                }
            }

        return $data;

        }
    }
if ( !function_exists( 'aff_current_url' ) ) {
    function aff_current_url($full = true)
        {
        if ( $full )
            return $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        else
            return $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        }
    }
if ( !function_exists( 'aff_reconstruct_url' ) ) {
    function aff_reconstruct_url($url)
        {
        $url_parts       = parse_url( aff_current_url() );
        $constructed_url = $url_parts['scheme'] . '://' . $url_parts['host'] . (isset($url_parts['path']) ? $url_parts['path'] : '');

        return $constructed_url;
        }
    }

if ( !function_exists( 'MH_Date_Array' ) ) {
    function MH_Date_Array($from, $to, $value = NULL)
        {
        $begin    = new DateTime( $from );
        $end      = new DateTime( $to );
        $end      = $end->modify( '+1 day' );
        $interval = DateInterval::createFromDateString( '1 day' );
        $days     = new DatePeriod( $begin, $interval, $end );

        $baseArray = array();
        foreach ( $days as $day ) {
            $dateKey = $day->format( "d-m" );
            // $dateKey = $day->format("d-m-Y");
            if ( $value != NULL )
                $baseArray[$dateKey] = $value;
            else
                $baseArray[] = $dateKey;
            }

        return $baseArray;
        }
    }

if ( !function_exists( 'MH_BuildTree' ) ) {

    function MH_BuildTree(array $elements, $parentId = 0, $parent_key = 'parent_id', $child_key = 'ID')
        {
        $branch = array();

        foreach ( $elements as $element ) {
            if ( (string) $element[$parent_key] === (string) $parentId ) {
                $children = MH_BuildTree( $elements, $element[$child_key], $parent_key, $child_key );
                if ( $children ) {
                    $element['children'] = $children;
                    }
                $branch[] = $element;
                }
            }

        return $branch;
        }
    }


if ( !function_exists( 'MH_BuildTreeCat' ) ) {
    function MH_BuildTreeCat(array &$elements, $parentId = 0)
        {
        $branch = array();
        foreach ( $elements as &$element ) {
            if ( $element->parent == $parentId ) {
                $children = MH_BuildTreeCat( $elements, $element->term_id );
                if ( $children )
                    $element->wpse_children = $children;

                $branch[$element->term_id] = $element;
                unset( $element );
                }
            }
        return $branch;
        }
    }


if ( !function_exists( 'AFF_SendMail' ) ) {

    function AFF_SendMail($email, $title, $content, $replace_arr)
        {
        $content = wpautop( $content );

        foreach ( $replace_arr as $key => $value ) {

            $content = str_replace( $key, $value, $content );

            }
        $html   = file_get_contents( AFF_PATH . '/public/partials/email-templates/email-template.html' );
        $html   = str_replace( '[content]', $content, $html );
        $header = [ 'Content-Type: text/html; charset=UTF-8' ];
        $html   = str_replace( '[site_link]', get_site_url(), $html );
        wp_mail( $email, $title, $html );

        }

    }

if ( !function_exists( 'check_base64_image' ) ) {

    function check_base64_image($data)
        {
        $img = getimagesize( $data );
        if ( $img )
            return true;
        return false;

        }
    }

if ( !function_exists( 'MH_Save_Image' ) ) {

    function MH_Save_Image($base64, $filename = '', $folder = 'vuacode-aff')
        {

        $pos       = strpos( $base64, ';' );
        $file_type = explode( ':', substr( $base64, 0, $pos ) )[1];
        $tail      = explode( '/', $file_type )[1];



        $filename = $filename . '.' . $tail;

        $upload_dir  = wp_upload_dir();
        $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['basedir'] ) . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
        $upload_url  = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['baseurl'] ) . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;

        $img = str_replace( 'data:image/jpeg;base64,', '', $base64 );
        $img = str_replace( 'data:image/png;base64,', '', $img );
        $img = str_replace( 'data:image/jpg;base64,', '', $img );
        $img = str_replace( 'data:image/gif;base64,', '', $img );


        $img     = str_replace( ' ', '+', $img );
        $decoded = base64_decode( $img );

        if ( !file_exists( $upload_path ) ) {

            mkdir( $upload_path, 0777, true );
            }
        // Save the image in the uploads directory.
        $upload_file = file_put_contents( $upload_path . $filename, $decoded );

        return $upload_url . $filename;
        }
    }
if ( !function_exists( 'MH_Save_Image_To_Media' ) ) {

    function MH_Save_Image_To_Media($base64)
        {

        $pos       = strpos( $base64, ';' );
        $file_type = explode( ':', substr( $base64, 0, $pos ) )[1];
        $tail      = explode( '/', $file_type )[1];
        $filename  = $filename . '-' . time() . '.' . $tail;

        $img = str_replace( 'data:image/jpeg;base64,', '', $base64 );
        $img = str_replace( 'data:image/png;base64,', '', $img );
        $img = str_replace( 'data:image/jpg;base64,', '', $img );
        $img = str_replace( 'data:image/gif;base64,', '', $img );


        $img     = str_replace( ' ', '+', $img );
        $decoded = base64_decode( $img );

        $upload_dir = wp_upload_dir();
        // @new
        $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;



        // @new
        $image_upload = file_put_contents( $upload_path . $filename, $decoded );


        //HANDLE UPLOADED FILE
        if ( !function_exists( 'wp_handle_sideload' ) ) {
            require_once (ABSPATH . 'wp-admin/includes/file.php');
            }

        // Without that I'm getting a debug error!?
        if ( !function_exists( 'wp_get_current_user' ) ) {
            require_once (ABSPATH . 'wp-includes/pluggable.php');
            }

        // @new
        $file             = array();
        $file['error']    = '';
        $file['tmp_name'] = $upload_path . $filename;
        $file['name']     = $filename;
        $file['type']     = 'image/png';
        $file['size']     = filesize( $upload_path . $filename );

        // upload file to server
        // @new use $file instead of $image_upload
        $file_return = wp_handle_sideload( $file, array( 'test_form' => false ) );

        $filename   = $file_return['file'];
        $attachment = array(
            'post_mime_type' => $file_return['type'],
            'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
            'post_content'   => '',
            'post_status'    => 'inherit',
            'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename )
        );
        $attach_id  = wp_insert_attachment( $attachment, $filename );
        return $attach_id;
        }
    }

if ( !function_exists( 'MH_Save_Image_1' ) ) {

    function MH_Save_Image_1($base64, $filename = '', $folder = 'vuacode-aff')
        {

        $pos       = strpos( $base64, ';' );
        $file_type = explode( ':', substr( $base64, 0, $pos ) )[1];
        $tail      = explode( '/', $file_type )[1];



        $filename = $filename . '.' . $tail;

        $upload_dir  = wp_upload_dir();
        $upload_path = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['basedir'] ) . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
        $upload_url  = str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['baseurl'] ) . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;

        $img = str_replace( 'data:image/jpeg;base64,', '', $base64 );
        $img = str_replace( 'data:image/png;base64,', '', $img );
        $img = str_replace( 'data:image/jpg;base64,', '', $img );
        $img = str_replace( 'data:image/gif;base64,', '', $img );


        $img     = str_replace( ' ', '+', $img );
        $decoded = base64_decode( $img );

        if ( !file_exists( $upload_path ) ) {

            mkdir( $upload_path, 0777, true );
            }
        // Save the image in the uploads directory.
        $upload_file = file_put_contents( $upload_path . $filename, $decoded );

        return $upload_url . $filename;
        }
    }

if ( !function_exists( 'AFF_SendTelegram' ) ) {
    function AFF_SendTelegram($data)
        {

        if ( true ) {
            $botToken = $data['telegram_token'];
            $website  = "https://api.telegram.org/bot" . $botToken;
            $chatId   = $data['telegram_group'];
            $params   = [
                'chat_id'    => $chatId,
                'text'       => get_site_url() . PHP_EOL . $data['text'],
                'parse_mode' => 'html'
            ];
            $ch       = curl_init( $website . '/sendMessage' );
            curl_setopt( $ch, CURLOPT_HEADER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, ($params) );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            $result = curl_exec( $ch );
            curl_close( $ch );
            }


        }
    }