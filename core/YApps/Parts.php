<?php
	class Parts extends App {


/////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////// PRIVATE AREA //////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		public function sendForm( $arIns ) {
			
			$this->Mailer->CharSet = 'utf-8';
			$this->Mailer->setFrom('alert@apps.yug-avto.ru', 'Оповещения Юг-Авто Apps');
			$this->Mailer->ClearAddresses();
			
			$sets = $this->getSettingsById( $arIns['site_id'] );
			$site = $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE id = ?i', (int)$arIns['site_id']);
			
			$arRec = explode( ', ', $sets['recipients'] );
			foreach ($arRec as $email) $this->Mailer->addAddress($email, '');
			
			$this->Mailer->Subject = 'Заявка на оптовую покупку запчастей. Сайт: '.$site['ru_name'];
			
			if ( $arIns['event_name'] == 'Незавершенная форма' ) $this->Mailer->Subject = 'Незавершенное заполнение заявки на техническое обслуживание. Сайт: '.$site['ru_name'];
			
			$message = 'Имя: '.$arIns['name'].'<br />';
			$message .= 'Телефон: '.(($arIns['phone'])?Helper::formatPhoneOut($arIns['phone']):'').'<br />';
			if ( $arIns['email'] ) $message .= 'Email: '.$arIns['email'].'<br />';
			if ( $arIns['date_timestamp'] ) $message .= 'Дата: '.date('d.m.Y H:i', $arIns['date_timestamp']).'<br />';
			$message .= '<br />';
			$message .= 'Автомобиль: '.$model.' '.$mod.'<br />';
			
			$message .= '<br /><br />Информация о посетителе: <a href="https://apps.yug-avto.ru/piwik/index.php?date=today&module=Widgetize&action=iframe&visitorId='.$arIns['piwik_visitorId'].'&idSite='.$site['piwik_id'].'&period=day&moduleToWidgetize=Live&actionToWidgetize=getVisitorProfilePopup&token_auth=ad2a99404863c986f6060a67e027078f" target="_blank">Matomo</a>';
			
			$this->Mailer->msgHTML($message);
			//return $this->Mailer->Send();
		}


