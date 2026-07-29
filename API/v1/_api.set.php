<?php
	
    $inc_file = ( file_exists(__DIR__.'/_SET/_set.'.$route->action.'.php') ) ? __DIR__.'/_SET/_set.'.$route->action.'.php' : __DIR__.'/_SET/_set.default.php';
	include $inc_file;
	