<?php

ini_set('display_errors', 1);
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../config/prod.php';
require __DIR__.'/../src/controllers.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stderr',
));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
      'driver' => 'pdo_mysql',
      'dbname' => 'heroku_2167e3847bc7c6e',
      'user' => 'b96c909064c4af',
      'password' => 'd48c0b81',
      'host'=> "us-cdbr-iron-east-05.cleardb.net",
    )
));
$app->register(new Silex\Provider\SessionServiceProvider, array(
    'session.storage.save_path' => dirname(__DIR__) . '/tmp/sessions'
));
$app->before(function(Request $request) use($app){
    $request->getSession()->start();
});
$app->get("/",function() use($app){
    return $app['twig']->render("index.html.twig");
});
$app->get("/add",function() use($app){
    return $app['twig']->render("add.html.twig");
});
$app->post("/url/addURL",function(Request $request) use($app){
    if($request->get("url"))
    {
        require("../classes/urlMaster.php");
        $url=new urlMaster;
        $response=$url->addURL($request->get("url"));
        return $response;
    }
    else
    {
        return "INVALID_PARAMETERS";
    }
});
$app->get("/url/getAddedURLs",function() use($app){
    require("../classes/urlMaster.php");
    $url=new urlMaster;
    $urls=$url->getURLs();
    if(is_array($urls))
    {
        return json_encode($urls);
    }
    return $urls;
});
$app->get("/url/getURLCount",function() use($app){
    require("../classes/urlMaster.php");
    $url=new urlMaster;
    $response=$url->countURLs();
    return $response;
});
$app->get("/test",function() use($app){
    $url='https://profiles.stanford.edu/proxy/api/cap/search/keyword?p=1&q=aaa&ps=10';
    $json=file_get_contents($url);
    $json=json_encode($json);
    return var_dump($json);
});
$app->run();
?>