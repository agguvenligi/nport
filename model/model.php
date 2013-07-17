<?php

class exclusion{
    protected $id;
    //protected $ip;
    protected $description;
    //protected $network;
    //protected $broadcast;
    protected $rawipStart;
    protected $rawipEnd;

    public static $excludedStatus = 1;
    public static $includedStatus = 0;
    public static $allStatus = -1;

    public static function getStatusString($status){
        if($status == exclusion::$excludedStatus)
            return "excluded";
        else if($status == exclusion::$includedStatus)
            return "included";
        return "all";
    }

    public static function getStatus($status){
        if($status == 1)
            return exclusion::$excludedStatus;
        else if($status == 0)
            return exclusion::$includedStatus;
        return exclusion::$allStatus;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function setRawIpStart($rawipStart) {
        $this->rawipStart = $rawipStart;
    }

    public function getRawIpStart() {
        return $this->rawipStart;
    }

    public function setRawIpEnd($rawipEnd) {
        $this->rawipEnd = $rawipEnd;
    }

    public function getRawIpEnd() {
        return $this->rawipEnd;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getDescription() {
        return $this->description;
    }
}

class ip{
    protected $id;
    protected $ip;
    protected $creationdate;
    protected $ports;
    protected $excluded;
    protected $rawip;

    public function setExcluded($status) {
        $this->excluded = $status;
    }

    public function getExcluded() {
        return $this->excluded;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getRawIp() {
        return $this->rawip;
    }

    public function setRawIp($rawip) {
        $this->rawip = $rawip;
    }

    public function setIp($ip) {
        $this->ip = $ip;
    }

    public function getIp() {
        return $this->ip;
    }

    public function setCreationdate($creationdate) {
        $this->creationdate = $creationdate;
    }

    public function getCreationdate() {
        return $this->creationdate;
    }

    public function setPorts($ports){
        $this->ports = $ports;
    }

    public function getPorts() {
        return $this->ports;
    }
}

class port{
    protected $id;
    protected $ipid;
    protected $port;
    protected $status;
    protected $updatedate;

    public function setId($id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function setPort($port) {
        $this->port = $port;
    }

    public function getPort() {
        return $this->port;
    }

    public function setIpid($ipid) {
        $this->ipid = $ipid;
    }

    public function getIpid() {
        return $this->ipid;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setUpdate($updatedate) {
        $this->updatedate = $updatedate;
    }

    public function getUpdate() {
        return $this->updatedate;
    }
}

class ipport{
    protected $ip;
    protected $creationdate;
    protected $excluded;
    protected $rawip;

    protected $port;
    protected $status;
    protected $updatedate;

    public function setExcluded($status) {
        $this->excluded = $status;
    }

    public function getExcluded() {
        return $this->excluded;
    }

    public function getRawIp() {
        return $this->rawip;
    }

    public function setRawIp($rawip) {
        $this->rawip = $rawip;
    }

    public function setIp($ip) {
        $this->ip = $ip;
    }

    public function getIp() {
        return $this->ip;
    }

    public function setCreationdate($creationdate) {
        $this->creationdate = $creationdate;
    }

    public function getCreationdate() {
        return $this->creationdate;
    }

    public function setPort($port) {
        $this->port = $port;
    }

    public function getPort() {
        return $this->port;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setUpdate($updatedate) {
        $this->updatedate = $updatedate;
    }

    public function getUpdate() {
        return $this->updatedate;
    }

}

class status{
    public static $filtered = 2;
    public static $open = 1;
    public static $closed = 0;
    public static $all = -1;

    public static function getStatus($status){
        if($status == 2)
            return status::$filtered;
        if($status == 1)
            return status::$open;
        else if($status == 0)
            return status::$closed;
        return status::$all;
    }

    public static function getStatusString($status){
        if($status == status::$open)
            return "open";
        else if($status == status::$closed)
            return "closed";
        else if($status == status::$filtered)
            return "filtered";
        return "all";
    }
}

class nmapscanoutput{
    protected $starttime;
    protected $endtime;
    protected $exit;
    protected $uphosts;
    
    public function nmapscanoutput() {
        $this->starttime = "-";
        $this->endtime = "-";
        $this->exit = "-";
        $this->uphosts = "0";        
    }
    
    public function setStarttime($starttime) {
        $this->starttime = $starttime;
    }

    public function getStarttime() {
        return $this->starttime;
    }

    public function setEndtime($endtime) {
        $this->endtime = $endtime;
    }

    public function getEndtime() {
        return $this->endtime;
    }
    
    public function setExit($exit) {
        $this->exit = $exit;
    }

    public function getExit() {
        return $this->exit;
    }
    
    public function setUphosts($uphosts) {
        $this->uphosts = $uphosts;
    }

    public function getUphosts() {
        return $this->uphosts;
    }    
}

class hydrascan{
    protected $id;
    protected $portid;
    protected $pid;
    protected $description;
    protected $startdate;
    protected $outputfile;

    public function setId($id) {
        $this->id = $id;
    }

    public function getId() {
        return $this->id;
    }

    public function setPid($pid) {
        $this->pid = $pid;
    }

    public function getPid() {
        return $this->pid;
    }

    public function setPortId($portid) {
        $this->portid = $portid;
    }

    public function getPortId() {
        return $this->portid;
    }

    public function setStartdate($startdate) {
        $this->startdate = $startdate;
    }

    public function getStartdate() {
        return $this->startdate;
    }
    
    public function setDescription($description) {
        $this->description = $description;
    }

    public function getDescription() {
        return $this->description;
    } 
    
    public function setOutputFile($outputfile) {
        $this->outputfile = $outputfile;
    }

    public function getOutputFile() {
        return $this->outputfile;
    }      
}

class naslscript{
    protected $pluginId;
    protected $cvssBaseScore;
    protected $pluginName;
    protected $ports;

    public function setPluginId($id) {
        $this->pluginId = $id;
    }

    public function getPluginId() {
        return $this->pluginId;
    }

    public function setPluginName($pluginName) {
        $this->pluginName = $pluginName;
    }

    public function getPluginName() {
        return $this->pluginName;
    }
    
    public function setCvssBaseScore($cvssBaseScore) {
        $this->cvssBaseScore = $cvssBaseScore;
    }

    public function getCvssBaseScore() {
        return $this->cvssBaseScore;
    }
    
    public function setPorts($ports) {
        $this->ports = $ports;
    }

    public function getPorts() {
        return $this->ports;
    }    
}
?>
