<?php 

    class AFF_App {
        static $settings = null;
        static function getSettings(){
            if(!self::$settings){
                self::$settings = AFF_Config::getConfigs();
            }
            return self::$settings;

        }
    } 


?>