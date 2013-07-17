<?php

require_once 'db/dao.php';
require_once 'config.php';

class DBImporter{
    
    public static function loadNmapToModel($file){
        Logger::debug($file . " nmap xml file is being loaded");                                        
        $nmaprun = simplexml_load_file($file);
        $scanoutput = new nmapscanoutput();
        
        if(!empty($nmaprun)){
            $scanoutput->setStarttime($nmaprun['startstr']);
            if(!empty($nmaprun->runstats) && sizeof($nmaprun->runstats->finished) > 0 && sizeof($nmaprun->runstats->hosts) > 0){
                $finished = $nmaprun->runstats->finished[0];
                $hosts = $nmaprun->runstats->hosts[0];
                $scanoutput->setEndtime($finished['timestr']);
                $scanoutput->setExit($finished['exit']);
                $scanoutput->setUphosts($hosts['up']);
            }
        }
        else
            Logger::debug($file . " nmap xml file is empty");                                        
        
        return $scanoutput;
    }
    
    public static function loadNmapToDB($file, $scannedPorts){
        Logger::debug($file . " nmap xml file is being loaded");                                        
        $nmaprun = simplexml_load_file($file);
        $ips = array();
        foreach ($nmaprun->host as $host){
            $ports = array();
            foreach ($host->ports->port as $port){                    
                $found = false;
                foreach($scannedPorts as $aScannedPort){
                    if(intval($port['portid']) == $aScannedPort){
                        $found = true;
                        $aPort = new port();
                        $aPort->setPort(intval($port['portid']));
                        if(strcmp($port->state['state'],"open") == 0)
                            $aPort->setStatus(status::getStatus(status::$open));
                        else if(strcmp($port->state['state'], "filtered") == 0)
                            $aPort->setStatus(status::getStatus(status::$filtered));
                        else
                            $aPort->setStatus(status::getStatus(status::$closed));
                        $ports[] = $aPort;
                    }
                }
                if(!$found){
                    $aPort = new port();
                    $aPort->setPort(intval($port['portid']));
                    if(strcmp($port->state['state'],"open") ==  0)
                        $aPort->setStatus(status::getStatus(status::$open));
                    else if(strcmp($port->state['state'], "filtered") == 0)
                        $aPort->setStatus(status::getStatus(status::$filtered));
                    else
                        $aPort->setStatus(status::getStatus(status::$closed));
                    $ports[] = $aPort;                        
                }
            }
            $anIp = new ip();
            $anIp->setIp($host->address['addr']);
            $anIp->setPorts($ports);
            $ips[] = $anIp;            
        }
        Logger::debug($file . " nmap xml file loaded and model is formed, now it will be saved to db");                                                
        $ipDAO = new IpDAO(Configuration::$dbhost, Configuration::$dbuser, Configuration::$dbpassword, Configuration::$dbschema);
        foreach($ips as $anIp)
            $ipDAO->save($anIp);
    }    
}

?>
