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
    public $headings=[];
    public $paragraphs=[];
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
        if(($url!="")&&($url!=NULL)&&(filter_var($url, FILTER_VALIDATE_URL)))
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
    function getURLs($offset=0) //to get list of urls
    {
        $offset=addslashes(htmlentities($offset));
        if(($offset!="")&&($offset!=NULL)&&(is_numeric($offset))&&($offset>=0))
        {
            $app=$this->app;
            $um="SELECT idurl_master FROM url_master WHERE stat!='0' ORDER BY idurl_master DESC LIMIT $offset,10";
            $um=$app['db']->fetchAll($um);
            $urlArray=array();
            for($i=0;$i<count($um);$i++)
            {
                $row=$um[$i];
                $urlID=$row['idurl_master'];
                urlMaster::__construct($urlID);
                $url=urlMaster::getURL();
                if(is_array($url))
                {
                    array_push($urlArray,$url);
                }
            }
            if(count($urlArray)>0)
            {
                return $urlArray;
            }
            else
            {
                return "NO_URLS_FOUND";
            }
        }
        else
        {
            return "INVALID_OFFSET_VALUE";
        }
    }
    function element_to_obj($element) {
        $obj = array( "tag" => $element->tagName );
        foreach ($element->attributes as $attribute) {
            $obj[$attribute->name] = $attribute->value;
        }
        foreach ($element->childNodes as $subElement) {
            if ($subElement->nodeType == XML_TEXT_NODE) {
                $obj["html"] = $subElement->wholeText;
            }
            else {
                $obj["children"][] = @$this->element_to_obj($subElement);
            }
        }
        return $obj;
    }
    function findHeadings($html)
    {
        foreach($html as $tag)
        {
            if(isset($tag['tag']))
            {
                $tagName=strtolower($tag['tag']);
                switch($tagName)
                {
                    case "h1":
                    case "h2":
                    case "h3":
                    case "h4":
                    case "h5":
                    case "h6":
                    $htmlContent=$tag["html"];
                    array_push($this->headings,$htmlContent);
                    break;
                    default:
                    $this->findHeadings($tag['children']);
                    break;
                }
            }
            else
            {
                $this->findHeadings($tag);
            }
        }
    }
    function findParagraphs($html)
    {
        foreach($html as $tag)
        {
            if(isset($tag['tag']))
            {
                $tagName=strtolower($tag['tag']);
                switch($tagName)
                {
                    case "p":
                    $htmlContent=$tag['html'];
                    array_push($this->paragraphs,$htmlContent);
                    break;
                    default:
                    $this->findParagraphs($tag['children']);
                    break;
                }
            }
            else
            {
                $this->findParagraphs($tag);
            }
        }
    }
    function processURL() //to process a url and extract all content
    {
        $app=$this->app;
        if($this->urlValid)
        {
            $urlID=$this->url_id;
            $um="SELECT url FROM url_master WHERE idurl_master='$urlID'";
            $um=$app['db']->fetchAssoc($um);
            if(($um!="")&&($um!=NULL))
            {
                $url=$um['url'];
                $output=@file_get_contents($url);
                if(($output!="")&&($output!=NULL))
                {
                    $content=new contentMaster;
                    $dom = new DOMDocument();
                    @$dom->loadHTML($output);
                    $json=@$this->element_to_obj($dom->documentElement);
                    $this->findHeadings($json['children']);
                    foreach($this->headings as $heading)
                    {
                        $response=$content->addContent($heading,11,$urlID);
                    }
                    $this->findParagraphs($json['children']);
                    foreach($this->paragraphs as $paragraph)
                    {
                        $response=$content->addContent($paragraph,1,$urlID);
                    }
                    // $response=$content->addContent($output,31,$urlID);
                    $up="UPDATE url_master SET stat='1' WHERE idurl_master='$urlID'";
                    $up=$app['db']->executeUpdate($up);
                    return "CONTENT_ADDED";
                }
                else
                {
                    return "NO_CONTENT_FOUND";
                }
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
    function deleteURL()
    {
        $app=$this->app;
        if($this->urlValid)
        {
            $urlID=$this->url_id;
            $um="UPDATE url_master SET stat='0' WHERE idurl_master='$urlID'";
            $um=$app['db']->executeUpdate($um);
            return "URL_DELETED";
        }
        else
        {
            return "INVALID_URL_ID";
        }
    }
}
?>