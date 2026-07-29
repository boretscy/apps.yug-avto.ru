<?php
	
	class Calc extends App {
		
/////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////// PRIVATE AREA //////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////

		private static function formatYears( $value ) {
			
			if ( in_array((int)$value, [1, 21, 31, 41, 51, 61, 71, 81, 91])) { $res = 'год'; }
			elseif ( in_array((int)$value, [2, 3, 4, 22, 23, 24, 32, 33, 34, 42, 43, 44, 52, 53, 54, 62, 63, 64, 72, 73, 74, 82, 83, 84, 92, 93, 94])) { $res = 'года'; }
			else { $res = 'лет'; }
			
			return $res;
		}
		
		public function sendForm( $arIns ) {
			
			$this->Mailer->CharSet = 'utf-8';
			$this->Mailer->setFrom('alert@apps.yug-avto.ru', 'Оповещения Юг-Авто Apps');
			$this->Mailer->ClearAddresses();
			
			$sets = $this->getColorSettingsById( $arIns['site_id'] );
			$site = $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE id = ?i', (int)$arIns['site_id']);
			$model = $this->MySQL->getOne('SELECT ru_name FROM yapps_app_calc_models WHERE id = ?i', $arIns['model_id']);
			$mod = $this->MySQL->getOne('SELECT ru_name FROM yapps_app_calc_mods WHERE id = ?i', $arIns['mod_id']);
			
			$arRec = preg_split( '/[\s,;]+/', $sets['recipients'] );
			foreach ($arRec as $email) $this->Mailer->addAddress($email, '');
			
			$this->Mailer->Subject = 'Заявка на техническое обслуживание. Сайт: '.$site['ru_name'];
			
			if ( $arIns['event_name'] == 'Незавершенная форма' ) $this->Mailer->Subject = 'Незавершенное заполнение заявки на техническое обслуживание. Сайт: '.$site['ru_name'];
			
			$message = 'Имя: '.$arIns['name'].'<br />';
			$message .= 'Телефон: '.(($arIns['phone'])?Helper::formatPhoneOut($arIns['phone']):'').'<br />';
			if ( $arIns['email'] ) $message .= 'Email: '.$arIns['email'].'<br />';
			if ( $arIns['date_timestamp'] ) $message .= 'Дата: '.date('d.m.Y H:i', $arIns['date_timestamp']).'<br />';
			$message .= '<br />';
			$message .= 'Автомобиль: '.$model.' '.$mod.'<br />';
			
			$this->Mailer->msgHTML($message);
			return $this->Mailer->Send();
		}


/////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// PUBLIC AREA //////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		
		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, PHPMailer &$mailer ) {
			
			$this->MySQL		= &$mysql;
			$this->Mailer	= &$mailer;
			$this->conf		= (object)$arConf['modules']['Calc'];
			$this->Yandex	= (object)$arConf['App']['Yandex'];
		}
		
		public function AppInfo() {
			
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_apps WHERE class = ?s', 'Calc');
		}
		
		// Checkpoints
		public function getCheckpoints( $q = 'All' ) {
			
			if ( $q == 'All' ) {
				
				return $this->MySQL->getAll('SELECT * FROM yapps_app_calc_checkpoints ORDER BY sort ASC');
				
			} else {
				
				$keys = array_keys($q);
				
				$query = 'SELECT * FROM yapps_app_calc_checkpoints WHERE ';
				foreach ( $keys as $k ) $query .= $k.' = '.$keys[$k].' AND ';
				$query = substr($query, 0, -5);
				$query = ' ORDER BY sort ASC';
				
				return $this->MySQL->getAll($query);
			}
		}
		
		public function getCheckpointById( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_calc_checkpoints WHERE id = ?i', (int)$id);
		}
		
		public function getCheckpointsByModelId( $id ) {
			
		}
		
		public function setCheckpoint( $POST ) {
			
			$arIns = $POST;
			unset($arIns['id'], $arIns['form']);
			
			$arIns['sort'] = (int)$arIns['sort'];
			
			if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_app_calc_checkpoints SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_calc_checkpoints SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
		}
		
		public function getCheckworks( $q = 'All' ) {
			
			if ( $q == 'All' ) {
				
				return $this->MySQL->getAll('SELECT * FROM yapps_app_calc_checkworks ORDER BY sort ASC');
				
			} else {
				
				$keys = array_keys($q);
				
				$query = 'SELECT * FROM yapps_app_calc_checkworks WHERE ';
				foreach ( $keys as $k ) $query .= $k.' = '.$keys[$k].' AND ';
				$query = substr($query, 0, -5);
				$query = ' ORDER BY sort ASC';
				
				return $this->MySQL->getAll($query);
			}
        }
        
        public function getMainCheckworks( $q = 'All' ) {

            if ( $q == 'All' ) {
                
                return $this->MySQL->getAll('SELECT * FROM yapps_app_calc_checkworks WHERE additional_flag = ?i', 0);

            }

        }

        public function getAdditionalCheckworks( $q = 'All' ) {

            if ( $q == 'All' ) {
                
                return $this->MySQL->getAll('SELECT * FROM yapps_app_calc_checkworks WHERE additional_flag = ?i', 1);

            }

        }
		
		public function getCheckworkById( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_calc_checkworks WHERE id = ?i', (int)$id);
		}
		
		public function setCheckwork( $POST ) {
			
			$arIns = $POST;
			unset($arIns['id'], $arIns['form'], $arIns['additional_flag'], $arIns['sort']);
			
			$arIns['sort'] = (int)$POST['sort'];
			
			$arIns['additional_flag'] = ($POST['additional_flag']=='on') ? 1 : 0 ;
			
			if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_app_calc_checkworks SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_calc_checkworks SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
		}
		
		
		public function getDiscounts( $q = 'All' ) {
			
			if ( $q == 'All' ) {
				
				return $this->MySQL->getAll('SELECT * FROM yapps_app_calc_discounts');
				
			} else {
				
				// TODO по моделям
				/*
				$keys = array_keys($q);
				
				$query = 'SELECT * FROM yapps_app_calc_discounts WHERE ';
				foreach ( $keys as $k ) $query .= $k.' = "'.$keys[$k].'" AND ';
				$query = substr($query, 0, -5);
				$query = ' ORDER BY sort ASC';
				
				return $this->MySQL->getAll($query);
				*/
			}
		}
		
		public function getDiscountById( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_calc_discounts WHERE id = ?i', (int)$id);
		}
		
		public function setDiscount( $POST ) {
			
			$arIns = $POST;
			unset($arIns['id'], $arIns['form']);
			
			if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_app_calc_discounts SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_calc_discounts SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
        }
        
		public function getWorkvalues() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_calc_workvalues');
		}
		
		public function getWorkvalueById( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_calc_workvalues WHERE id = ?i', (int)$id);
		}
		
		public function setWorkvalue( $POST ) {
			
			$arIns = $POST;
			unset($arIns['id'], $arIns['form']);
			
			if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_app_calc_workvaluess SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_calc_workvalues SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
        }
		

        // Models Area
        public function getModelById( $id ){
            
            $res = $this->MySQL->getRow('SELECT * FROM yapps_app_calc_models WHERE id = ?i', (int)$id);
			$query = '
				SELECT * 
				FROM yapps_app_calc_models_sites
				INNER JOIN yapps_sites 
				ON yapps_app_calc_models_sites.site_id = yapps_sites.id
				WHERE model_id = ?i
			';
            $res['sites'] = $this->MySQL->getAll($query, (int)$id);
			$res['sites_ids'] = $this->MySQL->getCol('SELECT site_id FROM yapps_app_calc_models_sites WHERE model_id = ?i', (int)$id);
			$res['points_ids'] = $this->MySQL->getCol('SELECT checkpoint_id FROM yapps_app_calc_model_connectors WHERE model_id = ?i AND checkpoint_id != ?i', (int)$id, 0);
			$res['works_ids'] = $this->MySQL->getCol('SELECT checkwork_id FROM yapps_app_calc_model_connectors WHERE model_id = ?i AND checkwork_id != ?i', (int)$id, 0);
			$res['discounts_ids'] = $this->MySQL->getCol('SELECT discount_id FROM yapps_app_calc_model_connectors WHERE model_id = ?i AND discount_id != ?i', (int)$id, 0);
			$res['discounts'] = $this->MySQL->getAll('SELECT * FROM yapps_app_calc_discounts WHERE id IN (?a)', $res['discounts_ids']);
			
			// Main Works
			$res['mainworks'] = $this->MySQL->getAll('SELECT * FROM yapps_app_calc_checkworks WHERE id IN (?a) AND additional_flag = ?i ORDER BY sort ASC', $res['works_ids'], 0);
			foreach ( $res['mainworks'] as $k => $w ) {
				
				$points = $this->MySQL->getAll('SELECT checkpoint_id, workvalue_id FROM yapps_app_calc_models_checkpoints WHERE model_id = ?i AND checkwork_id = ?i', (int)$id, $w['id']);
				foreach ( $points as $p ) $res['mainworks'][$k]['points_values'][$p['checkpoint_id']] = $p['workvalue_id'];
				
			} // foreach
			
			// Additional Works
			$res['addworks'] = $this->MySQL->getAll('SELECT * FROM yapps_app_calc_checkworks WHERE id IN (?a) AND additional_flag = ?i', $res['works_ids'], 1);
			foreach ( $res['addworks'] as $k => $w ) {
				
				$p = $this->MySQL->getOne('SELECT value FROM yapps_app_calc_prices WHERE model_id = ?i AND checkwork_id = ?i AND discount_id = ?i', (int)$id, $w['id'], 0);
				$res['addworks'][$k]['price'] = $p;
				
				foreach ( $res['discounts_ids'] as $d ) {
					
					$pD = $this->MySQL->getOne('SELECT value FROM yapps_app_calc_prices WHERE model_id = ?i AND checkwork_id = ?i AND discount_id = ?i', (int)$id, $w['id'], $d);
					$res['addworks'][$k]['price_discount'][$d] = $pD;
				
				} // foreach
				
			} // foreach*/
			
			// Modifications
			$res['mods'] = $this->getModsByModel( $id );
			foreach ( $res['mods'] as $k => $w ) {
				
				$points = $this->MySQL->getAll('SELECT checkpoint_id, value FROM yapps_app_calc_prices WHERE model_id = ?i AND mod_id = ?i AND checkwork_id = ?i AND discount_id = ?i', (int)$id, $w['id'], 0, 0);
				foreach ( $points as $p ) $res['mods'][$k]['points_values'][$p['checkpoint_id']] = $p['value'];
				
				foreach ( $res['discounts_ids'] as $d ) {
					
					$Dpoints = $this->MySQL->getAll('SELECT checkpoint_id, value FROM yapps_app_calc_prices WHERE model_id = ?i AND mod_id = ?i AND checkwork_id = ?i AND discount_id = ?i', (int)$id, $w['id'], 0, $d);
					foreach ( $Dpoints as $p ) $res['mods'][$k]['points_disc_values'][(int)$d][$p['checkpoint_id']] = $p['value'];
					
				} // foreach
				
			} // foreach
			
			foreach ( $res['mods'] as $m ) $tmp[$m['id']] = $m;
			$res['mods'] = $tmp;
			
			return $res;
        }
		
		public function getModelsBySite( $id ) {
			
			foreach ( $this->MySQL->getAll('SELECT * FROM yapps_app_calc_models_sites WHERE site_id = ?i', (int)$id) as $c ) {
				
				$res['models'][] = $this->getModelById( $c['model_id'] );
				
			}
			$res['site'] = $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE id = ?i', (int)$id);
			
			return $res;
		}

        public function setModel( $POST ) {
            
            $arIns = [
                'ru_name' => $POST['ru_name'],
                'disclamer' => $POST['disclamer']
            ];

            if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_app_calc_models SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_calc_models SET ?u', $arIns);
            }
            
            $modelId = ( $POST['id'] ) ? $POST['id'] : $this->MySQL->insertId();

            $checkW = array_merge( $POST['checkwork_id'], $POST['checkwork_id_add'] );
            $this->MySQL->query('DELETE FROM yapps_app_calc_model_connectors WHERE model_id = ?i AND checkwork_id != ?i', (int)$modelId, 0);
            foreach ( $checkW as $i ) $this->MySQL->query('INSERT INTO yapps_app_calc_model_connectors SET ?u', ['model_id'=>(int)$modelId, 'checkwork_id'=>(int)$i]);
            
            $this->MySQL->query('DELETE FROM yapps_app_calc_model_connectors WHERE model_id = ?i AND checkpoint_id != ?i', (int)$modelId, 0);
            foreach ( $POST['checkpoint_id'] as $i ) $this->MySQL->query('INSERT INTO yapps_app_calc_model_connectors SET ?u', ['model_id'=>(int)$modelId, 'checkpoint_id'=>(int)$i]);

            $this->MySQL->query('DELETE FROM yapps_app_calc_model_connectors WHERE model_id = ?i AND discount_id != ?i', (int)$modelId, 0);
            foreach ( $POST['discount_id'] as $i ) $this->MySQL->query('INSERT INTO yapps_app_calc_model_connectors SET ?u', ['model_id'=>(int)$modelId, 'discount_id'=>(int)$i]);

            $this->MySQL->query('DELETE FROM yapps_app_calc_models_sites WHERE model_id = ?i', (int)$modelId);
            foreach ( $POST['site_id'] as $i ) $this->MySQL->query('INSERT INTO yapps_app_calc_models_sites SET ?u', ['model_id'=>(int)$modelId, 'site_id'=>(int)$i]);
			
			return Helper::getRes(0);
        }
		
		public function setModelSettings ( $POST ) {
			
			$this->MySQL->query('DELETE FROM yapps_app_calc_models_checkpoints WHERE model_id = ?i', (int)$POST['id']);
			$this->MySQL->query('DELETE FROM yapps_app_calc_prices WHERE model_id = ?i', (int)$POST['id']);
			
			$points = $this->MySQL->getCol('SELECT checkpoint_id FROM yapps_app_calc_model_connectors WHERE model_id = ?i AND checkpoint_id != ?i', (int)$POST['id'], 0);
			
			foreach ( $POST as $k => $p ) {
				
				$arrKey = explode('--', $k);
				switch ( count($arrKey) ) {
					
					case 0:
						break;
					
					case 1:
						
						$arrKeyInner = explode('_', $arrKey[0]);
						
						switch ( $arrKeyInner[0] ) {
							
							case 'work':
								
								$arInsPoints = [];
								$arInsPoints['model_id'] = (int)$POST['id'];
								$arInsPoints['checkwork_id'] = (int)$arrKeyInner[1];
								
								foreach ( $p as $kInner => $v ) {
									
									$arInsPoints['checkpoint_id'] = (int)$points[$kInner];
									$arInsPoints['workvalue_id'] = (int)$v;
									
									$this->MySQL->query('INSERT INTO yapps_app_calc_models_checkpoints SET ?u', $arInsPoints);
									
								} // foreach
								
								break;
								
							case 'mod':
								
								$arInsPrices = [];
								$arInsPrices['model_id'] = (int)$POST['id'];
								$arInsPrices['mod_id'] = (int)$arrKeyInner[1];
								
								foreach ( $p as $kInner => $v ) {
									
									$arInsPrices['checkpoint_id'] = (int)$points[$kInner];
									$arInsPrices['value'] = (float)$v;
									
									$this->MySQL->query('INSERT INTO yapps_app_calc_prices SET ?u', $arInsPrices);
									
								} // foreach
								
								break;
							
							case 'addwork':
								
								$arInsPrices = [];
								$arInsPrices['model_id'] = (int)$POST['id'];
								$arInsPrices['checkwork_id'] = (int)$arrKeyInner[1];
								$arInsPrices['value'] = (float)$p;
								
								$this->MySQL->query('INSERT INTO yapps_app_calc_prices SET ?u', $arInsPrices);
								
								break;
								
						} // switch
						
						break;
						
					case 2:
						
						$arrKeyInner = explode('_', $arrKey[0]);
						$arrDiscInner = explode('_', $arrKey[1]);
						
						switch ( $arrKeyInner[0] ) {
							
							case 'mod';
								
								$arInsPrices = [];
								$arInsPrices['model_id'] = (int)$POST['id'];
								$arInsPrices['mod_id'] = (int)$arrKeyInner[1];
								$arInsPrices['discount_id'] = (int)$arrDiscInner[1];
								
								foreach ( $p as $kInner => $v ) {
									
									$arInsPrices['checkpoint_id'] = (int)$points[$kInner];
									$arInsPrices['value'] = (float)$v;
									
									$this->MySQL->query('INSERT INTO yapps_app_calc_prices SET ?u', $arInsPrices);
									
								} // foreach
								
								break;
								
							case 'addwork':
								
								$arInsPrices = [];
								$arInsPrices['model_id'] = (int)$POST['id'];
								$arInsPrices['checkwork_id'] = (int)$arrKeyInner[1];
								$arInsPrices['discount_id'] = (int)$arrDiscInner[1];
								$arInsPrices['value'] = (float)$p;
								
								$this->MySQL->query('INSERT INTO yapps_app_calc_prices SET ?u', $arInsPrices);
								
								break;
							
						} // switch
						
						break;
					
				} //switch
				
			} // foreach
			
			return Helper::getRes(0);
		}
		
		
		
		// Mods Area
		public function getModsByModel( $id ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_calc_mods WHERE model_id = ?i', (int)$id);
		}
		
		public function getModById( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_calc_mods WHERE id = ?i', (int)$id);
		}
		
		public function setMod( $POST ) {
			
			$arIns = [
				'model_id' => (int)$POST['model_id'],
				'ru_name' => $POST['ru_name']
			];
			
			if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_app_calc_mods SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_calc_mods SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
		}
		
		
		// Colors area
		
		public function getColorSettins( $sites = false ) {
			
			if ( !$sites ) {
				
				return false;
				
			} else {
				
				foreach ( $sites as $k => $s ) $sites [$k]['css'] = $this->MySQL->getRow('SELECT * FROM yapps_app_calc_settings WHERE site_id = ?i', $s['id']);
				
				return $sites;
			}
		}
		
		public function getColorSettingsByHost( $host ) {
			
			$site_id = (int)$this->MySQL->getOne('SELECT id FROM yapps_sites WHERE url = ?s', $host);
			return ( $res = $this->getColorSettingsById( $site_id ) ) ? $res : false;
		}
		
		public function getColorSettingsById( $site_id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_calc_settings WHERE site_id = ?i', (int)$site_id);
		}
		
		public function setColorSettings( $POST ) {
			
			$arIns = $POST;
			unset( $arIns['id'], $arIns['form'] );
			
			if ( $this->MySQL->getOne('SELECT id FROM yapps_app_calc_settings WHERE site_id = ?i', (int)$POST['id']) ) {
				
				$this->MySQL->query('UPDATE yapps_app_calc_settings SET ?u WHERE site_id = ?i', $arIns, (int)$POST['id']);
				
			} else {
				
				$arIns['site_id'] = (int)$POST['id'];
				$this->MySQL->query('INSERT INTO yapps_app_calc_settings SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
		}
		
		
		// Statisctics area
		
		public function getStats( $user, $date1, $date2 ) {
			
			$sites = $this->MySQL->getCol('SELECT site_id FROM yapps_users_sites WHERE user_id = ?i', (int)$user->id);
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_calc_stat WHERE site_id IN (?a) AND timestamp >= ?i AND timestamp < ?i', $sites, strtotime($date1), strtotime($date2));
		}
		
		// FrontEnd / API
		
		public function getAPIModelsByHost( $host ) {
			
			$site = $this->MySQL->getRow('SELECT id FROM yapps_sites WHERE url = ?s', $host);
			
			foreach ( $this->MySQL->getAll('SELECT * FROM yapps_app_calc_models_sites WHERE site_id = ?i', $site['id']) as $c ) {
				
				if ( $c ) $res[$c['model_id']] = $this->getModelById( $c['model_id'] );
				if ( empty($res[$c['model_id']]['mods']) ) unset($res[$c['model_id']]);
			}
			
			return ( $res && !empty($res) ) ? $res : false;
		}
		
		public function getAllModels() {
		}
		
		
		public function pushStat( $POST, $user, $ip ) {
            
            if ( Helper::isNotFakePhone($POST['Phone']) ) {

                $ids = [
                    'piwik_visitorId' => explode('.', $POST['PiwikVisitorID'])[0],
                    'yandex_visitorId' => $POST['YandexVisitorID'],
                    'google_visitorId' => explode('.', $POST['GoogleVisitorID'])[2].'.'.explode('.', $POST['GoogleVisitorID'])[3]
                ];
                
                $utms = Helper::getUtm( $POST['SourceLink'] );
                
                $st_data = [
                    'user_id' => $user->id,
                    'site_id' => (int)$POST['SiteID'],
                    'source_title' => $POST['SourceTitle'],
                    'source_url' => $POST['SourceLink'],
                    'event_name' => $POST['EventName'],
                    'timestamp' => time(),
                    'name' => $POST['Name'],
                    'phone' => Helper::formatPhoneIn( $POST['Phone'] ),
                    'item_id' => (int)$POST['ItemID'],
                    'referrer' => $POST['Referrer'],
                    'visitorIP' => $ip,
                ];

                if ( $utms ) $st_data = array_merge( $st_data, $utms );

                $st_data = array_merge( $st_data, $ids );
                $this->MySQL->query('INSERT INTO yapps_app_hot_stat SET ?u', $st_data);
                $lastId = $this->MySQL->insertId();

                $cl_data = [
                    'name' => $POST['Name'],
                    'phone' => Helper::formatPhoneIn( $POST['Phone'] ),
                    'url' => $POST['SourceLink'],
                    'event' => $POST['EventName'],
                    'stat_id' => $lastId,
                    'app_id' => $this->AppInfo()->id,
                    'site_id' => (int)$POST['SiteID'],
                    'referrer' => $POST['Referrer']
                ];
                
                if ( $utms ) $cl_data = array_merge( $cl_data, $utms );

                $geo = Helper::getGeo( $ip );

                $this->YApps_PushClient( $cl_data, $ids, $geo );

                return $this->sendForm( $st_data );
            
            } // if Not Fake Phone
		}
		
		
		
		
		// public function getScript( $user, $URL ) {
			
		// 	$host = parse_url( $URL )['host'];
		// 	$site = $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE url = ?s', $host);
		// 	$models = $this->getAPIModelsByHost( $host );
		// 	$workvalues = $this->getWorkvalues();
			
		// 	if ( $models ) {
				
		// 		foreach ( $models as $k => $v ) {
					
		// 			unset( $models[$k]['sites'], $models[$k]['sites_ids'], $models[$k]['discounts_ids'], $models[$k]['works_ids'] );
		// 			foreach ( $v['points_ids'] as $p ) {
						
		// 				$models[$k]['points'][$p] = $this->getCheckpointById($p);
		// 				$models[$k]['points'][$p]['name'] = number_format((int)$models[$k]['points'][$p]['milleage'], 0, '', ' ').' км / '.$models[$k]['points'][$p]['age'].' '.self::formatYears($models[$k]['points'][$p]['age']);
		// 			}
		// 			$models[$k]['workvalues'] = $workvalues;
		// 		}
				
		// 		$script = file_get_contents(__DIR__.'/js/Calc.js');
				
		// 		$script = str_replace( '%%JSON.MODELS%%', json_encode($models), $script );
		// 		$script = str_replace( '%%USER.ID%%', $user->public_key, $script );
		// 		$script = str_replace( '%%SITE.YANDEXID%%', $site['yandex_id'], $script );
				
		// 		return $script.PHP_EOL; 
		// 	}
		// }
		
		// public function getCSS( $user, $URL ) {
			
		// 	$host = parse_url( $URL )['host'];
		// 	$site = $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE url = ?s', $host);
		// 	$sets = $this->getColorSettingsByHost( $host );
			
		// 	if ( $site ) {
				
		// 		$css = file_get_contents(__DIR__.'/css/Calc.css');
		// 		$css = str_replace( '%%CALC.BLACK%%', (($sets['black'])?$sets['black']:'#2f3538'), $css );
		// 		$css = str_replace( '%%CALC.GRAY%%', (($sets['gray'])?$sets['gray']:'#d3d3d3'), $css );
		// 		$css = str_replace( '%%CALC.COLOR%%', (($sets['color'])?$sets['color']:'#007fff'), $css );
				
		// 		return $css.PHP_EOL;
		// 	}
			
			
		// }
		
		// public function getSVG() {
			
		// 	return file_get_contents(__DIR__.'/svg/Calc.php');
		// }
		
	}