<?php
    include 'base.php';
    require_once 'db/dao.php';
    require_once 'utility/validator.php';
    
    $ipDAO = new IpDAO(Configuration::$dbhost, Configuration::$dbuser, Configuration::$dbpassword, Configuration::$dbschema);
    
    $postMessage = "";
    
    if(Validator::isIPAuthentic($_SERVER["REMOTE_ADDR"]) && !empty($_GET['deleteHydraScanId'])){
        $currHydrascans = $ipDAO->getHydraScanById(intval($_GET['deleteHydraScanId']));        
        if(sizeof($currHydrascans) > 0){        
            if($ipDAO->deleteHydraScan($currHydrascans[0]->getId()) > 0){
                unlink($currHydrascans[0]->getOutputFile());
                $postMessage = "Scan is deleted successfully";
            }
            else
                $postMessage = "Scan could not be deleted";
        }
    }

    if(Validator::isIPAuthentic($_SERVER["REMOTE_ADDR"]) && !empty($_GET['stopHydraScanId'])){
        $currHydrascans = $ipDAO->getHydraScanById(intval($_GET['stopHydraScanId']));        
        if(sizeof($currHydrascans) > 0)
            exec("sudo kill " . $currHydrascans[0]->getPid());        
    }

    $fileContent = array();
    if(Validator::isIPAuthentic($_SERVER["REMOTE_ADDR"]) && !empty($_GET['viewHydraScanOutputId'])){
        $currHydrascans = $ipDAO->getHydraScanById(intval($_GET['viewHydraScanOutputId']));
        if(sizeof($currHydrascans) > 0){
            if (file_exists($currHydrascans[0]->getOutputFile())) 
                $fileContent = file($currHydrascans[0]->getOutputFile());
            else
                Logger::info("Non existing file name " . $currHydrascans[0]->getOutputFile());                                
        }
    }
    
    if(Validator::isIPAuthentic($_SERVER["REMOTE_ADDR"]))
        $hydrascans = $ipDAO->getHydraScans();   
    
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Welcome to NPort!</title>
        <link rel="stylesheet" type="text/css" href="../content/style.css" />
    </head>
    <body>  
        <div class="content">            
            <?php include "menu.php" ?>
            <div class="exclusionmessage">
                <?php echo $postMessage ?>
            </div>
            <form method="GET">
                <?php 
                    if(sizeof($hydrascans) > 0) {
                ?>
                    <table class="list" cellpadding="0px" cellspacing="0px" border="0px">
                        <thead>
                            <th> </th>
                            <th> PID </th>
                            <th> Port </th>
                            <th> IP Address </th>
                            <th> Start Date </th>
                            <th> Status </th>
                            <th> Description </th>
                            <!--
                            <th> Output File Name </th>
                            -->
                            <th>  </th>                            
                        </thead>
                        <tbody>
                            <?php 
                            $index = 0;
                            foreach($hydrascans as $hydrascan){
                                $index++;
                                $class = ($index%2==0)?"even":"odd";
                                
                                // here try to understand if the hydra scan was fruitful
                                if (file_exists($hydrascan->getOutputFile())) {
                                    $tmpFileContent = file_get_contents($hydrascan->getOutputFile());
                                    if(strpos($tmpFileContent, "login:") && strpos($tmpFileContent, "password:")) 
                                        $class = "selected";
                                }
                                
                                // here try to understand if the hydra scan is still running
                                $pState = array();
                                exec("sudo ps " . $hydrascan->getPid(), $pState);
                                if((count($pState) >= 2))
                                    $class = "running";
                                
                            ?>            
                                <tr class="<?php echo $class; ?>">
                                    <td>
                                        <?php echo $index; ?>
                                    </td>
                                    <td>
                                        <?php echo $hydrascan->getPid() ; ?>
                                    </td>
                                    <td>
                                        <?php 
                                            $ports = $ipDAO->getPortById($hydrascan->getPortId());
                                            if(sizeof($ports) > 0)
                                                echo Configuration::$ports_analyzed[$ports[0]->getPort()];
                                            else
                                                echo "-";
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                            if(sizeof($ports) > 0){
                                                $ips = $ipDAO->getIpById($ports[0]->getIpid());
                                                if(sizeof($ips) > 0)
                                                    echo $ips[0]->getIp();
                                                else
                                                    echo "-";
                                            }
                                            else
                                                echo "-";
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo htmlentities($hydrascan->getStartdate()); ?>
                                    </td>
                                    <td>
                                        <?php
                                            if((count($pState) >= 2))
                                                    echo "Running - <a href='?stopHydraScanId=" . $hydrascan->getId() . "'>Stop</a>";
                                            else
                                                    echo "Stopped";
                                        ?>                                        
                                    </td>
                                    <td>
                                        <?php echo htmlentities($hydrascan->getDescription()); ?>
                                    </td>
                                    <!--
                                    <td>
                                        <?php echo htmlentities(basename($hydrascan->getOutputFile())); ?>
                                    </td>
                                    -->
                                    <td>
                                        <a href="?deleteHydraScanId=<?php echo $hydrascan->getId(); ?>">DELETE</a>
                                        &nbsp;
                                        <a href="?viewHydraScanOutputId=<?php echo $hydrascan->getId(); ?>">VIEW</a>
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
            
            <?php
            if(!empty($fileContent)){
            ?>
                <div class="filecontent">
                    <h3>Hydra Output File Content</h3>
                    <?php 
                        foreach($fileContent as $line)
                            echo htmlentities($line) . '<br/>'; 
                    ?>
                </div>
            <?php
            }
            ?>
        </div>
    </body>
</html>