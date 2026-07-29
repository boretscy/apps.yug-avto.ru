<?
	if ($_POST) {
		include $_SERVER['DOCUMENT_ROOT'].'/core/_POST.php';
	}
	
	if ($_GET)
		include $_SERVER['DOCUMENT_ROOT'].'/core/_GET.php';
		
	if ($_GET['action'] == 'signup') {
		
		include $_SERVER['DOCUMENT_ROOT'].'/views/_signup_form.php';
		
	} elseif ($_GET['action'] == 'recovery') {
		
		include $_SERVER['DOCUMENT_ROOT'].'/views/_recovery_form.php';
		
	} else {
		
		include $_SERVER['DOCUMENT_ROOT'].'/views/_signin_form.php';
	}
?>