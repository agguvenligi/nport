<?php
    include 'base.php';
    require_once 'db/dao.php';
    require_once 'utility/validator.php';
    require_once 'utility/whois.php';
    
    $ipDAO = new IpDAO(Configuration::$dbhost, Configuration::$dbuser, Configuration::$dbpassword, Configuration::$dbschema);
    
    $ipports = array();
    
    $startip = "";
    $endip = "";
    $total = 0;
    $index = 0;
    
    if(isset($_GET["startip"]) && isset($_GET["endip"]) && 
            Validator::isValidIpAddress($_GET["startip"]) && Validator::isValidIpAddress($_GET["endip"])){
        $startip = $_GET["startip"];
        $endip = $_GET["endip"];
        $total = $ipDAO->getIpPortsInRangeTotal($startip, $endip);                
        if(isset($_GET["index"]) && isset($_GET["navigate"])){
            if(strcmp($_GET["navigate"], "next") == 0){
                $index = $_GET["index"] + Configuration::$resultLimit;
                if($index >= $total) $index = $_GET["index"];
            }
            else if(strcmp($_GET["navigate"], "prev") == 0){
                $index = $_GET["index"] - Configuration::$resultLimit;        
                if($index < 0) $index = 0;
            }
        }

        $ipports = $ipDAO->getIpPortsInRange($startip, $endip, $index);                
    }
    
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Welcome to NPort!</title>
        <link rel="stylesheet" type="text/css" href="../content/style.css" />
        <script>
            function navigate(val){
                var navigate = document.getElementById("navigate");
                navigate.value = val;
                document.forms[0].submit();
            }
        </script>
    </head>
    <body>  
        <div class="content">            
            <?php include "menu.php" ?>
            <form method="GET">
                <input type="hidden" value="<?php echo $index++ ?>" name="index" />
                <input type="hidden" value="" name="navigate" id="navigate" />
   
                <table class="formtable" cellpadding="0px" cellspacing="0px" border="0px">
                    <tr>
                        <td class="label">Start IP Address:</td>
                        <td><input type="text" name="startip" value="<?php echo $startip ?>" style="width:200px"/></td>
                    </tr>
                    <tr>
                        <td class="label">End IP Address:</td>
                        <td><input type="text" name="endip" value="<?php echo $endip ?>" style="width:200px"/></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center">
                            <input type="submit" value="SEARCH"/> 
                        </td>
                    </tr>
                </table>
                
                <div class="exclusionmessage">
                    <?php echo $total ?> ports found, <?php echo sizeof($ipports) ?> listed
                </div>

                <?php 
                    if(sizeof($ipports) > 0) {
                ?>
                    <div class="navigation">
                        <a href="javascript:navigate('prev')">Prev</a>
                        &nbsp;
                        &nbsp;
                        <a href="javascript:navigate('next')">Next</a>
                    </div>

                    <table class="list" cellpadding="0px" cellspacing="0px" border="0px">
                        <thead>
                            <th> </th>
                            <th> IP Address </th>
                            <th> Port </th>
                            <th> Status </th>
                            <th> Update </th>
                            <th> Is Excluded </th>
                            <th> WhoIs </th>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 0;
                            foreach($ipports as $ipport) { 
                                $i++;
                            ?>            
                                <tr class="<?php echo $i%2==0?"even":"odd"; ?>">
                                    <td>
                                        <?php echo $index++; ?>
                                    </td>
                                    <td>
                                        <?php echo $ipport->getIp() ?>
                                    </td>
                                    <td>
                                        <?php echo $ipport->getPort() ?>
                                    </td>
                                    <td>
                                        <?php echo status::getStatusString($ipport->getStatus()) ?>
                                    </td>
                                    <td>
                                        <?php echo $ipport->getUpdate() ?>
                                    </td>                                                                    
                                    <td>
                                        <?php echo exclusion::getStatusString($ipport->getExcluded()) ?>
                                    </td>
                                    <td>
                                        <?php
                                            $whois = Whois::Get($ipport->getIp());
                                            if($whois->isEmpty()){
                                        ?>
                                                <a target="_blank" href="<?php echo Configuration::$whois . $ipport->getIp() ?>">WhoIs<a>
                                                </br>
                                                <a href="javascript:exclude('<?php echo $ipport->getIp() ?>')">Exclude C Blok</a>                                                    
                                        <?php
                                            }
                                            else{
                                                echo $whois->getInetnum() . "<br/>" . $whois->getNetname();
                                            }
                                        ?>
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