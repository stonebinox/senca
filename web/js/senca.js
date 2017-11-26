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
    $scope.addURL=function(){
        var url=$.trim($("#url").val());
        if(validate(url)){
            $("#url").parent().removeClass("has-error");

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
                console.log(response);
            }
            else{
                response=$.trim(response);
                console.log(response);
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
});