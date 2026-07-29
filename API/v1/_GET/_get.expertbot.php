<?php
// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

$route = $app->Route->getAPIRoute( $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
$request = json_decode(file_get_contents('php://input'),true);

switch ( $route->id ) {

    case 'users': $apiRes = $app->Expertbot->apiDBGetUsers( $_GET ); break;
    case 'items': $apiRes = $app->Expertbot->apiDBGetItems( $_GET ); break;
    case 'data': $apiRes = $app->Expertbot->apiDBGetData(); break;

    case 'script': 
        $apiRes = $app->Expertbot->getScript();
        echo $apiRes; die;
        break;


        

    default: $apiRes = Helper::getRes(101); break;
}

echo json_encode( $apiRes );