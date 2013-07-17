<?php
require_once 'db/dao.php';
require_once 'config.php';
require_once 'utility/dbimporter.php';

class Nmap{
    protected $targetIPs = array();
    protected $targetPorts = array();
    protected $templateCmd;
    protected $randomOutputFileName;

    // parameters are all arrays
    public function Nmap($targetIPs, $targetPorts) {
        $this->targetIPs = $targetIPs;
        $this->targetPorts = $targetPorts;
    }    
    
    public function run(){
        if(sizeof($this->targetIPs) == 0 || sizeof($this->targetPorts) == 0)
            return;
        
        $i = 0;
        while($i < sizeof($this->targetIPs)){
            
            $slicedTargetIps = array_slice($this->targetIPs, $i, 5);
            $i = $i + 5;
            
            $this->randomOutputFileName = Configuration::$nmapOutputDirectory . uniqid("nport" . rand(), true) . ".xml";
            $this->templateCmd = Configuration::$nmap . " -n -Pn -sT -T3" . 
                                    " -p " . implode(",",  $this->targetPorts) . 
                                    " " . implode(" ",  $slicedTargetIps) .                                         
                                    " -oX " . $this->randomOutputFileName;

            //echo $this->templateCmd;

            exec($this->templateCmd, $out, $retval);

            Logger::debug($this->templateCmd . " is executed with retval " . $retval);                                        

            if(file_exists($this->randomOutputFileName)){
                DBImporter::loadNmapToDB($this->randomOutputFileName, $this->targetPorts);
                unlink($this->randomOutputFileName);
            }       
        }
    }
  
}

?>
