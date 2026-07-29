<?
	/* Classes */
	$classes_conf = [
		'SafeMySQL',
		'PHPMailer',
		'Route',
		'Helper',
		'OAuth',
		'Parser',
		'App',
		'User'
	];
	
	$config = [
		
		// DB Connection
		'db' => [
			'host'      => 'localhost',
			'user'      => 'admin_apps',
			'pass'      => 'n3oCTvk9NL',
			'db'        => 'admin_apps',
		],
		
		// URL's
		'AppURLs' => [
			'404',
			'user',
			'widgets',
			'stat',
			'chat'
		],
		
		// URL's
		'APIURLs' => [
			
			'WEB' => [
				'widget',
				'stat',
				'notify'
			],
			
			'API' => [
				
			]
		],
		
		//OAUTH
		'OAuth' => [
			'site'		=> 'apps.yug-avto.ru',
			'url'		=> 'https://oauth.yug-avto.ru/apiV1/',
			'token'		=> '6218bceede3bade31f766ca4af28c83f',
		],
		
		
		//Parser
		'Parser' => [
			'items' => [
				'avito' => [
					'urls' 	=> [
						'LR' => [
							'https://www.avito.ru/krasnodar/avtomobili/land_rover?s=101&user=1',
							'https://www.avito.ru/krasnodar/avtomobili/land_rover?p=2&s=101&user=1',
							'https://www.avito.ru/krasnodar/avtomobili/land_rover?p=3&s=101&user=1',
						],
						'J' => [
							'https://www.avito.ru/krasnodar/avtomobili/jaguar?s=101&user=1',
							'https://www.avito.ru/krasnodar/avtomobili/jaguar?p=2&s=101&user=1',
						],
					],
					
					'xpath'	=> [
						'query'	=> "//*[contains(concat(' ', normalize-space(@class), ' '), ' js-catalog-item-enum ')]",
					]
				],
				
				'auto' => [
					'url' 	=> [
						'LR' =>'https://auto.ru/krasnodar/cars/land_rover/used/?beaten=1&customs_state=1&geo_id=35&geo_radius=200&dealer_org_type=4&image=true&sort_offers=fresh_relevance_1-DESC&top_days=off&currency=RUR&output_type=list&page_num_offers=',
						'J' => 'https://auto.ru/krasnodar/cars/jaguar/used/?beaten=1&customs_state=1&geo_id=35&geo_radius=200&dealer_org_type=4&image=true&sort_offers=fresh_relevance_1-DESC&top_days=off&currency=RUR&output_type=list&page_num_offers=',
					],
					
					'pages' => [
						'LR' => 4,
						'J' => 1,
					],
					
					'xpath'	=> [
						'query'	=> "//*[contains(concat(' ', normalize-space(@class), ' '), ' listing-item ')]",
					]
				],
				
				'keyauto' => [
					'url' 	=> [
						'LR' =>'https://auto.ru/diler-oficialniy/cars/all/kluchavto_krasnodar_land_rover/?beaten=0&dealer_code=kluchavto_krasnodar_land_rover&geo_id=35&geo_radius=200&image=true&sort_offers=fresh_relevance_1-DESC&top_days=off&currency=RUR&output_type=list&page_num_offers=',
						'J' => 'https://auto.ru/diler-oficialniy/cars/all/kluchavto_krasnodar_jaguar/?beaten=0&dealer_code=kluchavto_krasnodar_jaguar&geo_id=35&geo_radius=200&image=true&sort_offers=fresh_relevance_1-DESC&top_days=off&currency=RUR&output_type=list&page_num_offers=',
					],
					
					'pages' => [
						'LR' => 2,
						'J' => 1,
					],
					
					'xpath'	=> [
						'query'	=> "//*[contains(concat(' ', normalize-space(@class), ' '), ' listing-item ')]",
					]
				],
			],
			
			'config' => [
				'Days' => [
					'Сегодня' => 'today',
					'Вчера' => 'yesterday',
				],
				
				'Month' => [
					'января' => 'jan',
					'февраля' => 'feb',
					'марта' => 'mar',
					'апреля' => 'apr',
					'мая' => 'may',
					'июня' => 'jun',
					'июля' => 'jul',
					'августа' => 'aug',
					'сентября' => 'sep',
					'октября' => 'oct',
					'ноября' => 'nov',
					'декабря' => 'dec',
				],
			],
		],
		
		
		//ERRORS
		'errors' => [
			
			// AUth errors
			11 => 'Недопустимый e-mail',
			12 => 'Пользователь заблокирован или не существует',
			13 => 'Неправильный пароль',
			
			//Sign Up errors
			21 => 'Такой пользователь существует',
			22 => 'Недопустимый пароль. Пароль дожен быть не менее 8 символов длиной и состоять из латинских букв, цифр и символов !@#$%_',
			23 => 'Пароль не прошел валидацию.<br />Пароль дожен быть не менее 8 символов длиной и состоять из латинских букв, цифр и символов !@#$%_',
		],
		
		//User Config
		'user' => [
			'USERS_DIR' => '/upload/users',
			'PUB_DIR' => '/pub',
		],
		
	];
	
	$arConf = [
		
		//App Config
		'App' => [
			
			//DB Config
			'db' => [
				'host'      => 'localhost',
				'user'      => 'admin_apps',
				'pass'      => 'n3oCTvk9NL',
				'db'        => 'admin_apps',
			],
			
			
			// Core Config
			'classes' => [
				
				// Application
				'App',
				// Vendor
				'SafeMySQL',
				'PHPMailer',
				// Core
				'Route',
				'Helper',
				'HTML',
				'Apps',
				// Users
				'User',
				'Admin',
				'Root',
				// Apps
				'Parser',
				'Chat',
				'Widget'
				
			],
			
			'secret' => '1e44f1edbe3474038f580a1bc3170071',
		],
		
		
		// Modules Area
		
		'modules' => [
		
			//Router  Config
			'Route' => [
				
				// URL's
				'AppURLs' => [
					'404',
					'user',
					'widgets',
					'stat',
					'chat'
				],
				
				// URL's
				'APIURLs' => [
					
					'WEB' => [
						'widget',
						'stat',
						'notify'
					],
					
					'API' => [
						
					]
				],
			],
			
			//User Config
			'User' => [
			
				'USERS_DIR' => '/upload/users',
				'PUB_DIR' => '/pub',
			],
			
			
			//Parser
			'Parser' => [
				'items' => [
					'avito' => [
						'urls' 	=> [
							'LR' => [
								'https://www.avito.ru/krasnodar/avtomobili/land_rover?s=101&user=1',
								'https://www.avito.ru/krasnodar/avtomobili/land_rover?p=2&s=101&user=1',
								'https://www.avito.ru/krasnodar/avtomobili/land_rover?p=3&s=101&user=1',
							],
							'J' => [
								'https://www.avito.ru/krasnodar/avtomobili/jaguar?radius=300&user=1',
								'https://www.avito.ru/krasnodar/avtomobili/jaguar?p=2&radius=300&user=1',
							],
						],
						
						'xpath'	=> [
							'query'	=> "//*[contains(concat(' ', normalize-space(@class), ' '), ' js-catalog-item-enum ')]",
						]
					],
					
					'auto' => [
						'url' 	=> [
							'LR' =>'https://auto.ru/krasnodar/cars/land_rover/used/?beaten=1&customs_state=1&geo_id=35&geo_radius=200&dealer_org_type=4&image=true&sort_offers=fresh_relevance_1-DESC&top_days=off&currency=RUR&output_type=list&page_num_offers=',
							'J' => 'https://auto.ru/krasnodar/cars/jaguar/used/?beaten=1&customs_state=1&geo_id=35&geo_radius=200&dealer_org_type=4&image=true&sort_offers=fresh_relevance_1-DESC&top_days=off&currency=RUR&output_type=list&page_num_offers=',
						],
						
						'pages' => [
							'LR' => 4,
							'J' => 1,
						],
						
						'xpath'	=> [
							'query'	=> "//*[contains(concat(' ', normalize-space(@class), ' '), ' listing-item ')]",
						]
					],
				],
				
				'config' => [
					'Days' => [
						'Сегодня' => 'today',
						'Вчера' => 'yesterday',
					],
					
					'Month' => [
						'января' => 'jan',
						'февраля' => 'feb',
						'марта' => 'mar',
						'апреля' => 'apr',
						'мая' => 'may',
						'июня' => 'jun',
						'июля' => 'jul',
						'августа' => 'aug',
						'сентября' => 'sep',
						'октября' => 'oct',
						'ноября' => 'nov',
						'декабря' => 'dec',
					],
				],
			],
		],
		
	];

?>