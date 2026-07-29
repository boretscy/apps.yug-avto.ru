<?php 
	return [
		'secret' => md5('Аукцион Эксперт Ю-Авто'),
		'FileDir' => '/upload/Auction',
        'ExportDir' => '/upload/Export/Auction',
        'DefaultPass' => '!Qaz12345',

        'Items' => [
            'RenewalTime' => 300, // s
            'ResidualTime' => 60 // s
        ],
        
        'DefaultCosts' => [
            1000, 3000, 5000, 10000
        ],
	];
