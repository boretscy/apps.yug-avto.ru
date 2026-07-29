<?php

	class Calltouch extends App {

		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, $mailer = false ) {

			$this->MySQL		= &$mysql;
			$this->conf			= (object)$arConf['modules']['Calltouch'];
		}

		// Private Area
		public function AppInfo() {

			return (object)$this->MySQL->getRow('SELECT * FROM yapps_apps WHERE class = ?s', get_class($this));
        }

        public static function getMatomoID( $url ) {

            foreach ( explode('&', parse_url($url)['query']) as $g) {

                $t = explode('=', $g);
                if ( $t[0] == 'piwik_id' ) return $t[1];
            }

            return false;
        }

        public static function sendCTEvent( $id, $url ) {
            
            $ct = 'https://analytics.yug-avto.ru/piwik.php?_id='.$id.'&idsite=1&rec=1&url='.urlencode($url).'&e_c=Calltouch&e_a='.urlencode('Звонок').'&e_n=Call';
            // file_get_contents( $ct );
        }
        
        public function setHookEvent( $POST ) {
            
            // Helper::sp( json_decode(array_keys($POST)[0], true) );

            $tPOST = json_decode(array_keys($POST)[0], true);

            if ( $tPOST['test'] ) $POST = $tPOST;

            $site = (array)$this->MySQL->getRow('SELECT * FROM yapps_sites WHERE calltouch_id = ?s', $POST['siteId']);

			if ( $site ) {

                $ids = [
                    'piwik_visitorId' => ( $POST['url'] ) ? self::getMatomoID( $POST['url'] ) : '',
                    'yandex_visitorId' => ( $POST['yaClientId'] ) ?: '',
                    'google_visitorId' => ( $POST['gcid'] ) ?: ''
                ];
                
                $utms = [];
                if ( $POST['utm_source'] && $POST['utm_source'] != 'null' ) $utms['utm_source'] = $POST['utm_source'];
                if ( $POST['utm_medium'] && $POST['utm_medium'] != 'null' ) $utms['utm_medium'] = $POST['utm_medium'];
                if ( $POST['utm_campaign'] && $POST['utm_campaign'] != 'null' ) $utms['utm_campaign'] = $POST['utm_campaign'];
                if ( $POST['utm_content'] && $POST['utm_content'] != 'null' ) $utms['utm_content'] = $POST['utm_content'];
                if ( $POST['utm_term'] && $POST['utm_term'] != 'null' ) $utms['utm_term'] = $POST['utm_term'];

                $st_data = [
                    'site_id' => $site['id'],
                    'source_url' => ( $POST['url'] ) ?: '',
                    'event_name' => 'Звонок',
                    'referrer' => ( $POST['ref'] ) ?: '',
                    
                    'timestamp' => time(),
                    'phone' => Helper::formatPhoneIn( $POST['callerphone'] ),
                    'ct_timestamp' => (int)$POST['timestamp'],
                    'ct_source' => $POST['source'],
                    'ct_callReferenceNumber' => $POST['callReferenceNumber'],
                    'ct_phonenumber' => Helper::formatPhoneIn( $POST['phonenumber'] ),
                    'ct_pool' => $POST['pool']
                ];

                if ( $POST['tags_auto_ct'] && $POST['tags_auto_ct'] != 'null' ) {
                    
                    $st_data['tag_dc'] = explode(',', $POST['tags_auto_ct'])[0];
                    $st_data['tag_departament'] = explode(',', $POST['tags_auto_ct'])[1];
                }
                
                if ( $POST['ip'] && $POST['ip'] != 'null' ) $st_data['visitorIP'] = $POST['ip'];

                $st_data = array_merge( $st_data, $ids, $utms );
                // $raw = json_encode($POST);
                $raw = ( $POST['test']=='test' ) ? json_encode($POST) : false;
                $lastId = $this->pushStat( $st_data, $raw );

                if ( $ids['piwik_visitorId'] ) self::sendCTEvent( $ids['piwik_visitorId'], $POST['url'] );

                $cl_data = [
                    'phone' => Helper::formatPhoneIn( $POST['callerphone'] ),
                    'url' => ( $POST['url'] ) ?: '',
                    'event' => 'Звонок',
                    'stat_id' => $lastId,
                    'app_id' => $this->AppInfo()->id,
                    'site_id' => $site['id'],
                    'referrer' => ( $POST['ref'] ) ?: '',
                    'user_agent' => (($POST['browser'])?:'').' '.(($POST['os'])?:'').' '.(($POST['device'])?:'')
                ];
                
                $cl_data = array_merge( $cl_data, $utms );

                $geo = Helper::getGeo( $POST['ip'] );

                if ( $ids['piwik_visitorId'] ) $this->YApps_PushClient( $cl_data, $ids, $geo );

            
            } // if site
		}
        
        
		private function pushStat( $data, $raw = false ) {

            if ( $raw ) $data['raw'] = $raw;
            $stat = ( $raw ) ? 'yapps_app_calltouch_stat_test' : 'yapps_app_calltouch_stat';
            $this->MySQL->query('INSERT INTO '.$stat.' SET ?u', $data);
            return $this->MySQL->insertId();
        }
        
        public function setResult( $POST ) {

            // Helper::sp( json_decode($POST, true) );
            $this->MySQL->query('INSERT INTO yapps_app_calltouch_results SET ?u', json_decode($POST, true));
            echo $this->MySQL->insertId();
        }



        ///////////////////////////////////////////////////////////////////////////////////////////
        // API ////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        public function getInfo( $idchain ) {

            $res = $this->MySQL->getRow(
                'SELECT 
                    phone, ct_source, ct_callReferenceNumber, ct_phonenumber, ct_pool
                FROM yapps_app_calltouch_stat WHERE ct_callReferenceNumber = ?s',
                $idchain
            );

            if ( !$res ) $res = ['status'=>'Не найдено'];

            return $res;
        }








        public function _setHookEvent( $POST ) {

            $site = (array)$this->MySQL->getRow('SELECT * FROM yapps_sites WHERE calltouch_id = ?s', $POST['siteId']);

			if ( $site ) {

                $ids = [
                    'piwik_visitorId' => ( $POST['url'] ) ? self::getMatomoID( $POST['url'] ) : '',
                    'yandex_visitorId' => ( $POST['yaClientId'] ) ?: '',
                    'google_visitorId' => ( $POST['gcid'] ) ?: ''
                ];
                
                $utms = [];
                if ( $POST['utm_source'] && $POST['utm_source'] != 'null' ) $utms['utm_source'] = $POST['utm_source'];
                if ( $POST['utm_medium'] && $POST['utm_medium'] != 'null' ) $utms['utm_medium'] = $POST['utm_medium'];
                if ( $POST['utm_campaign'] && $POST['utm_campaign'] != 'null' ) $utms['utm_campaign'] = $POST['utm_campaign'];
                if ( $POST['utm_content'] && $POST['utm_content'] != 'null' ) $utms['utm_content'] = $POST['utm_content'];
                if ( $POST['utm_term'] && $POST['utm_term'] != 'null' ) $utms['utm_term'] = $POST['utm_term'];

                $st_data = [
                    'site_id' => $site['id'],
                    'source_url' => ( $POST['url'] ) ?: '',
                    'event_name' => 'Звонок',
                    'referrer' => ( $POST['ref'] ) ?: '',
                    
                    'timestamp' => time(),
                    'phone' => Helper::formatPhoneIn( $POST['callerphone'] ),
                    'ct_timestamp' => (int)$POST['timestamp'],
                    'ct_source' => $POST['source'],
                    'ct_callReferenceNumber' => $POST['callReferenceNumber'],
                    'ct_phonenumber' => Helper::formatPhoneIn( $POST['phonenumber'] ),
                    'ct_pool' => $POST['pool']
                ];

                if ( $POST['tags_auto_ct'] && $POST['tags_auto_ct'] != 'null' ) {
                    
                    $st_data['tag_dc'] = explode(',', $POST['tags_auto_ct'])[0];
                    $st_data['tag_departament'] = explode(',', $POST['tags_auto_ct'])[1];
                }
                
                if ( $POST['ip'] && $POST['ip'] != 'null' ) $st_data['visitorIP'] = $POST['ip'];

                $st_data = array_merge( $st_data, $ids, $utms );
                $lastId = $this->_pushStat( $st_data );

                if ( $ids['piwik_visitorId'] ) self::sendCTEvent( $ids['piwik_visitorId'], $POST['url'] );

                $cl_data = [
                    'phone' => Helper::formatPhoneIn( $POST['callerphone'] ),
                    'url' => ( $POST['url'] ) ?: '',
                    'event' => 'Звонок',
                    'stat_id' => $lastId,
                    'app_id' => $this->AppInfo()->id,
                    'site_id' => $site['id'],
                    'referrer' => ( $POST['ref'] ) ?: '',
                    'user_agent' => (($POST['browser'])?:'').' '.(($POST['os'])?:'').' '.(($POST['device'])?:'')
                ];
                
                $cl_data = array_merge( $cl_data, $utms );

                $geo = Helper::getGeo( $POST['ip'] );

                if ( $ids['piwik_visitorId'] ) $this->YApps_PushClient( $cl_data, $ids, $geo );

            
            } // if site
		}
        
        
		private function _pushStat( $data ) {

            $data['raw'] = json_encode( $data );
            $this->MySQL->query('INSERT INTO yapps_app_calltouch_stat_test SET ?u', $data);
            return $this->MySQL->insertId();
        }
        
    }