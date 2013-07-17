<?php

require_once 'log/log.php';

class Configuration{
    // public static $ports_analyzed = array(21,22,23,25,80,443,445,1433,1521,3306,3389,8080);    
    public static $ports_analyzed = array(
        21 => "ftp",
        22 => "ssh",
        23 => "telnet",
        25 => "smtp",
        80 => "http-get",
        443 => "https-get",
        445 => "smb",
        1433 => "mssql",
        1521 => "oracle-listener",
        3306 => "mysql",
        3389 => "rdp",
        8080 => "http-get"        
    );
    public static $dbhost = "127.0.0.1";
    public static $dbuser = "root";
    public static $dbpassword = "toor";
    public static $dbschema = "nport";
    public static $nmap = "nmap";
    public static $hydra = "hydra";
    public static $whois = "https://apps.db.ripe.net/search/query.html?searchtext=";
    public static $authenticIPs = array("127.0.0.1", "::1");
    public static $resultLimit = 20;
    public static $errorLog = "/var/log/nport_error_log.txt";
    public static $logLevel = 3; /* DEBUG 3, INFO 2, ERROR 1*/
    public static $nmapOutputDirectory = "/var/nport/nmapoutput/";
    public static $hydraOutputDirectory = "/var/nport/hydraoutput/"; // usernames.txt and passwords.txt should be there
    public static $nessusPluginDirectory = "/opt/nessus/lib/nessus/plugins/";
    public static $nessusCVSSBaseScoreLimit = 9.5;
}
?>
