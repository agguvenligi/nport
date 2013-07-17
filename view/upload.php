<?php
    include 'base.php';   
    require_once 'utility/validator.php';
    require_once 'utility/dbimporter.php';
    
    if(!Validator::isIPAuthentic($_SERVER["REMOTE_ADDR"]))
        return;
    
    $postMessage = "";
    
    if(!empty($_FILES['file']['name'])){
        $date1 = time();    
        DBImporter::loadNmapToDB($_FILES['file']['tmp_name'], array_keys(Configuration::$ports_analyzed));
        
        $ipDAO = new IpDAO(Configuration::$dbhost, Configuration::$dbuser, Configuration::$dbpassword, Configuration::$dbschema);
        $ipDAO->updateOpenPortHistory(); // update open port history
        $date2 = time();
        $postMessage = "Success processing: " . $_FILES['file']['name'] . " in about " . ($date2 - $date1) / 60 . " minutes" ;
        Logger::info($postMessage);                    
    }
?> 
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Welcome to NPort!</title>
        <link rel="stylesheet" type="text/css" href="../content/style.css" />           
    </head>
    <body>
        <div class="content" >
            <?php include "menu.php" ?>            
            <div class="message">
                <?php echo $postMessage; ?>
            </div>
        </div>
    </body>
</html>        