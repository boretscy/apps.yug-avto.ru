<?php
#!/usr/bin/php
	
ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
include $_SERVER['DOCUMENT_ROOT'].'/core/App.php';

$users = $app->Expertbot->getDBActiveUsers();
foreach ( $users as $user ) {
    
    if ( time() > strtotime(date('Y-m-d 13:51')) && time() < strtotime(date('Y-m-d 14:09')) ) $type = 2;
    if ( time() > strtotime(date('Y-m-d 15:31')) && time() < strtotime(date('Y-m-d 16:09')) ) $type = 3;

    if ( !$user['is_admin'] )
        if ( count($app->Expertbot->getCronItems($user['id'], date('Y-m-d 00:00:00'), date('Y-m-d H:i:s'))) <= 1 ) 
            $app->Expertbot->sendPostMessage( $user['chat_id'], $type );
}