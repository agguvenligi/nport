<?php
    include 'base.php';
    require_once 'db/dao.php';
    require_once 'utility/dbimporter.php';

    $filename = "/var/nport/nmapoutput/ip_block_nmap.xml";

    $_SERVER["REMOTE_ADDR"] = "127.0.0.1";

    if (file_exists($filename)) {
        $date1 = time();
        DBImporter::loadNmapToDB($filename, array_keys(Configuration::$ports_analyzed));

        $ipDAO = new IpDAO(Configuration::$dbhost, Configuration::$dbuser, Configuration::$dbpassword, Configuration::$dbschema);
        $ipDAO->updateOpenPortHistory(); // update open port history
        $date2 = time();
        $postMessage = "Success processing: " . $filename . " in about " . ($date2 - $date1) / 60 . " minutes" ;
        unlink($filename);
        Logger::info($postMessage);
    }
    else
        Logger::info("Non existing file name " . $filename);


?>
