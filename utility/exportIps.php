<?php
    include 'base.php';
    require_once 'db/dao.php';
    require_once 'utility/validator.php';
    
    $portNumber = 80;
    if(isset($_GET["port"]))
        $portNumber = intval($_GET["port"]);

    $excludestatus = exclusion::$allStatus;
    if(isset($_GET["excludestatus"]))
        $excludestatus = exclusion::getStatus ($_GET["excludestatus"]);

    $openstatus = status::$all;
    if(isset($_GET["openstatus"]))
        $openstatus = status::getStatus ($_GET["openstatus"]);
    
    $ipDAO = new IpDAO(Configuration::$dbhost, Configuration::$dbuser, Configuration::$dbpassword, Configuration::$dbschema);
    
    if(Validator::isIPAuthentic($_SERVER["REMOTE_ADDR"])){
        $ips = $ipDAO->getIpsByPortAll($portNumber, $excludestatus, $openstatus);          
        if(sizeof($ips) > 0){
            header( 'Content-Type: text/csv' );
            header( 'Content-Disposition: attachment;filename=nport_export.csv' );            
            echocsv(array("Index", "IP Address", "Port", "Status", "Update"));
            $i = 0;
            foreach($ips as $ip) { 
                foreach($ip->getPorts() as $aPort){
                    if($aPort->getPort() == $portNumber){
                        $i++;
                        echocsv(array($i, $ip->getIp(), $aPort->getPort(), status::getStatusString($aPort->getStatus()),  $aPort->getUpdate()));
                    }
                }
            }
        }
    } 
    
    function echocsv( $fields )
    {
        $separator = '';
        foreach ( $fields as $field )
        {
        if ( preg_match( '/\\r|\\n|,|"/', $field ) )
        {
            $field = '"' . str_replace( '"', '""', $field ) . '"';
        }
        echo $separator . $field;
        $separator = ',';
        }
        echo "\r\n";
    }    
    
?>
