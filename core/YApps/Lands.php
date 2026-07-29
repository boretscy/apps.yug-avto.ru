<?php

	class Lands extends App {

		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, PHPMailer &$mailer ) {

			$this->MySQL	= &$mysql;
			$this->Mailer	= &$mailer;
			$this->Conf		= (object)$arConf['modules'][get_class($this)];
		}

		public function AppInfo() {

			return (object)$this->MySQL->getRow('SELECT * FROM yapps_apps WHERE class = ?s', get_class($this));
		}

		public function getConf() {

			return $this->Conf;
        }
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Private Area ///////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		private function sendForm( $POST ) {

			$this->Mailer->CharSet = 'utf-8';
			$this->Mailer->setFrom('alert@apps.yug-avto.ru', 'Оповещения Юг-Авто Apps');
			$this->Mailer->ClearAddresses();

            $landing = $this->getLand( $POST['land_id'] );
            foreach ( preg_split('/[\s,;]+/', $landing['recipients']) as $email ) $this->Mailer->addAddress($email, '');

			$this->Mailer->Subject = 'Лендинг: '.$landing['ru_name'].'. Заполнена форма: '.$POST['form'];

            $message = 'Форма: '.$POST['form'].'<br /><br />';

			$message .= '<h3>Посетитель</h3>';
			if ( !!$POST['Name'] ) $message .= 'Имя: '.$POST['Name'].'<br />';
			if ( !!$POST['Phone'] ) $message .= 'Телефон: '.Helper::formatPhoneOut($POST['Phone']).'<br />';
			if ( !!$POST['DcID'] ) $message .= 'Дилерский центр: '.$this->getDC($POST['DcID'])['ru_name'].'<br />';
			$message .= '<br /><br />';
            
            if ( !!$POST['Car'] ) {

                $message .= '<h3>Автомобиль</h3>';
                $message .= 'Модель: '.$POST['Car'].'<br />';
                if ( !!$POST['Compl'] ) $message .= 'Комплектация: '.$POST['Compl'].'<br />';
                if ( !!$POST['Credit'] ) $message .= 'Кредит: '.$POST['Credit'].'<br />';
                if ( !!$POST['Discount'] ) $message .= 'Выгода: '.$POST['Discount'].'<br />';
                $message .= '<br /><br />';
            }

            if ( !!$POST['DC'] ) $message .= 'Дилерский центр: '.$POST['DC'].'<br /><br />';
            if ( !!$POST['Comment'] ) $message .= 'Комментарий:<br />'.$POST['Comment'].'<br /><br />';

			$message .= 'Страница-источник: <a href="'.$POST['source_url'].'" target="_blank">'.$POST['source_title'].'</a>';

			$this->Mailer->msgHTML($message);
			return $this->Mailer->Send();
		}
		
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Public Area ////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getAllLands() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_lands');
		}
		
		public function getUserLands( $siteIDs ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_lands WHERE site_id IN (?a)', $siteIDs);
		}
		
		public function getSiteLands( $siteID ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_lands WHERE site_id = ?i', (int)$siteID);
		}
		
		public function getLand( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_lands WHERE id = ?i', (int)$id);
		}
		
		public function getLandByURL( $url ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_lands WHERE url = ?s', Helper::parseHostPathLink($url));
		}
		
		public function delLand( $id ) {
			
			return $this->MySQL->query('DELETE FROM yapps_app_lands WHERE id = ?i', (int)$id);
		}
		
		public function setLand( $POST ) {

			$arIns = $POST;
			unset($arIns['form'], $arIns['active'], $arIns['apps'], $arIns['use_widgets'], $arIns['use_chat'], $arIns['dc_id'] );
			$arIns['active'] = ($POST['active']=='on') ? 1 : 0 ;
			$arIns['use_lg'] = ($POST['use_lg']=='on') ? 1 : 0 ;
			$arIns['use_cb'] = ($POST['use_cb']=='on') ? 1 : 0 ;
			$arIns['use_nv'] = ($POST['use_nv']=='on') ? 1 : 0 ;
			$arIns['use_ch'] = ($POST['use_ch']=='on') ? 1 : 0 ;
			$arIns['use_av'] = ($POST['use_av']=='on') ? 1 : 0 ;
			$arIns['use_ht'] = ($POST['use_ht']=='on') ? 1 : 0 ;
            $arIns['use_qz'] = ($POST['use_qz']=='on') ? 1 : 0 ;
            $arIns['use_ms'] = ($POST['use_ms']=='on') ? 1 : 0 ;
            $arIns['use_eh'] = ($POST['use_eh']=='on') ? 1 : 0 ;
            $arIns['send_email'] = ($POST['send_email']=='on') ? 1 : 0 ;
			$arIns['url'] = Helper::parseHostPathLink( $POST['url'] );
            if ( !$arIns['token'] ) $arIns['token'] = md5( $arIns['ru_name'].' '.$arIns['url'] );

            $this->MySQL->query('REPLACE INTO yapps_app_lands SET ?u', $arIns);
            
            $lastId = ( $POST['id'] ) ?: $this->MySQL->insertId();
            $this->MySQL->query('DELETE FROM yapps_app_lands_dcs WHERE land_id = ?i', $lastId);
            foreach ( $POST['dc_id'] as $dcId ) $this->MySQL->query('INSERT INTO yapps_app_lands_dcs SET ?u', ['land_id'=>$lastId, 'dc_id'=>$dcId]);

            $res = 0;
            if ( $POST['dc_id'] ) $res = ( $this->flash($lastId, 'DCs', ['params'=>json_encode($this->YApps_GetSortDCsByIDs($POST['dc_id']))]) ) ? 0 : 200;
			
			return Helper::getRes($res);
        }
        
        public function getLandDCs( $id ) {

            return $this->YApps_GetDCsByIDs( $this->MySQL->getCol('SELECT dc_id FROM yapps_app_lands_dcs WHERE land_id = ?i', $id) );
        }

        public function getLandDCsIDs( $id ) {

            return $this->MySQL->getCol('SELECT dc_id FROM yapps_app_lands_dcs WHERE land_id = ?i', $id);
        }
        
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Content ////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        public function getContent( $id ) {

            return $this->MySQL->getRow('SELECT * FROM yapps_app_lands_content WHERE land_id = ?i', $id);
        }

        public function setContent( $POST, $FILES = false ) {

            $arIns = $POST;
            unset( $arIns['form'], $arIns['_wysihtml5_mode'], $arIns['banner_image'], $arIns['credit_image'], $arIns['tradein_image'], $arIns['service_image'] );
            $arIns['timer_use'] = ($POST['timer_use']=='on') ? 1 : 0 ;
            $arIns['dayoffer_use'] = ($POST['dayoffer_use']=='on') ? 1 : 0 ;
			$arIns['credit_use'] = ($POST['credit_use']=='on') ? 1 : 0 ;
			$arIns['tradein_use'] = ($POST['tradein_use']=='on') ? 1 : 0 ;
			$arIns['service_use'] = ($POST['service_use']=='on') ? 1 : 0 ;

            if ( $FILES ) {

                if ( !file_exists(__DIR__.'/../..'.$this->Conf->FileDir.'/'.$POST['land_id']) ) mkdir( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$POST['land_id'] );

                foreach ( $FILES as $k => $file ) {

                    if ( !$file['error'] ) {

                        $arF = explode('.', $file['name']);
                        $arIns[$k] = 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/'.$POST['land_id'].'/'.$k.'.'.$arF[count($arF)-1].'?'.md5_file($file['tmp_name']);
                        move_uploaded_file( $file['tmp_name'], __DIR__.'/../..'.$this->Conf->FileDir.'/'.$POST['land_id'].'/'.$k.'.'.$arF[count($arF)-1] );
                    }

                }
            }

            if ( $flash = $this->getContent($arIns['land_id']) ) {

                unset($arIns['land_id']);
                $this->MySQL->query('UPDATE yapps_app_lands_content SET ?u WHERE land_id = ?i', $arIns, $POST['land_id']);

            } else {

                $this->MySQL->query('INSERT INTO yapps_app_lands_content SET ?u', $arIns);
                $flash = $this->getContent($POST['land_id']);
            }

            $flash['brand'] = $this->YApps_getBrand( $arIns['brand_id'] )['en_name'];
            $flash['url'] = $this->getLand( $POST['land_id'] )['url'];
            $res = ( $this->flash($POST['land_id'], 'Content', ['params'=>json_encode($flash)]) ) ? 0 : 200;

            return Helper::getRes($res);
        }




		/*
		public function getScript( $user, $URL ) {
			
			$Lurl = parse_url($URL)['host'].parse_url($URL)['path'];
			
			if ( $arLand = $this->getLandByURL($Lurl) && $arLand['active'] ) {
				
				$script = file_get_contents( __DIR__.'/js/'.get_class($this).'.js' );
				if ( $this->MySQL->getOne('SELECT url FROM yapps_sites WHERE id = ?i', $arLand['site_id']) != parse_url($URL)['host'] ) {
					
					$script = file_get_contents( __DIR__.'/js/'.get_class($this).'.js' );
					$script .= 'window.TalkMeSetup = { domain: \''.parse_url($URL)['host'].'\' }';
				}
				
				return $script.PHP_EOL;
			}
		}
		*/
        
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        //API /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        public function flash( $land_id, $block = 'Content', $params = [] ) {

            $land = $this->getLand($land_id);

            $url = $land['url'].'api/';
            $headers = "Content-Type: application/x-www-form-urlencoded";
            $params['APIToken'] = $land['token'];
            $params['Block'] = $block;
			$params = http_build_query($params);
			$opts = [
				'http' => [
					'method' => "POST",
					'header' => $headers,
					'content' => $params
				]
			];

			$context = stream_context_create($opts);
            $res = file_get_contents($url, null, $context);
            
            return ( $res == 'success' ) ? true : false;

        }


		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Stats Area /////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function pushStat( $POST, $user, $ip ) {
            
			$site_id = $this->YApps_GetSiteIdByLand($POST['SourceLink']);
			
			if ( $site_id ) {
                
                $landing = $this->getLandByURL($POST['SourceLink']);

				if ( Helper::isNotFakePhone($POST['Phone']) ) {
					
					
					$ids = [
						'piwik_visitorId' => explode('.', $POST['PiwikVisitorID'])[0],
						'yandex_visitorId' => $POST['YandexVisitorID'],
						'google_visitorId' => explode('.', $POST['GoogleVisitorID'])[2].'.'.explode('.', $POST['GoogleVisitorID'])[3]
					];
					
					$st_data = [
						'site_id' => $site_id,
						'land_id' => $landing['id'],
						'source_title' => $POST['SourceTitle'],
						'source_url' => $POST['SourceLink'],
						'event_name' => 'Формы лендинга: '.$POST['Form'],
						'timestamp' => time(),
						'name' => $POST['Name'],
						'phone' => Helper::formatPhoneIn( $POST['Phone'] ),
						'form' => $POST['Form'],
						'referrer' => $POST['Referrer'],
						'visitorIP' => $ip,
                    ];
                    if ( $POST['Car'] ) $st_data['car'] = $POST['car'];
                    if ( $POST['DcID'] ) $st_data['dc_id'] = $POST['DcID'];
	
					// if ( $utms = Helper::getUtm($POST['SourceLink']) ) $st_data = array_merge( $st_data, $utms );
					$st_data = array_merge( $st_data, $ids );
					
					$this->MySQL->query('INSERT INTO yapps_app_lands_stat SET ?u', $st_data);
					$lastId = $this->MySQL->insertId();
	
					$cl_data = [
						'name' => $POST['Name'],
						'phone' => Helper::formatPhoneIn( $POST['Phone'] ),
						'url' => $POST['SourceLink'],
						'event' => $POST['Form'],
						'stat_id' => $lastId,
						'app_id' => $this->AppInfo()->id,
						'site_id' => (int)$POST['SiteID'],
						'referrer' => $POST['Referrer']
					];
					
					if ( $utms = Helper::getUtm($POST['SourceLink']) ) $cl_data = array_merge( $cl_data, $utms );
	
					$geo = Helper::getGeo( $ip );
					
                    $this->YApps_PushClient( $cl_data, $ids, $geo );
                    
                    if ( $landing['send_email'] ) {

                        if ( !!$POST['phone'] ) $st_data['Phone'] = $POST['phone'];
                        if ( !!$POST['Phone'] ) $st_data['Phone'] = $POST['Phone'];
                        if ( !!$POST['name'] ) $st_data['Name'] = $POST['name'];
                        if ( !!$POST['Name'] ) $st_data['Name'] = $POST['Name'];

                        if ( !!$POST['Car'] ) $st_data['Car'] = $POST['Car'];
                        if ( !!$POST['form_type_model_name'] ) $st_data['Car'] = $POST['form_type_model_name'];
                        if ( !!$POST['comlpl'] ) $st_data['Comlpl'] = $POST['comlpl'];
                        if ( !!$POST['credit'] ) $st_data['Credit'] = $POST['credit'];
                        if ( !!$POST['discount'] ) $st_data['Discount'] = $POST['discount'];
                        if ( !!$POST['DcID'] ) $st_data['DcID'] = $POST['DcID'];
                        if ( !!$POST['comment'] ) $st_data['Comment'] = $POST['comment'];

                        $this->sendForm( $st_data );
                    }
				
				} // if Not Fake Phone
			
			} // if site_id
		}



		public function getStats( $user, $date1, $date2 ) {

			$sites = $this->MySQL->getCol('SELECT site_id FROM yapps_users_sites WHERE user_id = ?i', (int)$user->id);
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_lands_stat WHERE site_id IN (?a) AND timestamp >= ?i AND timestamp < ?i', $sites, strtotime($date1), strtotime($date2));

			return $res;
		}
		
    }

?>