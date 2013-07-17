<?php
    require_once 'config.php';
    require_once 'utility/validator.php';
    
    if(!Validator::isIPAuthentic($_SERVER["REMOTE_ADDR"]))
        return;
    
        if(!empty($_GET['cidr'])){
                echo "starting<br/>";
                $range = $_GET['cidr'];
                $command = "sudo nmap -n -PN -sT -T3 -p 80,443,1521,21,23,22 $range -oX " . Configuration::$nmapOutputDirectory . "test.xml";
                echo "cmd: " . $command . "<br/>";
                $pid = exec("nohup $command > /dev/null 2>&1 & echo $!");
                echo "Running process PID: " . $pid . "<br/>";
                echo "<a href='runner.php?kill=$pid'>kill</a><br/>";
                echo "<a href='runner.php?pid=$pid'>still running?</a><br/>";
        }

        if(!empty($_GET['pid'])){
                $pid = $_GET['pid'];
                exec("sudo ps $pid", $pState);
                if((count($pState) >= 2))
                {
                        echo "yes, still running<br/>";
                        echo "Running process PID: " . $pid . "<br/>";
                        echo "<a href='runner.php?kill=$pid'>kill</a><br/>";
                        echo "<a href='runner.php?pid=$pid'>still running?</a><br/>";
                }
                else{
                        echo "no, not running<br/>";
                }
        }

        if(!empty($_GET['kill'])){
                $pid = $_GET['kill'];
                exec("sudo kill $pid");
                exec("sudo ps $pid", $pState);
                if((count($pState) >= 2))
                {
                        echo "still running, bro<br/>";
                        echo "Running process PID: " . $pid . "<br/>";
                        echo "<a href='runner.php?kill=$pid'>kill</a><br/>";
                        echo "<a href='runner.php?pid=$pid'>still running?</a><br/>";
                }
                else{
                        echo "killed<br/>";
                        echo "<a href='runner.php?pid=$pid'>still running?</a><br/>";
                }
        }

?>

<form method="GET">
        Enter CIDR: <input type="text" name="cidr" width="200px"/>
        &nbsp;<input type="submit" value="Scan" />
</form>
