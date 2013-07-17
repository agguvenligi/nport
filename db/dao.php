<?php

require_once 'model/model.php';
require_once 'utility/network.php';
require_once 'log/log.php';

class IpDAO {
    protected $connect;
    protected $db;

    public function IpDAO($host, $username, $password, $database) {
        $this->connect = mysql_connect($host, $username, $password);
        $this->db = mysql_select_db($database);
    }
    
    protected function executeForIp($sql) {
        $res = mysql_query($sql, $this->connect) or die(mysql_error());

        $ips = array();
        if(mysql_num_rows($res) > 0) {
            for($i = 0; $i < mysql_num_rows($res); $i++) {
                $row = mysql_fetch_assoc($res);
                $ips[$i] = new ip();
                $ips[$i]->setId($row["id"]);
                $ips[$i]->setCreationdate($row["creationdate"]);
                $ips[$i]->setIp($row["ip"]);
                $ips[$i]->setRawIp($row["rawip"]);
                $ips[$i]->setExcluded($row["excluded"]);
            }
        }
        return $ips;
    }
    
    protected function executeForIpPort($sql) {
        $res = mysql_query($sql, $this->connect) or die(mysql_error());

        $ipports = array();
        if(mysql_num_rows($res) > 0) {
            for($i = 0; $i < mysql_num_rows($res); $i++) {
                $row = mysql_fetch_assoc($res);
                $ipports[$i] = new ipport();
                $ipports[$i]->setCreationdate($row["creationdate"]);
                $ipports[$i]->setIp($row["ip"]);
                $ipports[$i]->setRawIp($row["rawip"]);
                $ipports[$i]->setExcluded($row["excluded"]);
                $ipports[$i]->setUpdate($row["update"]);
                $ipports[$i]->setPort($row["port"]);
                $ipports[$i]->setStatus($row["status"]);                
            }
        }
        return $ipports;
    }
    
    protected function executeForExclusion($sql) {
        $res = mysql_query($sql, $this->connect) or die(mysql_error());

        $exclusions = array();
        if(mysql_num_rows($res) > 0) {
            for($i = 0; $i < mysql_num_rows($res); $i++) {
                $row = mysql_fetch_assoc($res);
                $exclusions[$i] = new exclusion();
                $exclusions[$i]->setId($row["id"]);
                $exclusions[$i]->setDescription($row["description"]);
                $exclusions[$i]->setRawIpStart($row["rawipstart"]);
                $exclusions[$i]->setRawIpEnd($row["rawipend"]);
            }
        }
        return $exclusions;
    }
    
    protected function executeForHydraScan($sql) {
        $res = mysql_query($sql, $this->connect) or die(mysql_error());

        $hydrascans = array();
        if(mysql_num_rows($res) > 0) {
            for($i = 0; $i < mysql_num_rows($res); $i++) {
                $row = mysql_fetch_assoc($res);
                $hydrascans[$i] = new hydrascan();
                $hydrascans[$i]->setId($row["id"]);
                $hydrascans[$i]->setPortId($row["portid"]);
                $hydrascans[$i]->setDescription($row["description"]);
                $hydrascans[$i]->setStartdate($row["startdate"]);
                $hydrascans[$i]->setPid($row["pid"]);
                $hydrascans[$i]->setOutputFile($row["outputfile"]);                
            }
        }
        return $hydrascans;
    }

    protected function executeForPort($sql) {
        $res = mysql_query($sql, $this->connect) or die(mysql_error());

        $ports = array();
        if(mysql_num_rows($res) > 0) {
            for($i = 0; $i < mysql_num_rows($res); $i++) {
                $row = mysql_fetch_assoc($res);
                $ports[$i] = new port();
                $ports[$i]->setId($row["id"]);
                $ports[$i]->setUpdate($row["update"]);
                $ports[$i]->setIpid($row["ipid"]);
                $ports[$i]->setPort($row["port"]);
                $ports[$i]->setStatus($row["status"]);
            }
        }
        return $ports;
    }    

    protected function executeForCount($sql) {
        $res = mysql_query($sql, $this->connect) or die(mysql_error());

        if(mysql_num_rows($res) > 0) {
            for($i = 0; $i < mysql_num_rows($res); $i++) {
                $row = mysql_fetch_assoc($res);
                return $row["total"];
            }
        }
    }   

    public function getIps() {
        $sql = "SELECT * FROM ips";
        $ips = $this->executeForIp($sql);
        $this->getPorts($ips);
        return $ips;
    }

