var app=angular.module("senca",[]);
app.controller("editor",function($scope,$compile,$http){
    $scope.editor=null;
    $scope.loadEditor=function(){
        $scope.editor=new Quill("#editor");
    };
});