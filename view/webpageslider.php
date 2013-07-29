<?php

    include 'base.php';
    require_once 'db/dao.php';
    require_once 'scanner/hydra.php';
    require_once 'utility/validator.php';

    $webPageSliderResultLimit = 1;
    $currentIP = "";
    $total = 0;
    
    $portNumber = 80;
    if(isset($_GET["port"]))
        $portNumber = intval($_GET["port"]);

    if(Validator::isIPAuthentic($_SERVER["REMOTE_ADDR"])){

        $ipDAO = new IpDAO(Configuration::$dbhost, Configuration::$dbuser, Configuration::$dbpassword, Configuration::$dbschema);    
        
        $total = $ipDAO->getIpsByPortTotal($portNumber, exclusion::$includedStatus, status::$open);    

        if(isset($_GET["index"]))
            $index = intval($_GET["index"]);
        else
            $index = 0;

        if(isset($_GET["index"]) && isset($_GET["navigate"])){
            if(strcmp($_GET["navigate"], "next") == 0){
                $index = $_GET["index"] + $webPageSliderResultLimit;
                if($index >= $total) $index = intval($_GET["index"]);
            }
            else if(strcmp($_GET["navigate"], "prev") == 0){
                $index = intval($_GET["index"]) - $webPageSliderResultLimit;        
                if($index < 0) $index = 0;
            }
        }

        $ips = $ipDAO->getIpsByPort($portNumber, exclusion::$includedStatus, status::$open, $index, $webPageSliderResultLimit);  
        if(sizeof($ips) > 0)
            $currentIP = $ips[0];
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
                <input type="hidden" value="<?php echo $portNumber ?>" name="port" />
                <input type="hidden" value="<?php echo $index++ ?>" name="index" />
                <input type="hidden" value="" name="navigate" id="navigate" />                
            </form>
            <table>
                <tr>
                    <td colspan="3" style="text-align: center">
                        <?php 
                            $targetService = Configuration::$ports_analyzed[$portNumber];
                            if(Hydra::isServiceHttpRelated($targetService) && !empty($currentIP)){                                            
                                $protocol = "http://";
                                if(Hydra::isServiceHttps($targetService))
                                    $protocol = "https://";
                                
                                $url = $protocol . $currentIP->getIp() . ":" . $portNumber . "/";
                                echo "<a style='font-size:16px;padding:5px;' target='_blank' href='" . $url . "'>" . $currentIP->getIp() . "</a><br/>";
                                echo $index . " of " . $total;
                            }                        
                        ?>
                    </td>
                </tr>
                <tr>
                    <td><a style="font-size:16px;padding:5px;" href="javascript:navigate('prev')">Prev</a></td>
                    <td>
                        <iframe style="width:800px;height:600px;border:1px solid black;" 
                                src="../utility/webpagefetcher.php?id=<?php echo $currentIP->getId() ?>&port=<?php echo $portNumber ?>">
                        </iframe>
                    </td>
                    <td><a style="font-size:16px;padding:5px;" href="javascript:navigate('next')">Next</a></td>
                </tr>
            </table>
        </div>
    </body>
</html>   
        