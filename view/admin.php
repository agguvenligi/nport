<?php
    include 'base.php';
    require_once 'utility/validator.php';
    
    if(!Validator::isIPAuthentic($_SERVER["REMOTE_ADDR"]))
        return;
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
            <div class="alert">
                ALERT: <b>NPort is configured to analyze ports: 
                    <?php echo implode(",", array_keys(Configuration::$ports_analyzed)) ?>. 
                    Make sure you used that list or updated it. </b>                
                <br/>
                <br/>
                ALERT: <b>Make sure you didn't use --open directive when running nmap!</b>                
                <br/>
                <br/>
                ALERT: If an nmap run is resumed, Nmap XML output gets messed up. So, if you have resumed an nmap run,
                first remove the extra elements listed below;
                <i>
                    <br/>
                    <br/>
                    &lt?xml version="1.0"?&gt;
                    <br/>
                    &lt?xml-stylesheet href=..." type="text/xsl"&gt;
                    <br/>
                    &lt!-- Nmap 5.6 scan initiated Tue .. --&gt;
                    <br/>
                    &ltnmaprun scanner="nmap" args="... " version="5.61TEST4" xmloutputversion="1.05"&gt;
                    <br/>                    
                </i>
            </div>            
            <form action="upload.php" method="post" enctype="multipart/form-data"  style="margin:40px auto; text-align: center;">
                <span class="label">File Name:</span>
                <input type="file" name="file" id="file" /> &nbsp;&nbsp;
                <input type="submit" name="submit" value="Submit" />
            </form>
        </div>
    </body>
</html>
