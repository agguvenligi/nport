<?php
    include 'base.php';
    require_once 'db/dao.php';
    require_once 'utility/validator.php';
    require_once 'scanner/nmap.php';
    require_once 'scanner/hydra.php';
    require_once 'utility/whois.php';
    
    $ipDAO = new IpDAO(Configuration::$dbhost, Configuration::$dbuser, Configuration::$dbpassword, Configuration::$dbschema);
    
    $message = " ";
    if(Validator::isIPAuthentic($_SERVER["REMOTE_ADDR"]) && isset($_GET["rescan"]) 
            && isset($_GET["search"]) && Validator::isValidIpAddress($_GET["search"])){
        $nmap = new Nmap(array($_GET["search"]), array_keys(Configuration::$ports_analyzed));
        $nmap->run();
        $ipDAO->updateOpenPortHistory(); // update open port history
        $message = "IP address re-scanned";
    }
    
    $message = " ";
    if(Validator::isIPAuthentic($_SERVER["REMOTE_ADDR"]) && isset($_GET["brute"]) 
            && isset($_GET["search"]) && Validator::isValidIpAddress($_GET["search"])){

        $ips = $ipDAO->getIpByAddress(trim($_GET["search"]));
        if(sizeof($ips) == 1){
            $openports = array();
            $ports = $ips[0]->getPorts(); 
            foreach($ports as $port){
                if($port->getStatus() == status::$open)
                    $openports[] = $port;
            }               
            $hydra = new Hydra(array($ips[0]->getIp()), $openports, "Triggered via UI" );            
            $message = $hydra->run();
        }
    }
    
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
    }

    $searchedIp = "";
    if(isset($_GET["search"])){
        if(Validator::isValidIpAddress($_GET["search"])){
            $searchedIp = $_GET["search"];
            $ips = $ipDAO->getIpByAddress(trim($_GET["search"]));
            if(sizeof($ips) == 0)
                $message = "No IP Address found...";    
        }
        else{
            $message = "Search for single IP Address..";            
        }
        
    }

    $portUtilization = $ipDAO->openPortUtilization(array_keys(Configuration::$ports_analyzed));
    $portTrend = $ipDAO->openPortTrend();  
    
    $selectedPortStatus = array();
    if(isset($_GET["selecteddate"]))
        $selectedPortStatus = $ipDAO->getPortDifference($_GET["selecteddate"]);
    else{
        $portTrendDates = array_keys($portTrend);
        if(sizeof($portTrendDates) > 0)
            $selectedPortStatus = $ipDAO->getPortDifference($portTrendDates[count($portTrendDates) - 1]);
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Welcome to NPort!</title>
        <link rel="stylesheet" type="text/css" href="content/style.css" />
        <!--Load the AJAX API-->
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <script type="text/javascript">     
            google.load('visualization', '1.0', {'packages':['corechart']});
            google.setOnLoadCallback(drawChart);

            function drawChart() {

                var pieChartData = new google.visualization.DataTable();
                pieChartData.addColumn('string', 'Port');
                pieChartData.addColumn('number', 'IP Sayısı');
                pieChartData.addRows([ 
                    <?php
                        $i = 0;
                        foreach(array_keys(Configuration::$ports_analyzed) as $k=>$v){
                            $i++;
                            echo "['" . $v . "'," . $portUtilization[$v] . "]";
                            if($i < sizeof(array_keys(Configuration::$ports_analyzed))) 
                                echo ",";                            
                        }
                    ?>
                ]);

                var optionsPieChart = {'title':'Open Ports',
                            width:650,
                            height:400,
                            sliceVisibilityThreshold:0
                            };            
                
                var pieChart = new google.visualization.PieChart(document.getElementById('chart_piechart'));
                pieChart.draw(pieChartData, optionsPieChart);
                google.visualization.events.addListener(pieChart, 'select', selectHandlerPieChart); 
                function selectHandlerPieChart(e){   
                    document.location = "/nport/view/ips.php?openstatus=<?php echo status::$open?>&excludestatus=<?php echo exclusion::$includedStatus?>&port=" + pieChartData.getValue(pieChart.getSelection()[0].row, 0);
                }

                var trendChartData = new google.visualization.DataTable();
                trendChartData.addColumn('string', 'Month');
                trendChartData.addColumn('number', 'Open Ports');
                
                trendChartData.addRows([   
                    <?php 
                        $result = "";
                        foreach($portTrend as $key => $value) {
                            $result .= "['" . $key . "', " . $value . "],";
                        } 
                        
                        if(strlen($result) > 0)
                            $result = substr($result, 0, strlen($result) - 1);
                        
                        echo $result;
                    ?>
                ]);

                var optionsTrendChart = {'title':'Open Port Trend',
                            width:650,
                            height:450,
                            sliceVisibilityThreshold:0
                            };

                var trendChart = new google.visualization.LineChart(document.getElementById('chart_trendchart'));
                trendChart.draw(trendChartData, optionsTrendChart);
                google.visualization.events.addListener(trendChart, 'select', selectHandlerTrendChart); 
                function selectHandlerTrendChart(e){   
                    document.location = "/nport/view/portchanges.php?selecteddate=" + trendChartData.getValue(trendChart.getSelection()[0].row, 0);
                }

            }    
            
            function exclude(range){
                var exclude = document.getElementById("exclude");
                exclude.value = range;
                document.forms[0].submit();
            }
        </script>            
    </head>
    <body>
        <div class="content" >
            <?php include "view/menu.php" ?>
            <table cellpadding="0px" cellspacing="0px" border="0px">
                <tr>
                    <td style="text-align:center">
                        <div id="chart_trendchart"></div>
                    </td>  
                    <td style="text-align:center">
                        <div id="chart_piechart"></div>
                    </td>
                </tr>
            </table> 
            
            <form action="index.php" method="get">
                <input type="hidden" value="0" name="exclude" id="exclude"/>
                <table class="formtable">
                    <tr>
                        <td>
                            <span class="label">IP Address:</span>
                            <input type="text" name="search" id="text" value="<?php echo $searchedIp ?>"/> &nbsp;&nbsp;
                            <input type="submit" value="SEARCH" />
                        </td>
                    </tr>
                </table>
                <?php if(isset($ips) && sizeof($ips) == 1){ ?>
                    <table class="list" cellpadding="0px" cellspacing="0px" border="0px">
                        <thead>
                            <th>IP Address</th>
                            <th>Open Ports</th>
                            <th>Filtered Ports</th>
                            <th>Closed Ports</th>
                            <th>Creation Date</th>
                            <th>Is Excluded</th>
                            <th>WhoIs</th>
                            <th>ReScan</th>
                            <th>Brute</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo $ips[0]->getIp(); ?></td>
                                <td>
                                    <?php 
                                        $ports = $ips[0]->getPorts(); 
                                        $str = "";
                                        foreach($ports as $port){
                                            if($port->getStatus() == status::$open)
                                                $str .= $port->getPort() . ",";
                                        }    
                                        if(strlen($str) > 0)
                                            $str = substr($str, 0, strlen($str) - 1);

                                        echo $str;
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                        $ports = $ips[0]->getPorts(); 
                                        $str = "";
                                        foreach($ports as $port){
                                            if($port->getStatus() == status::$filtered)
                                                $str .= $port->getPort() . ",";
                                        }    
                                        if(strlen($str) > 0)
                                            $str = substr($str, 0, strlen($str) - 1);

                                        echo $str;
                                    ?>
                                </td>                                
                                <td>
                                    <?php 
                                        $str = "";
                                        foreach($ports as $port){
                                            if($port->getStatus() == status::$closed)
                                                $str .= $port->getPort() . ",";
                                        }    
                                        if(strlen($str) > 0)
                                            $str = substr($str, 0, strlen($str) - 1);

                                        echo $str;
                                    ?>
                                </td>
                                <td>
                                    <?php echo $ips[0]->getCreationdate(); ?>

                                </td>
                                <td>
                                    <?php 
                                        echo exclusion::getStatusString($ips[0]->getExcluded()) 
                                    ?>
                                </td>
                                <td>
                                    <?php
                                        $whois = Whois::Get($ips[0]->getIp());
                                        if($whois->isEmpty()){
                                    ?>
                                            <a target="_blank" href="<?php echo Configuration::$whois . $ips[0]->getIp() ?>">WhoIs<a>
                                            </br>
                                            <a href="javascript:exclude('<?php echo $ips[0]->getIp() ?>')">Exclude C Blok</a>                                                    
                                    <?php
                                        }
                                        else{
                                            echo $whois->getInetnum() . "<br/>" . $whois->getNetname();
                                    ?>
                                            </br>
                                            <a href="javascript:exclude('<?php echo $whois->getInetnum() ?>')">Exclude Range</a>                                            
                                    <?php
                                        }
                                     ?>
                                </td>                                
                                <td>
                                    <input type="submit" value="RESCAN" name="rescan"/>
                                </td>
                                <td>
                                    <input type="submit" value="BRUTE" name="brute"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>            
                <?php
                    }
                ?>
                <div class="message"><?php echo $message ?></div>
            </form>              
        </div>
    </body>
</html>
