<?php
	
	$res['App'] = require 'YApps/Configs/App.php';
	
	foreach ( array_slice(scandir(__DIR__.'/YApps/Configs/Class'), 2) as $d ) if ( is_dir( __DIR__.'/YApps/Configs/Class/'.$d ) ) {
		
		foreach ( array_slice(scandir(__DIR__.'/YApps/Configs/Class/'.$d), 2) as $f ) {
			
			$name = explode('.', $f)[0];
			$parts = explode('.', $f); if ( !isset($parts[2]) ) $res['Core'][$d][$name] = require 'YApps/Configs/Class/'.$d.'/'.$f;
		}
	}
	
	foreach ( array_slice(scandir(__DIR__.'/YApps/Configs/App'), 2) as $f ) {
		
		$name = explode('.', $f)[0];
		$parts = explode('.', $f); if ( !isset($parts[2]) ) $res['modules'][$name] = require 'YApps/Configs/App/'.$f;
	}
	
	
	
	return $res;
	