var app=angular.module("senca",[]);
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
});