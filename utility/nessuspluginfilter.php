<?php

require_once("config.php");
require_once("model/model.php");
    
class NessusPluginFilter{    

    protected $naslfiles;
    protected $ports;
    protected $matchNaslScripts;

    public function NessusPluginFilter(){
        $this->naslfiles = glob(Configuration::$nessusPluginDirectory . "*.nasl");    
        $this->ports = array_keys(Configuration::$ports_analyzed);
        $this->matchNaslScripts = array();
    }
    
    public function filter(){
        
        if(!empty($this->naslfiles)){        
            $date1 = time();
            $count = 0;        
            foreach($this->naslfiles as $naslfile){
                $count++;
                $content = file_get_contents($naslfile);            
                $lines = explode("\n", $content);
                $pluginId = -1;            
                $matchedRequiredPorts = array();
                $cvssBaseScore = -1;
                $pluginName = "";
                foreach($lines as $line){
                    $line = trim($line);

                    // get plugin id
                    if ($pluginId == -1 && preg_match("/script_id\((.+)\);/i", $line, $matches))
                        $pluginId = $matches[1];

                    // get plugin name
                    if (empty($pluginName) && preg_match("/script_name\(english:\"(.+)\"/i", $line, $matches))
                        $pluginName = $matches[1];

                    
                    // get script's required ports
                    if (sizeof($matchedRequiredPorts) == 0 && preg_match("/script_require_ports\((.+)\);/i", $line, $matches)){                    
                        $requiredPorts = array_map('trim', explode(',', $matches[1]));
                        $matchedRequiredPorts = array_intersect($this->ports, $requiredPorts);
                    }

                    // get cvss base vector value
                    if ($cvssBaseScore == -1 && preg_match("/script_set_cvss_base_vector\(\"(.+)\"\);/i", $line, $matches))
                        $cvssBaseScore = $this->cvss_vector_to_base_score($matches[1]);

                }
                if($pluginId != -1 && sizeof($matchedRequiredPorts) > 0 && $cvssBaseScore >= Configuration::$nessusCVSSBaseScoreLimit){
                    $naslScript = new naslscript();
                    $naslScript->setPluginId($pluginId);
                    $naslScript->setPluginName($pluginName);
                    $naslScript->setPorts($matchedRequiredPorts);
                    $naslScript->setCvssBaseScore($cvssBaseScore);
                    $this->matchNaslScripts[] = $naslScript;                
                    Logger::debug("Successfully filtered a nasl script: PluginId=" . $naslScript->getPluginId() . 
                                    " PluginName=" . $naslScript->getPluginName() . 
                                    " CvssBaseScore=" . $naslScript->getCvssBaseScore() . 
                                    " Ports=" . implode(",", $naslScript->getPorts()) );
                }

            }
            $date2 = time();
            Logger::info("Success processing " . $count . " nasl scripts with " . sizeof($this->matchNaslScripts) . 
                    " plugins above the cvss base score " . Configuration::$nessusCVSSBaseScoreLimit . 
                    " in " . number_format(($date2 - $date1) / 60, 2, '.', '') . " minutes") ;        
        }
        
        return $this->matchNaslScripts;
    }
    
    protected function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
    
    protected function cvss_vector_to_base_score($str)
    {
        $AccessVector = -1;
        $AccessComplexity = -1;
        $Authentication = -1;
        
        $c = -1;
        $i = -1;
        $a = -1;
        
        $q = -1;
        $z = -1;
        
        $cvss_score = -1;
        
        if (strlen(strstr($str, "AV:L/")) > 0) {
            $AccessVector = 2532;
        }
        else if (strlen(strstr($str,"AV:A/")) > 0) {
            $AccessVector = 1548;
        }
        else if (strlen(strstr($str,"AV:N/")) > 0) {
            $AccessVector = 1000;
        }
        else{
            return -1;
        }

        if (strlen(strstr($str, "AC:L/")) > 0) {
            $AccessComplexity = 1408;
        }
        else if (strlen(strstr($str,"AC:M/")) > 0) {
            $AccessComplexity = 1639;
        }
        else if (strlen(strstr($str,"AC:H/")) > 0) {
            $AccessComplexity = 2857;
        }
        else{
            return -1;
        }
        
        if (strlen(strstr($str, "Au:N/")) > 0) {
            $Authentication = 1420;
        }
        else if (strlen(strstr($str,"Au:S/")) > 0) {
            $Authentication = 1786;
        }
        else if (strlen(strstr($str,"Au:M/")) > 0) {
            $Authentication = 2222;
        }
        else{
            return -1;
        }

        if (strlen(strstr($str, "C:N/")) > 0) {
            $c = 1000;
        }
        else if (strlen(strstr($str,"C:P/")) > 0) {
            $c = 725;
        }
        else if (strlen(strstr($str,"C:C/")) > 0) {
            $c = 340;
        }
        else{
            return -1;
        }

        if (strlen(strstr($str, "I:N/")) > 0) {
            $i = 1000;
        }
        else if (strlen(strstr($str,"I:P/")) > 0) {
            $i = 725;
        }
        else if (strlen(strstr($str,"I:C/")) > 0) {
            $i = 340;
        }
        else{
            return -1;
        }

        if (strlen(strstr($str, "/A:N")) > 0) {
            $a = 1000;
        }
        else if (strlen(strstr($str,"/A:P")) > 0) {
            $a = 725;
        }
        else if (strlen(strstr($str,"/A:C")) > 0) {
            $a = 340;
        }
        else{
            return -1;
        }

        if ( $c + $i + $a == 3000 )
            return "0.0";

        $z = ($c*1000)/( (1000*1000)/$i);
        $z = ($z*1000)/( (1000*1000)/$a);
        $z = 1000 - $z;
        $z = (1000*1000)/$z;
        $z = (10410*1000)/$z;
        $z = ($z*1000)/1666;

        $q = ( $AccessComplexity  * 1000 ) / (( 1000 * 1000 )/$Authentication );
        $q = ( $q * 1000 ) / ( ( 1000 * 1000 ) / $AccessVector );
        $q = ( 1000 * 1000 ) / $q;
        $q = $q * 20000;
        $q = $q / 2500;

        $z = ( $z + $q ) - 1500;
        $z = ($z * 11760)/10000;
        if ( $z % 100 >= 50) 
            $z += ( 100 - ($z % 100) ); # Rounding
        if ( $z / 1000 < 2 )  # If the value is small, more generous rounding
        {
                if ( $z % 100 >= 40) 
                    $z += ( 100 - ($z % 100) ); 
        }
 
        $z = ($z/10)*10;
        $cvss_score = $z / 1000;
        return $cvss_score;
    }
}
    
    
?>
