<?php
	
	class Potboiler extends App {
		
		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, $mailer = false ) {
			
			$this->MySQL		= &$mysql;
			$this->conf		= (object)$arConf['modules']['Potboiler'];
			$this->secret	= $arConf['App']['secret'];
        }

        public function AppInfo() {
			
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_apps WHERE class = ?s', 'Potboiler');
        }
        
        public function getSettings() {
            
            return (object)$this->MySQL->getRow('SELECT * FROM yapps_app_potboiler_settings');
        }

        public function setSettings( $POST ) {
            
            return $this->MySQL->query('UPDATE yapps_app_potboiler_settings SET ?u WHERE id = ?i', $POST, 1);
        }
		
		public function getEmptyPhoneItems() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_potboiler WHERE user_phone IS NULL OR TRIM(user_phone) = "" LIMIT '.$this->conf->Parser['ItemsLimit']);
		}
		
		public function getFullPhoneItems() {
			
			return $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_app_potboiler WHERE user_phone NOT NULL AND user_phone != ?s', 'error');
		}

        public function getCount() {
            
            $url = $this->getSettings()->parse_url;
            $str = file_get_contents( $url );
			
			$doc = new DOMDocument();
			$doc->loadHTML($str);
			$doc->normalizeDocument();
			
			$finder = new DomXPath($doc);
            $CountNode = $finder->query( $this->conf->Xpath['Count'] );
            
            return (int)str_replace(' ', '', $CountNode[0]->textContent);
        }

        public function getList( $page = 1 ) {
            
            $url = $this->getSettings()->parse_url.'&p='.$page;
            $str = file_get_contents( $url );
			
			$doc = new DOMDocument();
			$doc->loadHTML($str);
			$doc->normalizeDocument();
			
			$finder = new DomXPath($doc);
            $Nodes = $finder->query( $this->conf->Xpath['List'] );
            
            foreach ( $Nodes as $n ) {
                
                $t['item_url'] = 'https://m.avito.ru'.$n->childNodes[0]->childNodes[1]->attributes[0]->value;
                $arId = explode( '_', explode('/', $t['item_url'])[5] );
                $t['item_id'] = $arId[count($arId)-1];
                $t['item_name'] = trim($n->childNodes[0]->childNodes[1]->childNodes[3]->nodeValue);
				$desc = explode(' руб.', trim( $n->childNodes[0]->childNodes[1]->childNodes[5]->textContent) );
				$t['item_price'] = (float)str_replace(' ', '', trim($desc[0]) );
				$inDate = $n->childNodes[0]->childNodes[1]->childNodes[7]->childNodes[3]->nodeValue;
				if ( $inDate === NULL ) $inDate = $n->childNodes[0]->childNodes[1]->childNodes[7]->childNodes[1]->nodeValue;
				$inDate = trim( $inDate );
				$arDate = explode( substr($inDate, -6, 1), $inDate);
				$arDate[0] = substr($arDate[0], 0, -1);
				$old = $arDate[0];
				foreach ( $this->conf->Date['Days'] as $k => $v ) $arDate[0] = str_replace($k, $v, $arDate[0]);
				if ($arDate[0] == $old) {
					$temp = explode(' ', $arDate[0]);
					$outDate = $this->conf->Date['Month'][$temp[1]].' '.$temp[0].', '.$arDate[1];
				} else {
					$outDate = $arDate[0].' '.$arDate[1];
				}
				$t['item_timestamp'] = strtotime($outDate);
				$t['item_hash'] = md5( $t['item_name'] );
				
				//$t['user'] = $this->getPhone( $t['item_url'] );
				
                $res[] = $t;
            }

            return $res;
        }
		
		public function getPhone( $url ) {
			
			$str = file_get_contents( $url );
			$headers = $http_response_header;
			
			$doc = new DOMDocument();
			$doc->loadHTML($str);
			$doc->normalizeDocument();
			
			$finder = new DomXPath($doc);
            
			$urlNode = $finder->query( '//a[@class="person-name person-name-link"]' );
			$res['user_url'] = 'https://m.avito.ru'.$urlNode[0]->attributes[0]->value;
			if ( $res['user_url'] == 'https://m.avito.ru' ) $res['user_url'] = 'error';
			$res['user_id'] = explode('=', explode('&', explode('?', $res['user_url'])[1] )[0] )[1];
			
			$phoneNode = $finder->query('//a[@title="Телефон продавца"]');
			
			$getPhoneLink = 'https://m.avito.ru'.$phoneNode[0]->attributes[1]->value.'?async';
			
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $getPhoneLink);
			curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36');
			curl_setopt($curl, CURLOPT_REFERER, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			
			$res['user_phone'] = preg_replace("/[^0-9]/", '', trim(json_decode(curl_exec($curl))->phone));
			if ( !$res['user_phone'] ) $res['user_phone'] = 'error';
			curl_close($curl);
			
			return $res;
		}
		
		
		public function checkItem( $item_id ) {
			
			return ( $id = $this->MySQL->getOne('SELECT id FROM yapps_app_potboiler WHERE item_id = ?s', $item_id) ) ? $id : false;
		}
		
		public function newItem( $POST ) {
			
			return $this->MySQL->query('INSERT INTO yapps_app_potboiler SET ?u', $POST);
		}
		
		public function updateItem( $POST, $id ) {
			
			return $this->MySQL->query('UPDATE yapps_app_potboiler SET ?u WHERE id = ?i', $POST, (int)$id);
		}
		
		public function getCompleteItems( $page = 1 ) {
			
			$res['items'] = $this->MySQL->getAll('SELECT * FROM yapps_app_potboiler WHERE user_phone IS NOT NULL AND user_phone != ?s LIMIT ?i, ?i', 'error', $this->conf->PageCount*((int)$page-1), $this->conf->PageCount);
			$res['percent'] = $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_app_potboiler WHERE user_phone IS NOT NULL AND user_phone != ?s', 'error')/$this->getCountItems()*100;
			
			return $res;
		}
		
		public function getCountItems() {
			
			return (int)$this->MySQL->getOne('SELECT COUNT(*) FROM yapps_app_potboiler');
		}
		
		public function clearItems() {
			
			$this->setSettings( ['status'=>0, 'next_page'=>1, 'percent'=>0, 'items'=>0, 'total_items'=>0] );
			$this->MySQL->query('TRUNCATE yapps_app_potboiler');
			
			return true;
		}
		
		public function clearErrors() {
			
			$this->setSettings( ['status'=>0, 'next_page'=>1, 'percent'=>0, 'items'=>0, 'total_items'=>0] );
			$this->MySQL->query('DELETE FROM yapps_app_potboiler WHERE user_phone = ?s', 'error');
			
			return true;
		}
    }