<?php
    include 'base.php';
    require_once 'utility/validator.php';
    
    if(!Validator::isIPAuthentic($_SERVER["REMOTE_ADDR"]))
        return;
    
    $msg = "";
    
    $ipDAO = new IpDAO(Configuration::$dbhost, Configuration::$dbuser, Configuration::$dbpassword, Configuration::$dbschema);
    if(isset($_GET["exclusionId"])){
        if($ipDAO->deleteExclusion($_GET["exclusionId"]) > 0){
            $ipDAO->updateOpenPortHistory(); // update open port history        
            $msg += "Range was successfully deleted<br/>";
        }
        else
            $msg += "Range could not be deleted<br/>";        
    }
       
    if(isset($_POST["startip"]) && isset($_POST["endip"]) 
       && Validator::isValidIpAddress($_POST["startip"]) && Validator::isValidIpAddress($_POST["endip"])){        
        if($ipDAO->saveExclusion($_POST["startip"], $_POST["endip"], $_POST["description"]) > 0){
            $ipDAO->updateOpenPortHistory(); // update open port history        
            $msg += "Range was successfully added<br/>";            
        }
        else
            $msg += "Range could not be added<br/>";        
    }
    
    $exclusions = $ipDAO->getExclusions(); 
    
    $msg += sizeof($exclusions) . " exclusions are found"
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
            <form method="POST">
                <table class="formtable" cellpadding="0px" cellspacing="0px" border="0px">
                    <tr>
                        <td class="label">Start IP Address:</td>
                        <td><input type="text" name="startip" style="width:200px"/></td>
                    </tr>
                    <tr>
                        <td class="label">End IP Address:</td>
                        <td><input type="text" name="endip" style="width:200px"/></td>
                    </tr>
                    <tr>
                        <td class="label">Description:</td>
                        <td><input type="text" name="description" style="width: 400px"/></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center">
                            <input type="submit" value="ADD RANGE" />
                        </td>
                    </tr>
                </table>
            </form>
            
            
            <div class="exclusionmessage">
                <?php $msg ?>
            </div>
            
            <table class="list" cellpadding="0px" cellspacing="0px" border="0px">
                <thead>
                    <th> </th>
                    <th> Start IP Address </th>
                    <th> End IP Address </th>
                    <th> Description </th>
                    <th> Operation </th>
                </thead>
                <tbody>
                    <?php 
                    $i = 0;
                    foreach($exclusions as $exclusion) { 
                        $i++;
                    ?>            
                        <tr class="<?php echo $i%2==0?"even":"odd"; ?>">
                            <td>
                                <?php echo $i; ?>
                            </td>
                            <td>
                                <?php echo long2ip($exclusion->getRawIpStart()) ?>
                            </td>
                            <td>
                            <?php echo long2ip($exclusion->getRawIpEnd()) ?>
                            </td>
                            <td>
                                <?php echo $exclusion->getDescription() ?>
                            </td>
                            <td>
                                <a href="?exclusionId=<?php echo $exclusion->getId(); ?>">Delete</a>
                            </td>
                        </tr>    
                    <?php 
                    } 
                    ?>    
                </tbody>
            </table>
        </div>
    </body>
</html>


