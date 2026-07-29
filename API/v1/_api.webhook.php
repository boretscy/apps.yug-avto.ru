<?php
	
	$APIres = $app->Chat->pushHook( $_POST, $user );
	echo (isset($_POST['callback']) ? $_POST['callback'] : '').json_encode($APIres);