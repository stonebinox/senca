var app=angular.module("senca",[]);
app.config(function($interpolateProvider){
    $interpolateProvider.startSymbol('{[{').endSymbol('}]}');
});
app.controller("editor",function($scope,$compile,$http){
    $scope.editor=null;
    $scope.loadEditor=function(){
        ContentTools.StylePalette.add([
            new ContentTools.Style("Author","author",["p"])
        ]);
        $scope.editor=ContentTools.EditorApp.get();
        $scope.editor.init('*[data-editable]','data-name');
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
                    table+='<button type="button" class="btn btn-primary btn-xs">Extract</button>';
                }
                else{
                    table+='<button type="button" class="btn btn-success btn-xs">Extracted</button>';
                }
                table+='<button type="button" class="btn btn-danger btn-xs">Delete</button></div></td></tr>';
            }
            table+='</tbody></table>';
            $("#urllist").html(table);
        }
    };
});