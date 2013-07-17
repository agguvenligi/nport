<?php
    include 'base.php';
    require_once 'scanner/http.php';
    require_once 'HTMLPurifier.auto.php';
    require_once 'db/dao.php';
    require_once 'scanner/hydra.php';
    require_once 'utility/validator.php';
    
    if(Validator::isIPAuthentic($_SERVER["REMOTE_ADDR"]) && !empty($_GET['id']) && !empty($_GET['port'])){
        
        $ipDAO = new IpDAO(Configuration::$dbhost, Configuration::$dbuser, Configuration::$dbpassword, Configuration::$dbschema);    
    
        $ips = $ipDAO->getIpById(intval($_GET['id']));
        if(sizeof($ips) > 0){
            $targetService = Configuration::$ports_analyzed[intval($_GET['port'])];
            if(Hydra::isServiceHttpRelated($targetService)){            

                $protocol = "http://";
                if(Hydra::isServiceHttps($targetService))
                    $protocol = "https://";

                $url = $protocol . $ips[0]->getIp() . ":" . intval($_GET['port']) . "/";
                
                $h = new Http($url);
                $body = $h->getResponseBody();

                $secure = false;
                if(!empty($_GET['secure']))
                    $secure = true;
                
                if($secure){
                    $config = HTMLPurifier_Config::createDefault();
                    $config->set('Core.DefinitionCache', null);
                    $config->set('Cache.DefinitionImpl', null);
                    $purifier = new HTMLPurifier($config);
                    $body = $purifier->purify($body);
                }
                
                echo $body;
            }
        }
    }
?>

