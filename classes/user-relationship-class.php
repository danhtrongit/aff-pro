<?php

class AFF_User_Relationship
    {

    static $table = 'vuacode_user_relationships';


    static function initRelationShip()
        {
        $users = AFF_User::getList( [], 1, 2000 );

        foreach ( $users['data'] as $user ) {
            $db     = MH_Query::init( null, self::$table );
            $record = $db->where( 'ancestor_id', $user['ID'] )
                ->where( 'descendant_id', $user['ID'] )
                ->where( 'distance', 0 )
                ->first();
            if ( !$record )
                $db->insert( [
                    'ancestor_id'   => $user['ID'],
                    'descendant_id' => $user['ID'],
                    'distance'      => 0,
                ] );
            }
        }

    static function getAncestor($user_id, $distance = 0)
        {

        $db    = MH_Query::init( null, self::$table );
        $query = $db->select( 'ancestor_id, distance' )
            ->where( 'descendant_id', $user_id )
            ->order_by( 'distance', 'ASC' );
        if ( $distance )
            $query = $query->where( 'distance', '<=', $distance );
        return $query->get();

        }

    static function getDescendants($user_id, $distance, $pluck = '')
        {
        $db    = MH_Query::init( null, self::$table );
        $query = $db->select( 'descendant_id, distance' )
            ->where( 'ancestor_id', $user_id )
            ->order_by( 'distance', 'ASC' );
        if ( $distance )
            $query = $query->where( 'distance', '<=', $distance );

        if ( $pluck )
            return $query->order_by( 'distance', 'ASC' )->pluckOneColumnArray( $pluck );

        return $query->order_by( 'distance', 'ASC' )->get();
        }


    static function setRelationship($descendant_id, $ancestor_id)
        {
        $level = AFF_Config::getConfig( 'relationship_level' );
        if ( !$level )
            return;
        $ancestors = self::getAncestor( $ancestor_id, 10000 );
        // debug($ancestors);
        if ( $ancestors ) {
            foreach ( $ancestors as $an ) {
                $db = MH_Query::init( null, self::$table );

                $db->where( 'ancestor_id', $an['ancestor_id'] );
                $db->where( 'descendant_id', $descendant_id );
                $db->where( 'distance', $an['distance'] + 1 );

                $db->findOrInsert( [
                    'ancestor_id'   => $an['ancestor_id'],
                    'descendant_id' => $descendant_id,
                    'distance'      => $an['distance'] + 1,
                ] );
                }
            }

        }


    static function setRelationship2($descendant_id, $ancestor_id)
        {

        $level = AFF_Config::getConfig( 'relationship_level' );
        if ( !$level )
            return;

        $descendants = self::getDescendants( $descendant_id, $level );


        $flag_sensitive = false; // Khi cha thành con, con thành cha
        foreach ( $descendants as $key => $des ) {
            if ( $des['descendant_id'] == $ancestor_id )
                $flag_sensitive = true;
            }


        if ( $flag_sensitive ) {
            // $ancestors = self::getAncestor($descendant_id, ($level - 1));
            // foreach ($descendants as $key => $des) {
            //     MH_Query::init(null, self::$table)->where('descendant_id', $des['descendant_id'])->where('distance', '>', 0)->delete();
            //     MH_Query::init(null, 'users')->where('ID', $des['descendant_id'])->update(['parent_id' => 0]);
            // }

            return [ 'success' => false, 'msg' => 'Xin lỗi không thể di chuyển cha con trong cùng một nhánh' ];
            } else {
            // debug($descendants);
            if ( $descendants ) {
                foreach ( $descendants as $key => $des ) {
                    MH_Query::init( null, self::$table )->where( 'descendant_id', $des['descendant_id'] )->where( 'distance', '>', 0 )->delete();
                    // MH_Query::init(null, 'users')->where('ID', $des['descendant_id'])->update(['parent_id' => 0]);
                    }
                }

            $bridgeId = $ancestor_id;
            foreach ( $descendants as $key => $des ) {
                // $bridgeUser = MH_Query::init(null, 'users')->where('ID', $des['descendant_id'])->update
                if ( $key == 0 ) {
                    self::setRelationship( $des['descendant_id'], $bridgeId );
                    MH_Query::init( null, 'users' )->where( 'ID', $des['descendant_id'] )->update( [ 'parent_id' => $bridgeId ] );
                    } else {
                    $user = MH_Query::init( null, 'users' )->where( 'ID', $des['descendant_id'] )->first();
                    self::setRelationship( $des['descendant_id'], $user['parent_id'] );

                    }

                // $bridgeId = $des['descendant_id'];
                }
            return [ 'success' => true, 'msg' => 'Thao tác thành công' ];
            }
        // debug($flag_sensitive);



        }




    }
// debug(AFF_User_Relationship::setRelationship(4,2));
// debug(AFF_User_Relationship::initRelationShip());

?>
