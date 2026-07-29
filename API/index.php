<?php
	
	error_reporting(0);
	ini_set('log_errors', 0);
	
	header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,HEAD,OPTIONS");
    header("Access-Control-Allow-Headers: Origin,Content-Type,Accept,Authorization");

	if ( (int)$_GET['debug'] == 1 ) {
		
		ini_set('error_reporting', E_ALL & ~E_NOTICE);
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
	}
    
    /*
	if ( parse_url($_SERVER['HTTP_REFERER'])['host'] == 'localhost' ) {
		ini_set('error_reporting', E_ALL);
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
	}
	*/
    include __DIR__.'/../core/App.php';
	
    $route = $app->Route->getCurrentRoute( $_SERVER['REQUEST_URI'] );
	if ( !$_POST['AppName'] ) $_POST = json_decode(file_get_contents('php://input'), true);
	
	if ( $_GET && $_GET['token'] ) {

		// if ( $_GET['token'] == '34b5ac8b71018c0bc7e5c050ed90b243' ) {
		// 	header("HTTP/1.0 403 Forbidden");
		// 	die;
		// }
        
        if ( $_GET['r'] ) $_SERVER['HTTP_REFERER'] = $_GET['r']; 
        $user = $app->YApps_GetUserByToken( $_GET['token'] );
		$_POST = Helper::VPost( $_POST );
		
		// Helper::sp( $route );
		
        $inc_file = ( file_exists(__DIR__.'/v1/_api.'.$route->view.'.php') ) ? __DIR__.'/v1/_api.'.$route->view.'.php' : __DIR__.'/v1/_api.default.php';
		include $inc_file;
	
	} else {
		
		// Helper::sp( $route );

		switch ( $route->view ) {
			
			case 'chat':
				
				switch ( $route->action ) {
					
					case 'webhook':
						
						//file_put_contents(__DIR__.'/POST/post__chat_.json', file_get_contents('php://input'));
						$obJson = json_decode( file_get_contents('php://input') );
						//$obJson = json_decode( file_get_contents( $_SERVER['DOCUMENT_ROOT'].'/API/POST/post_2018-09-25__13-52.json' ) );
						
						if ( $obJson->client->customData ) $app->Chat->setHookEvent( $obJson );
						break;
						
					default:
                        
                        $APIRes = Helper::getRes(101);
                        echo $APIRes->description;
						break;
				}
			
                break;
            
            //CallTouch
            case 'calltouch':
				
				switch ( $route->action ) {
					
					case 'webhook':
						
                        // file_put_contents(__DIR__.'/POST/ct_post_j.json', json_encode($_POST));
						$POST = ( $_POST ) ?: file_get_contents('php://input');
						if ( $POST ) $app->Calltouch->setHookEvent( $POST );
						break;

					case 'results':
						
                        // file_put_contents(__DIR__.'/POST/ct_post_j.json', json_encode($_POST));
						$POST = ( $_POST ) ?: file_get_contents('php://input');
						if ( $POST ) $app->Calltouch->setResult( $POST );
						break;
						
					default:
                        
                        $APIRes = Helper::getRes(101);
                        echo $APIRes->description;
						break;
				}
			
                break;
            
            // Widgets
            case 'widgets':
				
				switch ( $route->action ) {
					
					case 'webhook':
						
						if ( $_POST ) $app->Widgets->setOldEvent( array_merge($_POST, ['user_agent'=>$_SERVER['HTTP_USER_AGENT']]), $_SERVER['HTTP_REFERER'], $_SERVER['HTTP_X_REAL_IP'] );
						break;
						
					default:
                        
                        $APIRes = Helper::getRes(101);
                        echo $APIRes->description;
						break;
				}
			
                break;
            
            // Sale
            case 'sale':
				
				switch ( $route->action ) {
					
					case 'webhook':
											
						if ( $_POST ) $app->Sale->setEvent( array_merge($_POST, ['user_agent'=>$_SERVER['HTTP_USER_AGENT']]), $_SERVER['HTTP_REFERER'], $_SERVER['HTTP_X_REAL_IP'] );
						break;
						
					default:
                        
                        $APIRes = Helper::getRes(101);
                        echo $APIRes->description;
						break;
				}
			
				break;
            
            // Attak
			case 'attack':
				
				if ( $_POST ) {
					
					$APIRes = $app->Attack->pushStat( $_POST );
					//file_put_contents(__DIR__.'/../API/post-a.txt', json_encode($_POST));
					
					if ( $attack = $app->Attack->isAttack( $APIRes ) ) $app->Attack->CFBanIP( $attacks );
				}
				
				break;
            
            // Hot 
			case 'Hot':
				break;
            
            // Client 
			case 'Client':
				
				break;
            
            // Foots 
			case 'Foots':
				
				switch ( $route->action ) {
					
					case 'auth':
						
						$APIRes = $app->Foots->authHostess( json_decode(file_get_contents('php://input'), true) );
						echo json_encode( $APIRes );
						break;
					
					default:
						$APIRes = Helper::getRes(101);
						echo $APIRes->description;
						break;
				}
				
				
				break;
				
			// Auction 
			case 'Auction':
				
				$APIRes = Helper::getRes(101);
                echo $APIRes->description;
				break;

			// SendMail
			case 'Send':

				$APIRes = $app->SendMail( file_get_contents('php://input') );
				echo json_encode( $APIRes );
				break;
			
			case 'get':
				switch ( $route->action ) {
					case 'testpoint':
						$r = rand(1, 99);
						$APIRes = ['error'=>'Данные не отправлены'];
						if ( $_POST ) {
							$APIRes = [
								'status' => (($r>50)?true:false),
								// 'method' => 'POST'
							];
						} elseif ( file_get_contents('php://input') ) {
							$APIRes = [
								'status' => (($r>50)?true:false),
								// 'method' => 'php://input'
							];
						}
						echo json_encode( $APIRes );
						break;
				}
				break;
				
			default:
				$APIRes = Helper::getRes(101);
                echo $APIRes->description;
				break;
		}
	}
?>