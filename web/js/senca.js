var app=angular.module("senca",[]);
app.controller("editor",function($scope,$compile,$http){
    $scope.editor=null;
    $scope.loadEditor=function(){
        var options={
            placeholder: 'Start writing ...',
            theme: 'snow'
        };
        $scope.editor=new Quill("#editor", options);
    };
});