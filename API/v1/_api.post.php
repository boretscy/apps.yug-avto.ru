<?php
	
    $inc_file = ( file_exists(__DIR__.'/_POST/_post.'.$route->action.'.php') ) ? __DIR__.'/_POST/_post.'.$route->action.'.php' : __DIR__.'/_POST/_post.default.php';
	include $inc_file;
	