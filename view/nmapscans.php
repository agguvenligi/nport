<?php
    include 'base.php';
    require_once 'db/dao.php';
    require_once 'utility/dbimporter.php';
    
    $postMessage = "";

    if(isset($_GET["process"]) && isset($_SESSION["NMAPOUTPUT" . intval($_GET["process"])])){            
        $filename = $_SESSION["NMAPOUTPUT" . intval($_GET["process"])];
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

    }   

    $nmapresults = glob(Configuration::$nmapOutputDirectory . "*.xml");
    
    if(!empty($nmapresults)){        
        $index = 0;
        foreach($nmapresults as $nmapresult){
            $index++;
            $_SESSION["NMAPOUTPUT" . $index] = $nmapresult;
        }
    }
    
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Welcome to NPort!</title>
        <link rel="stylesheet" type="text/css" href="../content/style.css" />
        <script>
            function process(val){
                var process = document.getElementById("process");
                process.value = val;
                document.forms[0].submit();
            }
        </script>
    </head>
    <body>  
        <div class="content">            
            <?php include "menu.php" ?>
            <div class="exclusionmessage">
                <?php echo $postMessage ?>
            </div>
            <form method="GET">
                <input type="hidden" value="" name="process" id="process" />

                <?php 
                    if(sizeof($nmapresults) > 0) {
                ?>
                    <table class="list" cellpadding="0px" cellspacing="0px" border="0px">
                        <thead>
                            <th> </th>
                            <th> File Name </th>
                            <th> Scan Start Time </th>
                            <th> Scan End Time </th>
                            <th> Exit Status </th>
                            <th> Up Hosts </th>
                            <th>  </th>
                        </thead>
                        <tbody>
                            <?php 
                            $index = 0;
                            foreach($nmapresults as $nmapresult){
                                $scanoutput = DBImporter::loadNmapToModel($nmapresult);
                                $index++;
                            ?>            
                                <tr class="<?php echo $index%2==0?"even":"odd"; ?>">
                                    <td>
                                        <?php echo $index; ?>
                                    </td>
                                    <td>
                                        <?php echo basename($nmapresult); ?>
                                    </td>
                                    <td>
                                        <?php echo $scanoutput->getStarttime(); ?>
                                    </td>                                   
                                    <td>
                                        <?php echo $scanoutput->getEndtime(); ?>
                                    </td>                             
                                    <td>
                                        <?php echo $scanoutput->getExit(); ?>
                                    </td>                                                                       
                                    <td>
                                        <?php echo $scanoutput->getUphosts(); ?>
                                    </td>                                   
                                    <td>
                                        <a href="javascript:process(<?php echo $index; ?>)">PROCESS</a>
                                    </td>
                                </tr>    
                            <?php 
                            } 
                            ?>    
                        </tbody>
                    </table>
                <?php 
                    }                    
                ?>                
            </form>
        </div>
    </body>
</html>