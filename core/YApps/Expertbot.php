<?php

	class Expertbot extends App {

        ////////////////////////////////////////////////////////////////
		// Consts  /////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////

		const TEST_CHAT = 381421466;


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Init ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, PHPMailer &$mailer ) {
			
			$this->MySQL	= &$mysql;
			$this->Conf		= (object)$arConf['modules'][get_class($this)];
			$this->Mailer	= &$mailer;
		}

        public function AppInfo() {
	
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_apps WHERE class = ?s', get_class($this));
		}

		private static function normalizeMonth( $q ) {

			if ($q <= 0 ) $q = $q+12;
			if ($q > 12 ) $q = $q-12;

			return $q;
		}

		public function getConf() {

			return $this->Conf;
		}


		///////////////////////////////////////////////////////////////////////////////////////////
        // Helpers ////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		private static function getMonth( $q ) {

			// $q = static::normalizeMonth( $q );

			switch ( $q ) {
				case 1: return 'Январь'; break;
				case 2: return 'Февраль'; break;
				case 3: return 'Март'; break;
				case 4: return 'Апрель'; break;
				case 5: return 'Май'; break;
				case 6: return 'Июнь'; break;
				case 7: return 'Июль'; break;
				case 8: return 'Август'; break;
				case 9: return 'Сентябрь'; break;
				case 10: return 'Октябрь'; break;
				case 11: return 'Ноябрь'; break;
				case 12: return 'Декабрь'; break;
			}
		}

		public function makeAddSTR( $user ) {

			$res = '';

			switch ( $user['step'] ) {
				case 'dealership':
					$arSTR[] = 'Выберите дилерский центр 👇';
					break;
				case 'type':
					$arSTR[] = 'Добавляемый отзыв:';
					$arSTR[] = '*******';
					/////////////////////////////////////////////////
					/***********************************************/
					$query_id = ( $user['dealership_id'] ) ?: $this->MySQL->getOne('SELECT dealership_id FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
					$arSTR['dealership'] = 'Дилерский центр: '.$this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_dealerships WHERE id = ?i', $query_id);
					/***********************************************/
					////////////////////////////////////////
					$arSTR[] = '*******';
					$arSTR[] = '';
					$arSTR[] = 'Выберите направление 👇';
					break;
				case 'departament':
					$arSTR[] = 'Добавляемый отзыв:';
					$arSTR[] = '*******';
					/////////////////////////////////////////////////
					/***********************************************/
					$query_id = ( $user['dealership_id'] ) ?: $this->MySQL->getOne('SELECT dealership_id FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
					$arSTR['dealership'] = 'Дилерский центр: '.$this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_dealerships WHERE id = ?i', $query_id);
					/***********************************************/
					$query_id = ( $user['type_id'] ) ?: $this->MySQL->getOne('SELECT type_id FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
					$arSTR['type'] = 'Направление: '.$this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_types WHERE id = ?i', $query_id);
					/***********************************************/
					/////////////////////////////////////////////////
					$arSTR[] = '*******';
					$arSTR[] = '';
					$arSTR[] = 'Выберите тип сделки 👇';
					break;
				case 'source':
					$arSTR[] = 'Добавляемый отзыв:';
					$arSTR[] = '*******';
					/////////////////////////////////////////////////
					/***********************************************/
					$query_id = ( $user['dealership_id'] ) ?: $this->MySQL->getOne('SELECT dealership_id FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
					$arSTR['dealership'] = 'Дилерский центр: '.$this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_dealerships WHERE id = ?i', $query_id);
					/***********************************************/
					$query_id = ( $user['type_id'] ) ?: $this->MySQL->getOne('SELECT type_id FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
					$arSTR['type'] = 'Направление: '.$this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_types WHERE id = ?i', $query_id);
					/***********************************************/
					$query_id = ( $user['departament_id'] ) ?: $this->MySQL->getOne('SELECT departament_id FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
					$arSTR['departament'] = 'Тип сделки: '.$this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_departaments WHERE id = ?i', $query_id);
					/***********************************************/
					/////////////////////////////////////////////////
					$arSTR[] = '*******';
					$arSTR[] = '';
					$arSTR[] = 'Выберите источник 👇';
					break;
				case 'date':
					$arSTR[] = 'Добавляемый отзыв:';
					$arSTR[] = '*******';
					/////////////////////////////////////////////////
					/***********************************************/
					$query_id = ( $user['dealership_id'] ) ?: $this->MySQL->getOne('SELECT dealership_id FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
					$arSTR['dealership'] = 'Дилерский центр: '.$this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_dealerships WHERE id = ?i', $query_id);
					/***********************************************/
					$query_id = ( $user['type_id'] ) ?: $this->MySQL->getOne('SELECT type_id FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
					$arSTR['type'] = 'Направление: '.$this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_types WHERE id = ?i', $query_id);
					/***********************************************/
					$query_id = ( $user['departament_id'] ) ?: $this->MySQL->getOne('SELECT departament_id FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
					$arSTR['departament'] = 'Тип сделки: '.$this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_departaments WHERE id = ?i', $query_id);
					/***********************************************/
					$query_id = $this->MySQL->getOne('SELECT source_id FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
					$arSTR['source'] = 'Источник: '.$this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_sources WHERE id = ?i', $query_id);
					/***********************************************/
					/////////////////////////////////////////////////
					$arSTR[] = '*******';
					$arSTR[] = '';
					$arSTR[] = 'Выберите дату отзыва 👇';
					break;
				case 'screenshot':
					$arSTR[] = 'Добавляемый отзыв:';
					$arSTR[] = '*******';
					/////////////////////////////////////////////////
					/***********************************************/
					$query_id = ( $user['dealership_id'] ) ?: $this->MySQL->getOne('SELECT dealership_id FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
					$arSTR['dealership'] = 'Дилерский центр: '.$this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_dealerships WHERE id = ?i', $query_id);
					/***********************************************/
					$query_id = ( $user['type_id'] ) ?: $this->MySQL->getOne('SELECT type_id FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
					$arSTR['type'] = 'Направление: '.$this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_types WHERE id = ?i', $query_id);
					/***********************************************/
					$query_id = ( $user['departament_id'] ) ?: $this->MySQL->getOne('SELECT departament_id FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
					$arSTR['departament'] = 'Тип сделки: '.$this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_departaments WHERE id = ?i', $query_id);
					/***********************************************/
					$query_id = $this->MySQL->getOne('SELECT source_id FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
					$arSTR['source'] = 'Источник: '.$this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_sources WHERE id = ?i', $query_id);
					/***********************************************/
					$arSTR['date'] = 'Дата: '.$this->MySQL->getOne('SELECT date_feedback FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
					/***********************************************/
					/////////////////////////////////////////////////
					$arSTR[] = '*******';
					$arSTR[] = '';
					$arSTR[] = 'Отправьте скриншот 📷';
					break;
			}

			if ($arSTR) $res = PHP_EOL.PHP_EOL.implode($arSTR, PHP_EOL);

			return $res;
		}

		private static function makeRandUnknown() {

			$ar = [
				'Моя твоя не понимай',
				'Не знаю такой команды',
				'Ничего не понятно, но очень интересно'
			];

			return $ar[rand(0,count($ar)-1)];
		}

		private static function getMonthAction( $q ) {

			$test = [
				'⬅Январь',
				'⬅Февраль',
				'⬅Март',
				'⬅Аперль',
				'⬅Май',
				'⬅Июнь',
				'⬅Июль',
				'⬅Август',
				'⬅Сентябрь',
				'⬅Октябрь',
				'⬅Ноябрь',
				'⬅Декабрь',
				'Январь➡',
				'Февраль➡',
				'Март➡',
				'Аперль➡',
				'Май➡',
				'Июнь➡',
				'Июль➡',
				'Август➡',
				'Сентябрь➡',
				'Октябрь➡',
				'Ноябрь➡',
				'Декабрь➡',
			];

			$k = array_search($q, $test);
			if ( $k !== false ) return ( $k > 11 ) ? 'increment': 'decrement';
			return false;

		}

		private function getDate( $q, $id ) {

			$df = $this->MySQL->getRow('SELECT month, year FROM yapps_app_expertbot_stat WHERE id = ?i', $id);
			if ( (int)$q > 0 && (int)$q <= date('t', strtotime($df['year'].'-'.$df['month']) ) ) return $df['year'].'-'.$df['month'].'-'.$q;
			return false;
		}

		public function test() {
			
			$user = $this->MySQL->getRow('SELECT * FROM yapps_app_expertbot_users WHERE id = ?i', 16);
			Helper::sp( $user );

			$this->reSendPost( $user );
		}

		private function selectPhotoID( $q ) {

			if ( $q[2] ) return $q[2]['file_id'];
			if ( $q[1] ) return $q[1]['file_id'];
			if ( $q[0] ) return $q[0]['file_id'];
		}

		///////////////////////////////////////////////////////////////////////////////////////////
        // Bot ////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		private function doRequest(
			$data = [], 
			$method = 'GET', 
			$options = [],
			$api_method = 'sendMessage'
		)
		{
			$default_options = [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HEADER => false,
				CURLOPT_SSL_VERIFYPEER => false,
			];

			$url = 'https://api.telegram.org/bot'.$this->Conf->token.'/'.$api_method;
		
			if ($method === 'GET') {
				$url .= (strpos($url, '?') === false) ? '?' : '&';
				$url .= http_build_query($data);
			} 
			if ($method === 'POST') {
				$options[CURLOPT_POSTFIELDS] = http_build_query($data);
			} 
			if ($method === 'JSON') {
				$options[CURLOPT_POSTFIELDS] = json_encode($data);
				$options[CURLOPT_HTTPHEADER][] = 'Content-Type:application/json';
			}
		
			$ch = curl_init($url);
			curl_setopt_array($ch, array_replace($default_options, $options));
		
			$result = curl_exec($ch);
			if ($result === false) {
				throw new ErrorException("Curl error: ".curl_error($ch), curl_errno($ch));
			}
			curl_close($ch);
			return $result;
		}


		public function hook( $request ) {

			file_put_contents(__DIR__.'/../../API/data/Expertbot/r.json', json_encode($request));
			
			if ( $request['message']['entities'][0]['type'] == 'bot_command' ) {

				switch ( $request['message']['text'] ) {

					case '/start':
						file_put_contents(__DIR__.'/../../API/data/Expertbot/start.json', json_encode($request));
						$user = $this->getUser($request['message']['chat']['id']);
						if ( !$user ) {
							$this->MySQL->query(
								'INSERT INTO yapps_app_expertbot_users SET ?u',
								[
									'chat_id' => $request['message']['chat']['id'],
								]
							);
							$this->sendPostContact( $request['message']['chat']['id'] );
						} elseif ( !$user['phone'] || $user['step'] == 'contact') {
							$this->sendPostContact( $request['message']['chat']['id'] );
						} else {
							$this->setUser(
								$request['message']['chat']['id'],
								['step' => 'begin']
							);
							$this->sendPostStart( $request['message']['chat']['id'] );
						}
						break;

					case '/begin':

						$user = $this->getUser($request['message']['chat']['id']);
						if ( $user ) {
							if ( $user['is_admin'] ) {
								$this->sendPostMessage( $request['message']['chat']['id'] );
								exit;
							}
							/////////////////////////////////////////////////
							/***********************************************/
							if ( $user['stat_id'] ) $this->MySQL->query('DELETE FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
							$this->MySQL->query(
								'INSERT INTO yapps_app_expertbot_stat SET ?u',
								[
									'user_id' => $user['id'], 
									'user_name' => $user['name'],
									'month' => date('n'), 
									'year' => date('Y'),
									'timestamp' => time()
								]
							);
							$user['stat_id'] = $this->MySQL->insertId();
							$this->setUser(
								$request['message']['chat']['id'],
								['stat_id' => $user['stat_id']]
							);
							if ( !$user['dealership_id'] ) {
								$this->setUser(
									$request['message']['chat']['id'],
									['step' => 'dealership']
								);
								$user['step'] = 'dealership';
								$this->sendPostDealerships( $request['message']['chat']['id'], $this->makeAddSTR($user) );
								exit;
							} else {
								$this->MySQL->query('UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', ['dealership_id'=>$user['dealership_id']], $user['stat_id']);
							}
							/***********************************************/
							if ( !$user['type_id'] ) {
								$this->setUser(
									$request['message']['chat']['id'],
									['step' => 'type']
								);
								$user['step'] = 'type';
								$this->sendPostType( $request['message']['chat']['id'], $this->makeAddSTR($user) );
								exit;
							} else {
								$this->MySQL->query('UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', ['type_id'=>$user['type_id']], $user['stat_id']);
							}
							/***********************************************/
							if ( !$user['departament_id'] ) {
								$this->setUser(
									$request['message']['chat']['id'],
									['step' => 'departament']
								);
								$user['step'] = 'departament';
								$this->sendPostDepartament( $request['message']['chat']['id'], $this->makeAddSTR($user) );
								exit;
							} else {
								$this->MySQL->query('UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', ['departament_id'=>$user['departament_id']], $user['stat_id']);
							}
							$this->setUser(
								$request['message']['chat']['id'],
								['step' => 'source']
							);
							$user['step'] = 'source';
							$this->sendPostSource( $request['message']['chat']['id'], $this->makeAddSTR($user) );
							/////////////////////////////////////////////////
						}
						break;

					default:
						$this->sendPostMessage( $request['message']['chat']['id'] );
						$this->setUnknown($request['message']['chat']['id'], $request['message']['text']);
						break;
				}

			} elseif ( $request['my_chat_member'] ) {

				$user = $this->getUser($request['my_chat_member']['chat']['id']);
				if ( !$user ) {
					$this->MySQL->query(
						'INSERT INTO yapps_app_expertbot_users SET ?u',
						[
							'chat_id' => $request['my_chat_member']['chat']['id'],
						]
					);
					$this->sendPostContact( $request['my_chat_member']['chat']['id'] );
				} elseif ( !$user['phone'] || $user['step'] == 'contact') {
					$this->sendPostContact( $request['my_chat_member']['chat']['id'] );
				} else {
					$this->setUser(
						$request['my_chat_member']['chat']['id'],
						['step' => 'begin']
					);
					$this->sendPostStart( $request['my_chat_member']['chat']['id'] );
				}

			} elseif ( $request['message']['voice'] || $request['message']['sticker'] ) {

				$user = $this->getUser($request['message']['chat']['id']);
				if ( !$user ) {
					$this->MySQL->query(
						'INSERT INTO yapps_app_expertbot_users SET ?u',
						[
							'chat_id' => $request['message']['chat']['id'],
						]
					);
					$this->sendPostContact( $request['message']['chat']['id'] );
				} elseif ( !$user['phone'] || $user['step'] == 'contact') {
					$this->sendPostContact( $request['message']['chat']['id'] );
				} else {
					$this->setUser(
						$request['message']['chat']['id'],
						['step' => 'begin']
					);
					$this->sendPostStart( $request['message']['chat']['id'] );
				}

			} elseif ( $request['message']['contact'] ) {
				file_put_contents(__DIR__.'/../../API/data/Expertbot/contact.json', json_encode($request));
				$this->setUser(
					$request['message']['chat']['id'],
					[
						'phone' => $request['message']['contact']['phone_number'],
						'step' => 'begin'
					]
				);
				// $this->sendPostSuccessContact( $request['message']['chat']['id'] );
			
			} elseif ( $request['message']['photo'] ) {

				file_put_contents(__DIR__.'/../../API/data/Expertbot/photo.json', json_encode($request));

				$user = $this->getUser($request['message']['chat']['id']);
				if ( $user ) {

					if (
						$user['stat_id'] &&
						$user['step'] == 'screenshot'
						) {

						$f = $this->MySQL->getRow(
							'SELECT * FROM yapps_app_expertbot_stat WHERE id = ?i',
							$user['stat_id']
						);
						$file_path = json_decode(file_get_contents('https://api.telegram.org/bot'.$this->Conf->token.'/getFile?file_id='.static::selectPhotoID($request['message']['photo'])), true)['result']['file_path'];

						$file_name = date('YmdHis').'-f'.date('Ymd',strtotime($f['date_feedback']));
						$file_name .= '-u'.$user['ext_id'];
						$file_name .= '-dc'.$f['dealership_id'];
						$file_name .= '-t'.(($f['passenger'])?'passenger':'').(($f['commercial'])?'commercial':'');
						$file_name .= '-d'.(($f['sale'])?'sale':'').(($f['buyout'])?'buyout':'');
						$file_name .= '-s'.$f['source_id'];
						$file_name .= '.jpg';
						if ( copy('https://api.telegram.org/file/bot'.$this->Conf->token.'/'.$file_path, __DIR__.'/../..'.$this->Conf->FileDir.'/'.$file_name) ) {

							$this->MySQL->query(
								'UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', 
								[
									'screenshot' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/'.$file_name,
									'timestamp' => time()
								], 
								$user['stat_id']
							);
							$this->setUser(
								$request['message']['chat']['id'],
								[
									'stat_id' => 0,
									'step' => 'begin'
								]
							);
							$this->delNotification($user['id'], 4);
							$this->sendPostSuccess( $request['message']['chat']['id'] );

						} else {

							$this->sendPostError( $request['message']['chat']['id'] );
							$this->reSendPost( $user );
						}

					} else {

						$this->sendPostError( $request['message']['chat']['id'] );
						$this->reSendPost( $user );
					}
					
				}
			
			} elseif ( $request['message']['document'] ) {

				file_put_contents(__DIR__.'/../../API/data/Expertbot/document.json', json_encode($request));

				$user = $this->getUser($request['message']['chat']['id']);
				
				if ( $user['phone'] ) {

					if (
						$user['stat_id'] &&
						$user['step'] == 'screenshot' &&
						(
							$request['message']['document']['mime_type'] == 'image/jpeg' ||
							$request['message']['document']['mime_type'] == 'image/png'
						)
					) {
						
						$f = $this->MySQL->getRow(
							'SELECT * FROM yapps_app_expertbot_stat WHERE id = ?i',
							$user['stat_id']
						);
						$file_path = json_decode(file_get_contents('https://api.telegram.org/bot'.$this->Conf->token.'/getFile?file_id='.$request['message']['document']['file_id']), true)['result']['file_path'];
	
						$file_name = date('YmdHis').'-f'.date('Ymd',strtotime($f['date_feedback']));
						$file_name .= '-u'.$user['ext_id'];
						$file_name .= '-dc'.$f['dealership_id'];
						$file_name .= '-t'.(($f['passenger'])?'passenger':'').(($f['commercial'])?'commercial':'');
						$file_name .= '-d'.(($f['sale'])?'sale':'').(($f['buyout'])?'buyout':'');
						$file_name .= '-s'.$f['source_id'];
						if ( $request['message']['document']['mime_type'] == 'image/jpeg' ) $file_name .= '.jpg';
						if ( $request['message']['document']['mime_type'] == 'image/png' ) $file_name .= '.png';
	
						if ( copy('https://api.telegram.org/file/bot'.$this->Conf->token.'/'.$file_path, __DIR__.'/../..'.$this->Conf->FileDir.'/'.$file_name) ) {
	
							$this->MySQL->query(
								'UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', 
								[
									'screenshot' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/'.$file_name,
									'timestamp' => time()
								], 
								$user['stat_id']
							);
							$this->setUser(
								$request['message']['chat']['id'],
								[
									'stat_id' => 0,
									'step' => 'begin'
								]
							);
							$this->delNotification($user['id'], 4);
							$this->sendPostSuccess( $request['message']['chat']['id'] );
	
						} else {
	
							$this->sendPostError( $request['message']['chat']['id'] );
						}

					} else {
						
						$this->sendPostError( $request['message']['chat']['id'] );
						$this->reSendPost( $user );
					}
					
				}

			} else {

				$user = $this->getUser($request['message']['chat']['id']);
				if ( $user['phone'] ) {

					if ( $request['message']['text'] == '❌Сбросить и начать заново❌' ) {
						// file_put_contents(__DIR__.'/../../API/data/Expertbot/r.json', json_encode($request));
						if ( $user['is_admin'] ) {
							$this->sendPostMessage( $request['message']['chat']['id'] );
							exit;
						}
						/////////////////////////////////////////////////
						/***********************************************/
						if ( $user['stat_id'] ) $this->MySQL->query('DELETE FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
						$this->MySQL->query(
							'INSERT INTO yapps_app_expertbot_stat SET ?u',
							[
								'user_id' => $user['id'], 
								'user_name' => $user['name'],
								'month' => date('n'), 
								'year' => date('Y'),
								'timestamp' => time()
							]
						);
						$user['stat_id'] = $this->MySQL->insertId();
						$this->setUser(
							$request['message']['chat']['id'],
							['stat_id' => $user['stat_id']]
						);
						/***********************************************/
						if ( !$user['dealership_id'] ) {
							$this->setUser(
								$request['message']['chat']['id'],
								['step' => 'dealership']
							);
							$this->sendPostDealerships( $request['message']['chat']['id'], $this->makeAddSTR($user) );
							exit;
						} else {
							$this->MySQL->query('UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', ['dealership_id'=>$user['dealership_id']], $user['stat_id']);
						}
						/***********************************************/
						if ( !$user['type_id'] ) {
							$this->setUser(
								$request['message']['chat']['id'],
								['step' => 'type']
							);
							$this->sendPostType( $request['message']['chat']['id'], $this->makeAddSTR($user) );
							exit;
						} else {
							$this->MySQL->query('UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', ['type_id'=>$user['type_id']], $user['stat_id']);
						}
						/***********************************************/
						if ( !$user['departament_id'] ) {
							$this->setUser(
								$request['message']['chat']['id'],
								['step' => 'departament']
							);
							$this->sendPostDepartament( $request['message']['chat']['id'], $this->makeAddSTR($user) );
							exit;
						} else {
							$this->MySQL->query('UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', ['departament_id'=>$user['departament_id']], $user['stat_id']);
						}
						/***********************************************/
						$this->setUser(
							$request['message']['chat']['id'],
							['step' => 'source']
						);
						$this->sendPostSource( $request['message']['chat']['id'], $this->makeAddSTR($user) );
						/////////////////////////////////////////////////

					} else { // if $request['message']['text'] == '❌Сбросить и начать заново❌'

						switch ( $user['step'] ) {

							case 'dealership':
								$id = $this->MySQL->getOne('SELECT id FROM yapps_app_expertbot_dealerships WHERE name = ?s', $request['message']['text']);
								if ( $id ) {
									/***********************************************/
									$this->MySQL->query('UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', ['dealership_id'=>$id], $user['stat_id']);
									/***********************************************/
									if ( !$user['type_id'] ) {
										$this->setUser(
											$request['message']['chat']['id'],
											['step' => 'type']
										);
										$user['step'] = 'type';
										$this->sendPostType( $request['message']['chat']['id'], $this->makeAddSTR($user) );
										exit;
									} else {
										$this->MySQL->query('UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', ['type_id'=>$user['type_id']], $user['stat_id']);
									}
									/***********************************************/
									if ( !$user['departament_id'] ) {
										$this->setUser(
											$request['message']['chat']['id'],
											['step' => 'departament']
										);
										$user['step'] = 'departament';
										$this->sendPostDepartament( $request['message']['chat']['id'], $this->makeAddSTR($user) );
										exit;
									} else {
										$this->MySQL->query('UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', ['departament_id'=>$user['departament_id']], $user['stat_id']);
									}
									$this->setUser(
										$request['message']['chat']['id'],
										['step' => 'source']
									);
									$user['step'] = 'source';
									$this->sendPostSource( $request['message']['chat']['id'], $this->makeAddSTR($user) );
									/***********************************************/
								} else {
									$this->sendPostMessage( $request['message']['chat']['id'] );
									$this->setUnknown($request['message']['chat']['id'], $request['message']['text']);
								}
								break;
							case 'type':
								$id = $this->MySQL->getOne('SELECT id FROM yapps_app_expertbot_types WHERE name = ?s', $request['message']['text']);
								if ( $id ) {
									/***********************************************/
									$this->MySQL->query('UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', ['type_id'=>$id], $user['stat_id']);
									/***********************************************/
									if ( !$user['departament_id'] ) {
										$this->setUser(
											$request['message']['chat']['id'],
											['step' => 'departament']
										);
										$user['step'] = 'departament';
										$this->sendPostDepartament( $request['message']['chat']['id'], $this->makeAddSTR($user) );
										exit;
									} else {
										$this->MySQL->query('UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', ['departament_id'=>$user['departament_id']], $user['stat_id']);
									}
									$this->setUser(
										$request['message']['chat']['id'],
										['step' => 'source']
									);
									$user['step'] = 'source';
									$this->sendPostSource( $request['message']['chat']['id'], $this->makeAddSTR($user) );
									/***********************************************/
								} else {
									$this->sendPostMessage( $request['message']['chat']['id'] );
									$this->setUnknown($request['message']['chat']['id'], $request['message']['text']);
								}
								break;
							case 'departament':
								$id = $this->MySQL->getOne('SELECT id FROM yapps_app_expertbot_departaments WHERE name = ?s', $request['message']['text']);
								if ( $id ) {
									/***********************************************/
									$this->MySQL->query('UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', ['departament_id'=>$id], $user['stat_id']);
									/***********************************************/
									$this->setUser(
										$request['message']['chat']['id'],
										['step' => 'source']
									);
									$user['step'] = 'source';
									$this->sendPostSource( $request['message']['chat']['id'], $this->makeAddSTR($user) );
									/***********************************************/
								} else {
									$this->sendPostMessage( $request['message']['chat']['id'] );
									$this->setUnknown($request['message']['chat']['id'], $request['message']['text']);
								}
								break;
							case 'source':
								$id = $this->MySQL->getOne('SELECT id FROM yapps_app_expertbot_sources WHERE name = ?s', $request['message']['text']);
								if ( $id ) {
									/***********************************************/
									$this->MySQL->query('UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', ['source_id'=>$id], $user['stat_id']);
									/***********************************************/
									$this->setUser(
										$request['message']['chat']['id'],
										['step' => 'date']
									);
									$user['step'] = 'date';
									$this->sendPostDate( $request['message']['chat']['id'], $this->makeAddSTR($user) );
									/***********************************************/
								} else {
									$this->sendPostMessage( $request['message']['chat']['id'] );
									$this->setUnknown($request['message']['chat']['id'], $request['message']['text']);
								}
								break;
							case 'date':
								if ( $actMonth = static::getMonthAction($request['message']['text']) ) {
									$df = $this->MySQL->getRow('SELECT month, year FROM yapps_app_expertbot_stat WHERE id = ?i', $user['stat_id']);
									$this->MySQL->query(
										'UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', 
										[
											'month' => date('m', strtotime($df['year'].'-'.$df['month'].(($actMonth=='increment')?'+':'-').' 1 month')),
											'year' => date('Y', strtotime($df['year'].'-'.$df['month'].(($actMonth=='increment')?'+':'-').' 1 month'))
										], 
										$user['stat_id']
									);
									$this->sendPostDate( $request['message']['chat']['id'], $this->makeAddSTR($user) );
								} elseif ( $date = static::getDate( $request['message']['text'], $user['stat_id']) ) {
									$this->MySQL->query(
										'UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i',
										['date_feedback' => $date],
										$user['stat_id']
									);
									$this->setUser(
										$request['message']['chat']['id'],
										['step' => 'screenshot']
									);
									$user['step'] = 'screenshot';
									$this->sendPostScreenshot( $request['message']['chat']['id'], $this->makeAddSTR($user) );
								}
								break;
							default: 
								$this->sendPostMessage( $request['message']['chat']['id'] );
								$this->setUnknown($request['message']['chat']['id'], $request['message']['text']);
								break;
						}
	
					} // else $request['message']['text'] == '❌Сбросить и начать заново❌'

				} else { // if user
					$this->sendPostError( $request['message']['chat']['id'] );
				} // else use
			}
			
		}


		public function sendPostContact( $chat_id ) {

			$this->doRequest([
				'chat_id' => $chat_id,
				'text' => 'Сообщите свой номер телефона и дождитесь приглашения к началу работы с ботом. Время ожидания не более 5 минут.',
				'parse_mod' => 'html',
				'reply_markup' => json_encode(
					[
						'keyboard' => [
							[
								[
									'text' => 'Отправить телефон',
									'request_contact' => true
								]
							]
						],
						'resize_keyboard' => true
					]
				)
			]);
		}
		private function sendPostSuccessContact( $chat_id ) {

			$this->doRequest([
				'chat_id' => $chat_id,
				'text' => 'Принято. Дождитесь приглашения к началу работы с ботом.',
				'parse_mod' => 'html',
				'reply_markup' => json_encode(['remove_keyboard' => true])
			]);
		}
		private function sendPostStart( $chat_id ) {

			$this->doRequest([
				'chat_id' => $chat_id,
				'text' => 'Воспользуйся меню 👇 чтобы добавить отзыв',
				'parse_mod' => 'html',
				'reply_markup' => json_encode(['remove_keyboard' => true])
			]);
		}

		private function sendPostDealerships( $chat_id, $str = '' ) {

			$post = [
				'chat_id' => $chat_id,
				'text' => $str,
				'parse_mod' => 'html'
			];
			$dealerships = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_dealerships ORDER BY name ASC');
			foreach ( array_chunk($dealerships, 2) as $r ) {
				$row = [];
				foreach ( $r as $i )$row[] = ['text'=>$i['name']];
				$buttons['keyboard'][]= $row;
			}
			$buttons['keyboard'][] = [
				[
					'text' => '❌Сбросить и начать заново❌',
				]
			];
			$buttons['one_time_keyboard'] = true;
			$buttons['resize_keyboard'] = true;
			$post['reply_markup'] = json_encode($buttons);

			$this->doRequest($post);
		}
		private function sendPostType( $chat_id, $str = '' ) {

			$post = [
				'chat_id' => $chat_id,
				'text' => $str,
				'parse_mod' => 'html'
			];
			$dealerships = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_types ORDER BY name ASC');
			foreach ( array_chunk($dealerships, 2) as $r ) {
				$row = [];
				foreach ( $r as $i )$row[] = ['text'=>$i['name']];
				$buttons['keyboard'][]= $row;
			}
			$buttons['keyboard'][] = [
				[
					'text' => '❌Сбросить и начать заново❌',
				]
			];
			$buttons['one_time_keyboard'] = true;
			$buttons['resize_keyboard'] = true;
			$post['reply_markup'] = json_encode($buttons);

			$this->doRequest($post);
		}
		private function sendPostDepartament( $chat_id, $str = '' ) {

			$post = [
				'chat_id' => $chat_id,
				'text' => $str,
				'parse_mod' => 'html'
			];
			$dealerships = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_departaments ORDER BY name ASC');
			foreach ( array_chunk($dealerships, 2) as $r ) {
				$row = [];
				foreach ( $r as $i )$row[] = ['text'=>$i['name']];
				$buttons['keyboard'][]= $row;
			}
			$buttons['keyboard'][] = [
				[
					'text' => '❌Сбросить и начать заново❌',
				]
			];
			$buttons['one_time_keyboard'] = true;
			$buttons['resize_keyboard'] = true;
			$post['reply_markup'] = json_encode($buttons);

			$this->doRequest($post);
		}
		private function sendPostSource( $chat_id, $str = '' ) {

			$post = [
				'chat_id' => $chat_id,
				'text' => $str,
				'parse_mod' => 'html'
			];
			$dealerships = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_sources');
			foreach ( array_chunk($dealerships, 2) as $r ) {
				$row = [];
				foreach ( $r as $i )$row[] = ['text'=>$i['name']];
				$buttons['keyboard'][]= $row;
			}
			$buttons['keyboard'][] = [
				[
					'text' => '❌Сбросить и начать заново❌',
				]
			];
			$buttons['one_time_keyboard'] = true;
			$buttons['resize_keyboard'] = true;
			$post['reply_markup'] = json_encode($buttons);

			$this->doRequest($post);
		}
		
		private function sendPostDate( $chat_id, $str = '' ) {

			$df = $this->MySQL->getRow(
				'SELECT month, year FROM yapps_app_expertbot_stat WHERE id = ?i',
				$this->MySQL->getOne(
					'SELECT stat_id FROM yapps_app_expertbot_users WHERE chat_id = ?s',
					$chat_id
				)
			);
			for ( $i = 1; $i <= date('t', strtotime($df['year'].'-'.$df['month'])); $i++ ) $days[] = (($i<10)?'0':'').$i;
			for ( $i = 1; $i <= date('N', strtotime($df['year'].'-'.$df['month'].'-01'))-1; $i++ ) array_unshift($days, ' ');
			for ( $i = date('N', strtotime($df['year'].'-'.$df['month'].'-'.date('t', strtotime($df['year'].'-'.$df['month']))))+1; $i <= 7; $i++ ) array_push($days, ' ');

			foreach ( array_chunk($days, 7) as $r ) {
				$row = [];
				foreach ( $r as $i )$row[] = ['text'=>$i];
				$buttons['keyboard'][]= $row;
			}
			$buttons['keyboard'][] = [
				[
					'text' => '⬅'.static::getMonth( date('n', strtotime($df['year'].'-'.$df['month'].' - 1 month')) )
				],
				[
					'text' => static::getMonth( date('n', strtotime($df['year'].'-'.$df['month'])) )
				],
				[
					'text' => static::getMonth( date('n', strtotime($df['year'].'-'.$df['month'].' + 1 month')) ).'➡'
				]
			];
			$buttons['keyboard'][] = [
				[
					'text' => '❌Сбросить и начать заново❌'
				]
			];
			$buttons['resize_keyboard'] = true;


			$this->doRequest([
				'chat_id' => $chat_id,
				'text' => $str,
				'parse_mod' => 'html',
				'reply_markup' => json_encode($buttons)
			]);
		}
		
		private function sendPostScreenshot( $chat_id, $str = '' ) {

			$buttons['keyboard'][] = [
				[
					'text' => '❌Сбросить и начать заново❌',
				]
			];
			$buttons['resize_keyboard'] = true;
			$this->doRequest([
				'chat_id' => $chat_id,
				'text' => $str,
				'parse_mod' => 'html',
				'reply_markup' => json_encode($buttons)
			]);
		}
		private function sendPostSuccess( $chat_id ) {

			$this->doRequest([
				'chat_id' => $chat_id,
				'text' => 'Отзыв успешно добавлен',
				'parse_mod' => 'html',
				'reply_markup' => json_encode(['remove_keyboard' => true])
			]);
		}

		private function sendPostError( $chat_id ) {

			$this->doRequest([
				'chat_id' => $chat_id,
				'text' => 'Что-то пошло не так. Попробуйте снова.',
				'parse_mod' => 'html',
				'reply_markup' => json_encode(['remove_keyboard' => true])
			]);
		}
		private function sendPostDenied( $chat_id ) {

			$this->doRequest([
				'chat_id' => $chat_id,
				'text' => 'Доступ к боту запрещен',
				'parse_mod' => 'html',
				'reply_markup' => json_encode(['remove_keyboard' => true])
			]);
		}
		public function sendPostMessage( $chat_id, $type_id = 6, $post = ' ' ) {

			if ( $type_id ) {
				$message = $this->getRandMessage($type_id);
				if ( $type_id != 4 && $type_id != 7 ) $message['text'] .= PHP_EOL.PHP_EOL.'Воспользуйся меню 👇 чтобы добавить отзыв';
			}

			$this->doRequest([
				'chat_id' => $chat_id,
				'text' => ( !$type_id ) ? $post : $message['text'],
				'parse_mod' => 'html',
			]);
		}

		public function reSendPost( $user ) {

			switch ( $user['step'] ) {

				case 'dealership': $this->sendPostDealerships( $user['chat_id'], $this->makeAddSTR($user) ); break;
				case 'type': $this->sendPostType( $user['chat_id'], $this->makeAddSTR($user) ); break;
				case 'departament': $this->sendPostDepartament( $user['chat_id'], $this->makeAddSTR($user) ); break;
				case 'source': $this->sendPostSource( $user['chat_id'], $this->makeAddSTR($user) ); break;
				case 'date': $this->sendPostDate( $user['chat_id'], $this->makeAddSTR($user) ); break;
				default: $this->sendPostStart( $user['chat_id'] ); break;
			}
		}


		///////////////////////////////////////////////////////////////////////////////////////////
        // User ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		private function setUser( $chat_id, $data = [] ) {

			$this->MySQL->query('UPDATE yapps_app_expertbot_users SET ?u WHERE chat_id = ?s', $data, $chat_id);
		}
		private function getUser( $chat_id ) {

			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_expertbot_users WHERE chat_id = ?s', $chat_id);

			return $res;
		}

		public function getDBUsers() {

			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_users');
			foreach ( $res as $k => $item ) {
				if ( $item['dealership_id'] ) $res[$k]['dealership'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_dealerships WHERE id = ?i', (int)$item['dealership_id']);
				if ( $item['type_id'] ) $res[$k]['type'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_types WHERE id = ?i', (int)$item['type_id']);
				if ( $item['departament_id'] ) $res[$k]['departament'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_departaments WHERE id = ?i', (int)$item['departament_id']);
			}

			return $res;
		}
		public function getDBActiveUsers() {

			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_users WHERE ext_id != ?i AND phone != ?s', 0, '');
			return $res;
		}
		public function getDBUser($id) {

			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_expertbot_users WHERE id = ?i', $id);
			return $res;
		}
		public function setDBUser($POST) {
			
			$arIns['dealership_id'] = $POST['dealership_id'];
			$arIns['type_id'] = $POST['type_id'];
			$arIns['departament_id'] = $POST['departament_id'];

			// Helper::sp( $arIns ); die;

            $this->MySQL->query('UPDATE yapps_app_expertbot_users SET ?u WHERE id = ?i', $arIns, $POST['id']);
			return Helper::getRes(0);
		}
		

		///////////////////////////////////////////////////////////////////////////////////////////
        // Sources ////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function getSources() {

			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_sources');
			return $res;
		}
		public function getSource($id) {

			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_expertbot_sources WHERE id = ?i', $id);
			return $res;
		}
		public function setSource($POST) {

			$arIns = $POST;
            unset($arIns['form']);

            $this->MySQL->query('REPLACE INTO yapps_app_expertbot_sources SET ?u', $arIns);
			return Helper::getRes(0);
		}

		///////////////////////////////////////////////////////////////////////////////////////////
        // Messages ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function getAllMessages() {

			$this->MySQL->query('SET NAMES utf8mb4');
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_messages');
			return $res;
		}
		public function getMessages( $type ) {

			$this->MySQL->query('SET NAMES utf8mb4');
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_messages WHERE type_id = ?i', $type);
			return $res;
		}
		public function getMessage($id) {

			$this->MySQL->query('SET NAMES utf8mb4');
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_expertbot_messages WHERE id = ?i', $id);
			return $res;
		}
		public function getRandMessage( $type_id = 6 ) {

			$this->MySQL->query('SET NAMES utf8mb4');
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_expertbot_messages WHERE type_id = ?i ORDER BY rand() LIMIT ?i', $type_id, 1);
			return $res;
		}
		public function setMessage($POST) {

			$arIns = $POST;
            unset($arIns['form']);

			$this->MySQL->query('SET NAMES utf8mb4');
            $this->MySQL->query('REPLACE INTO yapps_app_expertbot_messages SET ?u', $arIns);
			return Helper::getRes(0);
		}
		public function delMessage($id) {

			$res = $this->MySQL->query('DELETE FROM yapps_app_expertbot_messages WHERE id = ?i', $id);
			return Helper::getRes(0);
		}

		public function getMessageTypes() {
			
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_message_types');
			return $res;
		}
		public function getMessageType( $id ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_expertbot_message_types WHERE id = ?i', $id);
			return $res;
		}

		///////////////////////////////////////////////////////////////////////////////////////////
        // Dealerships ////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function getDealerships() {

			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_dealerships ORDER BY name ASC');
			return $res;
		}
		public function getDealership($id) {

			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_expertbot_dealerships WHERE id = ?i', $id);
			return $res;
		}
		public function setDealership($POST) {

			$arIns = $POST;
            unset($arIns['form']);

            $this->MySQL->query('REPLACE INTO yapps_app_expertbot_dealerships SET ?u', $arIns);
			return Helper::getRes(0);
		}

		///////////////////////////////////////////////////////////////////////////////////////////
        // Types //////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function getTypes() {

			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_types');
			return $res;
		}
		public function getType($id) {

			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_expertbot_types WHERE id = ?i', $id);
			return $res;
		}
		public function setType($POST) {

			$arIns = $POST;
            unset($arIns['form']);

            $this->MySQL->query('REPLACE INTO yapps_app_expertbot_types SET ?u', $arIns);
			return Helper::getRes(0);
		}

		///////////////////////////////////////////////////////////////////////////////////////////
        // Unknowns ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function getUnknowns( $GET = [] ) {

			$date_from = ( $GET['date_from'] ) ? strtotime($GET['date_from'].' 00:00:00') : strtotime(date('Y-m-d 00:00:00', strtotime('-3 days')));
			$date_to = ( $GET['date_to'] ) ? strtotime($GET['date_to'].' 23:59:59') :  strtotime(date('Y-m-d 23:59:59'));

			$w = [];
			$w[] = $this->MySQL->parse('(timestamp BETWEEN ?i AND ?i)', $date_from, $date_to);
			$query = "WHERE ".implode(' AND ',$w);

			$this->MySQL->query('SET NAMES utf8mb4');
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_unknowns ?p', $query);
			foreach ( $res as $k => $item ) $res[$k]['user'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_users WHERE id = ?i', $item['user_id']);

			return $res;
		}
		public function setUnknown($chat_id, $text) {

			$this->MySQL->query('SET NAMES utf8mb4');
            $this->MySQL->query(
				'INSERT INTO yapps_app_expertbot_unknowns SET ?u', 
				[
					'user_id' => $this->getUser($chat_id)['id'],
					'text' => $text,
					'timestamp' => time()
				]
			);
			return Helper::getRes(0);
		}

		///////////////////////////////////////////////////////////////////////////////////////////
        // Departaments ///////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function getDepartaments() {

			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_departaments');
			return $res;
		}
		public function getDepartament($id) {

			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_expertbot_departaments WHERE id = ?i', $id);
			return $res;
		}
		public function setDepartament($POST) {

			$arIns = $POST;
            unset($arIns['form']);

            $this->MySQL->query('REPLACE INTO yapps_app_expertbot_departaments SET ?u', $arIns);
			return Helper::getRes(0);
		}

		///////////////////////////////////////////////////////////////////////////////////////////
        // Statuses ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function getStatuses() {

			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_statuses');
			return $res;
		}
		public function getStatus($id) {

			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_expertbot_statuses WHERE id = ?i', $id);
			return $res;
		}
		public function setStatus($POST) {

			$arIns = $POST;
            unset($arIns['form']);

            $this->MySQL->query('REPLACE INTO yapps_app_expertbot_statuses SET ?u', $arIns);
			return Helper::getRes(0);
		}

		///////////////////////////////////////////////////////////////////////////////////////////
        // Items //////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function getCronItem( $user_id = null, $date1 = null, $date2 = null, $final = true ) {

			$t1 = ( $date1 ) ? strtotime( $date1 ) : strtotime( date('Y-m-d', '- 6 days') );
			$t2 = ( $date2 ) ? strtotime( $date2 ) : time();

			$w = [];
			$w[] = $this->MySQL->parse('(timestamp BETWEEN ?i AND ?i)', $t1, $t2);
			if ( $user_id ) {
				$w[] = $this->MySQL->parse('user_id = ?i', $user_id);
				$w[] = $this->MySQL->parse('id '.(($final)?'!':'').'= ?i', $this->getDBUser($user_id)['stat_id']);
			}
			$query = "WHERE ".implode(' AND ',$w);

			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_expertbot_stat ?p ORDER BY id DESC', $query);
			return $res;

		}
		public function getCronItems( $user_id = null, $date1 = null, $date2 = null, $final = true ) {

			$t1 = ( $date1 ) ? strtotime( $date1 ) : strtotime( date('Y-m-d', '- 6 days') );
			$t2 = ( $date2 ) ? strtotime( $date2 ) : time();

			$w = [];
			$w[] = $this->MySQL->parse('(timestamp BETWEEN ?i AND ?i)', $t1, $t2);
			if ( $user_id ) {
				$w[] = $this->MySQL->parse('user_id = ?i', $user_id);
				$w[] = $this->MySQL->parse('id '.(($final)?'!':'').'= ?i', $this->getDBUser($user_id)['stat_id']);
			}
			$query = "WHERE ".implode(' AND ',$w);

			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_stat ?p', $query);
			return $res;

		}
		public function setNotificationForItem( $id ) {

			return $this->MySQL->query('UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', ['notification'=>1], $id);
		}
		public function getItems( $GET, $field = 'date_feedback' ) {

			$date_from = ( $GET['date_from'] ) ?: date('Y-m-d', strtotime('-3 days'));
			$date_to = ( $GET['date_to'] ) ?: date('Y-m-d');

			$w = [];

			if ( $field == 'date_feedback' ) $w[] = $this->MySQL->parse('(date_feedback BETWEEN ?s AND ?s)', $date_from, $date_to);
			if ( $field == 'timestamp' ) $w[] = $this->MySQL->parse('(timestamp BETWEEN ?i AND ?i)', strtotime($date_from.' 00:00:00'), strtotime($date_to.' 23:59:59'));
			
			if ($GET['dealership']) $w[] = $this->MySQL->parse('dealership_id = ?i', $GET['dealership']);
			if ($GET['source']) $w[] = $this->MySQL->parse('source_id = ?i', $GET['source']);
			if ($GET['status']) $w[] = $this->MySQL->parse('status_id = ?i', $GET['status']);
			if ($GET['type']) $w[] = $this->MySQL->parse('type_id = ?i', $GET['type']);
			if ($GET['departament']) $w[] = $this->MySQL->parse('departament_id = ?i', $GET['departament']);
			if ($GET['user']) $w[] = $this->MySQL->parse('user_id = ?i', $this->MySQL->getOne('SELECT id FROM yapps_app_expertbot_users WHERE ext_id = ?i', $GET['user']));
			$query = "WHERE ".implode(' AND ',$w);
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_stat ?p', $query);

			foreach ( $res as $k => $item ) {

				$res[$k]['user'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_users WHERE id = ?i', $item['user_id']);
				$res[$k]['dealership'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_dealerships WHERE id = ?i', $item['dealership_id']);
				$res[$k]['source'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_sources WHERE id = ?i', $item['source_id']);
				$res[$k]['status'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_statuses WHERE id = ?i', $item['status_id']);
				$res[$k]['type'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_types WHERE id = ?i', $item['type_id']);
				$res[$k]['departament'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_departaments WHERE id = ?i', $item['departament_id']);
				$res[$k]['date'] = date('Y-m-d H:i', $item['timestamp']);
				$res[$k]['success'] = false;
				$res[$k]['process'] = false;
			}

			return $res;
		}

		///////////////////////////////////////////////////////////////////////////////////////////
        // Notifications //////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function truncNotifications() {

			$this->MySQL->query('TRUNCATE yapps_app_expertbot_notifications');
			return true;
		}
		public function getNotification( $user_id, $type_id ) {

			return $this->MySQL->getRow('SELECT * FROM yapps_app_expertbot_notifications WHERE user_id = ?i AND type_id = ?i', $user_id, $type_id);
		}
		public function setNotification( $user_id, $type_id ) {

			return $this->MySQL->query('INSERT INTO yapps_app_expertbot_notifications SET ?u', ['user_id'=>$user_id, 'type_id'=>$type_id]);
		}
		public function delNotification( $user_id, $type_id = 4 ) {

			return $this->MySQL->query('DELETE FROM yapps_app_expertbot_notifications WHERE user_id = ?i AND type_id = ?i', $user_id, $type_id);
		}
		



		///////////////////////////////////////////////////////////////////////////////////////////
        // API ////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////


		public function apiDBGetUsers() {

			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_users');
			return $res;
		}
		public function apiDBSetUsers( $POST) {

			foreach ( $POST['success'] as $item ) {
				$this->MySQL->query(
					'UPDATE yapps_app_expertbot_users SET ?u WHERE chat_id = ?s', 
					[
						'ext_id' => $item['ext_id'],
						'name' => $item['name'],
						'is_admin' => ( $item['is_admin'] ) ? 1 : 0
					], 
					(int)$item['chat_id']);
				$this->sendPostStart( $item['chat_id'] );
			}
			foreach ( $POST['error'] as $item ) {
				$this->sendPostDenied($item['chat_id']);
				$this->MySQL->query('DELETE FROM yapps_app_expertbot_users WHERE chat_id = ?s', $item['chat_id']);
			}
		}

		public function apiDBGetItems( $GET, $field = 'date_feedback' ) {

			$date_from = ( $GET['date_from'] ) ?: date('Y-m-d', strtotime('-3 days'));
			$date_to = ( $GET['date_to'] ) ?: date('Y-m-d');

			$w = [];

			if ( $field == 'date_feedback' ) $w[] = $this->MySQL->parse('(date_feedback BETWEEN ?s AND ?s)', $date_from, $date_to);
			if ( $field == 'timestamp' ) $w[] = $this->MySQL->parse('(timestamp BETWEEN ?i AND ?i)', strtotime($date_from.' 00:00:00'), strtotime($date_to.' 23:59:59'));
			
			if ($GET['dealership']) $w[] = $this->MySQL->parse('dealership_id = ?i', $GET['dealership']);
			if ($GET['source']) $w[] = $this->MySQL->parse('source_id = ?i', $GET['source']);
			if ($GET['status']) $w[] = $this->MySQL->parse('status_id = ?i', $GET['status']);
			if ($GET['type']) $w[] = $this->MySQL->parse('type_id = ?i', $GET['type']);
			if ($GET['departament']) $w[] = $this->MySQL->parse('departament_id = ?i', $GET['departament']);
			if ($GET['user']) $w[] = $this->MySQL->parse('user_id = ?i', $this->MySQL->getOne('SELECT id FROM yapps_app_expertbot_users WHERE ext_id = ?i', $GET['user']));
			$w[] = $this->MySQL->parse('screenshot != ?s', '');
			$query = "WHERE ".implode(' AND ',$w);
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_stat ?p', $query);

			foreach ( $res as $k => $item ) {

				$res[$k]['user'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_users WHERE id = ?i', $item['user_id']);
				$res[$k]['dealership'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_dealerships WHERE id = ?i', $item['dealership_id']);
				$res[$k]['source'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_sources WHERE id = ?i', $item['source_id']);
				$res[$k]['status'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_statuses WHERE id = ?i', $item['status_id']);
				$res[$k]['type'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_types WHERE id = ?i', $item['type_id']);
				$res[$k]['departament'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_departaments WHERE id = ?i', $item['departament_id']);
				$res[$k]['date'] = date('Y-m-d H:i', $item['timestamp']);
				$res[$k]['success'] = false;
				$res[$k]['process'] = false;
			}

			return $res;
		}
		public function apiDBGetItem( $id ) {
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_expertbot_stat WHERE id = ?i', $id);

			$res['user'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_users WHERE id = ?i', $res['user_id']);
			$res['dealership'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_dealerships WHERE id = ?i', $res['dealership_id']);
			$res['source'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_sources WHERE id = ?i', $res['source_id']);
			$res['type'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_types WHERE id = ?i', $res['type_id']);
			$res['departament'] = $this->MySQL->getOne('SELECT name FROM yapps_app_expertbot_departaments WHERE id = ?i', $res['departament_id']);

			return $res;
		}
		public function apiDBSetItem( $request ) {

			$arIns = $request;
			unset($arIns['id'], $arIns['form']);
			$this->MySQL->query('UPDATE yapps_app_expertbot_stat SET ?u WHERE id = ?i', $arIns, $request['id']);

			return Helper::getRes(0);
		}
		

		public function apiDBGetData() {

			$res = [
				'dealerships' => $this->MySQL->getAll('SELECT * FROM yapps_app_expertbot_dealerships ORDER BY name ASC'),
				'sources' => $this->MySQL->getInd('id', 'SELECT * FROM yapps_app_expertbot_sources'),
				'statuses' => $this->MySQL->getInd('id', 'SELECT * FROM yapps_app_expertbot_statuses'),
				'from' => date('Y-m-d', strtotime('-3 days')),
				'to' => date('Y-m-d'),
			];

			return $res;
		}








		

		public function getScript() {

			$script = '';
			foreach ( glob($_SERVER['DOCUMENT_ROOT'].$this->Conf->FrontendDir.'/dist/js/*.js') as $file ) {
				$script .= file_get_contents($file).PHP_EOL;
				$arF = explode('/', $file);
				$script .= '//@ sourceMappingURL='.$this->Conf->FrontendDir.'/dist/js/'.$arF[count($arF)-1].'.map';
			}
			return $script;
		}
    }