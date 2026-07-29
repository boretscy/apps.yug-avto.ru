<?php
	$POSTRes = $app->User->update($_POST, $_FILES, $_SESSION['SSID'], ($_POST['from_admin']=='Y') ? true : false);