    public function getIpPortsInRange($ipstart, $ipend, $index, $limit){
        $sql = "SELECT i.ip, i.creationdate, i.rawip, i.excluded, p.port, p.status, p.update " . 
               "FROM ips as i, ports as p where i.id = p.ipid and rawip >= INET_ATON('" . $ipstart. "') and rawip <= INET_ATON('" . $ipend . "') limit " . $index . ", " . $limit;
        $ipports = $this->executeForIpPort($sql);
        return $ipports;        
    }

    public function getIpPortsInRangeTotal($ipstart, $ipend){
        $sql = "SELECT count(*) as total FROM ips as i, ports as p where i.id = p.ipid and rawip >= INET_ATON('" . $ipstart . "') and rawip <= INET_ATON('" . $ipend . "')";
        return $this->executeForCount($sql);
    }
    
    public function getExcludedIps() {
        $sql = "SELECT * FROM ips where excluded = 1";
        $ips = $this->executeForIp($sql);
        return $ips;
    }
    
    public function getIncludedIps() {
        $sql = "SELECT * FROM ips where excluded = 0";
        $ips = $this->executeForIp($sql);
        return $ips;
    }

    public function getExclusions() {
        $sql = "SELECT * FROM exclusions";
        $exclusions = $this->executeForExclusion($sql);
        return $exclusions;
    }    
    
    public function getHydraScans() {
        $sql = "SELECT * FROM hydrascans order by startdate desc ";
        $hydrascans = $this->executeForHydraScan($sql);
        return $hydrascans;
    }    

    public function getPorts($ips){
        foreach($ips as $ip){
            $sql = "SELECT * FROM ports where ipid = " . intval($ip->getId());
            $ports = $this->executeForPort($sql);
            $ip->setPorts($ports);
        }        
    }

    public function getPortById($id){
        $sql = "SELECT * FROM ports where id = " . intval($id);
        $ports = $this->executeForPort($sql);
        return $ports;
    }

    public function getIpsByPort($port, $excludestatus, $openstatus, $index, $limit) {
        
        $sql = "SELECT i.id,i.ip,i.creationdate,i.excluded,i.rawip FROM ips i, ports p WHERE i.id = p.ipid and p.port = " . intval($port) . " order by p.`update` desc limit " . intval($index) . ", " . $limit;
        
        if($excludestatus != exclusion::$allStatus && $openstatus != status::$all)
            $sql = "SELECT i.id,i.ip,i.creationdate,i.excluded,i.rawip  FROM ips i, ports p WHERE i.id = p.ipid and p.status = " 
                                    . $openstatus . " and i.excluded = " . intval($excludestatus) . " and p.port = ". intval($port) . " order by p.`update` desc limit " . intval($index) . "," . $limit;        
        else if($excludestatus != exclusion::$allStatus && $openstatus == status::$all)
            $sql = "SELECT i.id,i.ip,i.creationdate,i.excluded,i.rawip  FROM ips i, ports p WHERE i.id = p.ipid and i.excluded = " 
                                                                           . intval($excludestatus) . " and p.port = ". intval($port) . " order by p.`update` desc limit " . intval($index) . ", " . $limit;        
        else if($excludestatus == exclusion::$allStatus && $openstatus != status::$all)
            $sql = "SELECT i.id,i.ip,i.creationdate,i.excluded,i.rawip  FROM ips i, ports p WHERE i.id = p.ipid and p.status = " 
                                                                            . intval($openstatus) . " and p.port = ". intval($port) . " order by p.`update` desc limit " . intval($index) . ", " . $limit;        
       
        $ips = $this->executeForIp($sql);
        $this->getPorts($ips);
        return $ips;
    }    

