<?php
	
	return [
			
		// Core Config
		'Yandex' => [
			
			'AppID' => '0ffadb1d0bd94174ad728a4a3dfefe4b',
			'AppSecret' => '2f68192d71cc484fa17e482ca1ba742f',
			'AppToken' => file_get_contents(__DIR__.'/../../yandex_token.txt'),
			'RefreshToken' => file_get_contents(__DIR__.'/../../yandex_refresh.txt'),
			
		],
		
		'secret' => '1e44f1edbe3474038f580a1bc3170071',
	];