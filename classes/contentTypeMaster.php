<?php
/*-------------------------------------------------
Author: Anoop Santhanam
Date Created: 26/11/17 16:41
Last modified: 26/11/17 16:41
Comments: Main class file for content_type_master
table.
-------------------------------------------------*/
class contentTypeMaster extends urlMapMaster
{
    public $app=NULL;
    public $contentTypeValid=false;
    private $content_type_id=NULL;
    function __construct($contentTypeID=NULL)
    {
        $this->app=$GLOBALS['app'];
        if($contentTypeID!=NULL)
        {
            $this->content_type_id=addslashes(htmlentities($contentTypeID));
            $this->contentTypeValid=$this->verifyContentType();
        }
    }
    function verifyContentType()
    {
        if($this->content_type_id!=NULL)
        {
            $app=$this->app;
            $contentTypeID=$this->content_type_id;
            $ctm="SELECT idcontent_type_master FROM content_type_master WHERE stat='1' AND idcontent_type_master='$contentTypeID'";
            $ctm=$app['db']->fetchAssoc($ctm);
            if(($ctm!="")&&($ctm!=NULL))
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
    function getContenTypeIDByType($type) //to get a content type ID by its name
    {
        $type=trim(addslashes(htmlentities($type)));
        $app=$this->app;
        $ctm="SELECT idcontent_type_master FROM content_type_master WHERE stat='1' AND content_type='$type'";
        $ctm=$app['db']->fetchAssoc($ctm);
        if(($ctm!="")&&($ctm!=NULL))
        {
            return $ctm['idcontent_type_master'];
        }
        else
        {
            return "NO_CONTENT_TYPE_FOUND";
        }
    }
    function getContentType() //to get a content type ID by its name
    {
        if($this->contentTypeValid)
        {
            $app=$this->app;
            $contentTypeID=$this->content_type_id;
            $ctm="SELECT * FROM content_type_master WHERE idcontent_type_master='$contentTypeID'";
            $ctm=$app['db']->fetchAssoc($ctm);
            if(($ctm!="")&&($ctm!=NULL))
            {
                return $ctm;
            }
            else
            {
                return "INVALID_CONTENT_TYPE_ID";
            }
        }
        else
        {
            return "INVALID_CONTENT_TYPE_ID";
        }
    }
}
?>