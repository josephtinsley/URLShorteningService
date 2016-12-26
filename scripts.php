<?php

/**
 * Document   : URL Shortening Service
 * Author     : josephtinsley
 * Description: PHP Class, HTML File and txt flat file database used for generating a Short URL Service
 * http://twitter.com/josephtinsley 
*/

class ShortUrlGenerator {


    public function saveUrls($long_url, $key) 
    {
        $str = implode("|", [$long_url, $key])."\n";
        $fd  = fopen('ShortUrl/_flatdatabase.txt', 'a');
        $out = print_r($str, true);
        fwrite($fd, $out);
    } 

    public function readUrls() 
    {
        $file = file('ShortUrl/_flatdatabase.txt',FILE_SKIP_EMPTY_LINES);
        foreach($file as $row)
        {
            $arv[] = explode("|", $row);
        }
        return $arv;
    } 

    public function checkLongUrl($long_url) 
    {
       //READ FROM DATABASE HERE
      $db_list = $this->readUrls();      
      for ($x = 0; $x < count($db_list); $x++) 
      {
          if($db_list[$x][0] === $long_url)
          {
            return $db_list[$x][1]; // RETURN KEYID HERE   
          }
      }
      //CREATE KEYID HERE
      return $this->createKeyId($long_url);
    } 
    
    public function createKeyId($long_url) 
    {
        $randKeys = 'abcdefghijklmonpqrstuvwxyz';
        do{
            for ($x = 0; $x < 5; $x++) 
            {
                $ranNum = mt_rand(0, strlen($randKeys)-1);
                $str   .= substr($randKeys, $ranNum, 1); 
            }
            $doesKeyExists = $this->isKeyValid($str);
            if($doesKeyExists === 'T') //IF KEY IS IN SYSTEM KEEP GOING
            {
                $status = 'GO';  
            }else
            {
                $status = 'STOP'; //IF KEY IS NOT IN SYSTEM STOP
            }

        }while($status === 'GO');
        
        //WE HAVE A NEW KEY, SAVE IT
        $this->saveUrls($long_url, $str);
        return $str;
        
    } 
    
    public function isKeyValid($key) 
    {
        $db_list = $this->readUrls();
        for ($x = 0; $x < count($db_list); $x++) 
        {
          if($db_list[$x][1] === $key)
          {
              return 'T';
          }
        }
        return 'F';
    } 
}//END CLASS



if(!empty($_POST['url']))
{
    $url = filter_var(trim($_POST['url']), FILTER_SANITIZE_URL);
    
    if(filter_var($url, FILTER_VALIDATE_URL) === FALSE )
    {
        echo json_encode(['status'=>0]);
        return false;
    }
    
    $Short = new ShortUrlGenerator();
    $keyId = $Short->checkLongUrl($url);
    echo json_encode(['status'=>1,'url'=>'http://domain.com/'.$keyId]); 
    
    
}else
{
  echo json_encode(['status'=>0]);  
}

