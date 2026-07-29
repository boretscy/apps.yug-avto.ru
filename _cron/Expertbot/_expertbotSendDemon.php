<?php
#!/usr/bin/php
	
ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
include $_SERVER['DOCUMENT_ROOT'].'/core/App.php';

// $app->Expertbot->truncNotifications();

if ( time() > strtotime(date('Y-m-d 10:09')) && time() < strtotime(date('Y-m-d 17:51')) ) {

    $users = $app->Expertbot->getDBActiveUsers();
    
    foreach ( $users as $user ) {

        if ( !$user['is_admin'] ) {
            
            Helper::sp($user, false, 'User');

            $items = $app->Expertbot->getCronItems($user['id'], date('Y-m-d 00:00:00'), date('Y-m-d H:i:s'));
            if ( count($items) == 1 && $items[0]['timestamp'] < time()-10*60 && !$user['stat_id'] ) {
                if ( !$app->Expertbot->getNotification( $user['id'], 5 ) ) {
                    $app->Expertbot->setNotification( $user['id'], 5 );
                    $app->Expertbot->sendPostMessage( $user['chat_id'], 5 );
                }
            }

            $item = $app->Expertbot->getCronItem($user['id'], date('Y-m-d 00:00:00'), date('Y-m-d H:i:s'), false);
            if ( $item  && $item['timestamp'] < time()-10*60 ) {
                if ( !$app->Expertbot->getNotification( $user['id'], 4 ) ) {
                    $app->Expertbot->setNotification( $user['id'], 4 );
                    $app->Expertbot->setNotificationForItem( $item['id'] );
                    $app->Expertbot->sendPostMessage( $user['chat_id'], 4 );
                }
            }
        }
    }

} else {
    
    $app->Expertbot->truncNotifications();
}
