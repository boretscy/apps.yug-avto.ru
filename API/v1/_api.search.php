<?php
	
	$class = $route->action;
				
	$APIres = $app->$class->Search( $_POST, $user, $_SERVER['HTTP_X_REAL_IP'] );
	echo (isset($_POST['callback']) ? $_POST['callback'] : '').json_encode($APIres);