    public function getIpsByPortAll($port, $excludestatus, $openstatus) {
        
        $sql = "SELECT i.id,i.ip,i.creationdate,i.excluded,i.rawip FROM ips i, ports p WHERE i.id = p.ipid and p.port = " . intval($port) . " order by p.`update` desc";
        
        if($excludestatus != exclusion::$allStatus && $openstatus != status::$all)
            $sql = "SELECT i.id,i.ip,i.creationdate,i.excluded,i.rawip  FROM ips i, ports p WHERE i.id = p.ipid and p.status = " 
                                    . $openstatus . " and i.excluded = " . intval($excludestatus) . " and p.port = ". intval($port) . " order by p.`update` desc";        
        else if($excludestatus != exclusion::$allStatus && $openstatus == status::$all)
            $sql = "SELECT i.id,i.ip,i.creationdate,i.excluded,i.rawip  FROM ips i, ports p WHERE i.id = p.ipid and i.excluded = " 
                                                                           . intval($excludestatus) . " and p.port = ". intval($port) . " order by p.`update` desc";        
        else if($excludestatus == exclusion::$allStatus && $openstatus != status::$all)
            $sql = "SELECT i.id,i.ip,i.creationdate,i.excluded,i.rawip  FROM ips i, ports p WHERE i.id = p.ipid and p.status = " 
                                                                            . intval($openstatus) . " and p.port = ". intval($port) . " order by p.`update` desc";        
       
        $ips = $this->executeForIp($sql);
        $this->getPorts($ips);
        return $ips;
    }    
    
    public function getIpsByPortTotal($port, $excludestatus, $openstatus) {
        
        $sql = "SELECT count(*) as total FROM ips i, ports p WHERE i.id = p.ipid and p.port = " . intval($port);
        
        if($excludestatus != exclusion::$allStatus && $openstatus != status::$all)
            $sql = "SELECT count(*) as total FROM ips i, ports p WHERE i.id = p.ipid and p.status = " 
                                    . $openstatus . " and i.excluded = " . intval($excludestatus) . " and p.port = ". intval($port);
        else if($excludestatus != exclusion::$allStatus && $openstatus == status::$all)
            $sql = "SELECT count(*) as total FROM ips i, ports p WHERE i.id = p.ipid and i.excluded = " 
                                                                           . intval($excludestatus) . " and p.port = ". intval($port);
        else if($excludestatus == exclusion::$allStatus && $openstatus != status::$all)
            $sql = "SELECT count(*) as total FROM ips i, ports p WHERE i.id = p.ipid and p.status = " 
                                                                            . intval($openstatus) . " and p.port = ". intval($port);
       
        return $this->executeForCount($sql);
    }    

    public function getIpById($ipId) {
        $sql = "SELECT * FROM ips WHERE id = ". intval($ipId);
        $ips = $this->executeForIp($sql);
        $this->getPorts($ips);
        return $ips;
    }
    
    public function getHydraScanByOutputFileName($outputfile) {
        $sql = "SELECT * FROM hydrascans WHERE outputfile = '" . $outputfile . "'";
        $hydrascans = $this->executeForExclusion($sql);
        return $hydrascans;
    }    

    public function getExclusionByAddress($ipstart, $ipend) {
        $sql = "SELECT * FROM exclusions WHERE rawipstart = INET_ATON('" . $ipstart . "') and rawipend = INET_ATON('" . $ipend . "')";
        $exclusions = $this->executeForExclusion($sql);
        return $exclusions;
    }    

    public function getExclusionById($exclusionId) {
        $sql = "SELECT * FROM exclusions WHERE id = ". intval($exclusionId);
        $exclusions = $this->executeForExclusion($sql);
        return $exclusions;
    }    

    public function getIpByAddress($ip) {
        $sql = "SELECT * FROM ips WHERE ip = '". $ip . "'";
        $ips = $this->executeForIp($sql);
        $this->getPorts($ips);
        return $ips;
    }        
    
    public function getHydraScanById($id) {
        $sql = "SELECT * FROM hydrascans WHERE id = ". intval($id);
        $hydrascans = $this->executeForHydraScan($sql);
        return $hydrascans;
    }     

