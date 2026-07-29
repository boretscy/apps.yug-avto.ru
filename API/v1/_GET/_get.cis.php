<?php
if ( $_GET['debug'] ) {
    // ini_set('error_reporting', E_ALL);
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);

}

$route = $app->Route->getAPIRoute( $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] );
$request = json_decode(file_get_contents('php://input'),true);
if ( !$request ) $request = $_POST;

switch ( $route->id ) {

    case 'filter': $apiRes = $app->Cis->apiDBGetFilter( $route->entity, $_GET ); break;
    case '__filter': $apiRes = $app->Cis->__apiDBGetFilter( $route->entity, $_GET ); break;
    case 'brands': $apiRes = $app->Cis->apiDBGetBrands( $route->entity, $_GET ); break;
    case '__brands': $apiRes = $app->Cis->__apiDBGetBrands( $route->entity, $_GET ); break;
    case 'brandsmodels': $apiRes = $app->Cis->apiDBGetBrandsModels( $route->entity, $_GET ); break;
    case 'footerbrands': $apiRes = $app->Cis->apiDBGetBrandsForFooters( $route->entity, $_GET ); break;
    case 'filter-brands': $apiRes = $app->Cis->apiGetFilterBrands( $route->entity, $_GET ); break;
    case 'brand': $apiRes = $app->Cis->apiDBGetBrand($route->entity, $route->item, $_GET); break;
    case 'models': $apiRes = $app->Cis->apiDBGetModels( $route->entity, $_GET ); break;
    case '__models': $apiRes = $app->Cis->__apiDBGetModels( $route->entity, $_GET ); break;
    case 'model': $apiRes = $app->Cis->apiDBGetModel( $route->entity, $route->item, $_GET); break;
    case 'vehicles': $apiRes = $app->Cis->apiDBGetVehicles($route->entity, $_GET); break;
    case '__vehicles': $apiRes = $app->Cis->__apiDBGetVehicles($route->entity, $_GET); break;
    case 'item': 
    case 'vehicle': 
        $apiRes = $app->Cis->apiDBGetVehicle($route->entity, $route->item, $_GET['brand'], $_GET); break;
    case '__vehicle': 
        $apiRes = $app->Cis->__apiDBGetVehicle($route->entity, $route->item, $_GET['brand'], $_GET); break;
    case 'qrfeed': $apiRes = $app->Cis->apiDBQRFeed($route->entity, $route->item); break;
    case 'count': $apiRes = $app->Cis->apiDBGetCount( $route->entity, $_GET ); break;
    case '__count': $apiRes = $app->Cis->__apiDBGetCount( $route->entity, $_GET ); break;
    case 'modelscount': $apiRes = $app->Cis->apiDBGetModelsCount( $route->entity, $_GET ); break;
    case 'others': $apiRes = $app->Cis->apiDBGetOther( $route->item ); break;
    case 'limit': $apiRes = $app->Cis->apiDBGetLimitVehicles($route->entity, $_GET); break;
    case 'random': $apiRes = $app->Cis->apiDBGetRandomVehicles($route->entity, $_GET); break;
    case 'random_new': $apiRes = $app->Cis->apiDBGetRandomVehicles_NEW($route->entity, $_GET); break;
    case 'dealerships': $apiRes = $app->Cis->apiDBGetDealerships($route->entity, $_GET); break;
    case 'meta': $apiRes = $app->Cis->getDirectMeta($route->entity, $_GET); break;
    case '_meta': $apiRes = $app->Cis->_getDirectMeta($route->entity, $_GET); break;
    case 'script': 
        $apiRes = $app->Cis->getScript();
        echo $apiRes; die;
        break;
    


    case 'send': $apiRes = $app->Cis->pushStat( $request ); break;
    case 'story': $apiRes = $app->Cis->getStory( $route->item ); break;
    case 'search': $apiRes = $app->Cis->searchVehicles( $_GET ); break;
    // case 'test': $apiRes = $app->Cis->_pushStat( $request ); break;

    default: $apiRes = Helper::getRes(101); break;
}

echo json_encode( $apiRes );