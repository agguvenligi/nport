<?php
    include 'base.php';
    require_once 'db/dao.php';
    $ipDAO = new IpDAO(Configuration::$dbhost, Configuration::$dbuser, Configuration::$dbpassword, Configuration::$dbschema);

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
        <link rel="stylesheet" type="text/css" href="../content/style.css" />
    </head>
    <body>  
        <div class="content">            
            <?php include "menu.php" ?>
            <?php if(isset($selectedPortStatus) && sizeof($selectedPortStatus) > 0){ ?>
                <table class="portdifference" cellpadding="0px" cellspacing="0px" border="0px">
                    <thead>
                        <th>&nbsp;</th>
                        <th>IP Address</th>
                        <th>Port</th>
                        <th>Status</th>
                        <th>Update</th>
                    </thead>
                    <tbody>
                        <?php for($i = 0; $i < count($selectedPortStatus); $i++) { ?>
                            <?php $status = status::getStatusString($selectedPortStatus[$i]->getStatus()); ?>
                            <tr class="<?php echo $status ?>">
                                <td><?php echo $i+1; ?></td>
                                <td><?php echo $selectedPortStatus[$i]->getIp(); ?></td>
                                <td><?php echo $selectedPortStatus[$i]->getPort(); ?></td>
                                <td><?php echo $status ?></td>
                                <td><?php echo $selectedPortStatus[$i]->getUpdate(); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php
                }
            ?>               
        </div>
    </body>
</html>