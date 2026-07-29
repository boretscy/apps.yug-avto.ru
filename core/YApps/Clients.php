<?php
	
	class Clients extends App {
		
		public function __construct( $arConf, SafeMySQL &$mysql, &$mssql, $mailer = false ) {
			
			$this->MySQL	= &$mysql;
			$this->Conf		= (object)$arConf['modules']['Clients'];
		}
		
		public function AppInfo() {
			
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_apps WHERE class = ?s', 'Clients');
		}
		
		public function getCLients( $user, $date1, $date2, $page = 1 ) {
			
			$user_sites = $this->YApps_GetUserSitesIds( $user );
			return $this->MySQL->getAll('SELECT * FROM yapps_clients WHERE init_site_id IN (?a) AND timestamp >= ?i AND timestamp < ?i LIMIT ?i, ?i', $user_sites, strtotime($date1), strtotime($date2), ($this->Conf->PageCount)*( (int)$page - 1 ), $this->Conf->PageCount );
        }
		
		public function getClientByStatId( $id, $app_id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_clients WHERE last_app_id = ?i AND last_stat_id = ?i ', (int)$app_id, (int)$id);
		}

        public function getItemsByFilter( $user, $filter ) {
            
            $user_sites = $this->YApps_GetUserSitesIds( $user );

            $w = []; $where = '';
            
            $w[] = $this->MySQL->parse('init_site_id IN (?a)', $user_sites );
            if ( $filter['init_app_id'] ) $w[] = $this->MySQL->parse('init_app_id = ?i', (int)$filter['init_app_id'] );
            if ( $filter['last_site_id'] ) $w[] = $this->MySQL->parse('last_site_id = ?i', (int)$filter['last_site_id'] );
            if ( $filter['last_app_id'] ) $w[] = $this->MySQL->parse('last_app_id = ?i', (int)$filter['last_app_id'] );

            $w[] = $this->MySQL->parse('timestamp >= ?i', strtotime($filter['date1']) );
            $w[] = $this->MySQL->parse('timestamp < ?i', strtotime($filter['date2']) );
            
            if ( count($w) ) $where = "WHERE ".implode(' AND ',$w);
            
            return $this->MySQL->getAll('SELECT * FROM yapps_clients ?p LIMIT ?i,?i', $where, ($this->Conf->PageCount)*( (int)$filter['page']- 1 ), $this->Conf->PageCount );
        }
        
        public function getAllCount() {

            return $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_clients');
        }
		
		public function getDashboard( $user, $dStart = false, $dEnd = false ) {
			
			$user_sites = $this->MySQL->getCol('SELECT site_id FROM yapps_users_sites WHERE user_id = ?i', (int)$user->id);
			$res['all'] = $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_clients WHERE init_site_id IN (?a)', $user_sites);
			
			$apps = $this->MySQL->getAll('SELECT * FROM yapps_apps');
			
			foreach ( $apps as $app ) {
				
				if ( $count = $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_clients WHERE init_app_id = ?i AND init_site_id IN (?a)', $app['id'], $user_sites) ) {
					
					$res['charts']['apps'][] = array_merge($app, ['count'=>$count]);
				}
			}
			
			$sites = $this->MySQL->getAll('SELECT * FROM yapps_sites WHERE id IN (?a)', $user_sites);
			foreach ( $sites as $site ) if ( $count = $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_clients WHERE init_site_id = ?i', $site['id']) ) $res['charts']['sites'][] = array_merge($site, ['count'=>$count]);
			
			$month = 30*24*3600;
			$step = 24*3600;
			
			if ( !$dStart ) $dStart = strtotime( date('Y-m-d', time()-$month) );
			if ( !$dEnd ) $dEnd = time();
			
			$dCur = $dStart+$step;
			
			while ( $dCur < time() ) {
				
				$res['charts']['time'][date('Y-m-d', $dStart)]['all'] = $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_clients WHERE timestamp >= ?i AND timestamp < ?i AND init_site_id IN (?a)', $dStart, $dCur, $user_sites);
				$res['charts']['time'][date('Y-m-d', $dStart)]['paid'] = $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_clients WHERE timestamp >= ?i AND timestamp < ?i AND init_utm_source IS NOT NULL AND init_site_id IN (?a)', $dStart, $dCur, $user_sites);
				
				$dCur = $dCur + $step;
				$dStart = $dStart + $step;
			}
			
			return $res;
		}
		
		public function pushClient( $POST, $ip ) {
			
			$where = '';
			if ( $POST['name'] ) $w[] = $this->MySQL->parse( 'name = ?s', $POST['name'] );
			if ( $POST['phone'] ) $w[] = $this->MySQL->parse( 'phone = ?s', Helper::formatPhoneIn( $POST['phone'] ) );
			if ( $POST['email'] ) $w[] = $this->MySQL->parse( 'email = ?s', $POST['email'] );
			if (count($w)) $where = 'WHERE '.implode(' AND ',$w);
			
			$client = $this->MySQL->getRow('SELECT * FROM yapps_clients ?p', $where);
			
			if ( $POST['name'] ) $arIns['name'] = $POST['name'];
			if ( $POST['email'] ) $arIns['email'] = $POST['email'];
			if ( $POST['phone'] ) $arIns['phone'] = Helper::formatPhoneIn( $POST['phone'] );
			if ( $POST['SourceLink'] ) $arIns['last_url'] = $POST['SourceLink'];
			if ( $POST['EventAction'] ) $arIns['last_event'] = $POST['EventAction'];
			if ( $POST['AppAction'] ) $arIns['last_app_id'] = (int)$this->MySQL->getOne('SELECT id FROM yapps_apps WHERE class = ?s', $POST['AppAction']);
			
			if ( $client ) {
				
				if ( !$client['global_id'] ) $arIns['global_id'] = Helper::newGlobalID( ['name'=>$arIns['name'], 'phone'=>$arIns['phone']] );
				
				$this->MySQL->query('UPDATE yapps_clients SET ?u WHERE id = ?i', $arIns, $client['id']);
				
			} else {
				
				$arIns['global_id'] = Helper::newGlobalID( ['name'=>$arIns['name'], 'phone'=>$arIns['phone']] );
				
				$arIns['init_url'] = $POST['SourceLink'];
				$arIns['init_referrer'] = $POST['Referrer'];
				
				// UTM
				$url = parse_url($POST['SourceLink']);
				$GET = explode( '&', $url['query'] );
				foreach ( $GET as $g ) {
					
					$t = explode( '=', $g );
					if ( explode('_', $t[0])[0] == 'utm' ) $arIns['init_'.$t[0]] = $t[1];
				} ///
				
				if ( $POST['PiwikVisitorID'] ) $arIns['piwik_visitorId'] = explode('.', $POST['PiwikVisitorID'])[0];
				if ( $POST['YandexVisitorID'] ) $arIns['yandex_visitorId'] = $POST['YandexVisitorID'];
				if ( $POST['GoogleVisitorID'] ) $arIns['google_visitorId'] = explode('.', $POST['GoogleVisitorID'])[2].'.'.explode('.', $POST['GoogleVisitorID'])[3];
				
				// Geo
				$Geo = json_decode( file_get_contents('https://api.sypexgeo.net/json/'.$ip) );
				if ( $Geo->country->name_ru ) $arIns['country'] = $Geo->country->name_ru;
				if ( $Geo->region->name_ru ) $arIns['region'] = $Geo->region->name_ru;
				if ( $Geo->city->name_ru ) $arIns['city'] = $Geo->city->name_ru; ///
				
				$arIns['init_app_id'] = $arIns['last_app_id'];
				$arIns['init_site_id'] = (int)$this->MySQL->getOne('SELECT id FROM yapps_sites WHERE url = ?s', $url['host']);
				$arIns['timestamp'] = time();
				
				$this->MySQL->query('INSERT INTO yapps_clients SET ?u', $arIns);
				
			} // if
			
		} // function
		
		public function findClientByPhone( $q ) {
			
			$res = ( $this->MySQL->getRow('SELECT * FROM yapps_clients WHERE phone LIKE ?s', '%'.$q.'%') ) ?: Helper::getRes(71);
//			if ( !$res['status'] && !$res['name'] ) $res['name'] = 'Найден, но имя неизвестно'; 
			return (object)$res;
		}
		
		public function getClient( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_clients WHERE id = ?i', (int)$id);
		}
		
		public function viewClient( $id ) {
			
			$res['client'] = $this->MySQL->getRow('SELECT * FROM yapps_clients WHERE piwik_visitorId = ?s', $id);
			$apps = $this->MySQL->getCol('SELECT url_key FROM yapps_apps WHERE activity = ?i', 1);
			$res['stat'] = [];
			foreach ( $apps as $k => $a ) {
				
				$res['stat'] = array_merge($res['stat'], $this->MySQL->getAll('SELECT * FROM ?n WHERE piwik_visitorId = ?s ORDER BY timestamp', 'yapps_app_'.$a.'_stat', $res['client']['piwik_visitorId']));
			}
			
			return $res;
		}
	}