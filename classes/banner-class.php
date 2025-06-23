<?php

class AFF_Banner extends AFF_App
    {

    static $table = 'vuacode_banners';

    static function get()
        {
        $query = MH_Query::init( null, self::$table )->get();
        return $query ? $query : [];
        }


    static function add($data)
        {
        MH_Query::init( null, self::$table )->insert( $data );
        }

    static function remove($id)
        {
        MH_Query::init( null, self::$table )->where( 'id', $id )->delete();
        }


    }

?>
