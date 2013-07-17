<?php

require_once 'utility/validator.php';

class Whois{

    protected $inetnum;
    protected $netname;

    public function Whois(){
        $this->inetnum = "";
        $this->netname = "";
    }
    
    public function isEmpty(){
        if(empty($this->inetnum) && empty($this->netname))
            return true;
        return false;
    }
    
    public function setInetnum($inetnum) {
        $this->inetnum = $inetnum;
    }
    
    public function getInetnum() {
        return $this->inetnum;
    } 

    public function setNetname($netname) {
        $this->inetnum = $netname;
    }
    
    public function getNetname() {
        return $this->netname;
    }  
    
    public static function Get($ip){

        if(!Validator::isValidIpAddress($ip)){
            Logger::error("Not a valid IP address when executing whois " . $ip );                                        
            return;
        }
        
        $whois = new Whois();

        exec("whois " . $ip, $out, $retval);

        Logger::debug("Whois executed for " . $ip . " with retval " . $retval);                                                

        if($retval != 0)
            return $whois;
        
        $output = implode("\n", $out);                

        if (preg_match("/netname:\s+(.+)/i", $output, $matches))
            $whois->netname  = $matches[1];            
       
        if (preg_match("/(inetnum|netrange):\s+(.+)/i", $output, $matches))
            $whois->inetnum = $matches[2];            

        return $whois;
        
    }
    
}
?>
