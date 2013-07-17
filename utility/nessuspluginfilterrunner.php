<?php

    require_once("base.php");
    require_once("nessuspluginfilter.php");
    
    $nessusPluginFilter = new NessusPluginFilter();
    $matchNaslScripts = $nessusPluginFilter->filter();
    if(sizeof($matchNaslScripts) > 0){
        foreach($matchNaslScripts as $matchNaslScript)
            echo $matchNaslScript->getPluginId() . ",";
    }

?>
