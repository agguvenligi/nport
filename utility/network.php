<?php

class Network {
    // based on http://snipplr.com/view/15557/cidr-class-for-ipv4/
    
    /*
    * CIDR::cidrToRange("127.0.0.128/25");
    * array(2) {
    * [0]=> string(11) "127.0.0.128"
    * [1]=> string(11) "127.0.0.255"
    * }
    */
    public static function cidrToRange($cidr) {
        $range = array();
        $cidr = explode('/', $cidr);
        if(sizeof($cidr) == 2){
            $range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1]))));
            $range[1] = long2ip((ip2long($cidr[0])) + pow(2, (32 - (int)$cidr[1])) - 1);
        }
        return $range;
    }
    
    public static function alignedCIDR($ipinput,$netmask){
        $alignedIP = long2ip((ip2long($ipinput)) & (ip2long($netmask)));
        return "$alignedIP/" . self::maskToCIDR($netmask);
    }    
    
    public static function maskToCIDR($netmask){
        if(self::validNetMask($netmask)){
            return self::countSetBits(ip2long($netmask));
        }
        else{
            
        }
    }    
    
    public static function CIDRtoMask($int) {
        return long2ip(-1 << (32 - (int)$int));
    } 
        
    public static function countSetbits($int){
        $int = $int - (($int >> 1) & 0x55555555);
        $int = ($int & 0x33333333) + (($int >> 2) & 0x33333333);
        return (($int + ($int >> 4) & 0xF0F0F0F) * 0x1010101) >> 24;
    }    
    
    public static function validNetMask($netmask){
        $netmask = ip2long($netmask);
        $neg = ((~(int)$netmask) & 0xFFFFFFFF);
        return (($neg + 1) & $neg) === 0;
    }    
    
    public static function isIPWithinCIDR($ipinput, $cidr){
        $cidr = explode('/',$cidr);
        $cidr = self::alignedCIDR($cidr[0], self::CIDRtoMask((int)$cidr[1]));
        $cidr = explode('/',$cidr);
        $ipinput = (ip2long($ipinput));
        $ip1 = (ip2long($cidr[0]));
        $ip2 = ($ip1 + pow(2, (32 - (int)$cidr[1])) - 1);
        return (($ip1 <= $ipinput) && ($ipinput <= $ip2));
    }    
    
    public static function unsigned_ip2long($ip) {
        return sprintf("%u", ip2long($ip));
    }
   
    public static function rangeToCIDRList($startIPinput,$endIPinput=NULL) {
        $start = ip2long($startIPinput);
        $end =(empty($endIPinput))?$start:ip2long($endIPinput);
        while($end >= $start) {
            $maxsize = self::maxBlock(long2ip($start));
            $maxdiff = 32 - intval(log($end - $start + 1)/log(2));
            $size = ($maxsize > $maxdiff)?$maxsize:$maxdiff;
            $listCIDRs[] = long2ip($start) . "/$size";
            $start += pow(2, (32 - $size));
        }
        return $listCIDRs;
    }    
    
    public static function maxBlock($ipinput) {
        return self::maskToCIDR(long2ip(-(ip2long($ipinput) & -(ip2long($ipinput)))));
    }    

}

?>
