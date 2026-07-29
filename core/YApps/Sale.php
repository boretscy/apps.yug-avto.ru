<?php
    class Sale extends App {
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Init ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
        public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, PHPMailer &$mailer ) {
			
			$this->MySQL		= &$mysql;
			$this->Conf		= (object)$arConf['modules'][get_class($this)];
			$this->Mailer	= &$mailer;
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // System /////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function AppInfo() {
			
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_apps WHERE class = ?s', get_class($this));
		}
		
		public function getConf() {
			
			return $this->Conf;
        }
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Forms //////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getForms() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_sale_forms');
		}
		
		public function getForm( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_sale_forms WHERE id = ?i', (int)$id);
		}
		
		public function getFormByName( $q ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_sale_forms WHERE ru_name = ?s', (string)$q);
		}
		
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Settinghs //////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        public function getSettings() {

            return $this->MySQL->getRow('SELECT * FROM yapps_app_sale_settings WHERE id = ?i', 1);
        }
        
        public function setSettings( $POST, $FILES = false ) {

            $arIns = $POST;
            unset($arIns['form'], $arIns['id'], $arIns['_wysihtml5_mode']);
            $arIns['phone'] = Helper::formatPhoneIn($arIns['phone']);
            $arIns['maintenance'] = ($POST['maintenance']=='on') ? 1 : 0 ;
            $arIns['timer_use'] = ($POST['timer_use']=='on') ? 1 : 0 ;

            if ( $FILES ) {

                foreach ( $FILES as $k => $file ) {

                    if ( $file['error'] == 0 ) {

                        $arF = explode('.', $file['name']);
                        $arIns[$k] = 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/'.$k.'.'.$arF[count($arF)-1].'?'.md5_file($file['tmp_name']);
                        move_uploaded_file( $file['tmp_name'], __DIR__.'/../..'.$this->Conf->FileDir.'/'.$k.'.'.$arF[count($arF)-1] );
                    }
                }
            }

            $this->MySQL->query('UPDATE yapps_app_sale_settings SET ?u WHERE id = ?i', $arIns, 1);
            $res = ( $this->flashSale('Content', ['params'=>json_encode($this->getSettings())]) ) ? 0 : 200;

            return Helper::getRes($res);
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Brands /////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        public function getBrandsByUser( $user ) {
            
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Items //////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        public function getItems() {

            return $this->MySQL->getAll('SELECT * FROM yapps_app_sale');
        }

        public function getItemsByUser( $user ) {

            $brands_ids = $this->YApps_GetBrandsIDsByUser($user);
            return $this->MySQL->getAll('SELECT * FROM yapps_app_sale WHERE brand_id IN (?a) ORDER BY brand_id', $brands_ids);
        }

        public function getItemsByBrand( $id ) {

            return $this->MySQL->getAll('SELECT * FROM yapps_app_sale WHERE brand_id = ?i', $id);
        }

        public function getItem( $id ) {

            return $this->MySQL->getRow('SELECT * FROM yapps_app_sale WHERE id = ?i', $id);
        }

        public function setItem( $POST ) {

            $arIns = $POST;
            unset( $arIns['form'] );
            if ( !$arIns['en_name'] ) $arIns['en_name'] = $this->YApps_GetModel($arIns['model_id'])['en_name'];
            if ( !$arIns['photo'] ) $arIns['photo'] = $this->YApps_GetModel($arIns['model_id'])['photo'];
            $arIns['is_price'] = ($POST['is_price']=='on') ? 1 : 0 ;

            $this->MySQL->query('REPLACE INTO yapps_app_sale SET ?u', $arIns);

            $res = ( $this->flashSale('Data', ['params'=>json_encode($this->prepareItems())]) ) ? 0 : 200;

            return Helper::getRes(0);
        }

        public function delItem( $id ) {

            $this->MySQL->query('DELETE FROM yapps_app_sale WHERE id = ?i', $id);

            $res = ( $this->flashSale('Data', ['params'=>json_encode($this->prepareItems())]) ) ? 0 : 200;

            return Helper::getRes(0);
        }

        private function prepareItems() {

            $brand_ids = $this->MySQL->getCol('SELECT DISTINCT brand_id FROM yapps_app_sale');
            $data = $this->MySQL->getInd('url_key', 'SELECT * FROM yapps_brands WHERE id IN (?a) ORDER BY en_name ASC', $brand_ids);
            
            foreach ( $data as $k => $r ) {

                $data[$k]['models'] = $this->MySQL->getAll('SELECT * FROM yapps_app_sale WHERE brand_id = ?i', $r['id']);
                $dcs = $this->MySQL->getAll('SELECT * FROM yapps_dcs WHERE brand_id = ?i', $r['id']);

                if ( $r['id'] == 17 ) $dcs = $this->MySQL->getAll('SELECT * FROM yapps_dcs WHERE brand_id = ?i AND id IN(?a)', $r['id'], [9,10,13]);
                if ( $r['id'] == 2 ) $dcs = $this->MySQL->getAll('SELECT * FROM yapps_dcs WHERE brand_id = ?i AND id IN(?a)', $r['id'], [47,48]);

                foreach ( $dcs as $d ) {

                    $dc = [
                        'addr' => $d['address'],
                        'coords' => [
                            $d['coords_lat'],
                            $d['coords_lon']
                        ],
                        'en_name' => explode(',', $d['address'])[0]
                    ];

                    $data[$k]['dc'][] = $dc;

                }
            }

            return $data;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////
        //API /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        public function flashSale( $block = 'Content', $params = [] ) {

            $url = 'https://sale.yug-avto.ru/api/';
            $headers = "Content-Type: application/x-www-form-urlencoded";
            $params['APIToken'] = $this->Conf->APIToken;
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
        // Stats //////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        
        public function setEvent( $POST, $url, $ip ) {
            
            $site = (array)$this->YApps_GetSiteByHost( parse_url($url)['host'] );

			if ( $site && Helper::isNotFakePhone($POST['phone']) ) {

                $ids = [
                    'piwik_visitorId' => explode('.', $POST['PiwikVisitorID'])[0],
                    'yandex_visitorId' => $POST['YandexVisitorID'],
                    'google_visitorId' => explode('.', $POST['GoogleVisitorID'])[2].'.'.explode('.', $POST['GoogleVisitorID'])[3]
                ];

                $st_data = [
                    'user_id' => 0,
                    'site_id' => 28,
                    'source_title' => 'Сайт распродаж. '.$POST['brand'].'.',
                    'source_url' => $POST['SourceLink'],
                    'event_name' => 'Сайт распродаж: '.$POST['EventAction'],
                    'car' => $_POST['car'],
                    'brand' => $_POST['brand'],
					'brand_id' => $this->YApps_GetBrandByEnName($_POST['brand'])['id'],
					'form' => $_POST['form'],
					'form_id' => $this->getFormByName($_POST['form'])['id'],
                    'name' => $POST['name'],
                    'phone' => Helper::formatPhoneIn( $POST['phone'] ),
                    'email' => $POST['email'],
                    'visitorIP' => $ip,
                    'timestamp' => time(),
                ];

                $st_data = array_merge( $st_data, $ids );
                if ( $utms = Helper::getUtm( $url ) ) $st_data = array_merge( $st_data, $utms );
                
                $lastId = $this->pushStat( $st_data );

                $cl_data = [
                    'name' => $POST['name'],
                    'phone' => Helper::formatPhoneIn( $POST['phone'] ),
                    'email' => $POST['email'],
                    'url' => $url,
                    'event' => 'Сайт распродаж. '.$POST['brand'].'.',
                    'stat_id' => $lastId,
                    'app_id' => $this->AppInfo()->id,
                    'site_id' => 28,
                    'referrer' => $POST['Referrer'],
                    'user_agent' => $POST['UserAgent']
                ];

                if ( $utms ) $cl_data = array_merge( $cl_data, $utms );

                $geo = Helper::getGeo( $ip );
                //Helper::sp($cl_data);
                $this->YApps_PushClient( $cl_data, $ids, $geo );

			} // if
		}
        
        
		private function pushStat( $data ) {

            $this->MySQL->query('INSERT INTO yapps_app_sale_stat SET ?u', $data);
            return $this->MySQL->insertId();
        }
		
		
		
    }
?>