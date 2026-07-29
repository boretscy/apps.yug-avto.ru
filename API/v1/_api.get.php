<?php
	
    $inc_file = ( file_exists(__DIR__.'/_GET/_get.'.$route->action.'.php') ) ? __DIR__.'/_GET/_get.'.$route->action.'.php' : __DIR__.'/_GET/_get.default.php';
	include $inc_file;
	