<?php

	class App {
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Init ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function __construct( $arConf = array(), $request, $appMode = 'Web' ) {
			
			$this->MySQL		= new SafeMySQL( $arConf['Core']['Init']['SafeMySQL']['params'] );
//			$this->MSSQL		= new SafeMSSQL( $arConf['Core']['Init']['SafeMSSQL']['params'] );
//			$this->Funcs		= new Funcs( $this->MySQL/*, $this->MSSQL */);
			$this->Route 		= new Route( $arConf );
			$this->Mailer 		= new PHPMailer();
			
			foreach ( array_merge( $arConf['Core']['Global'], $arConf['Core'][$appMode] ) as $class ) {
				
				$name			= $class['name'];
				$arConf			= ($class['params']['conf']) ? $arConf : false;
				$mysql			= ($class['params']['mysql']) ? $this->MySQL : false;
//				$mssql			= ($class['params']['mssql']) ? $this->MSSQL : false;
				$mssql 			= false;
				$mailer			= ($class['params']['mailer']) ? $this->Mailer : false;
				
				$this->$name  	= new $name( $arConf, $mysql, $mssql, $mailer );
				
			} // foreach
			
            $this->Mode			= $appMode;
        }



		public function SendMail( $STR ) {


			parse_str($STR, $POST);

			$this->Mailer->CharSet = 'utf-8';
			$this->Mailer->setFrom($POST['from']);
			$this->Mailer->ClearAddresses();
            $this->Mailer->addAddress($POST['email'], '');
			$this->Mailer->Subject = $POST['subject'];
			$this->Mailer->msgHTML($POST['body']);
			return $this->Mailer->Send();
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Sites //////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getSites() {
			
			$query = 'SELECT * FROM yapps_sites ';
			if ( $GLOBALS['AUTH_USER']->role_id>1 ) $query .= ' WHERE hidden = 0';
			$query .= ' ORDER BY ru_name ASC';
			return $this->MySQL->getAll( $query );
		}
		
		public function getSite( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE id = ?i', (int)$id);
		}
		
		public function getSiteByHost( $host ) {
			
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_sites WHERE url = ?s', $host);
		}
		
		public function delSite( $id ) {
			
			return $this->MySQL->query('DELETE FROM yapps_sites WHERE id = ?i', (int)$id);
		}
		
		public function setSite( $POST ) {
			
			$arIns = $POST;
			unset($arIns['id'], $arIns['form']);
			
			if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_sites SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_sites SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
		}
		
		
		public function getUserSites( $user ) {
			
			$ss = $this->MySQL->getCol('SELECT site_id FROM yapps_users_sites WHERE user_id = ?i', (int)$user->id);
			
			if ( $ss ) {
				
				$res['sites'] = $this->MySQL->getInd('id', 'SELECT * FROM yapps_sites WHERE id IN (?a) ORDER BY ru_name ASC', $ss );
				foreach ( $res['sites'] as $s ) $res['sites_ids'][] = $s['id'];

				$res['sites_string'] = '';
				foreach ( $res['sites'] as $s ) $res['sites_string'] .= $s['ru_name'].' / ';
				$res['sites_string'] = mb_substr($res['sites_string'], 0, -3);
				
			} else {
				
				$res = false;
			}
			
			return $res;
		}
		
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // DCs ////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getDCs() {
			
			$query = 'SELECT * FROM yapps_dcs ';
			if ( $GLOBALS['AUTH_USER']->role_id>1 ) $query .= ' WHERE hidden = 0';
			$query .= ' ORDER BY ru_name ASC';
			return $this->MySQL->getAll( $query );
		}
		
		public function getDC( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_dcs WHERE id = ?i', (int)$id);
		}
		
		
		public function delDC( $id ) {
			
			return $this->MySQL->query('DELETE FROM yapps_dcs WHERE id = ?i', (int)$id);
		}
		
		public function setDC( $POST ) {
			
			$arIns = $POST;
            unset($arIns['id'], $arIns['form']);
            $arIns['phone'] = Helper::formatPhoneIn( $POST['phone'] );
			
			if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_dcs SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_dcs SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
		}
		
		public function getUserDCs( $user ) {
			
			$user_sites = $this->MySQL->getCol('SELECT site_id FROM yapps_users_sites WHERE user_id = ?i', (int)$user->id);
			$res = $this->MySQL->getAll('SELECT * FROM yapps_dcs WHERE site_id IN (?a)', $user_sites);
			
			return $res;
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Brands | Models ////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function setBrand( $POST ) {
			
			$arIns = $POST;
			unset( $arIns['id'], $arIns['form'], $arIns['site_ids'] );
			
			if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_brands SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
			
			} else {
			
				$this->MySQL->query('INSERT INTO yapps_brands SET ?u', $arIns);
			}
			
			if ( $POST['id'] && $POST['site_ids'] ) {
				
				$this->MySQL->query('DELETE FROM yapps_brands_sites WHERE brand_id = ?i', (int)$POST['id']);
				foreach ( $POST['site_ids'] as $id ) $this->MySQL->query('INSERT INTO yapps_brands_sites SET ?u', ['brand_id'=>(int)$POST['id'], 'site_id'=>(int)$id]);
			}
			
			return Helper::getRes(0);
		}
		
		public function setModel( $POST ) {
            
            Helper::sp($POST);

			$arIns = $POST;
			unset( $arIns['id'], $arIns['form'], $arIns['dc_id'] );
			
			if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_models SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
			
			} else {
			
				$this->MySQL->query('INSERT INTO yapps_models SET ?u', $arIns);
			}
			
			if ( $POST['dc_id'] ) {
				
				$id = ( $POST['id'] ) ?: $this->MySQL->insertId();
				$this->setModelDCs( $id, $POST['dc_id'] );
			}
			
			return Helper::getRes(0);
		}
		
		public function setModelDCs( $model_id, $dcs ) {
			
			$this->MySQL->query('DELETE FROM yapps_models_dcs WHERE model_id = ?i', (int)$model_id);
			foreach ( $dcs as $dc ) $this->MySQL->query('INSERT INTO yapps_models_dcs SET ?u', ['model_id'=>(int)$model_id, 'dc_id'=>(int)$dc]);
		}
		
		
		
		
		/////////////////////////////////////////////////////////////////////////
		// Global Func Brands Area///////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////
		
		public function YApps_GetBrands() {
			
			$res = $this->MySQL->getInd('id', 'SELECT * FROM yapps_brands');
			foreach ( $res as $k => $r ) $res[$k]['site_ids'] = $this->YApps_GetBrandSiteIDs($r['id']);
			
			return $res;
		}
		
		public function YApps_GetBrandsIDs() {
			
			return $this->MySQL->getCol('SELECT id FROM yapps_brands');
		}
		
		public function YApps_GetBrandSiteIDs( $id ) {
			
			return $this->MySQL->getCol('SELECT DISTINCT site_id FROM yapps_brands_sites WHERE brand_id = ?i', (int)$id);
		}
		
		public function YApps_GetBrand( $id ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_brands WHERE id = ?i', (int)$id);
			$res['site_ids'] = $this->YApps_GetBrandSiteIDs($res['id']);
			
			return $res;
		}
		
		public function YApps_GetBrandsByIds( $ids ) {
			
			return $this->MySQL->getInd('id', 'SELECT * FROM yapps_brands WHERE id IN (?a)', $ids);
		}
		
		public function YApps_GetBrandsIDsBySiteId( $id ) {
			
			return $this->MySQL->getCol('SELECT brand_id FROM yapps_brands_sites WHERE site_id = ?i', (int)$id);
		}
		
		public function YApps_GetBrandsIDsBySiteIDs( $ids ) {
			
			return $this->MySQL->getCol('SELECT DISTINCT brand_id FROM yapps_brands_sites WHERE site_id IN (?a)', $ids);
        }
        
        public function YApps_GetBrandsIDsByUser( $user ) {

            $sites_ids = $this->YApps_GetUserSiteIDs( $user );
            foreach ( $sites_ids as $id ) if ( $id != 28 ) $sites[] = $id;

            return $this->YApps_GetBrandsIDsBySiteIDs( $sites );
        }
		
		public function YApps_GetBrandsBySiteId( $ids ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_brands WHERE id IN (?a)', $ids);
		}
		
		public function YApps_GetIndBrandsBySiteId( $ids ) {
			
			return $this->MySQL->getInd('id', 'SELECT * FROM yapps_brands WHERE id IN (?a)', $ids);
		}
		
		public function YApps_GetBrandByEnName( $en_name ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_brands WHERE en_name = ?s', (string)$en_name);
		}
		
		public function searchBrandsIDsByURL( $url ) {
			
			$arUrl = parse_url( $url );
			$site = $this->getSiteByHost( $arUrl['host'] );
			
			if ( $res = $this->YApps_GetBrandsIDsBySiteId( $site->id ) ) {
				
				return $res;
				
			} else {
				
				$brands = $this->YApps_GetBrands();
				foreach ( $brands as $brand ) if ( $p = strripos($url, $brand['url_key']) ) $res[] = $brand['id'];
			}
			
			return $res;
		}
		
		
		/////////////////////////////////////////////////////////////////////////
		// Global Func Models Area///////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////
		
		public function YApps_GetModels() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_models');
		}
		
		public function YApps_GetModelsByBrand( $id ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_models WHERE brand_id = ?i', (int)$id);
		}
		
		public function YApps_GetModelsByDCs( $ids ) {
			
			return $this->MySQL->getCol('SELECT model_id FROM yapps_models_dcs WHERE dc_id IN (?a)', $ids);
		}
		
		public function YApps_GetModelsByBrands( $ids ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_models WHERE brand_id IN (?a)', $ids);
		}
		
		
		public function YApps_GetIndexedModelsByBrands( $ids ) {
			
			return $this->MySQL->getInd('id', 'SELECT * FROM yapps_models WHERE brand_id IN (?a)', $ids);
		}
		
		public function YApps_GetModelsByIds( $ids ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_models WHERE id IN (?a)', (array)$ids);
		}
		
		public function YApps_GetModelByEnName( $en_name ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_models WHERE en_name = ?s', (string)$en_name);
		}
		
		public function YApps_GetModelByKey( $key ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_models WHERE url_key = ?s', (string)$key);
		}
		
		public function YApps_GetModelByExtId( $key ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_models WHERE ext_id = ?s', (string)$key);
		}
		
		public function YApps_GetModel( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_models WHERE id = ?i', (int)$id);
		}
		
		public function searchModelsIDsByURL_( $url ) {
			
			$brands = $this->searchBrandsIDsByURL( $url );
			foreach ( $this->YApps_GetModelsByBrands($brands) as $model ) {
				
				$p = ( strripos($url, $model['site_url']) ) ?: ( strripos($url, $model['url_key']) ) ?: ( strripos($url, $model['en_name']) ) ?: strripos($url, $model['ru_name']);
				if ( $p ) $res[] = $model['id'];
			}
			
			return $res;
		}
		
		public function searchModelsIDsByURL( $url ) {
			
			$brands = $this->searchBrandsIDsByURL( $url );
			$models = $this->YApps_GetIndexedModelsByBrands($brands);
			foreach ( $models as $model ) {
				
				if ( strrpos($url, $model['site_url']) ) {
					
					$res[] = ['id'=>$model['id'], 'key'=>'site_url'];
				
				} elseif ( strrpos($url, $model['url_key']) ) {
					
					$res[] = ['id'=>$model['id'], 'key'=>'url_key'];
				
				} elseif ( strrpos($url, $model['en_name']) ) {
					
					$res[] = ['id'=>$model['id'], 'key'=>'en_name'];
				
				} elseif ( strrpos($url, $model['ru_name']) ) {
					
					$res[] = ['id'=>$model['id'], 'key'=>'ru_name'];
				}
			}
			
			$key = ['', false];			
			if ( count($res) > 1 ) {
				
				for ( $i=0; $i<count($res); $i++ ) {
					
					foreach ( $res as $k => $r ) {
						
						if ( mb_strlen($models[$r['id']][$r['key']]) > mb_strlen($key[0]) ) {
							
							$key = [$models[$r['id']][$r['key']], $k];
						}
					}
					
					if ( !strrpos($url, $key[0]) ) unset( $res[$key[1]] );
				}
			}
			
			foreach ($res as $r) $ret[] = $r['id'];
			
			return [$res[$key[1]]['id']];
		}
		
		
		public function YApps_GetUserModels( $sites_ids ) {
			
			$res = $this->YApps_GetBrandsByIds( $this->YApps_GetBrandsIDsBySiteIDs($sites_ids) );
			foreach ( $res as $k => $b ) $res[$k]['items'] = $this->YApps_GetModelsByBrand( $b['id'] );
			
			return $res;
		}
		
		
		
		
		/////////////////////////////////////////////////////////////////////////
		// Global Funcs DCs Area ////////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////
		
		public function YApps_GetDCsBySite( $id ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_dcs WHERE site_id = ?i ORDER BY ru_name ASC', (int)$id);
		}
		
		public function YApps_GetDCIDsBySite( $id ) {
			
			return $this->MySQL->getCol('SELECT id FROM yapps_dcs WHERE site_id = ?i ORDER BY ru_name ASC', (int)$id);
		}
		
		public function YApps_GetDC( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_dcs WHERE id = ?i', (int)$id);
		}
		
		public function YApps_GetDCByName( $q ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_dcs WHERE ru_name = ?s', (string)$q);
		}
		
		public function YApps_GetDCIDsByModel( $id ) {
			
			return $this->MySQL->getCol('SELECT dc_id FROM yapps_models_dcs WHERE model_id = ?i', (int)$id);
		}
		
		public function YApps_GetDCsByModel( $id ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_dcs WHERE id IN (?a)', $this->YApps_GetDCIDsByModel($id));
		}
		
		public function YApps_GetDCRuNamesByModel( $id ) {
			
			return $this->MySQL->getCol('SELECT ru_name FROM yapps_dcs WHERE id IN (?a)', $this->YApps_GetDCIDsByModel($id));
		}
		
		public function YApps_GetDCs() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_dcs');
        }
        
        public function YApps_GetDCsByIDs( $ids ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_dcs WHERE id IN (?a)', $ids);
		}

		public function YApps_GetSortDCsByIDs( $ids ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_dcs WHERE id IN (?a) ORDER BY sort ASC', $ids);
		}
		
		public function YApps_GetDcIDByUrl( $url ) {
			
			return $this->MySQL->getOne('SELECT id FROM yapps_dcs WHERE url_key = ?s', $url);
		}
		
		public function YApps_RefreshDCAV( $url ) {
			
			$om = json_decode(file_get_contents('https://yug-avto.ru/car-filter/new-cars?Company='.$url));
			$brand = $this->YApps_GetBrandByEnName( $om->filterResult->items[0]->Brand->Title );
			
			if ( $brand['id'] == 17 ) {
		
				foreach ( $om->filterResult->items as $i ) {
						
					$ids[] = $model_id = $this->YApps_GetModelByEnName( $i->Title )['id'];
					
					$suff_dc = ( in_array($model_id, [79,80,81,82,83,85,91]) ) ? '_nfz' : '_pkw';
					$dc_id = $this->YApps_GetDcIDByUrl($url.$suff_dc);
					
					$this->MySQL->query('UPDATE yapps_models_dcs SET ?u WHERE model_id = ?i AND dc_id = ?i', ['in_stock'=>(int)$i->FilteredCount], $model_id, $dc_id);
					$this->MySQL->query('UPDATE yapps_models_dcs SET ?u WHERE model_id NOT IN (?a) AND dc_id = ?i', ['in_stock'=>0], $ids, $dc_id);
				}
				
			} else {
				
				$dc_id = $this->YApps_GetDcIDByUrl($url);
				
				foreach ( $om->filterResult->items as $i ) {
						
					$ids[] = $model_id = $this->YApps_GetModelByEnName( $i->Title )['id'];
					$this->MySQL->query('UPDATE yapps_models_dcs SET ?u WHERE model_id = ?i AND dc_id = ?i', ['in_stock'=>(int)$i->FilteredCount], $model_id, $dc_id);
				}
				$this->MySQL->query('UPDATE yapps_models_dcs SET ?u WHERE model_id NOT IN (?a) AND dc_id = ?i', ['in_stock'=>0], $ids, $dc_id);
			}
			
			return Helper::getRes(0);
		}
		
		public function YApps_GetDCAV( $id ) {
			
			return $this->MySQL->getOne('SELECT SUM(in_stock) FROM yapps_models_dcs WHERE dc_id = ?i', (int)$id);
		}
		
		
		/////////////////////////////////////////////////////////////////////////
		// Global Funcs Showrooms Area //////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////
		
		public function YApps_GetShowrooms() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_showrooms');
		}
		
		public function YApps_GetShowroomBySite( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_showrooms WHERE site_id = ?i', (int)$id);
		}
		
		public function YApps_GetShowroomByUrl( $url ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_showrooms WHERE url = ?s', Helper::parseHostLink($url));
		}
		
		public function YApps_GetShowroom( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_showrooms WHERE id = ?i', (int)$id);
		}
		
		public function YApps_GetSiteIdByShowroom( $url ) {
			
			return $this->MySQL->getOne('SELECT site_id FROM yapps_showrooms WHERE url = ?s', Helper::parseHostLink($url));
		}
		
		public function YApps_UseSiteScripts( $id ) {
			
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_sites_scripts WHERE site_id = ?i', (int)$id);
		}
		
		public function setShowroom( $POST ) {
			
			
			$arIns = $POST;
			unset( $arIns['form'] );
			
			$this->MySQL->query('REPLACE INTO yapps_showrooms SET ?u', $arIns);
			return Helper::getRes(0);
		}
		
		
		/////////////////////////////////////////////////////////////////////////
		// Global Funcs Lands Area //////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////
		
		public function YApps_GetSiteIdByLand( $url ) {
			
			return $this->MySQL->getOne('SELECT site_id FROM yapps_app_lands WHERE url = ?s', Helper::parseHostPathLink($url));
		}
		
		public function YApps_GetLandIdByUrl( $url ) {
			
			return $this->MySQL->getOne('SELECT id FROM yapps_app_lands WHERE url = ?s', Helper::parseHostPathLink($url));
		}
		
		public function YApps_GetLandByUrl( $url ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_lands WHERE url = ?s', Helper::parseHostPathLink($url));
		}
		
		public function YApps_GetLandSettings( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_lands WHERE id = ?i', (int)$id);
		}
		
		
		
		
		/////////////////////////////////////////////////////////////////////////
		// Global Funcs Sites Area //////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////
		public function YApps_GetSites() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_sites ORDER BY ru_name ASC');
        }
        
        public function YApps_GetGlobalSites() {
			
			return $this->MySQL->getInd('id', 'SELECT * FROM yapps_sites');
		}
		
		public function YApps_GetSiteByID( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE id = ?i', (int)$id);
		}
		
		public function YApps_GetSiteByHost( $host ) {
			
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_sites WHERE url = ?s', $host);
		}
		
		public function YApps_DelSite( $id ) {
			
			return $this->MySQL->query('DELETE FROM yapps_sites WHERE id = ?i', (int)$id);
		}
		
		public function YApps_GetUserSiteIDs( $user ) {
			
			return $this->MySQL->getCol('SELECT site_id FROM yapps_users_sites WHERE user_id = ?i', $user->id);
		}
		
		public function YApps_GetUserSitesIds($user) {

			return $this->MySQL->getCol('SELECT site_id FROM yapps_users_sites WHERE user_id = ?i', (int)$user->id);
		}
		
		public function YApps_GetUserApps($user) {
			
			$AppsIDs = $this->MySQL->getCol('SELECT app_id FROM yapps_apps_users WHERE user_id = ?i', (int)$user->id);
			return $this->MySQL->getAll('SELECT * FROM yapps_apps WHERE id IN (?a)', $AppsIDs);
		}
		
		public function YApps_GetUserActivityApps($user) {
			
			$AppsIDs = $this->MySQL->getCol('SELECT app_id FROM yapps_apps_users WHERE user_id = ?i', (int)$user->id);
			return $this->MySQL->getAll('SELECT * FROM yapps_apps WHERE id IN (?a) AND activity = ?i', $AppsIDs, 1);
		}
		
		
		/////////////////////////////////////////////////////////////////////////
		// Global Func Search Area///////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////
		
		public function YApps_searchBrandsIDsByURL( $url ) {
			
			return $this->Search->searchBrandsIDsByURL( $url );
		}
		
		public function YApps_searchModelsIDsByURL( $url ) {
			
			return $this->Search->searchModelsIDsByURL( $url );
		}
		
		public function YApps_searchBrandIdByString( $string ) {
			
			$string = preg_replace("/[^(\w)|(\x7F-\xFF)|(\s)]/", '', $string);
			
			$brands = $this->MySQL->getAll('SELECT * FROM yapps_brands');
			$res = false;
			
			foreach ( $brands as $brand ) 
			
				if ( strripos($string, $brand['en_name']) !== false || 
					 strripos($string, $brand['ru_name']) !== false || 
					 strripos($string, $brand['url_key']) !== false 
					 ) $res = (int)$brand['id'];
			
			return $res;
		}
		
		public function YApps_searchModelIdByString( $string ) {
			
			// $string = preg_replace("/[^(\w)|(\x7F-\xFF)|(\s)]/", '', $string);
			
			$models = $this->YApps_GetModelsByBrands( [$this->YApps_searchBrandIdByString($string)] );
			$res = false;
			
			foreach ( $models as $model ) 
				
				if ( strripos($string, $model['en_name']) !== false ||
					 strripos($string, $model['ru_name']) !== false || 
					 strripos($string, $model['url_key']) !== false ||
					 strripos($string, $model['site_url']) !== false
					 )   $res = (int)$model['id'];
			
			return $res;
		}
		
        
        /////////////////////////////////////////////////////////////////////////
		// Global Func Goals Area ///////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////


		
		/////////////////////////////////////////////////////////////////////////
		// Global Func User Area ////////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////
		
		public function YApps_GetUserByToken( $str ) {
			
			$res = $this->MySQL->getRow( 'SELECT * FROM yapps_users WHERE public_key = ?s', (string)$str );
			$res['role'] = (object)$this-> MySQL->getRow('SELECT * FROM yapps_user_roles WHERE id = ?i', (int)$res['id']);
			
			return (object)$res;
		}
		
		public function YApps_GetUserById( $id ) {
			
			$res = $this->MySQL->getRow( 'SELECT * FROM yapps_users WHERE id = ?i', (int)$id );
			$res['role'] = (object)$this-> MySQL->getRow('SELECT * FROM yapps_user_roles WHERE id = ?i', (int)$res['id']);
			
			return (object)$res;
		}
		
		public function YApps_GetUsersByApp( $id ) {
			
			$users_ids = $this->MySQL->getCol('SELECT user_id FROM yapps_apps_users WHERE app_id = ?i', (int)$id);
			return $this->MySQL->getAll('SELECT * FROM yapps_users WHERE id IN (?a)', $users_ids);
		}
		
		public function YApps_GetUsersByRole( $id ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_users WHERE active = ?i AND role_id = ?i', 1, (int)$id);
		}
		
		public function YApps_GetUsersByIDs( $ids ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_users WHERE id IN (?a)', $ids);
		}
		
		
		/////////////////////////////////////////////////////////////////////////
		// Global Func Leads Area////////////////////////////////////////////////
		/////////////////////////////////////////////////////////////////////////
		
		public function YApps_PushClient( $data, $ids, $geo = false ) {
            
            if ( $data['name'] ) $arIns['name'] = ( mb_stripos($data['name'], 'осетител') ) ? explode(' ', $data['name'])[0].'. '.$this->MySQL->getOne('SELECT ru_name FROM yapps_sites WHERE id = ?i', (int)$data['site_id']) : $data['name'];
            if ( $data['name'] ) $arIns['name'] = $data['name'];
            if ( $data['email'] ) $arIns['email'] = $data['email'];
            if ( $data['phone'] ) $arIns['phone'] = $data['phone'];
            
            if ( $ids['piwik_visitorId'] ) $arIns['piwik_visitorId'] = $ids['piwik_visitorId'];
            if ( $ids['yandex_visitorId'] ) $arIns['yandex_visitorId'] = $ids['yandex_visitorId'];
            if ( $ids['google_visitorId'] ) $arIns['google_visitorId'] = $ids['google_visitorId'];

            if ( $data['url'] ) $arIns['last_url'] = $data['url'];
            if ( $data['referrer'] ) $arIns['last_referrer'] = $data['referrer'];
            if ( $data['event'] ) $arIns['last_event'] = $data['event'];
            if ( $data['stat_id'] ) $arIns['last_stat_id'] = (int)$data['stat_id'];
            if ( $data['site_id'] ) $arIns['last_site_id'] = (int)$data['site_id'];
            if ( $data['app_id'] ) $arIns['last_app_id'] = (int)$data['app_id'];
            if ( $data['utm_campaign'] ) $arIns['last_utm_campaign'] = $data['utm_campaign'];
            if ( $data['utm_source'] ) $arIns['last_utm_source'] = $data['utm_source'];
            if ( $data['utm_medium'] ) $arIns['last_utm_medium'] = $data['utm_medium'];
            if ( $data['utm_content'] ) $arIns['last_utm_content'] = $data['utm_content'];
            if ( $data['utm_term'] ) $arIns['last_utm_term'] = $data['utm_term'];
            
			if ( $geo ) {
				if ( $geo['country'] ) $arIns['last_country'] = $geo['country'];
				if ( $geo['region'] ) $arIns['last_region'] = $geo['region'];
				if ( $geo['city'] ) $arIns['last_city'] = $geo['city'];
			}


			if ( $client = $this->MySQL->getRow('SELECT * FROM yapps_clients WHERE google_visitorId = ?s OR name = ?s OR email = ?s OR phone = ?s', (string)$ids['google_visitorId'], $data['name'], $data['email'], $data['phone']) ) {

                $this->MySQL->query('UPDATE yapps_clients SET ?u WHERE id = ?i', $arIns, (int)$client['id'] );
                if ( $client['google_visitorId'] != $arIns['google_visitorId'] ) {
					
					$vIns = [
						'client_id'=>$client['id'],
						'piwik_visitorId'=>$arIns['piwik_visitorId'],
						'google_visitorId'=>$arIns['google_visitorId']
					];
					$vIns['user_agent'] = ( $data['user_agent'] ) ?: ''; 
					
					$this->MySQL->query('INSERT INTO yapps_clients_visitorIds SET ?u', $vIns);
				}

			} else {

                $arIns['global_id'] = Helper::newGlobalID( ['name'=>$arIns['name'], 'phone'=>$arIns['phone']] );

                $arIns['init_url'] = $arIns['last_url'];
                $arIns['init_referrer'] = $arIns['last_referrer'];
                $arIns['init_event'] = $arIns['last_event'];
                $arIns['init_stat_id'] = $arIns['last_stat_id'];
                $arIns['init_site_id'] = $arIns['last_site_id'];
                $arIns['init_app_id'] = $arIns['last_app_id'];
                $arIns['init_utm_campaign'] = $arIns['last_utm_campaign'];
                $arIns['init_utm_source'] = $arIns['last_utm_source'];
                $arIns['init_utm_medium'] = $arIns['last_utm_medium'];
                $arIns['init_utm_content'] = $arIns['last_utm_content'];
                $arIns['init_utm_term'] = $arIns['last_utm_term'];
                $arIns['init_country'] = $arIns['last_country'];
                $arIns['init_region'] = $arIns['last_region'];

				$arIns['timestamp'] = time();
                
                if ( $arIns['phone'] || $arIns['email'] || ($arIns['name'] && !mb_stripos($arIns['name'], 'осетител')) ) {
                    
                    $this->MySQL->query('INSERT INTO yapps_clients SET ?u', $arIns);
                    $client_id = $this->MySQL->insertId();
                    $vIns = [
						'client_id'=>$client['id'],
						'piwik_visitorId'=>$arIns['piwik_visitorId'],
						'google_visitorId'=>$arIns['google_visitorId']
					];
					$vIns['user_agent'] = ( $data['user_agent'] ) ?: ''; 
					
					if ( $client_id ) $this->MySQL->query('INSERT INTO yapps_clients_visitorIds SET ?u', $vIns);
        
                } // if isset data
        
            } // if else
        
        }  //function
		
		public function YApps_SendOrderCallTouch( $data ) {
			
			$ct_url = 'https://api-node'.$data['calltouch_node'].'.calltouch.ru/calls-service/RestAPI/requests/'.$data['calltouch_id'].'/register/';
			$ct_url .= '?subject='.urlencode( $data['subject'] );
			$ct_url .= '&sessionId='.$data['ct_sess'];
			if ( $data['name'] ) $ct_url .= '&fio='.urlencode( $data['name'] );
			if ( $data['email'] ) $ct_url .= '&email='.urlencode( $data['email'] );
			if ( $data['phone'] ) $ct_url .= '&phone='.urlencode( Helper::formatPhoneIn( $data['phone'] ) );

			file_get_contents( $ct_url );
		}
		
		public function YApps_PushIKK( $POST ) {
			
			
		}
	}

?>