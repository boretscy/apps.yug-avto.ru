<?php
	if ( $_POST ) {
		
		$_POST = Helper::VPost( $_POST );
		$_GET = Helper::VPost( $_GET );
		
		switch ($_POST['form']) {

			case 'signup':

				$POSTRes = $app->User->add($_POST, false);
				if ($POSTRes->status == 'success') $app->Route->redirect('/');
				break;

			case 'signin':

				$POSTRes = $app->User->AUth($_POST);
				if ($POSTRes->status == 'success') $app->Route->redirect( $_SERVER['REQUEST_URI']);
				break;
			
			case 'recovery':

				$POSTRes = $app->User->Recovery($_POST);
				//if ($POSTRes->status == 'success') $app->Route->redirect('/');
				break;

		}
	}
?>