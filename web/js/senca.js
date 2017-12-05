var app=angular.module("senca",[]);
app.config(function($interpolateProvider){
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
});
app.controller("editor",function($scope,$compile,$http){
    $scope.editor=null;
    $scope.content=null;
    $scope.timeout=null;
    $scope.contentArray=[];
    $scope.loadEditor=function(){
        ContentTools.StylePalette.add([
            new ContentTools.Style("Author","author",["p"])
        ]);
        $scope.editor=ContentTools.EditorApp.get();
        $scope.editor.init('*[data-editable]','data-name');
        $scope.editor.addEventListener('start', function (ev) {
            function autoSave() {
                $scope.searchContent();
            }
            $scope.timeout = setInterval(autoSave, 10 * 1000);
        });
        
        $scope.editor.addEventListener('stop', function (ev) {
            clearInterval($scope.timeout);
        });
    };
    $scope.searchContent=function(){
        if(validate($scope.editor)){
            var content=$.trim($('[data-name="main-content"]').html());
            if(validate(content)){
                var sp=content.split('>');
                content=sp[sp.length-2];
                sp=content.split("</");
                content=sp[0];
                sp=content.split(" ");
                if(sp.length>=50){                    
                    $.ajax({
                        method:"POST",
                        url:"search",
                        data: {
                            content: content
                        },
                        error: function(error){
                            console.log(error);
                            messageBox("Problem","Something went wrong while searching for suggestions. Please try again later.");
                        },
                        success: function(response){
                            $("#suggestions").html("");
                            if((validate(response))&&(response!="INVALID_PARAMETERS")){
                                if(response=="NO_CONTENT_FOUND"){
                                    $("#suggestions").html("<small>No matches found.</small>");
                                }
                                else if(response=="INVALID_CONTENT"){
                                    $("#suggestions").html("<small>Please enter some text to have suggestions show up.</small>");
                                }
                                else{
                                    response=JSON.parse(response);
                                    $scope.contentArray=response.slice();
                                    $scope.displayContent();
                                }
                            }  
                            else{
                                messageBox("Problem","Something went wrong while searching for suggestions. Please try again later. This is what we see: "+response);
                            }
                        },
                        beforeSend:function(){
                            if($("#suggestions").html().indexOf("Suggestions show up here")!=-1){
                                $("#suggestions").html("Searching ...");
                            }
                        }
                    });
                }
            }
        }
    };
    $scope.displayContent=function(){
        if(validate($scope.contentArray)){
            var content=$scope.contentArray;
            $("#suggestions").html('<small>Results found: </small><hr>');
            var text='';
            for(var i=0;i<content.length;i++){
                var contentRow=content[i];
                var contentID=content.idcontent_master;
                var contentValue=stripslashes(contentRow.content_value);
                $("#parser").html(contentValue);
                var parsed=$("#parser").text();
                $("#parser").html('');
                if(parsed.length>100){
                    parsed=parsed.substring(0,100)+' ...';
                }
                var contentType=contentRow.content_type_master_idcontent_type_master;
                var contentTypeName=contentType.content_type;
                text+='<div class="panel panel-default"><div class="panel-heading">'+contentTypeName+' result</div><div class="panel-body">'+parsed+'</div><div class="panel-footer"><div class="text-right"><div class="btn-group"><button type="button" class="btn btn-default btn-sm" ng-click="showFullContent('+contentID+')">Read full text</button><button type="button" class="btn btn-info btn-sm">Include <span class="glyphicon glyphicon-chevron-right"></span></button></div></div></div></div>';
            }
            text+='<br><br>';
            $("#suggestions").append(text);
            $compile("#suggestions")($scope);
        }
    };
    $scope.showFullContent=function(contentID){
        var content=$scope.contentArray.slice();
        var pos=null;
        for(var i=0;i<content.length;i++){
            var cont=content[i];
            console.log(cont);
            if(cont.idcontent_master==contentID){
                pos=i;
                break;
            }
        }
        console.log(pos);
        if(pos!=null){
            var contentData=content[pos];
            console.log(contentData);
            var contentValue=stripslashes(contentData.content_value);
            $("#parser").html(contentValue);
            var parsed=$("#parser").text();
            $("#parser").html('');
            messageBox("Full Content",contentValue);
        }
    };
});
var appURL=angular.module("sencaurl",[]);
appURL.config(function($interpolateProvider){
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
});
appURL.controller("url",function($scope,$compile,$http){
    $scope.url=null;
    $scope.urlArray=[];
    $scope.addURL=function(){
        var url=$.trim($("#url").val());
        if(validate(url)){
            $("#url").parent().removeClass("has-error");
            $("#url").val('');
            $.ajax({
                url: "url/addURL",
                method: "POST",
                data: {
                    url: url
                },
                error: function(err){
                    console.log(err);
                    messageBox("Problem","Something went wrong while adding this URL. Please try again later.");
                },
                success: function(response){
                    $("#addbut").removeClass("disabled");
                    if((validate(response))&&(response!="INVALID_PARAMETERS")){
                        if(response=="URL_ALREADY_EXISTS"){
                            messageBox("Already Added","This URL has already been added.");
                        }
                        else if(response.indexOf("URL_ADDED_")!=-1){
                            $scope.getAddedURLs();
                        }
                        else{
                            messageBox("Problem","Something went wrong while adding this product. Please try again later. This is the error we see: "+response);
                        }
                    }
                    else{
                        messageBox("Problem","Something went wrong while adding this product. Please try again later.");
                    }
                },
                beforeSend: function(){
                    $("#addbut").addClass("disabled");
                }
            });
        }
        else{
            $("#url").parent().addClass("has-error");
        }
    };
    $scope.getAddedURLs=function(){
        $http.get("url/getAddedURLs")
        .then(function success(response){
            response=response.data;
            if(typeof response=="object"){
                $scope.urlArray=response;
                $scope.displayURLs();
            }
            else{
                response=$.trim(response);
                switch(response){
                    case "INVALID_PARAMETERS":
                    default:
                    messageBox("Problem","Something went wrong while loading past added URLs. This is the error we see: "+response);
                    break;
                    case "NO_URLS_FOUND":
                    $("#urllist").append('<p>No URLs found. Add one now!</p>');
                    break;
                }
            }
        },
        function error(response){
            console.log(response);
            messageBox("Problem","Something went wrong while fetching past URLs. Please try again later.");
        });
    };
    $scope.displayURLs=function(){
        if(validate($scope.urlArray)){
            var urls=$scope.urlArray;
            var table='<table class="table"><thead><tr><th>URL</th><th>Status</th><th>Actions</th></thead><tbody>';
            for(var i=0;i<urls.length;i++){
                var url=urls[i];
                var urlID=url.idurl_master;
                var link=url.url;
                var status=parseInt(url.stat);
                if(status==1){
                    var statusText='<span class="text-success">Extracted</span>';
                }
                else{
                    var statusText='<span class="text-warning">Pending</span>';
                }
                table+='<tr><td><a href="'+link+'" target="_blank">'+link+'</a></td><td>'+statusText+'</td><td><div class="btn-group">';
                if(status==2){
                    table+='<button type="button" class="btn btn-primary btn-xs" ng-click="scrapeURL('+urlID+')">Extract</button>';
                }
                else{
                    table+='<button type="button" class="btn btn-success btn-xs">Extracted</button>';
                }
                table+='<button type="button" class="btn btn-danger btn-xs" ng-click="deleteURL('+urlID+')">Delete</button></div></td></tr>';
            }
            table+='</tbody></table>';
            $("#urllist").html(table);
            $compile("#urllist")($scope);
        }
    };
    $scope.scrapeURL=function(urlID){
        $http.get("extract/"+urlID)
        .then(function success(response){
            response=$.trim(response.data);
            switch(response){
                case "INVALID_PARAMETERS":
                default:
                messageBox("Problem","Something went wrong while processing this URL. Please try again later. This is the error we see: "+response);
                break;
                case "INVALID_URL_ID":
                messageBox("Invalid URL","The URL you're trying to process is inavlid.");
                break;
                case "NO_CONTENT_FOUND":
                case "INVALID_CONTENT":
                messageBox("No Content","There was no content found in the URL.");
                break;
                case "INVALID_CONTENT_TYPE_ID":
                messageBox("Invalid Content Type","Something went wrong with the content type processed. Please try again later.");
                break;
                case "CONTENT_ALREADY_ADDED":
                messageBox("Content Already Added","This URL has already been processed.");
                break;
                case "CONTENT_ADDED":
                messageBox("URL Processed","The URL was process successfully!");
                $scope.getAddedURLs();
                break;
            }
        },
        function error(response){
            console.log(response);
            messageBox("Problem","Something went wrong while trying to process this URL. Please try again later.");
        });
    };
    $scope.deleteURL=function(urlID){
        $http.get("url/delete/"+urlID)
        .then(function success(response){
            response=$.trim(response.data);
            switch(response){
                case "INVALID_PARAMETERS":
                default:
                messageBox("Problem","Something went weong while deleting this URL. This is the error we see: "+response);
                break;
                case "URL_DELETED":
                messageBox("URL Deleted","The URL was deleted successfully.");
                $scope.getAddedURLs();
                break;
                case "INVALID_URL_ID":
                messageBox("Invalid URL","The URl you are trying to delete is invalid.");
                break;
            }
        },
        function error(response){
            console.log(response);
            messageBox("Problem","Something went wrong while deleting this URL.");
        });
    };
});