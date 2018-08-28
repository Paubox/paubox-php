<?php
include_once dirname(__DIR__).'/common/Constants.php';
class ConfigurationManager {
    
    private static function readProperties($fileAndPath){
    
        $properties = parse_ini_file($fileAndPath);
        foreach ($properties as $key => $value) {
            if ("APIUSER"===$key) {
               Constants::$API_USER=$value;
            }elseif ("APIKEY"===$key){
                Constants::$API_KEY=$value;
            }
        }
    }

    public static function  getProperties($fileAndPath){
       ConfigurationManager::readProperties($fileAndPath);
    }
}
