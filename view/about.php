<?php
    include 'base.php';
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
            <p class="about">
                <a href="/nport/">NPort</a> is an attempt to handle critical port 
                views of big networks. If you are legally responsible for the 
                security of big IP v4 blocks, this is for you. By determining
                critical ports that your organization possesess, you can easily 
                maintain your global view by;
                <ul>
                    <li>importing large xml nmap results,</li>
                    <li>live & automatic whois queries,</li>
                    <li>excluding IP ranges you don't own or don't cover,</li> 
                    <li>on the fly excluding whois ranges,</li> 
                    <li>open port trend snapshots,</li> 
                    <li>listing & processing nmap xml outputs locally,</li> 
                    <li>granularly rescan existing results,</li>
                    <li>search existing results given ip ranges,</li>
                    <li>blazing fast initial web analysis by web page slider,</li>
                    <li>automatic credential brute forcing of the open ports of a given ip address,</li>
                    <li>and not the least graphically present results to your eager-to-see-pictures management. ;)</li>                
                </ul>
            </p>
            <p class="about">
                It's mostly object oriented, any comments are welcome @ <b>bedirhan.urgun</b>{gmail}                
            </p>
        </div>
    </body>
</html>
