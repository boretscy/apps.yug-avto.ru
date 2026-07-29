<?php
#!/usr/bin/php
	$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
	include $_SERVER['DOCUMENT_ROOT'].'/core/application.php';
	
	$app->Parser->getAutoSTR('LR');
?>