<?php
require_once 'db/dao.php';
require_once 'config.php';

class Http{
    protected $targetURL;
    protected $isSsl;
    protected $connection;

    public function Http($targetURL) {
        $this->targetURL = $targetURL;
        $this->isSsl = false;
        if(substr($this->targetURL, 0, strlen("https")) === "https")
            $this->isSsl = true;        
    }    
    
    public function getResponseCode(){
        $httpCode = 500;
        $this->connection = curl_init();
        $this->setOpts(false);
        $body = curl_exec($this->connection);
        if(!empty($body))
            $httpCode = curl_getinfo($this->connection, CURLINFO_HTTP_CODE);
        else
            Logger::debug("HTTP request to " . $this->targetURL . " threw exception");                                        
        curl_close($this->connection); 
        return $httpCode;
    }
    
    protected function setOpts($isBody){        
        curl_setopt($this->connection, CURLOPT_URL, $this->targetURL);
        if(!$isBody)
            curl_setopt($this->connection, CURLOPT_HEADER, 1);
        curl_setopt($this->connection, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->connection, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($this->connection, CURLOPT_TIMEOUT, 1);
        curl_setopt($this->connection, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($this->connection, CURLOPT_MAXREDIRS, 1);
                
        if($this->isSsl){
            curl_setopt($this->connection, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($this->connection, CURLOPT_SSL_VERIFYHOST, 0); //??? I don't need this            
        }
    }

    public function getResponseBody(){
        $body = "";
        $this->connection = curl_init();
        $this->setOpts(true);
        $body = curl_exec($this->connection);
        if(empty($body))
            Logger::debug("HTTP request to " . $this->targetURL . " threw exception");                                        
        curl_close($this->connection); 
        return $body;
    }    
  
}

?>

