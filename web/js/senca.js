var app=angular.module("senca",[]);
app.controller("editor",function($scope,$compile,$http){
    $scope.editor=null;
    $scope.loadEditor=function(){
        var option={
            debug: 'info',
            modules:{
                toolbar: '#toolbar'
            },
            placeholder: 'Start writing ...',
            theme: 'snow'
        };
        $scope.editor=new Quill("#editor", options);
    };
});