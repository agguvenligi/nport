<?php

require_once 'config.php';

class Logger{
    
    public static $DEBUG = 3;
    public static $INFO = 2;
    public static $ERROR = 1;    

    public static function info($msg){
        Logger::log($msg, Logger::$INFO);
    }

    public static function debug($msg){
        Logger::log($msg, Logger::$DEBUG);
    }
    
    public static function error($msg){
        Logger::log($msg, Logger::$ERROR);
    }
    
    protected static function log($msg, $logLevel){
        // todo: sanitize $msg
        if(Configuration::$logLevel >= $logLevel)
            error_log(Logger::getLevelString($logLevel) . " " . date("Y-m-d H:i:s") . " " . $_SERVER["REMOTE_ADDR"] . " " . $msg  . "\n", 3, Configuration::$errorLog);            
    }
    
    protected static function getLevelString($level){
        if(Logger::$DEBUG == $level)
            return "DEBUG";
        if(Logger::$INFO == $level)
            return "INFO";
        if(Logger::$ERROR == $level)
            return "ERROR";        
        return "N/A ERROR TYPE";
    }
}

?>
