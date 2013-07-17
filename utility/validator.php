<?php

require_once 'config.php';

class Validator {
    
    public static function isValidIpAddress($ip_addr)
    {
        if(preg_match("/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/",$ip_addr))
        {
            $parts=explode(".",$ip_addr);
            foreach($parts as $ip_parts)
                if(intval($ip_parts)>255 || intval($ip_parts)<0)
                    return false; 
            return true;
        }
        else
            return false; 
    } 
    
    public static function isIPAuthentic($ip_addr){        
        foreach(Configuration::$authenticIPs as $ip)
            if(strcmp($ip_addr, $ip) == 0){
                Logger::debug($ip_addr . " successfully accessed NPort!");
                return true;
            }        
        Logger::error($ip_addr . " failed to access NPort!");            
        return false;        
    }
    
    public static function isValidCIDRAddress($addr)
    {
        $parts = explode("/", $addr);
        if(sizeof($parts) == 2 && Validator::isValidIpAddress($parts[0]) && (intval($parts[1]) > 0 && intval($parts[1]) < 33) )
            return true;
        return false;
    }     
}
?>
