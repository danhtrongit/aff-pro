<?php
global $wpdb;
$wpdb->query( "ALTER TABLE  {$wpdb->prefix}affpro_history ADD  `order_id` varchar(25)  DEFAULT NULL" );
