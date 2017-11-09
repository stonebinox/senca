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