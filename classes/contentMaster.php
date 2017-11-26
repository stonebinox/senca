<?php
/*-----------------------------------------
Author: Anoop Santhanam
Date created: 26/11/17 16:50
Last modified: 26/11/17 16:50
Comments: Main class file for 
content_master table.
-----------------------------------------*/
class contentMaster extends contentTypeMaster
{
    public $app=NULL;
    public $contentValid=false;
    private $content_id=NULL;
    function __construct($contentID=NULL)
    {
        $this->app=$GLOBALS['app'];
        if($contentID!=NULL)
        {
            $this->content_id=addslashes(htmlentities($contentID));
            $this->contentValid=$this->verifyContent();
        }
    }
    function verifyContent() //to verify a row of content
    {
        if($this->content_id!=NULL)
        {
            $app=$this->app;
            $contentID=$this->content_id;
            $cm="SELECT content_type_master_idcontent_type_master,url_master_idurl_master FROM content_master WHERE stat='1' AND idcontent_master='$contentID'";
            $cm=$app['db']->fetchAssoc($cm);
            if(($cm!="")&&($cm!=NULL))
            {
                $contentTypeID=$cm['content_type_master_idcontent_type_master'];
                contentTypeMaster::__construct($contentTypeID);
                if($this->contentTypeValid)
                {
                    $urlID=$cm['url_master_idurl_master'];
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
    function getContent() //to get a row of content
    {
        if($this->contentValid)
        {
            $app=$this->app;
            $contentID=$this->content_id;
            $cm="SELECT * FROM content_master WHERE idcontent_master='$contentID'";
            $cm=$app['db']->fetchAssoc($cm);
            if(($cm!="")&&($cm!=NULL))
            {
                $contentTypeID=$cm['content_type_master_idcontent_type_master'];
                contentTypeMaster::__construct($contentTypeID);
                $contentType=contentTypeMaster::getContentType();
                if(is_array($contentType))
                {
                    $cm['content_type_master_idcontent_type_master']=$contentType;
                }
                $urlID=$cm['url_master_idurl_master'];
                urlMaster::__construct($urlID);
                $url=urlMaster::getURL();
                if(is_array($url))
                {
                    $cm['url_master_idurl_master']=$url;
                }
                return $cm;
            }
            else
            {
                return "INVALID_CONTENT_ID";
            }
        }
        else
        {
            return "INVALID_CONTENT_ID";
        }
    }
}
?>