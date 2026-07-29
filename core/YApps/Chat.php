<?php

	class Chat extends App {

		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, $mailer = false ) {

			$this->MySQL		= &$mysql;
			$this->conf			= (object)$arConf['modules']['Chat'];
			$this->Yandex		= (object)$arConf['App']['Yandex'];
        }

		// Private Area
		public function AppInfo() {

			return (object)$this->MySQL->getRow('SELECT * FROM yapps_apps WHERE class = ?s', get_class($this));
		}


		// Settings Area
		public function getSetByHost( $host ) {

			return $this->MySQL->getRow( 'SELECT * FROM yapps_app_chat_settings WHERE site_id = (SELECT id FROM yapps_sites WHERE url = ?s) AND active = ?i', $host, 1 );
		}

		public function getSetByAppId( $app ) {

			return $this->MySQL->getRow( 'SELECT * FROM yapps_app_chat_settings WHERE app_id = ?s', $app );
		}

		public function getSetById( $id ) {

			return $this->MySQL->getRow( 'SELECT * FROM yapps_app_chat_settings WHERE id =  ?i', (int)$id );
		}

		public function getSets() {

			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_chat_settings');
			foreach ( $res as $k => $v ) $res[$k]['site'] = $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE id = ?i', $v['site_id']);

			return $res;
		}
		
		public function activateSets($id, $action = true) {
			
			$arIns['active'] = ( $action ) ? 1 : 0;
			return ( $id == 'all' ) ? $this->MySQL->query('UPDATE yapps_app_chat_settings SET ?u', $arIns) : $this->MySQL->query('UPDATE yapps_app_chat_settings SET ?u WHERE id = ?i', $arIns, (int)$id);
		}

		public function setSet( $POST ) {

			$arIns = $POST;
			unset( $arIns['id'], $arIns['form'], $arIns['active'] );

			$arIns['active'] = ($POST['active']=='on') ? 1 : 0 ;

			if ( $POST['id'] ) {

				$res = ($this->MySQL->query('UPDATE yapps_app_chat_settings SET ?u WHERE id = ?i', $arIns, (int)$POST['id'])) ? 0 : 41;

			} else {

				$res = ($this->MySQL->query('INSERT INTO yapps_app_chat_settings SET ?u', $arIns)) ? 0 : 41;
			}
			
			if ( $res == 0 ) {
				
				if ( !$this->checkGoals($arIns['site_id']) ) {
					
					$goals = $this->MySQL->getAll('SELECT * FROM yapps_app_chat_goals');
					$site = $this->YApps_GetSiteByID( (int)$arIns['site_id'] );
					
					foreach ( $goals as $goal ) {
						
						$arGoal = [
							'name' => $goal['ru_name'],
							'goal' => $goal['goal']
						];
						
						Yandex::setGoal( $this->Yandex, $site['yandex_id'], $arGoal );
					}
				}
			}

			return Helper::getRes($res);
		}

		public function delSet( $id ) {

			$res = ($this->MySQL->query('DELETE FROM yapps_app_chat_settings WHERE id = ?i', (int)$id)) ? 0 : 41;
			return Helper::getRes($res);
		}

		
		
		
		// Goals
		public function checkGoals( $site_id ) {
			
			$site = $this->YApps_GetSiteByID( (int)$site_id );
			$cur_goals = Yandex::getGoals( (array)$this->Yandex, $site['yandex_id'] )->goals;
			
			$chat_goals = $this->MySQL->getCol('SELECT goal FROM yapps_app_chat_goals');
			
			foreach ( $cur_goals as $goal ) if ( $goal->type == 'action' && in_array( $goal->conditions[0]->url, $chat_goals) ) return true;
			
			return false;
		}
		
		
		
		// Stats Area
		
		public function setHookEvent( $obj ) {

			$sets = $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE id = ?i', (int)$obj->client->customData->site_id );

			if ( $sets && Helper::isNotFakePhone($obj->client->phone) ) {
                
                /*
                if ( $obj->action == 'operatorUpdatesClientInfo' ) {
                    
                    $ct_data = [
                        'calltouch_node' => $sets['calltouch_node'],
                        'calltouch_id' => $sets['calltouch_id'],
                        'subject' => 'Чат: Оператор обновил данные посетителя',
                        'ct_sess' => $obj->client->customData->ct_sess,
                        'name' => $obj->client->name,
                        'email' => $obj->client->email,
                        'phone' => $obj->client->phone
                    ];
                    
                    $this->YApps_SendOrderCallTouch($ct_data);
                }
                */

                $st_data = [
                    'site_id' => (int)$obj->client->customData->site_id,
                    'phone' => Helper::formatPhoneIn( $obj->client->phone ),
                    'source_title' => $obj->client->lastVisit->page->title,
                    'source_url' => $obj->client->lastVisit->page->url,
                    'event_name' => ( $obj->action == 'operatorUpdatesClientInfo' ) ? 'Чат: Оператор обновил данные посетителя' : 'Чат: Посетитель оставил контакные данные',
                    'chat_visitorId' => $obj->client->clientId,
                    'piwik_visitorId' => explode('.', $obj->client->customData->matomo_id)[0],
                    'yandex_visitorId' => $obj->client->customData->yandex_id,
                    'google_visitorId' => explode('.', $obj->client->customData->google_id)[2].'.'.explode('.', $obj->client->customData->google_id)[3],
                    'utm_campaign' => ( $obj->client->utm->utm_campaign ) ? $obj->client->utm->utm_campaign : '',
                    'utm_source' => ( $obj->client->utm->utm_source ) ? $obj->client->utm->utm_source : '',
                    'utm_medium' => ( $obj->client->utm->utm_medium ) ? $obj->client->utm->utm_medium : '',
                    'utm_content' => ( $obj->client->utm->utm_content ) ? $obj->client->utm->utm_content : '',
                    'utm_term' => ( $obj->client->utm->utm_term ) ? $obj->client->utm->utm_term : '',
                    'referrer' => $obj->client->referer,
                    'visitorIP' => ( $obj->client->ip ) ? $obj->client->ip : '',
                    'timestamp' => time(),
                ];
				if ( $obj->client->name && !stripos($obj->client->name, 'посетител') ) $st_data['name'] = $obj->client->name;
				if ( $obj->client->email ) $st_data['email'] = $obj->client->email;
                
                $lastId = $this->setStat( $st_data );

                $cl_data = [
                    'phone' => Helper::formatPhoneIn( $obj->client->phone ),
                    'url' => $obj->client->lastVisit->page->url,
                    'event' => ( $obj->action == 'operatorUpdatesClientInfo' ) ? 'Чат: Оператор обновил данные посетителя' : 'Чат: Посетитель оставил контакные данные',
                    'stat_id' => $lastId,
                    'app_id' => $this->AppInfo()->id,
                    'site_id' => (int)$obj->client->customData->site_id,
                    'referrer' => $obj->client->referer,
                    'utm_campaign' => $obj->client->utm->utm_campaign,
                    'utm_source' => $obj->client->utm->utm_source,
                    'utm_medium' => $obj->client->utm->utm_medium,
                    'utm_content' => $obj->client->utm->utm_content,
                    'utm_term' => $obj->client->utm->utm_term,
                    'user_agent' => $obj->client->useragent

                ];
				if ( $obj->client->name ) $cl_data['name'] = $obj->client->name;
				if ( $obj->client->email ) $cl_data['email'] = $obj->client->email;
                $ids = [
                    'piwik_visitorId' => explode('.', $obj->client->customData->matomo_id)[0],
                    'yandex_visitorId' => $obj->client->customData->yandex_id,
                    'google_visitorId' => explode('.', $obj->client->customData->google_id)[2].'.'.explode('.', $obj->client->customData->google_id)[3]
                ];
                $geo = Helper::getGeo( $obj->client->ip );

                $this->YApps_PushClient( $cl_data, $ids, $geo );

			} // if Sets
		}

		private function setStat( $data ) {

            $this->MySQL->query('INSERT INTO yapps_app_chat_stat SET ?u', $data);
            return $this->MySQL->insertId();
		}

		// Statisctics area

		public function getStats( $user, $date1, $date2 ) {

			$sites = $this->YApps_GetUserSiteIDs($user);

			return $this->MySQL->getAll('SELECT * FROM yapps_app_chat_stat WHERE site_id IN (?a) AND timestamp >= ?i AND timestamp < ?i', $sites, strtotime($date1), strtotime($date2));
		}


		// Script Out

		public function getScript( $user, $URL ) {

			$host = parse_url( $URL )['host'];
			$site = $this->YApps_GetSiteByHost( $host );
			if ( !$site->id ) $site = (object)$this->YApps_GetSiteByID( $this->YApps_GetSiteIdByShowroom($URL) );
			if ( !$site->id ) $site = (object)$this->YApps_GetSiteByID( $this->YApps_GetLandByUrl($URL)['site_id'] );
			
			$land = $this->YApps_GetLandSettings( $this->YApps_GetLandIdByUrl($URL) );
			
			if ( in_array( $site->id, $this->YApps_GetUserSiteIDs($user) ) ) {
				
				$sets = $this->getSetByHost( $site->url );
				
				if ( $sets &&
					( !$land ||
						( $land &&  $land['use_ch'] )
					)
				) {

					$script = file_get_contents( __DIR__.'/js/'.get_class($this).'.js' );
					$script = str_replace( '{{SITE.YANDEXID}}', $site->yandex_id, $script );
					$script = str_replace( '{{SITE.ID}}', $site->id, $script );
					$script = str_replace( '{{SITE.CT_NODE}}', $site->calltouch_node, $script );
					$script = str_replace( '{{SITE.CT_ID}}', $site->calltouch_id, $script );
					$script = str_replace( '{{SITE.CT_SESS}}', $site->calltouch_sess, $script );
					$script = str_replace( '{{API.TOKEN}}', $sets['token'], $script );

					return $script.PHP_EOL;
				
				} // if
			
			} // if
			
			if ( $script ) return $script.PHP_EOL;
			
        } // function
	}
