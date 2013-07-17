<?php
    include 'base.php';
    require_once 'db/dao.php';
    require_once 'scanner/nmap.php';
    require_once 'utility/validator.php';
    require_once 'utility/whois.php';
    require_once 'scanner/hydra.php';

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
    
    $indexToScroll = 0;
    
    if(Validator::isIPAuthentic($_SERVER["REMOTE_ADDR"]) && !empty($_GET["exclude"])){                
        
        $result  = 0;
        $range = explode("-", $_GET["exclude"]); 
        
        // $range can be both single IP address or an IP range with - between
        if(sizeof($range) == 1 && Validator::isValidIpAddress($range))
            $result = $ipDAO->saveExclusion($range, $range, "Excluded via user interface");
        else if(sizeof($range) == 2 && Validator::isValidIpAddress(trim($range[0])) && Validator::isValidIpAddress(trim($range[1])) ) 
            $result = $ipDAO->saveExclusion(trim($range[0]), trim($range[1]), "Excluded via user interface");

        if($result > 0){
            $ipDAO->updateOpenPortHistory(); // update open port history
            $message = "IP range was excluded successfully";
        }
        else
            $message = "IP range couldn't be excluded!";
        
        if(empty($_GET["indexToScroll"]))
            $indexToScroll = 1;
        else
            $indexToScroll = intval($_GET["indexToScroll"]);
    }    
    
    if(Validator::isIPAuthentic($_SERVER["REMOTE_ADDR"]) && isset($_GET["rescan"])){
        $ips = $ipDAO->getIpsByPortAll($portNumber, $excludestatus, $openstatus);   
        if(sizeof($ips) > 0){
            $allIPs = array();
            foreach($ips as $ip)
                $allIPs[] = $ip->getIp();
            $nmap = new Nmap($allIPs, array($portNumber));
            $nmap->run(false);
            $ipDAO->updateOpenPortHistory(); // update open port history        
        }
    }
    
    $total = $ipDAO->getIpsByPortTotal($portNumber, $excludestatus, $openstatus);    

    if(isset($_GET["index"]))
        $index = intval($_GET["index"]);
    else
        $index = 0;
    
    if(isset($_GET["index"]) && isset($_GET["navigate"])){
        if(strcmp($_GET["navigate"], "next") == 0){
            $index = $_GET["index"] + Configuration::$resultLimit;
            if($index >= $total) $index = intval($_GET["index"]);
        }
        else if(strcmp($_GET["navigate"], "prev") == 0){
            $index = intval($_GET["index"]) - Configuration::$resultLimit;        
            if($index < 0) $index = 0;
        }
    }
            
    $ips = $ipDAO->getIpsByPort($portNumber, $excludestatus, $openstatus, $index, Configuration::$resultLimit);   
         
    function isExclusionSelected($status, $excludestatus){
        if($excludestatus == $status)
            return "selected='selected'";
    }
    
    function isOpenStatusSelected($status, $openstatus){
        if($openstatus == $status)
            return "selected='selected'";
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
            function exclude(range, indexToScroll){
                var exclude = document.getElementById("exclude");
                exclude.value = range;
                var index = document.getElementById("indexToScroll");
                index.value = indexToScroll;
                document.forms[0].submit();
            }            
            function ScrollToTarget() {      
                var elementToScroll = document.getElementById("target<?php echo $indexToScroll ?>");
                if(elementToScroll)
                    elementToScroll.scrollIntoView(true); 
                var elementToColor = document.getElementById("row<?php echo $indexToScroll ?>");
                if(elementToColor)
                    elementToColor.className = "selected";                 
            }            
        </script>
    </head>
    <body onload="ScrollToTarget()">  
        <div class="content">            
            <?php include "menu.php" ?>
            <form method="GET">
                <input type="hidden" value="<?php echo $portNumber ?>" name="port" />
                <input type="hidden" value="0" name="exclude" id="exclude" />
                <input type="hidden" value="<?php echo $index++ ?>" name="index" />
                <input type="hidden" value="" name="navigate" id="navigate" />
                <input type="hidden" value="" name="indexToScroll" id="indexToScroll"/>
                <input type="hidden" value="" name="export" id="export" />
                
                <table class="formtable" cellpadding="0px" cellspacing="0px" border="0px">
                    <tr>
                        <td class="label">Select Exclusion:</td>
                        <td>                        
                            <select name="excludestatus">
                                <option <?php echo isExclusionSelected(exclusion::$allStatus, $excludestatus); ?> value="<?php echo exclusion::$allStatus ?>"><?php echo exclusion::getStatusString(exclusion::$allStatus) ?></option>
                                <option <?php echo isExclusionSelected(exclusion::$excludedStatus, $excludestatus); ?> value="<?php echo exclusion::$excludedStatus ?>"><?php echo exclusion::getStatusString(exclusion::$excludedStatus) ?></option>
                                <option <?php echo isExclusionSelected(exclusion::$includedStatus, $excludestatus); ?> value="<?php echo exclusion::$includedStatus ?>"><?php echo exclusion::getStatusString(exclusion::$includedStatus) ?></option>
                            </select>                         
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Select Port Status:</td>
                        <td>
                            <select name="openstatus">
                                <option <?php echo isOpenStatusSelected(status::$all, $openstatus); ?> value="<?php echo status::$all ?>"><?php echo status::getStatusString(status::$all) ?></option>
                                <option <?php echo isOpenStatusSelected(status::$open, $openstatus); ?> value="<?php echo status::$open ?>"><?php echo status::getStatusString(status::$open) ?></option>
                                <option <?php echo isOpenStatusSelected(status::$filtered, $openstatus); ?> value="<?php echo status::$filtered ?>"><?php echo status::getStatusString(status::$filtered) ?></option>
                                <option <?php echo isOpenStatusSelected(status::$closed, $openstatus); ?> value="<?php echo status::$closed ?>"><?php echo status::getStatusString(status::$closed) ?></option>
                            </select>                                                         
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align: center">
                            <input type="submit" value="GET" /> 
                            &nbsp; &nbsp; 
                            <input type="submit" value="RESCAN" name="rescan"/>
                        </td>
                    </tr>
                </table>
                
                <div class="exclusionmessage">
                    <?php echo $total ?> IP Addresses found, <?php echo sizeof($ips) ?> listed
                </div>
                
                
                <?php 
                    if(sizeof($ips) > 0) {
                ?>
                    <div class="navigation">
                        <a href="javascript:navigate('prev')">Prev</a>
                        &nbsp;
                        &nbsp;
                        <a href="javascript:navigate('next')">Next</a>
                        &nbsp;
                        &nbsp;
                        <?php 
                            $targetService = Configuration::$ports_analyzed[$portNumber];
                            if(Hydra::isServiceHttpRelated($targetService)){
                        ?>
                                <a href="webpageslider.php?port=<?php echo $portNumber ?>">WebPage Slider</a>                        
                                &nbsp;
                                &nbsp;
                        <?php
                            }
                        ?>
                        <a href="../utility/exportIps.php?port=<?php echo $portNumber ?>&openstatus=<?php echo $openstatus ?>&excludestatus=<?php echo $excludestatus ?>">Export</a>                        
                    </div>                
                    <table class="list" cellpadding="0px" cellspacing="0px" border="0px">
                    <thead>
                        <th> </th>
                        <th> IP Address </th>
                        <th> Port </th>
                        <th> Status </th>
                        <th> Update </th>
                        <th> Is Excluded </th>
                        <th> BruteForced </th>
                        <th> WhoIs </th>
                        <th> Focus </th>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 0;
                        foreach($ips as $ip) { 
                            $i++;
                            foreach($ip->getPorts() as $aPort) { 
                                if($aPort->getPort() == $portNumber){
                        ?>            
                                <tr class="<?php echo $i%2==0?"even":"odd"; ?>" id="row<?php echo $index?>">
                                    <td>
                                        <span id="target<?php echo $index?>"></span>
                                        <?php echo $index; ?>
                                    </td>
                                    <td>
                                        <?php echo $ip->getIp() ?>
                                    </td>
                                    <td>
                                        <?php 
                                            echo $aPort->getPort();
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo status::getStatusString($aPort->getStatus()) ?>
                                    </td>
                                    <td>
                                        <?php echo $aPort->getUpdate() ?>
                                    </td>                                                                    
                                    <td>
                                        <?php echo exclusion::getStatusString($ip->getExcluded()) ?>
                                    </td>
                                    <td>
                                        <?php 
                                            if($ipDAO->isPortBruteForced($aPort->getId()))
                                                echo "Yes";
                                            else
                                                echo "No";
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            $whois = Whois::Get($ip->getIp());
                                            if($whois->isEmpty()){
                                        ?>
                                                <a target="_blank" href="<?php echo Configuration::$whois . $ip->getIp() ?>">WhoIs<a>
                                                </br>
                                                <a href="javascript:exclude('<?php echo $ip->getIp() ?>', <?php echo $index-1 ?>)">Exclude C Blok</a>                                                    
                                        <?php
                                            }
                                            else{
                                                echo $whois->getInetnum() . "<br/>" . $whois->getNetname();
                                        ?>
                                                </br>
                                                <a href="javascript:exclude('<?php echo $whois->getInetnum() ?>', <?php echo $index-1 ?>)">Exclude Range</a>                                            
                                        <?php
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <a target="_blank" href="/nport/index.php?search=<?php echo $ip->getIp() ?>">Focus<a>
                                    </td>
                                </tr>    
                        <?php 
                                $index++;
                                }                                
                            }
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