<?php
#!/usr/bin/php
/*
	ini_set('error_reporting', E_ALL);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
*/
	$_SERVER['DOCUMENT_ROOT'] = '/home/u2064/domains/cabinet.cust.one';
		
	include $_SERVER['DOCUMENT_ROOT'].'/core/application.php';	
	$users = $app->getUsers('all');
	
	foreach ($users as $user) {
		
		if ($user->active == 1 && $user->balance >= $user->plan->cost) {
			
			$arIns = [
				'balance' => $user->balance - $user->plan->cost
			];
			$app->db->query('UPDATE wd_users SET ?u WHERE id = ?i', $arIns, $user->id);
		}
	}
?>