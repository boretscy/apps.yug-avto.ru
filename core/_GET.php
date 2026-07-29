<?php
	switch ($app->Route->getCurrentRoute($_SERVER['REQUEST_URI'])->section) {
		
		case 'user':
			
			switch ($_GET['action']) {
		
				case 'logout':
					$app->User->unAUth();
					$app->Route->redirect('/');
					break;
			}
			
			break;
			
			
		default:
			break;
	}
?>