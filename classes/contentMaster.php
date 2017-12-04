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
    function addContent($content,$contentTypeID,$urlID) //to add content
    {
        $app=$this->app;
        $content=trim(addslashes($content));
        if(($content!="")&&($content!=NULL))
        {
            $contentTypeID=addslashes(htmlentities($contentTypeID));
            contentTypeMaster::__construct($contentTypeID);
            if($this->contentTypeValid)
            {
                $urlID=addslashes(htmlentities($urlID));
                urlMaster::__construct($urlID);
                if($this->urlValid)
                {
                    $cm="SELECT idcontent_master FROM content_master WHERE stat='1' AND url_master_idurl_master='$urlID' AND content_type_master_idcontent_type_master='$contentTypeID' AND content_value='$content'";
                    $cm=$app['db']->fetchAssoc($cm);
                    if(($cm=="")||($cm==NULL))
                    {
                        $in="INSERT INTO content_master (timestamp,url_master_idurl_master,content_type_master_idcontent_type_master,content_value) VALUES (NOW(),'$urlID','$contentTypeID','$content')";
                        $in=$app['db']->executeQuery($in);
                        return "CONTENT_ADDED";
                    }
                    else
                    {
                        return "CONTENT_ALREADY_ADDED";
                    }
                }
                else
                {
                    return "INVALID_URL_ID";
                }
            }
            else
            {
                return "INVALID_CONTENT_TYPE_ID";
            }
        }
        else
        {
            return "INVALID_CONTENT";
        }
    }
    function searchContent($content) //to search for matching content
    {
        $app=$this->app;
        $content=trim(addslashes($content));
        if(($content!="")&&($content!=NULL))
        {
            $e=explode(" ",$content);
            $cm="SELECT idcontent_master FROM content_master WHERE stat='1' AND ";
            for($i=0;$i<count($e);$i++)
            {
                $word=$e[$i];
                $cm.="content_value LIKE '%$word%'";
                if(($i!=0)&&($i<count($e)-1))
                {
                    $cm.=" AND ";
                }
            }
            $cm.=" ORDER BY idcontent_master DESC LIMIT 10";
            $cm=$app['db']->fetchAll($cm);
            $contentArray=array();
            for($i=0;$i<count($cm);$i++)
            {
                $contentRow=$cm[$i];
                $contentID=$contentRow['idcontent_master'];
                $this->__construct($contentID);
                $contentData=$this->getContent();
                if(is_array($contentData))
                {
                    array_push($contentArray,$contentData);
                }
            }
            if(count($contentArray)>0)
            {
                return $contentArray;
            }
            else
            {
                return "NO_CONTENT_FOUND";
            }
        }
        else
        {
            return "INVALID_CATEGORY";
        }
    }
}
?>