<?php
/*----------------------------------------
Author: Anoop Santhanam
Date created: 26/11/17 17:02
Last modified: 26/11/17 17:02
Comments: Main class file for 
url_map_master table.
----------------------------------------*/
class urlMapMaster extends urlMaster
{
    public $app=NULL;
    public $urlMapValid=false;
    private $url_map_id=NULL;
    function __construct($urlMapID=NULL)
    {
        $this->app=$GLOBALS['app'];
        if($urlMapID!=NULL)
        {
            $this->url_map_id=addslashes(htmlentities($urlMapID));
            $this->urlMapValid=$this->verifyURLMap();
        }
    }
    function verifyMapURL() //to verify a url map row
    {
        if($this->url_map_id!=NULL)
        {
            $app=$this->app;
            $urlMapID=$this->url_map_id;
            $umm="SELECT linked_url,url_master_idurl_master FROM url_map_master WHERE stat='1' AND idurl_map_master='$urlMapID'";
            $umm=$app['db']->fetchAssoc($umm);
            if(($umm!="")&&($umm!=NULL))
            {
                $linkedURL=$umm['linked_url'];
                urlMaster::__construct($linkedURL);
                if($this->urlValid)
                {
                    $urlID=$umm['url_master_idurl_master'];
                    urlMaster::__construct($urlID);
                    if($this->urlValid)
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
    function getURLMap() //to get a row of URL mapping
    {
        if($this->urlMapValid)
        {
            $app=$this->app;
            $urlMapID=$this->url_map_id;
            $umm="SELECT * FROM url_map_master WHERE idurl_map_master='$urlMapID'";
            $umm=$app['db']->fetchAssoc($umm);
            if(($umm!="")&&($um!=NULL))
            {
                $linkedURL=$umm['linked_url'];
                urlMaster::__construct($linkedURL);
                $linked=urlMaster::getURL();
                if(is_array($linked))
                {
                    $umm['linked_url']=$linked;
                }
                $urlID=$umm['url_master_idurl_master'];
                urlMaster::__construct($urlID);
                $url=urlMaster::getURL();
                if(is_array($url))
                {
                    $umm['url_master_idurl_master']=$url;
                }
                return $umm;
            }
            else
            {
                return "INVALID_URL_MAP_ID";
            }
        }
        else
        {
            return "INVALID_URL_MAP_ID";
        }
    }
}
?>