<?php
/*-----------------------------------------
Author: Anoop Santhanam
Date Created: 26/11/17 16:00
Last modified: 26/11/17 16:00
Comments: Main class file for url_master 
table.
------------------------------------------*/
class urlMaster
{
    public $app=NULL;
    private $url_id=NULL;
    public $urlValid=false;
    function __construct($urlID=NULL)
    {
        $this->app=$GLOBALS['app'];
        if($urlID!=NULL)
        {
            $this->url_id=addslashes(htmlentities($urlID));
            $this->urlValid=$this->verifyURL();
        }
    }
    function verifyURL() //to verify a url row
    {
        if($this->url_id!=NULL)
        {
            $app=$this->app;
            $urlID=$this->url_id;
            $um="SELECT idurl_master FROM url_master WHERE stat!='0' AND idurl_master='$urlID'";
            $um=$app['db']->fetchAssoc($um);
            if(($um!="")&&($um!=NULL))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    function getURL() //to get a url row
    {
        if($this->urlValid)
        {
            $app=$this->app;
            $urlID=$this->url_id;
            $um="SELECT * FROM url_master WHERE idurl_master='$urlID'";
            $um=$app['db']->fetchAssoc($um);
            if(($um!="")&&($um!=NULL))
            {
                return $um;
            }
            else
            {
                return "INVALID_URL_ID";
            }
        }
        else
        {
            return "INVALID_URL_ID";
        }
    }
    function getURLIDByURL($url)
    {
        $app=$this->app;
        $url=trim(strtolower(addslashes(htmlentities($url))));
        if(($url="")&&($url!=NULL))
        {
            $um="SELECT idurl_master FROM url_master WHERE stat!='0' AND url='$url'";
            $um=$app['db']->fetchAssoc($um);
            if(($um!="")&&($um!=NULL))
            {
                return $um['idurl_master'];
            }
            else
            {
                return "NO_URL_FOUND";
            }
        }
        else
        {
            return "INVALID_URL";
        }
    }
    function addURL($url) //to add a URL
    {
        $app=$this->app;
        $url=trim(strtolower(addslashes(htmlentities($url))));
        if(($url!="")&&($url!=NULL))
        {
            $urlID=$this->getURLIDByURL($url);
            if(is_numeric($urlID))
            {
                return "URL_ALREADY_EXISTS";
            }
            $in="INSERT INTO url_master (timestamp,stat,url) VALUES (NOW(),'2','$url')";
            $in=$app['db']->executeQuery($in);
            $um="SELECT idurl_master FROM url_master WHERE stat='2' AND url='$url' ORDER BY idurl_master DESC LIMIT 1";
            $um=$app['db']->fetchAssoc($um);
            $urlID=$um['idurl_master'];
            return "URL_ADDED_".$urlID;
        }
        else
        {
            return "INVALID_URL";
        }
    }
}
?>