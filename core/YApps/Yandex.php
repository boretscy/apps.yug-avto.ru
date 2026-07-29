<?php

	class Yandex {
		
		private static function Headers( $q, $token ) {
			
			$res = [
				'Authorization: OAuth '.$token,
				"Accept: application/x-yametrika+json",
				"Content-Type: application/json",
				"Content-Length: ".strlen( $q )
			];
			
			return $res;
		}
		
		public static function getOAuthToken( $arConf, $code ) {

			$url = 'https://oauth.yandex.ru/token';
			$headers = "Content-Type: application/x-www-form-urlencoded";
			$params = [
				'grant_type' => 'authorization_code',
				'code' => $code,
				'client_id' => $arConf['AppID'],
				'client_secret' => $arConf['AppSecret']
			];
			$params = http_build_query($params);
			$opts = [
				'http' => [
					'method' => "POST",
					'header' => $headers,
					'content' => $params
				]
			];

			$context = stream_context_create($opts);

			$token = json_decode(file_get_contents($url, null, $context));

			file_put_contents($_SERVER['DOCUMENT_ROOT'].'/core/yandex_token.txt', $token->access_token);
			file_put_contents($_SERVER['DOCUMENT_ROOT'].'/core/yandex_refresh.txt', $token->refresh_token);
		}
		
		public static function refreshOAuthToken( $arConf ) {

			$url = 'https://oauth.yandex.ru/token';
			$headers = "Content-Type: application/x-www-form-urlencoded";
			$params = [
				'grant_type' => 'refresh_token',
				'refresh_token' => $arConf['RefreshToken'],
				'client_id' => $arConf['AppID'],
				'client_secret' => $arConf['AppSecret']
			];
			$params = http_build_query($params);
			$opts = [
				'http' => [
					'method' => "POST",
					'header' => $headers,
					'content' => $params
				]
			];

			$context = stream_context_create($opts);

			$token = json_decode(file_get_contents($url, null, $context));
			
			file_put_contents(__DIR__.'/../yandex_token.txt', $token->access_token);
			file_put_contents(__DIR__.'/../yandex_refresh.txt', $token->refresh_token);
			
			return $token;
		}

		public static function getGoals( $arConf, $counter ) {

			$url = 'https://api-metrika.yandex.ru/management/v1/counter/'.$counter.'/goals?oauth_token='.$arConf['AppToken'];

			return json_decode( file_get_contents($url) );
		}

		public static function setGoal( $arConf, $counter, $arGoal ) {

			$url = 'https://api-metrika.yandex.ru/management/v1/counter/'.$counter.(($arGoal['id'])?'/goal/'.$arGoal['id']:'/goals');
			
			if ( $arGoal['id'] ) $params['goal']['id'] = (int)$arGoal['id'];
			$params['goal']['name'] = $arGoal['name'];

			if ( $arGoal['url'] ) {

				$params['goal']['type'] = 'step';
				$params['goal']['steps'] = [
			    [
			        'name' => 'Посещение страницы',
			        'type' => 'url',
			        'conditions' => [
			            [
			                'type' => 'contain',
			                'url' => $arGoal['url']
			            ]
			        ]
			    ],
			    [
			        'name' => 'Отправка данных',
			        'type' => 'url',
			        'conditions' => [
			            [
			                'type' => 'action',
			                'url' => $arGoal['goal']
			            ],
			        ]
			    ]
			];

			} else {

				$params['goal']['type'] = 'action';
				$params['goal']['conditions'] = [
					[
						'type' => 'exact',
						'url' => $arGoal['goal']
					]
				];
			}
			
			$params = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
			$headers = self::Headers( $params, $arConf->AppToken );

			$opts = [
				'http' => [
					'method' => ( $arGoal['id'] ) ? 'PUT' : 'POST',
					'header' => $headers,
					'content' => $params
				]
			];

			$context = stream_context_create($opts);

			$res = json_decode(file_get_contents($url, null, $context));
			
			return $res;
		}
		
		public static function delGoal( $arConf, $counter, $id ) {
			
			// TODO
		}
	}