    public function save($ip) { 
        
        $currIp = $this->getIpByAddress($ip->getIp()); 
        if(sizeof($currIp) == 1) {
            foreach($ip->getPorts() as $aScannedPort){
                $found = false;
                foreach($currIp[0]->getPorts() as $existingPort){
                    if($existingPort->getPort() == $aScannedPort->getPort()){
                        $found  = true;
                        if($existingPort->getStatus() != $aScannedPort->getStatus()){
                            $sql = "UPDATE ports SET ".
                                "status = " . status::getStatus($aScannedPort->getStatus()) . ", " .
                                "`update` = '" . date("Y-m-d H:i:s") . "' ".
                                "WHERE id = " . $existingPort->getId();
                            mysql_query($sql, $this->connect) or die(mysql_error());
                            Logger::info($currIp[0]->getIp() . ":" . $aScannedPort->getPort() . " saved as " . status::getStatusString($aScannedPort->getStatus()));
                        }
                        break;
                    }
                }
                if(!$found){
                    $sql = "INSERT INTO ports (port, ipid, status, `update`) VALUES (".
                                    intval($aScannedPort->getPort()) . ", " .
                                    $currIp[0]->getId() . ", " .
                                    status::getStatus($aScannedPort->getStatus()) . ", " .
                                    "'" . date("Y-m-d H:i:s") . "')";
                    mysql_query($sql, $this->connect) or die(mysql_error());
                    Logger::info($currIp[0]->getIp() . ":" . $aScannedPort->getPort() . " inserted as " . status::getStatusString($aScannedPort->getStatus()));                                        
                }
            }            
        }
        else {
            // when a new ip is added, it's compared against the exclusion list to
            // understand if it's excluded or not
            $excluded = 0;            
            if($this->isIPInExclusions($ip->getIP()))
                $excluded = 1;

            $sql = "INSERT INTO ips (ip, creationdate, excluded, rawip) VALUES('".
                                     $ip->getIp() . "', '" . date("Y-m-d H:i:s") . "'," . 
                                     $excluded .  ", INET_ATON('" . $ip->getIp() . "'))";
            mysql_query($sql, $this->connect) or die(mysql_error());            
            error_log($ip->getIP() . " inserted" , 3, Configuration::$errorLog);                                                    
            $newIpId = mysql_insert_id();            
            foreach($ip->getPorts() as $newPort){
                $sql = "INSERT INTO ports (port, ipid, status, `update`) VALUES (".
                                intval($newPort->getPort()) . ", " .
                                $newIpId . ", " .
                                status::getStatus($newPort->getStatus()) . ", " .
                                "'" . date("Y-m-d H:i:s") . "')";
                mysql_query($sql, $this->connect) or die(mysql_error());
                Logger::info($ip->getIp() . ":" . $newPort->getPort() . " inserted as " . status::getStatusString($newPort->getStatus()));                                                        
            }
        }
    }
    
    public function delete($ip) {
        $affectedRows = 0;

        if($ip->getId() != "") {
            $sql = "DELETE FROM ports WHERE ipid = " . $ip->getId();
            mysql_query($sql, $this->connect) or die(mysql_error());

            $sql = "DELETE FROM ips WHERE id= " . $ip->getId();
            mysql_query($sql, $this->connect) or die(mysql_error());
            $affectedRows = mysql_affected_rows();
        }

        return $affectedRows;
    }      

    public function deleteHydraScan($id) {
        $affectedRows = 0;

        $sql = "DELETE FROM hydrascans WHERE id= " . $id;
        mysql_query($sql, $this->connect) or die(mysql_error());
        $affectedRows = mysql_affected_rows();

        return $affectedRows;
    } 
    
    public function saveHydraScan($pid, $outputfile, $portid, $description){
        $affectedRows = 0;
        $currHydraScan = $this->getHydraScanByOutputFileName($outputfile); 
        
        if(sizeof($currHydraScan) == 0) {       
            $sql = "INSERT INTO hydrascans (portid, pid, outputfile, startdate, description) VALUES(" . 
                                            intval($portid) . "," . intval($pid) . ",'" . $outputfile . 
                                            "','" . date("Y-m-d H:i:s") . "','" . $description . "')";

            mysql_query($sql, $this->connect) or die(mysql_error());
            $affectedRows = mysql_affected_rows();

            Logger::info("An hydrascan is saved with output file name: " . $outputfile . " pid: " . $pid);            
        }
        
        return $affectedRows;        
    }
        
    public function saveExclusion($ipstart, $ipend, $description){
        $affectedRows = 0;
        $currExclusion = $this->getExclusionByAddress($ipstart, $ipend); 
        
        if(sizeof($currExclusion) == 0) {       
            $sql = "INSERT INTO exclusions (description, rawipstart, rawipend) VALUES('" . 
                                            $description . "', INET_ATON('" . $ipstart . 
                                            "'), INET_ATON('" . $ipend . "'))";

            mysql_query($sql, $this->connect) or die(mysql_error());
            $affectedRows = mysql_affected_rows();

            // when an exclusion is added, check the exclusion status of all included IPs against this newly added exclusion
            if($affectedRows > 0){
                $sql = "UPDATE ips SET excluded = 1 WHERE excluded = 0 and rawip >= INET_ATON('" . 
                                            $ipstart . "') and rawip <= INET_ATON('" . $ipend . "')";
                mysql_query($sql, $this->connect) or die(mysql_error());                    
            }
            Logger::info($ipstart . " - " . $ipend . " exclusion range successfully saved");            
        }
        
        return $affectedRows;        
    }