/////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// PUBLIC AREA //////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////

		
		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, PHPMailer &$mailer ) {
			
			$this->MySQL	= &$mysql;
			$this->Mailer	= &$mailer;
			$this->Conf		= (object)$arConf['modules'][get_class($this)];
		}
		
		public function AppInfo() {
			
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_apps WHERE class = ?s', get_class($this));
		}
		
		public function importCSV( $POST, $FILES ) {
			
			if ( $FILES['import']['type'] == 'text/csv' || $FILES['import']['type'] == 'application/vnd.ms-excel' ) {
				
				$arIns['site_id'] = (int)$POST['site_id'];
				
				$this->MySQL->query('DELETE FROM yapps_app_parts WHERE site_id = ?i', $arIns['site_id']);
				
				$handle = fopen($FILES['import']['tmp_name'], "r");
				while (($line = fgetcsv($handle, 0, ";")) !== FALSE)  $importCSV[] = $line;
				fclose($handle);
				
				foreach ( $importCSV as $csv ) {
					
					foreach ($csv as $key => $value) $csv[$key] = iconv('WINDOWS-1251', 'UTF-8', $value);
					
					$arIns['manufacturer'] = $csv[0];
					$arIns['ru_name'] = $csv[1];
					$arIns['sku'] = $csv[3];
					$arIns['stock'] = (int)$csv[2];
					$arIns['price'] = (float)$csv[4];
					$arIns['min_order'] = (int)$csv[5];
					$arIns['timestamp'] = time();
					
					$this->MySQL->query('INSERT INTO yapps_app_parts SET ?u', $arIns);
				
				} // foreach
				
				return Helper::getRes(0);
				
			} else {
				
				return Helper::getRes(51);
			}
			
		}
		
		public function setItem( $POST ) {
			
			$arIns = [
				'site_id' => (int)$POST['site_id'],
				'sku' => $POST['sku'],
				'ru_name' => $POST['ru_name'],
				'stock' => (int)$POST['stock'],
				'price' => (float)$POST['price'],
				'manufacturer' => $POST['manufacturer'],
				'timestamp' => time()
			];
			
			if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_app_parts SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_parts SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
			
		}
		
		public function getItem( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_parts WHERE id = ?i', (int)$id);	
		}
		
		public function delItem( $id ) {
			
			return $this->MySQL->query('DELETE FROM yapps_app_parts WHERE id = ?i', (int)$id);
		}
		
		public function getParts( $site_id, $page = 1 ) {
			
			$res['count'] = $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_app_parts WHERE site_id = ?i', (int)$site_id);
			$res['site'] = $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE id = ?i', (int)$site_id);
			$res['items'] = $this->MySQL->getAll('SELECT * FROM yapps_app_parts WHERE site_id = ?i LIMIT ?i, ?i', (int)$site_id, $this->Conf->PageCount*((int)$page-1), $this->Conf->PageCount);
			
			return $res;
		}
		
		
		// Settings
		public function getSettings( $sites = false ) {
			
			if ( !$sites ) {
				
				return false;
				
			} else {
				
				foreach ( $sites as $k => $s ) $sites [$k]['css'] = $this->MySQL->getRow('SELECT * FROM yapps_app_parts_settings WHERE site_id = ?i', $s['id']);
				
				return $sites;
			}
		}
		
		public function getSettingsByHost( $host ) {
			
			$site_id = (int)$this->MySQL->getOne('SELECT id FROM yapps_sites WHERE url = ?s', $host);
			return ( $res = $this->getSettingsById( $site_id ) ) ? $res : false;
		}
		
		public function getSettingsById( $site_id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_parts_settings WHERE site_id = ?i', (int)$site_id);
		}
		
		public function setSettings( $POST ) {
			
			$arIns = $POST;
			unset( $arIns['id'], $arIns['form'], $arIns['active'] );
			$arIns['active'] = ( $POST['active'] == 'on' ) ? 1 : 0;
			
			if ( $this->MySQL->getOne('SELECT id FROM yapps_app_parts_settings WHERE site_id = ?i', (int)$POST['id']) ) {
				
				$this->MySQL->query('UPDATE yapps_app_parts_settings SET ?u WHERE site_id = ?i', $arIns, (int)$POST['id']);
				
			} else {
				
				$arIns['site_id'] = (int)$POST['id'];
				$this->MySQL->query('INSERT INTO yapps_app_parts_settings SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
		}
		
		
		// Statisctics area
		
		public function getStats( $user, $date1, $date2 ) {
			
			$sites = $this->MySQL->getCol('SELECT site_id FROM yapps_users_sites WHERE user_id = ?i', (int)$user->id);
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_parts_stat WHERE site_id IN (?a) AND timestamp >= ?i AND timestamp < ?i', $sites, strtotime($date1), strtotime($date2));
		}
		
		
		
		
		// API Area
		
		public function getScript( $user, $URL ) {
			
			$host = parse_url( $URL )['host'];
			$site = $this->YApps_GetSiteByHost( $host );
			
			if ( in_array( $site->id, $this->YApps_GetUserSiteIDs($user) ) ) {
				
				$sets = $this->getSettingsByHost( $host );
				
				if ( $sets ) {
				
					$script = file_get_contents(__DIR__.'/js/'.get_class($this).'.js');

					$script = str_replace( '{{USER.TOKEN}}', $user->public_key, $script );
					$script = str_replace( '{{SITE.ID}}', $site['id'], $script );
					$script = str_replace( '{{SITE.YANDEXID}}', $site['yandex_id'], $script );
					$script = str_replace( '{{SITE.PIWIKID}}', $site['piwik_id'], $script );
					$script = str_replace( '{{SITE.GOOGLEID}}', $site['google_id'], $script );
					$script = str_replace( '{{SITE.YANDEXID}}', $site['yandex_id'], $script );
					$script = str_replace( '{{SITE.YANDEXID}}', $site['yandex_id'], $script );
					$script = str_replace( '{{PARTS.STARTHTML}}', file_get_contents(__DIR__.'/html/'.get_class($this).'.html'), $script );
					$script = str_replace( '{{PARTS.DISCLAMER}}', $sets['disclamer'], $script );

					return $script.PHP_EOL; 
				}
			}
			
		}
		
		public function getCSS( $user, $URL ) {
			
			$host = parse_url( $URL )['host'];
			$site = $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE url = ?s', $host);
			$sets = $this->getSettingsByHost( $host );
			
			if ( $site ) {
				
				$css = file_get_contents(__DIR__.'/css/'.get_class($this).'.css');
				$css = str_replace( '{{COLOR.BLACK}}', (($sets['black'])?$sets['black']:'#2f3538'), $css );
				$css = str_replace( '{{COLOR.GRAY}}', (($sets['gray'])?$sets['gray']:'#d3d3d3'), $css );
				$css = str_replace( '{{COLOR.COLOR}}', (($sets['color'])?$sets['color']:'#007fff'), $css );
				
				return $css.PHP_EOL;
			}
			
			
		}
		
		public function getSVG() {
			
			return file_get_contents(__DIR__.'/svg/'.get_class($this).'.php');
		}
		
		
		public function Search( $POST, $user, $ip ) {
			
			$res['Items'] = $this->MySQL->getAll('SELECT * FROM yapps_app_parts WHERE MATCH (ru_name,sku) AGAINST (?s) AND site_id = ?i', $POST['Search'], (int)$POST['SiteID']);
			$res['Status'] = ( $res['Items'] ) ? 'success' : 'error';
			
			$arStat = [
				'user_id' => $user->id,
				'site_id' => (int)$POST['SiteID'],
				'source_title' => $POST['SourceTitle'],
				'source_url' => $POST['SourceLink'],
				'event_name' => $POST['EventName'],
				'piwik_visitorId' => explode('.', $POST['PiwikVisitorID'])[0],
				'yandex_visitorId' => $POST['YandexVisitorID'],
				'google_visitorId' => explode('.', $POST['GoogleVisitorID'])[2].'.'.explode('.', $POST['GoogleVisitorID'])[3],
				'timestamp' => time(),
				'visitorIP' => $ip,
			];
			
			$this->MySQL->query('INSERT INTO yapps_app_parts_stat SET ?u', $arStat);
			
			return $res;
		}
		
		public function pushStat( $POST, $user, $ip ) {
			
			$arStat = [
				'user_id' => $user->id,
				'site_id' => (int)$POST['SiteID'],
				'source_title' => $POST['SourceTitle'],
				'source_url' => $POST['SourceLink'],
				'event_name' => $POST['EventName'],
				'piwik_visitorId' => explode('.', $POST['PiwikVisitorID'])[0],
				'yandex_visitorId' => $POST['YandexVisitorID'],
				'google_visitorId' => explode('.', $POST['GoogleVisitorID'])[2].'.'.explode('.', $POST['GoogleVisitorID'])[3],
				'timestamp' => time(),
				'name' => $POST['Name'],
				'email' => ($POST['Email']) ? $POST['Email'] : '',
				'phone' => Helper::formatPhoneIn( $POST['Phone'] ),
				'items_ids' => $POST['ItemsIDS'],
				'visitorIP' => $ip,
			];
			
			$this->MySQL->query('INSERT INTO yapps_app_parts_stat SET ?u', $arStat);
			
			$arIns = [];
			
			$arIns['name'] = $POST['Name'];
			if ( $POST['Email'] ) $arIns['email'] = $POST['Email'];
			$arIns['phone'] = Helper::formatPhoneIn( $POST['Phone'] );
			$arIns['last_url'] = $POST['SourceLink'];
			$arIns['last_event'] = $POST['EventName'];
			$arIns['last_app_id'] = (int)$this->AppInfo()->id;
			$arIns['last_stat_id'] = $this->MySQL->insertId();
			
			if ( $client = $this->MySQL->getRow('SELECT * FROM yapps_clients WHERE piwik_visitorId = ?s', (string)$arStat['piwik_visitorId']) ) {
				
				$this->MySQL->query('UPDATE yapps_clients SET ?u WHERE piwik_visitorId = ?s', $arIns, (string)$arStat['piwik_visitorId']);
				
			} else {
				
				$arIns['init_url'] = $POST['SourceLink'];
				$arIns['init_referrer'] = $POST['Referrer'];
				
				$GET = explode( '&', parse_url($POST['SourceLink'])['query'] );
				foreach ( $GET as $g ) {
					
					$t = explode( '=', $g );
					if ( explode('_', $t[0])[0] == 'utm' ) $arIns['init_'.$t[0]] = $t[1];
				}
				
				if ( $POST['PiwikVisitorID'] ) $arIns['piwik_visitorId'] = explode('.', $POST['PiwikVisitorID'])[0];
				if ( $POST['YandexVisitorID'] ) $arIns['yandex_visitorId'] = $POST['YandexVisitorID'];
				if ( $POST['GoogleVisitorID'] ) $arIns['google_visitorId'] = explode('.', $POST['GoogleVisitorID'])[2].'.'.explode('.', $POST['GoogleVisitorID'])[3];
				
				$Geo = json_decode( file_get_contents('https://api.sypexgeo.net/json/'.$ip) );
				
				if ( $Geo->country->name_ru ) $arIns['country'] = $Geo->country->name_ru;
				if ( $Geo->region->name_ru ) $arIns['region'] = $Geo->region->name_ru;
				if ( $Geo->city->name_ru ) $arIns['city'] = $Geo->city->name_ru;
				
				$arIns['init_app_id'] = (int)$this->AppInfo()->id;
				$arIns['init_site_id'] = (int)$POST['SiteID'];
				$arIns['timestamp'] = time();
				
				$this->MySQL->query('INSERT INTO yapps_clients SET ?u', $arIns);
			}
			
			return $this->sendForm( $arStat );
		}

	}