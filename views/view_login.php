<?php
	switch ($_GET['action']) {
		
		case 'signup':
			include __DIR__.'/layouts/forms/_signup_form.php';
			break;
			
		case 'recovery':
			include __DIR__.'/layouts/forms/_recovery_form.php';
			break;
			
		default:
			include __DIR__.'/layouts/forms/_signin_form.php';
			break;
	}
	
	// Helper::sp($currentRote);
?>