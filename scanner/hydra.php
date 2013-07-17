<?php
require_once 'db/dao.php';
require_once 'config.php';
require_once 'scanner/http.php';

class Hydra{
    protected $targetIPs = array();
    protected $targetPorts = array();
    protected $templateCmd;
    protected $randomOutputFileName;
    protected $description;

    // parameters are all arrays
    // $targetPorts is of type model.port
    public function Hydra($targetIPs, $targetPorts, $description) {
        $this->targetIPs = $targetIPs;
        $this->targetPorts = $targetPorts;
        $this->description = $description;
    }  
    
    public static function isServiceHttpRelated($targetService){
        if( strcmp($targetService, "http-get-form") == 0 || strcmp($targetService, "http-post-form") == 0 ||
            strcmp($targetService, "http-get") == 0 || strcmp($targetService, "http-head") == 0 ||
            strcmp($targetService, "https-get") == 0 || strcmp($targetService, "https-head") == 0  ||
            strcmp($targetService, "https-get-form") == 0 || strcmp($targetService, "https-post-form") == 0  )
            return true;
        return false;
    }
    
    public static function isServiceHttps($targetService){
        if( strcmp($targetService, "https-get") == 0 || strcmp($targetService, "https-head") == 0  ||
            strcmp($targetService, "https-get-form") == 0 || strcmp($targetService, "https-post-form") == 0  )
            return true;
        return false;        
    }

    
    public function run(){
        $message = "";
        
        if(sizeof($this->targetIPs) == 0 || sizeof($this->targetPorts) == 0){
            $message = "No targets or ports are defined, refusing to brute force";
            return $message;
        }
        
        $ipDAO = new IpDAO(Configuration::$dbhost, Configuration::$dbuser, Configuration::$dbpassword, Configuration::$dbschema);
        
        foreach($this->targetIPs as $targetIP){
            foreach($this->targetPorts as $targetPort){
                $this->randomOutputFileName = Configuration::$hydraOutputDirectory . uniqid("nport" . rand(), true) . ".txt";
                $targetService = Configuration::$ports_analyzed[$targetPort->getPort()];
                
                /*************UNDERSTAND IF THIS TARGET & PORT IS BRUTE FORCEABLE*****************/
                // For Http related services this means, whether the / request returns with 401
                // 'cause we are able to brute force Basic/Digest/NTLM for now 
                $bruteforceable = true;
                $additionalArgument = "";
                $responseCode = "";
                if(Hydra::isServiceHttpRelated($targetService) ){
                    $protocol = "http://";
                    if(Hydra::isServiceHttps($targetService))
                        $protocol = "https://";
                    
                    $additionalArgument = " / "; // no need for >= Hydra 7.3
                    $h = new Http($protocol . $targetIP . ":" . $targetPort->getPort() . "/");
                    $responseCode = $h->getResponseCode();
                    if($responseCode != 401)
                        $bruteforceable = false;
                }
                    
                if(!empty($targetService) && $bruteforceable){
                    $this->templateCmd = "sudo " . Configuration::$hydra . 
                                            " -f -e ns -L " . Configuration::$hydraOutputDirectory . 
                                            "usernames.txt -P " . Configuration::$hydraOutputDirectory . 
                                            "passwords.txt -t 10 -o " . $this->randomOutputFileName .
                                            " " . $targetIP . " " . $targetService . " " . $additionalArgument;

                    //echo $this->templateCmd;
                    $pid = exec("nohup $this->templateCmd > /dev/null 2>&1 & echo $!", $out, $retval);

                    $ipDAO->saveHydraScan($pid, $this->randomOutputFileName, $targetPort->getId(), $this->description);
                    
                    Logger::debug($this->templateCmd . " is executed and saved to db with retval " . $retval . " with pid " . $pid);                                        
                    $message .= "Brute force started for service: " . $targetService . " on ip: " . $targetIP . "<br/>";
                }
                else{
                    Logger::error("Unknown service when running Hydra: port " . $targetPort->getPort() . " on ip " . $targetIP);                                        
                    $message .= "Brute force not started for service: " . $targetService . " on ip: " . $targetIP . " with response code: " . $responseCode . "<br/>";
                }
            }
        }
        
        return $message;
    }

  
}

?>
