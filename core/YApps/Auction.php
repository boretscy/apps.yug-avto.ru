<?php
	
	class Auction extends App {
		
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
		
		public function sendNotify( $subj = '', $msg = '', $emails = [] ) {

			$this->Mailer->CharSet = 'utf-8';
			$this->Mailer->setFrom('notify@auction.yug-avto.ru', 'Аукцион Эксперт Юг-Авто');
			$this->Mailer->ClearAddresses();

			foreach ( array_unique($emails) as $email )$this->Mailer->addAddress($email, '');
			$this->Mailer->Subject = $subj;

			$this->Mailer->msgHTML($msg);
			return $this->Mailer->Send();
		}
		
		
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Traders ////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getTrader( $id ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_auction_traders WHERE id = ?i', (int)$id);
			$res['profile'] = $this->getProfileByTrader( $res['id'] );
			$res['categories'] = $this->getCategoriesIDsByProfile( $res['profile']['id'] );
			
			return $res;
		}
		
		public function getTraderByKey( $q ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_auction_traders WHERE ssid = ?s', (string)$q);
			if ( $res ) $res['profile'] = $this->getProfileByTrader( $res['id'] );
			
			return $res;
		}
		
		public function getTraderByPhone( $phone ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_auction_traders WHERE phone = ?s', (string)Helper::formatPhoneIn($phone));
			if ( $res ) $res['profile'] = $this->getProfileByTrader( $res['id'] );
			
			return $res;
		}
		
		public function getTraders() {
			
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_auction_traders');
			foreach ( $res as $k => $r ) if ( $p = $this->getProfileByTrader($r['id']) ) $res[$k]['profile'] = $p; 
			
			return $res;
		}
		
		public function getTradersByIds( $ids ) {
			
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_auction_traders WHERE id IN (?a)', $ids);
			foreach ( $res as $k => $r ) if ( $p = $this->getProfileByTrader($r['id']) ) $res[$k]['profile'] = $p; 
			
			return $res;
		}
		
		public function setTrader( $POST, $FILES ) {

			$arIns['name'] = $POST['name'];
			if ( $POST['phone'] ) $arIns['phone'] = Helper::formatPhoneIn($POST['phone']);
			$arIns['email'] = $POST['email'];
            $arIns['active'] = ( $POST['active'] == 'on' ) ? 1 : 0;

            $ssid = ($POST['trader_id']) ? $this->MySQL->getOne('SELECT ssid FROM yapps_app_auction_traders WHERE id = ?i', $POST['trader_id']) : md5( $this->Conf->secret.$arIns['phone'].date('Y-m-d, H:i:s') );

            if ( $POST['changepasswd'] == 'on' ) password_hash($this->Conf->secret.$this->Conf->DefaultPass, PASSWORD_DEFAULT);

            if ( $POST['new_passwd'] ) {
                
                if ( !password_verify($this->Conf->secret.$POST['passwd'], $trader['passwd']) ) {
					
                    return Helper::getRes(13);

                } else {

                    if ( !Helper::checkNewPass($POST['new_passwd'], $POST['confim_passwd']) ) {
					
                        return Helper::getRes(23);
                        
                    } else {
                        
                        $arIns['passwd'] = password_hash($this->Conf->secret.$POST['passwd'], PASSWORD_DEFAULT);
                        $arIns['ssid'] = md5( $this->Conf->secret.$arIns['phone'].date('Y-m-d, H:i:s') );
                        $arIns['active'] = 1;
                        $arIns['verify'] = random_int(10000, 99999);
                        $arIns['verify_time'] = time()+15*60;
                        $this->MySQL->query('INSERT INTO yapps_app_auction_traders SET ?u', $arIns);
                        $this->MySQL->query('INSERT INTO yapps_app_auction_traders_profiles SET ?u', ['trader_id'=>$this->MySQL->insertId()]);
                        
                        if ( !file_exists($_SERVER['DOCUMENT_ROOT'].$this->Conf->FileDir.'/Traders/'.$arIns['ssid']) ) mkdir( $_SERVER['DOCUMENT_ROOT'].$this->Conf->FileDir.'/Traders/'.$arIns['ssid'] );
                        
                        $res = Helper::getRes(0);
                        $res->ssid = $arIns['ssid'];
                    }
                }
            }
            
            if ( $FILES ) {
				
				foreach( ['passport01_image'=>$FILES['passport01_image'], 'passport02_image'=>$FILES['passport02_image']] as $n => $f ) {
					
					if ( $f['error'] == 0 ) {
						
						$arF_N = explode('.', $f['name']);
						$file_name = md5($f['name'].$POST['name'].$POST['phone'].$this->Conf->secret.time()).'.'.$arF_N[count($arF_N)-1];
						
						$arIns[$n] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$this->Conf->FileDir.'/Traders/'.$ssid.'/'.$file_name;
                        move_uploaded_file( $f['tmp_name'], __DIR__.'/../..'.$this->Conf->FileDir.'/Traders/'.$ssid.'/'.$file_name );
                        
                        if ( $POST['trader_id'] ) {
                            
                            $ar = explode('/', $this->MySQL->getOne('SELECT ?n FROM yapps_app_auction_traders WHERE id = ?i', $n, $POST['trader_id']));
                            unlink(__DIR__.'/../..'.$this->Conf->FileDir.'/Traders/'.$ssid.'/'.$ar[count($ar)-1]);
                        }
					}
				}
			}
            
            if ( $POST['trader_id'] ) {
                
                $this->MySQL->query('UPDATE yapps_app_auction_traders SET ?u WHERE id = ?i', $arIns, (int)$POST['trader_id']);
            
            } else {

                $arIns['ssid'] = $ssid;
                $this->MySQL->query('INSERT INTO yapps_app_auction_traders SET ?u', $arIns);
                $POST['trader_id'] = $this->MySQL->insertId();
            }

			$this->setProfile( $POST, $FILES );
			
			return Helper::getRes(0);
		}
		
		public function activateTrader( $id, $active = true ) {
			
			return $this->MySQL->query('UPDATE yapps_app_auction_traders SET ?u WHERE id = ?i', ['active'=>(int)$active]);
		}
		
		public function delTrader( $id ) {
            
            $ssid = $this->MySQL->getOne('SELECT ssid FROM yapps_app_auction_traders WHERE id = ?i', $id);
            
			$this->MySQL->query('DELETE FROM yapps_app_auction_traders WHERE id = ?i', $id);
			foreach( $this->MySQL->getRow('SELECT org_ogrn_image, org_inn_image FROM yapps_app_auction_traders_profiles WHERE trader_id = ?i', (int)$id) as $image ) {
				$ar = explode('/', $image);
				unlink(__DIR__.'/../..'.$this->Conf->FileDir.'/Traders/'.$ssid.'/'.$ar[count($ar)-1]);
            }
            $this->MySQL->query('DELETE FROM yapps_app_auction_traders_profiles WHERE trader_id = ?i', $id);
			
			return Helper::getRes(0);
		}
		
		
		public function setProfile( $POST, $FILES ) {
			
			$arIns = $POST;
			unset( $arIns['form'], $arIns['trader_id'], $arIns['name'], $arIns['phone'], $arIns['email'], $arIns['active'], $arIns['accepted'], $arIns['org_phone'], $arIns[''], $arIns['contact_phone'], $arIns['categories_id'], $arIns['categories'], $arIns['category'] );
			
			$arIns['active'] = ( $POST['accepted'] == 'on' ) ? 1 : 0;
			$arIns['org_phone'] = Helper::formatPhoneIn($POST['org_phone']);
            $arIns['contact_phone'] = Helper::formatPhoneIn($POST['contact_phone']);
            
            $ssid = $this->MySQL->getOne('SELECT ssid FROM yapps_app_auction_traders WHERE id = ?i', $POST['trader_id']);
			
			if ( $FILES ) {
				
				foreach( ['org_inn_image'=>$FILES['org_inn_image'], 'org_ogrn_image'=>$FILES['org_ogrn_image']] as $n => $f ) {
					
					if ( $f['error'] == 0 ) {
						
						$arF_N = explode('.', $f['name']);
						$file_name = md5($f['name'].$POST['name'].$POST['phone'].$this->Conf->secret.time()).'.'.$arF_N[count($arF_N)-1];
						
						$arIns[$n] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$this->Conf->FileDir.'/Traders/'.$ssid.'/'.$file_name;
                        move_uploaded_file( $f['tmp_name'], __DIR__.'/../..'.$this->Conf->FileDir.'/Traders/'.$ssid.'/'.$file_name );
                        
                        if ( $image = $this->MySQL->getOne('SELECT ?n FROM yapps_app_auction_traders_profiles WHERE id = ?i', $n, $POST['trader_id']) ) {
                            
                            $ar = explode('/', $image);
                            unlink(__DIR__.'/../..'.$this->Conf->FileDir.'/Traders/'.$ssid.'/'.$ar[count($ar)-1]);
                        }
					}
				}
			}
			
			$this->MySQL->query('UPDATE yapps_app_auction_traders_profiles SET ?u WHERE trader_id = ?i', $arIns, (int)$POST['trader_id']);
			$this->MySQL->query('UPDATE yapps_app_auction_traders SET ?u WHERE id = ?i', ['profile_flag'=>1], (int)$POST['trader_id']);
			
			$id = $this->MySQL->getOne('SELECT id FROM yapps_app_auction_traders_profiles WHERE trader_id = ?i', (int)$POST['trader_id']);
			$this->MySQL->query('DELETE FROM yapps_app_auction_traders_profiles_categories WHERE profile_id = ?i', $id);
			foreach ( $POST['categories_id'] as $c_id ) {
				
				$this->MySQL->query('INSERT INTO yapps_app_auction_traders_profiles_categories SET ?u', [
					'profile_id' => $id,
					'category_id' => (int)$c_id
				]);
			}
			
			return Helper::getRes(0);
		}
		
		public function getProfile( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_auction_traders_profiles WHERE id = ?i', (int)$id);
		}
		
		public function getProfileByTrader( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_auction_traders_profiles WHERE trader_id = ?i', (int)$id);
		}
		
		public function getCountWins( $id ) {
			
			return $this->MySQL->getOne('SELECT COUNT(id) FROM yapps_app_auction_wins WHERE trader_id = ?i AND place = ?i', $id, 1);
		}
		
		public function getCountTraderItems( $id ) {
			
			return count($this->MySQL->getCol('SELECT DISTINCT item_id FROM yapps_app_auction_costs WHERE trader_id = ?i', $id));
		}
		
		public function getCountTraderCosts( $id ) {
			
			return $this->MySQL->getOne('SELECT COUNT(id) FROM yapps_app_auction_costs WHERE trader_id = ?i', $id);
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Admins /////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function setAdmins( $POST ) {
			
			$this->MySQL->query('TRUNCATE yapps_app_auction_admins');
			foreach ( $POST['admins'] as $a ) $this->MySQL->query('INSERT INTO yapps_app_auction_admins SET ?u', ['user_id'=>(int)$a]);
			
			return Helper::getRes(0);
		}
		
		public function getAdmins() {
			
			return $this->MySQL->getCol('SELECT user_id FROM yapps_app_auction_admins');
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Templates //////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getTemplates() {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_auction_settings WHERE id = ?i', 1);	
		}
		
		public function setTemplates( $POST ) {
			
			unset ( $POST['form'], $POST['id'], $POST['_wysihtml5_mode'] );
			
			$this->MySQL->query('UPDATE yapps_app_auction_settings SET ?u WHERE id = ?i', $POST, 1);
			return Helper::getRes(0);
		}
		
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Categories /////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function setCategory( $POST ) {
            
            $arIns = $POST;
            unset ( $arIns['form'] );
            $arIns['default_costs'] = json_encode( $POST['default_costs'] );
			
			$this->MySQL->query('REPLACE INTO yapps_app_auction_categories SET ?u', $arIns);
			return Helper::getRes(0);
		}
		
		public function getCategory( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_auction_categories WHERE id = ?i', (int)$id);
		}
		
		public function getCategories() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_auction_categories');
		}
		
		public function getCategoriesforAPI() {
			
			return $this->MySQL->getInd('id', 'SELECT * FROM yapps_app_auction_categories');
		}
		
		public function getCategoriesIDsByProfile( $id ) {
			
			return $this->MySQL->getCol('SELECT category_id FROM yapps_app_auction_traders_profiles_categories WHERE profile_id = ?i', (int)$id);
        }
        
        public function getCategoriesIDs() {
			
			return $this->MySQL->getCol('SELECT id FROM yapps_app_auction_categories');
		}
		
		public function getCategoriesByProfile( $id ) {
			
			$c = $this->MySQL->getCol('SELECT category_id FROM yapps_app_auction_traders_profiles_categories WHERE profile_id = ?i', (int)$id);
			return $this->MySQL->getAll('SELECT * FROM yapps_app_auction_categories WHERE id IN (?a)', $c);
		}
		
		
		public function getCategoriesNamesByProfile( $id ) {
			
			$c = $this->MySQL->getCol('SELECT category_id FROM yapps_app_auction_traders_profiles_categories WHERE profile_id = ?i', (int)$id);
			return $this->MySQL->getCol('SELECT ru_name FROM yapps_app_auction_categories WHERE id IN (?a)', $c);
		}
		
		public function getPriorityCategoryIDByProfile( $id ) {
			
			return $this->MySQL->getOne('SELECT category_id FROM yapps_app_auction_traders_profiles_categories WHERE profile_id = ?i AND priority = ?i', (int)$id, 1);
		}
		
		public function getCategoryIdByItemPrice( $price ) {
			
			return $this->MySQL->getOne('SELECT id FROM yapps_app_auction_categories WHERE min <= ?i AND max >= ?i', $price, $price);
		}
		
		public function getCategoryByItemPrice( $price ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_auction_categories WHERE min <= ?i AND max >= ?i', $price, $price);
		}
		
		public function delCategory( $id ) {
			
			$this->MySQL->query('DELETE FROM yapps_app_auction_categories WHERE id = ?i', (int)$id);
			return Helper::getRes(0);
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Engine /////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getEngineTypes() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_auction_engine');	
		}
		
		public function getEngineType( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_auction_engine WHERE id = ?i', $id);	
		}
		
		public function getTransmissions() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_auction_transmission');	
		}
		
		public function getTransmission( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_auction_transmission WHERE id = ?i', $id);	
		}
		
		public function getDrives() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_auction_drive');	
		}
		
		public function getDrive( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_auction_drive WHERE id = ?i', $id);	
		}
		
		public function getItemsTypes() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_auction_types');	
        }
        
        public function getItemType( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_auction_types WHERE id = ?i', $id);	
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Types //////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function setType( $POST ) {
			
			unset ( $POST['form'] );
			
			$this->MySQL->query('REPLACE INTO yapps_app_auction_types SET ?u', $POST);
			return Helper::getRes(0);
		}
		
		public function getType( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_auction_types WHERE id = ?i', (int)$id);
		}
		
		public function getTypes() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_auction_types');
		}
		
		public function delType( $id ) {
			
			$this->MySQL->query('DELETE FROM yapps_app_auction_types WHERE id = ?i', (int)$id);
			return Helper::getRes(0);
		}
		
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Items //////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getStatuses() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_auction_statuses');	
		}
		
		public function getStatus( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_auction_statuses WHERE id = ?i', (int)$id);	
		}
		
		public function geItemsToStart() {
			
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_auction WHERE auto_start = ?i AND status_id = ?i AND (UNIX_TIMESTAMP(datetime_start) BETWEEN ?i AND ?i)', 1, 1, 30, time()+10);
			return $res;
		}
		public function getItemsToEnd() {
			
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_auction WHERE status_id = ?i AND (UNIX_TIMESTAMP(datetime_end) BETWEEN ?i AND ?i)', 2, time()-36000, time()+30);
			return $res;
		}
		
		public function getItemsByFilter( $arF ) {
			
			$query = 'SELECT * FROM yapps_app_auction';
			
			foreach ( $arF['params'] as $k => $v ) {
				
				if ( is_array($v) ) $w[] = $this->MySQL->parse('?n IN (?a)', $k, $v);
				if ( is_int($v) ) $w[] = $this->MySQL->parse('?n = ?i', $k, $v);
				if ( is_string($v) ) $w[] = $this->MySQL->parse('?n = ?s', $k, $v);
			}
			$w[] = $this->MySQL->parse('(DATE(datetime_start) BETWEEN ?s AND ?s)', $arF['date1'], $arF['date2'].' 23:59:59');
			$where = 'WHERE '.implode(' AND ', $w);
			
			return $this->MySQL->getAll($query.' '.$where);
		}
		
		public function getPublicItemsByCategory( $id, $limit = false ) {
			
			$cat = $this->getCategory( $id );
			$query = 'SELECT * FROM yapps_app_auction WHERE status_id = 2 AND current_price BETWEEN ?i AND ?i ORDER BY id DESC';
			if ( $limit ) $query .= ' LIMIT '.$limit;
			$res = $this->MySQL->getAll($query, $cat['min'], $cat['max']);
			foreach ( $res as $k => $r ) {
				
				$res[$k]['image'] = $this->getItemPhotos($r['id'])[0];
				$res[$k]['engine_type'] = $this->getEngineType($r['engine_type_id'])['ru_name'];
				$res[$k]['transmission'] = $this->getTransmission($r['transmission_id'])['ru_name'];
				$res[$k]['drive'] = $this->getDrive($r['drive_id'])['ru_name'];
				$res[$k]['datetime_end'] = date('d.m.Y в H:i', strtotime($res[$k]['datetime_end']));
                $res[$k]['costs_count'] = $this->getItemCosts($r['id']);
                $res[$k]['type'] = $this->getItemType($r['id']);
				
				unset($res[$k]['engine_type_id']);
				unset($res[$k]['drive_id']);
				unset($res[$k]['transmission_id']);
				unset($res[$k]['owners']);
				unset($res[$k]['description']);
				unset($res[$k]['datetime_start']);
				unset($res[$k]['start_price']);
				unset($res[$k]['joined_traders']);
				unset($res[$k]['joined_categories']);
				unset($res[$k]['timestamp']);
				unset($res[$k]['vin']);
			}
			
			return $res;
		}
		
		public function getActiveItems() {
			
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_auction WHERE DATE(datetime_end) < NOW() AND (status_id = ?i OR status_id = ?i)', 1, 2);
			return $res;
		}
		
		public function getItemPhotos( $id ) {
			
			return $this->MySQL->getCol('SELECT url FROM yapps_app_auction_images WHERE item_id = ?i AND type = ?s', (int)$id, 'photo');
        }
        
        public function getItemDamages( $id ) {
			
			return $this->MySQL->getCol('SELECT url FROM yapps_app_auction_images WHERE item_id = ?i AND type = ?s', (int)$id, 'damage');
		}
		
		public function getItemCards( $id ) {
			
			return $this->MySQL->getCol('SELECT url FROM yapps_app_auction_images WHERE item_id = ?i AND type = ?s', (int)$id, 'card');
        }
        
        public function getItemVideo( $id ) {
			
			return $this->MySQL->getOne('SELECT url FROM yapps_app_auction_images WHERE item_id = ?i AND type = ?s', (int)$id, 'video');
		}
		
		public function getItem( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_auction WHERE id = ?i', (int)$id);
		}
		
		public function getItemByKey( $key ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_auction WHERE url_key = ?s', $key);
		}
		
		public function getCurrentPrice( $id ) {
			
			return $this->MySQL->getOne('SELECT current_price FROM yapps_app_auction WHERE id = ?i', (int)$id);
		}
		
		public function setItem( $POST, $FILES = false ) {
			
			$arIns = $POST;
			unset( $arIns['form'], $arIns['id'], $arIns['trader_ids'], $arIns['category_ids'] );
			$arIns['url_key'] = md5( $this->Conf->secret.$POST['vin'].time() );
			$arIns['short_url'] = file_get_contents('https://clck.ru/--?url=https://auction.yug-avto.ru/items/'.$arIns['url_key']);
			$arIns['status_id'] = ( $POST['id'] && $this->MySQL->getOne('SELECT status_id FROM yapps_app_auction WHERE id = ?i', $POST['id'])!=1 ) ? 5 : 1;
			$arIns['current_price'] = $arIns['start_price'];
			if ( !$POST['id'] ) $arIns['timestamp'] = time();
			$arIns['datetime_start'] = date('Y-m-d H:i:s', strtotime($POST['datetime_start']));
			$arIns['datetime_end'] = date('Y-m-d H:i:s', strtotime($POST['datetime_end']));
			$arIns['auto_start'] = ( $POST['auto_start'] == 'on' ) ? 1 : 0;
			
			$arIns['joined_traders'] = json_encode($POST['trader_ids']);
			$arIns['joined_categories'] = json_encode($POST['category_ids']);
            

			( $POST['id'] ) ? $this->MySQL->query('UPDATE yapps_app_auction SET ?u WHERE id = ?i', $arIns, (int)$POST['id']) : $this->MySQL->query('INSERT INTO yapps_app_auction SET ?u', $arIns);
			$id = ( (int)$POST['id'] ) ?: $this->MySQL->insertId();
			
			if ( $FILES ) {
				
				$item_dir = $_SERVER['DOCUMENT_ROOT'].$this->Conf->FileDir.'/Items/'.$arIns['url_key'];
				if ( !file_exists($item_dir) ) mkdir($item_dir);
				
				foreach ( $FILES as $type => $file ) {
					
					$file_dir = $item_dir.'/'.$type;
					if ( !empty($file['name'][0]) && file_exists($file_dir) ) {
						
						Helper::clearDir($file_dir);
						$this->MySQL->query('DELETE FROM yapps_app_auction_images WHERE item_id = ?i AND type = ?s', $id, $type);
					}
					if ( !file_exists($file_dir) ) mkdir($file_dir);
					$site_dir = $this->Conf->FileDir.'/Items/'.$arIns['url_key'].'/'.$type;
					
					foreach ( $file['name'] as $k_name => $file_name ) {
						
						if ( $file['error'][$k_name] == 0 && !empty($file['name'][$k_name]) ) {
							
							$arN = explode('.', $file_name);
							$ext = $arN[count($arN)-1];
							
							move_uploaded_file( $file['tmp_name'][$k_name], $file_dir.'/'.$arIns['url_key'].'_'.$k_name.'.'.$ext );
							
							$this->MySQL->query('INSERT INTO yapps_app_auction_images SET ?u', [
								'item_id' => $id,
								'url' => $site_dir.'/'.$arIns['url_key'].'_'.$k_name.'.'.$ext,
								'type' => $type
							]);
						}
					}
				}
			}
			
			$res = Helper::getRes(0);
			$res->id = $id;
			
			return $res;
		}
		
		public function delItem( $id ) {
			
			return $this->MySQL->query('UPDATE yapps_app_auction SET ?u WHERE id = ?i', ['status_id'=>4], $id);
		}
		
		public function publicItem( $id ) {
			
			return $this->MySQL->query('UPDATE yapps_app_auction SET ?u WHERE id = ?i', ['status_id'=>2], $id);
		}
		
		public function closeItem( $id ) {
			
			return $this->MySQL->query('UPDATE yapps_app_auction SET ?u WHERE id = ?i', ['status_id'=>3], $id);
		}
		
		public function getItemWinnerId( $id ) {
			
			return ( $this->MySQL->getOne('SELECT trader_id FROM yapps_app_auction_wins WHERE item_id = ?i AND place = ?i', $id, 1) ) ?: false;
		}
		
		public function getItemWinnersId( $id ) {
			
			return $this->MySQL->getCol('SELECT DISTINCT trader_id FROM yapps_app_auction_costs WHERE item_id = ?i ORDER BY value DESC LIMIT 3', $id);
        }

        public function getItemsByIds( $ids ) {

            return $this->MySQL->getAll('SELECT * FROM yapps_app_auction WHERE id IN (?a)', $ids);
        }
        
        public function getItemsByWinner( $id ) {
            
            // $ids = $this->MySQL->getAll('SELECT item_id');
        }

        public function getItemsByTrader( $id ) {

            $ids = $this->MySQL->getCol('SELECT DISTINCT item_id FROM yapps_app_auction_costs WHERE trader_id = ?i', $id);
            foreach ( $res = $this->getItemsByIds($ids) as $k => $item ) {
                
                $res[$k]['place'] = array_search($id, $this->getItemWinnersId($item['id']));
                $res[$k]['cost'] = $this->getItemSelfBet($item['id'], $id);
            }
            return $res;
        }

        public function extendItem( $id ) {


        }
		
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Costs //////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		
		public function getItemCosts( $id ) {
			
			$res = $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_app_auction_costs WHERE item_id = ?i', $id);
			return $res;
		}

		public function getBetsByItem( $id ) {

			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_auction_costs WHERE item_id = ?i ORDER BY id DESC', $id);
			return $res;
		}
		
		public function getLastCost( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_auction_costs WHERE item_id = ?i ORDER BY id DESC LIMIT 1', $id);
		}
		
		public function getItemSelfBet( $id, $trader ) {
			
			return $this->MySQL->getOne('SELECT value FROM yapps_app_auction_costs WHERE item_id = ?i AND trader_id = ?i ORDER BY id DESC LIMIT 1', $id, $trader);
		}
		
		public function isWinnerItem( $id, $trader ) {
			
			$res = $this->getLastCost( $id );
			return ( $res['trader_id'] == $trader ) ? true : false;
		}
		
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Stat ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function pushWiners( $id, $winners ) {
			
			foreach ( $winners as $p => $winner ) $this->MySQL->query('INSERT INTO yapps_app_auction_wins SET ?u', ['item_id'=>$id, 'trader_id'=>$winner['id'], 'place'=>$p+1]);
		}
		
		public function pushStat( $q ) {
			
		}
		
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // API ////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function API_getTrader( $ssid ) {
			
            $res = $this->getTraderByKey( $ssid );
            if ( !$res) return false;
			$res['categories'] = $this->getCategoriesforAPI();
			if ( $res['profile'] ) $res['profile']['categories'] = $this->getCategoriesIDsByProfile( $res['profile']['id'] );
			
			unset( $res['passwd'], $res['recovery'], $res['recovery_time'] );
			unset( $res['profile']['id'] );
			return $res;
		}
		
		public function API_sendVCode( $ssid ) {
			
			$trader = $this->getTraderByKey( $ssid );
			
			$arIns['verify'] = random_int(10000, 99999);
			$arIns['verify_time'] = time()+15*60;
			
			$this->MySQL->query('UPDATE yapps_app_auction_traders SET ?u WHERE id = ?i', $arIns, $trader['id']);
			
			Helper::sendBeelineSMS($trader['phone'], 'Проверочный код: '.$arIns['verify']);
			$res = Helper::getRes(0);
			
			return $res;
		}
		
		public function API_verifyVCode( $POST ) {
			
			$trader = $this->getTraderByKey( $POST['ssid'] );
			
			if ( $trader['verified'] ) return Helper::getRes(26);
			if ( time() > $trader['verify_time'] ) {
				
				$this->API_sendVCode( $POST['ssid'] );
				return Helper::getRes(25);
			}
			if ( $trader['verify'] != (int)$POST['code'] ) return Helper::getRes(27);
			
			$this->MySQL->query('UPDATE yapps_app_auction_traders SET ?u WHERE id = ?i', ['verified'=>1], $trader['id']);
			
			return Helper::getRes(0);$res;
		}
		
		public function API_authTrader( $POST ) {
            
            /*
			if ( Helper::isFakePhone($POST['phone']) ) {
				
				return Helper::getRes(24);
			
			} else {
				
				$trader = $this->getTraderByPhone($POST['phone']);
				
				if ( !$trader || !$trader['active'] ) {
					
					return Helper::getRes(12);
					
				} else {
					
					if ( !password_verify($this->Conf->secret.$POST['passwd'], $trader['passwd']) ) {
					
						return Helper::getRes(13);
						
					} else {
						
						unset( $trader['passwd'], $trader['recovery'], $trader['recovery_time'], $trader['verify'], $trader['verify_time'] );
						$trader['status'] = 'success';
						return $trader;
					}
				}
            }
            */
            
            if ( Helper::isFakePhone($POST['phone']) ) return Helper::getRes(24);
            
            $trader = $this->getTraderByPhone($POST['phone']);

            if ( !$trader || !$trader['active'] ) return Helper::getRes(12);
            if ( !password_verify($this->Conf->secret.$POST['passwd'], $trader['passwd']) ) return Helper::getRes(13);

            unset( $trader['passwd'], $trader['recovery'], $trader['recovery_time'], $trader['verify'], $trader['verify_time'] );
			$trader['status'] = 'success';
			return $trader;

		}
		
		public function API_registerTrader( $POST ) {
			
			$arIns['phone'] = Helper::formatPhoneIn($POST['phone']);
			
			if ( Helper::isFakePhone($arIns['phone']) ) {
				
				$res = Helper::getRes(24);
			
			} else {
				
				if ( $trader = $this->getTraderByPhone($arIns['phone']) ) {
					
					$res = Helper::getRes(21);
					$res->ssid = $trader['ssid'];
					
				} else {
					
					if ( !Helper::checkNewPass($POST['passwd'], $POST['confim_passwd']) ) {
					
						$res = Helper::getRes(23);
						
					} else {
						
						$arIns['passwd'] = password_hash($this->Conf->secret.$POST['passwd'], PASSWORD_DEFAULT);
						$arIns['ssid'] = md5( $this->Conf->secret.$arIns['phone'].date('Y-m-d, H:i:s') );
						$arIns['active'] = 1;
						$arIns['verify'] = random_int(10000, 99999);
						$arIns['verify_time'] = time()+15*60;
						$this->MySQL->query('INSERT INTO yapps_app_auction_traders SET ?u', $arIns);
                        $this->MySQL->query('INSERT INTO yapps_app_auction_traders_profiles SET ?u', ['trader_id'=>$this->MySQL->insertId()]);
                        
                        if ( !file_exists($_SERVER['DOCUMENT_ROOT'].$this->Conf->FileDir.'/Traders/'.$arIns['ssid']) ) mkdir( $_SERVER['DOCUMENT_ROOT'].$this->Conf->FileDir.'/Traders/'.$arIns['ssid'] );
						
						$res = Helper::getRes(0);
						$res->ssid = $arIns['ssid'];
					}
				}
			}
			
			return $res; 
		}
		
		public function API_setTrader( $POST, $FILES ) {
            
            $POST['active'] = 'on';
            $POST['categories_id'] = explode(',', $POST['categories_id']);
            
            // отправить уведомление админам
            $subj = 'Трейдер изменил свои данные';
            $msg = 'Трейдер изменил свои данные, требуется подтверждение и повторный допуск к торгам<br /><br />';
            $msg .= '<a href="https://apps.yug-avto.ru/auction/traders/view/'.$POST['trader_id'].'/" target="_blank">https://apps.yug-avto.ru/auction/traders/view/'.$POST['trader_id'].'/</a>';
            $emails = $this->MySQL->getCol('SELECT email FROM yapps_users WHERE id IN (?a)', $this->getAdmins());
            $this->sendNotify($subj, $msg, $emails);
            
            return $this->setTrader( $POST, $FILES );
		}
		
		public function API_getRecovery( $POST ) {
			
			if ( Helper::isFakePhone($POST['phone']) ) {
				
				return Helper::getRes(24);
			
			} else {
				
				$trader = $this->getTraderByPhone($POST['phone']);
			}
		}
		
		public function API_getAddItems( $cats, $exect_cat = false ) {
			
			foreach ( $cats as $i => $c ) {
				
				$cats[$i]['items'] = $this->getPublicItemsByCategory( $c['id'], 4 );
				$cats[$i]['count_items'] = $this->MySQL->getOne('SELECT COUNT(id) FROM yapps_app_auction WHERE status_id = 2 AND current_price BETWEEN ?i AND ?i', $c['min'], $c['max']);
				/*
                if ($exect_cat && $c['id'] == $exect_cat) unset( $cats[$i] );
                */
			}
			
			return $cats;
		}
		
		public function API_getItemsForTrader( $ssid ) {
			
			$trader = $this->getTraderByKey( $ssid );
			$profile = $this->getProfileByTrader( $trader['id'] );
            
            /*
			$res[] = $this->getCategory( $this->getPriorityCategoryIDByProfile( $profile['id'] ) );
			$res[0]['items'] = $this->getPublicItemsByCategory( $res[0]['id'], 4 );
            $res[0]['count_items'] = $this->MySQL->getOne('SELECT COUNT(id) FROM yapps_app_auction WHERE status_id = 2 AND current_price BETWEEN ?i AND ?i', $res[0]['min'], $res[0]['max']);
            */
			
			$add_cats = $this->getCategoriesByProfile($profile['id']);
			$res = $this->API_getAddItems( $add_cats );
			
			return $res;
		}
		
		public function API_getItems( $POST ) {
			
			$limit = ( !$POST['category'] ) ? 4 : false;
			
			$trader = $this->getTraderByKey( $POST['ssid'] );
			$profile = $this->getProfileByTrader( $trader['id'] );
			/*
			$cats = ( $POST['category'] ) ? [$POST['category']] : $this->getCategoriesIDs();
			/*
			$res[] = $this->getCategory( $cat );
			$res[0]['items'] = $this->getPublicItemsByCategory( $res[0]['id'], 4 );
			$res[0]['count_items'] = $this->MySQL->getOne('SELECT COUNT(id) FROM yapps_app_auction WHERE status_id = 2 AND start_price BETWEEN ?i AND ?i', $res[0]['min'], $res[0]['max']);
			
			if ( !$POST['category'] ) {
				
				$add_cats = $this->getCategoriesByProfile($profile['id']);
				$res = array_merge( $res, $this->API_getAddItems( $add_cats, $res[0]['id'] ) );
			}
            
            
            return $this->API_getAddItems( $cats );
            */
            
            if ( $POST['category'] ) {

                $res[0] = $this->getPublicItemsByCategory( $POST['category'] );
                $res[0]['count_items'] = $this->MySQL->getOne('SELECT COUNT(id) FROM yapps_app_auction WHERE status_id = 2 AND current_price BETWEEN ?i AND ?i', $c['min'], $c['max']);

            } else {

                $res = $this->API_getAddItems( $this->getCategoriesIDs() );
            }

            return $res;
		}
		
		public function API_getItem( $POST ) {
			
			$trader = $this->getTraderByKey( $POST['ssid'] );
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_auction WHERE url_key = ?s', $POST['car']);
			$res['images'] = $this->getItemPhotos($res['id']);
            $res['cards'] = $this->getItemCards($res['id']);
            $res['damages'] = $this->getItemDamages($res['id']);
            $res['video'] = $this->getItemVideo($res['id']);
			$res['engine_type'] = $this->getEngineType($res['engine_type_id'])['ru_name'];
			$res['transmission'] = $this->getTransmission($res['transmission_id'])['ru_name'];
			$res['drive'] = $this->getDrive($res['drive_id'])['ru_name'];
			$res['type'] = $this->getType($res['type_id']);
			$res['auction']['cost'] = $res['auction']['min_cost'] = $res['current_price'] + $this->MySQL->getOne('SELECT cost_step FROM yapps_app_auction_categories WHERE min <= ?i AND max >= ?i', $res['start_price'], $res['start_price']);
			$res['auction']['timer']['raw'] = strtotime($res['datetime_end']) - time();
			$res['auction']['timer']['d'] = intdiv($res['auction']['timer']['raw'], 24*60*60);
			$res['auction']['timer']['h'] = intdiv($res['auction']['timer']['raw'] - $res['auction']['timer']['d']*24*60*60, (60*60));
			$res['auction']['timer']['m'] = intdiv($res['auction']['timer']['raw'] - $res['auction']['timer']['d']*24*60*60 - $res['auction']['timer']['h']*60*60, 60);
			$res['auction']['timer']['s'] = $res['auction']['timer']['raw'] - $res['auction']['timer']['d']*24*60*60 - $res['auction']['timer']['h']*60*60 - $res['auction']['timer']['m']*60;
			$res['auction']['costs_count'] = $this->getItemCosts($res['id']);
			$res['auction']['current_winner'] = $this->getItemWinnerId($res['id']);
			$res['auction']['self_cost'] = $this->getItemSelfBet($res['id'], $trader['id']);
            $res['auction']['self_winner'] = $this->isWinnerItem($res['id'], $trader['id']);
            $res['category'] = $this->getCategoryByItemPrice( $res['start_price'] );
			
			return $res;
		}
		
		public function API_checkCurrentPrices( $POST ) {
			
			foreach ( $POST as $g => $group ) {
				
				foreach ( $group as $i => $item ) {
					
					$POST[$g][$i]['current_price'] = $this->getCurrentPrice( $POST[$g][$i]['id'] );
				}
			}
			
			return $POST;
		}
		
		public function API_checkItemCosts( $token ) {
			
			
        }
        
        public function API_getItemsByWTrader( $ssid ) {

            $trader = $this->getTraderByKey( $ssid );
            $profile = $this->getProfileByTrader( $trader['id'] );
            
            $items = $this->MySQL->getCol('SELECT DISTINCT item_id FROM yapps_app_auction_wins WHERE ');

            return $items;
        }
		
		public function API_makeBet( $POST ) {
            
            $datetime_end =  strtotime( $this->MySQL->getOne('SELECT datetime_end FROM yapps_app_auction WHERE url_key = ?s', $POST['car']) );
            
            if ( $datetime_end > time() ) {
                
                $arIns = [
                    
                    'item_id' => $this->getItemByKey( $POST['car'] )['id'],
                    'trader_id' => $this->getTraderByKey( $POST['ssid'] )['id'],
                    'value' => (int)$POST['value'],
                    'datetime' => date('Y-m-d H:i:s')
                ];
                
                $this->MySQL->query('INSERT INTO yapps_app_auction_costs SET ?u', $arIns);
                $this->MySQL->query('UPDATE yapps_app_auction SET ?u WHERE url_key = ?s', ['current_price'=>(int)$POST['value']], $POST['car']);

                if ( $datetime_end <= time()+$this->Conf->Item->ResidualTime ) $this->MySQL->query('UPDATE yapps_app_auction SET ?u WHERE url_key = ?s', ['datetime_end'=>date('Y-m-d H:i:s', $datetime_end+$this->Conf->Item->RenewalTime)], $POST['car']);
            }
			
			$res = $this->API_getItem( $POST );
			
			return $res;
		}
		
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // CRON ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function CRON_startItems() {
			
        }
        
        public function CRON_endItems() {
			
        }
        
        public function CRON_archItems() {

        }
	}
?>