    public function deleteExclusion($exclusionId){
        $affectedRows = 0;

        $sql = "DELETE FROM exclusions WHERE id = " . $exclusionId;
        mysql_query($sql, $this->connect) or die(mysql_error());
        $affectedRows = mysql_affected_rows();

        // when an exclusion is deleted, check the exclusion status of all excluded IPs against all remained exclusions    
        if($affectedRows > 0){
            $sql = "update ips i set excluded = 0 where excluded = 1 and (select count(*) from exclusions where i.rawip >= rawipstart and i.rawip <= rawipend) = 0";
            mysql_query($sql, $this->connect) or die(mysql_error());                    
        }
        
        return $affectedRows;                
    }
    
    public function openPortUtilization($ports_analyzed){
        $ports = array();
        
        foreach($ports_analyzed as $k=>$v)
            $ports[$v] = 0;
        
        foreach($ports as $k=>$v){
            $sql = "SELECT count(*) as total  FROM ports as p, ips as i where port = " . $k . " and i.id = p.ipid and p.status = 1 and i.excluded = 0";
            $ports[$k] = $this->executeForCount($sql);        
        } 
        
        foreach($ports as $k=>$v){
            if($v == 0)
                $ports[$k] = 0.005;
        }

        return $ports;         
    }
    
    public function openPortTrend(){        
        $sql = "SELECT `update`, value from openporthistory where `update` >= '2012-12-13' order by `update` desc";
        $res = mysql_query($sql, $this->connect) or die(mysql_error());

        $trend = array();
        if(mysql_num_rows($res) > 0) {
            for($i = 0; $i < mysql_num_rows($res); $i++) {
                $row = mysql_fetch_assoc($res);
                $trend[$row["update"]] = $row["value"];
            }
        }
        ksort($trend); // php :) crazy by key
        return $trend;        
    }
    
    public function updateOpenPortHistory(){        
        $sql = "SELECT count(*) as total FROM ports as p, ips as i where status = " . status::$open . " and i.id = p.ipid and i.excluded = 0";
        $total = $this->executeForCount($sql);   
  
        $sql = "INSERT INTO openporthistory (`update`, value) VALUES('". date("Y-m-d H:i:s") . "', " . $total . ")";
        mysql_query($sql, $this->connect) or die(mysql_error());
    }    

    public function isIPInExclusions($ip){
        $sql = "select count(*) as total from exclusions where INET_ATON('" . $ip . "') >= rawipstart and INET_ATON('" . $ip . "') <= rawipend";
        $total = $this->executeForCount($sql);   
        if($total > 0)
            return true;
        return false;
    } 
    
    public function isPortBruteForced($portid){
        $sql = "select count(*) as total from hydrascans as h, ports as p where p.id = h.portid and p.id = "  . intval($portid) ;
        $total = $this->executeForCount($sql);   
        if($total > 0)
            return true;
        return false;
    }     
    
    public function getPortDifference($endDate){
        
        if (($timestamp = strtotime($endDate)) === false)
            $endDate = date("Y-m-d H:i:s");
        
        $sql = "select `update` from openporthistory where `update` < '" . $endDate . "'";
        
        $res = mysql_query($sql, $this->connect) or die(mysql_error());

        $trend = array();
        if(mysql_num_rows($res) > 0) {
            for($i = 0; $i < mysql_num_rows($res); $i++) {
                $row = mysql_fetch_assoc($res);
                $trend[] = $row["update"];
            }
        }

        if(sizeof($trend) <= 0)
            return array();
        
        $beginDate = $trend[sizeof($trend) - 1];
        
        $sql = "select i.ip, i.creationdate, i.rawip, i.excluded, p.port, p.status, p.update " .
                "FROM ips as i, ports as p where i.id = p.ipid and " . 
                " p.`update` > '" . $beginDate . "' and p.`update` <= '" . $endDate . "'" . 
                //" and i.excluded = 0 and p.status = " . status::$open . " order by p.status";
                " and i.excluded = 0 order by p.status, i.ip";
        //echo "$sql";
        $ipports = $this->executeForIpPort($sql);
        
        return $ipports;          
    }
}

?>

