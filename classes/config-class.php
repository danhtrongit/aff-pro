<?php

class AFF_Config
	{

	static $table = 'vuacode_configs';


	static function getConfigs()
		{
		$db   = MH_Query::init( null, self::$table );
		$data = [];
		$rows = $db->get();
		foreach ( $rows as $key => $row ) {
			$data[$row['config_name']] = MH_FormatConfigValue( $row['config_value'] );
			}
		return $data;
		}

	static function getConfig($config_name)
		{
		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}vuacode_configs WHERE config_name = '$config_name'";
		$row = $wpdb->get_row( $sql, ARRAY_A );
		if ( $row )
			return $row['config_value'];
		return false;
		}

	static function setConfig($config_name, $config_value)
		{

		if ( strpos( $config_name, 'noti_' ) !== false ) {
			// global $wpdb;
			// debug("UPDATE {$wpdb->prefix}vuacode_configs SET config_value = '{$config_value}' WHERE config_name = '{$config_name}'");
			// $wpdb->query("UPDATE {$wpdb->prefix}vuacode_configs SET config_value = '{$config_value}' WHERE config_name = '{$config_name}'");
			// return;
			$config_value = stripslashes( $config_value );
			}

		$db = MH_Query::init( null, self::$table );
		if ( !$config_name )
			return;
		$db->where( 'config_name', $config_name );
		$config = $db->first();

		$data = [
			'config_name'  => $config_name,
			'config_value' => $config_value,
			// 'config_value' => stripslashes_deep($config_value),
		];

		if ( $config ) {
			$db->where( 'config_name', $config_name );
			$db->update( $data );
			} else {
			$db->insert( $data );

			}

		}





	}


?>
