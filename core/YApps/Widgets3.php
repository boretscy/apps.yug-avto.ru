<?php

	class Widgets3 extends App {
        

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Init ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, $mailer = false) {
  
			$this->MySQL	= &$mysql;
			$this->Conf		= (object)$arConf['modules'][get_class($this)];
			$this->Mailer	= &$mailer;
			$this->Yandex	= (object)$arConf['App']['Yandex'];
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
        // Types //////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getTypes() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_v3_types');
		}

		public function getTypesIDs() {
			
			return $this->MySQL->getCol('SELECT id FROM yapps_app_widgets_v3_types');
		}
		
		public function getTypeById( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_v3_types WHERE id = ?i', (int)$id);
		}
		
		public function getTypeByKey( $key ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_v3_types WHERE keyword = ?s', (string)$key);
		}



        ///////////////////////////////////////////////////////////////////////////////////////////
        // Recipients /////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getWidgetRecipients( $id ) {
			
			return $this->MySQL->getCol('SELECT recipient FROM yapps_app_widgets_v3_recipients WHERE widget_id = ?i', (int)$id);
		}
		
		public function setWidgetRecipients( $recipients, $widget_id ) {
			
			$this->MySQL->query('DELETE FROM yapps_app_widgets_v3_recipients WHERE widget_id = ?i', $widget_id);
			foreach ( Helper::findEmails($recipients) as $email ) $this->MySQL->query('INSERT INTO yapps_app_widgets_v3_recipients SET ?u', ['widget_id'=>$widget_id, 'recipient'=>$email]);
		}

		public function getRecipients( $site_id, $widget_id = false ) {
			
			$res = $this->MySQL->getCol('SELECT recipient FROM yapps_app_widgets_recipients WHERE site_id = ?i AND widget_id = ?i', (int)$site_id, 0);
			if ( $widget_id ) $res = array_merge($res, $this->MySQL->getCol('SELECT recipient FROM yapps_app_widgets_recipients WHERE widget_id = ?i', (int)$widget_id));
			
			return $res;
		}



        ///////////////////////////////////////////////////////////////////////////////////////////
        // Settings ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getSettings( $sites = false ) {
			
			if ( $sites ) foreach ( $sites as $k => $s ) $sites[$k]['settings'] = $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_v3_settings WHERE site_id = ?i', $s['id']);
			return $sites;
		}
		
		public function getSettingsById( $site_id ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_v3_settings WHERE site_id = ?i', (int)$site_id);
			$res['shutdown'] = $this->getSetsShutdowns( $res['site_id'] );
			
			return $res;
		}
		
		public function getSettingsByHost( $host ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_v3_settings WHERE site_id = ?i', $this->YApps_GetSiteByHost($host)->id);
			
			return $res;
		}
		
		public function getSettingsByShowroom( $url ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_v3_settings WHERE site_id = ?i', $this->YApps_GetShowroomByUrl( $url )['site_id']);
			
			return $res;
		}
		
		public function setSettings( $POST) {
			
			$arIns = $POST;
			unset( $arIns['form'], $arIns['active'], $arIns['use_libs'], $arIns['use_cb'], $arIns['use_lg'], $arIns['use_nv'], $arIns['use_cis'], $arIns['term_checked'], $arIns['shutdown'] );
            $arIns['active'] = ( $POST['active'] == 'on' ) ? 1 : 0;
            $arIns['use_libs'] = ( $POST['use_libs'] == 'on' ) ? 1 : 0;
            $arIns['use_cb'] = ( $POST['use_cb'] == 'on' ) ? 1 : 0;
            $arIns['use_lg'] = ( $POST['use_lg'] == 'on' ) ? 1 : 0;
            $arIns['use_nv'] = ( $POST['use_nv'] == 'on' ) ? 1 : 0;
            $arIns['use_cis'] = ( $POST['use_cis'] == 'on' ) ? 1 : 0;
            $arIns['term_checked'] = ( $POST['term_checked'] == 'on' ) ? 1 : 0;
			
			if ( $this->MySQL->getOne('SELECT id FROM yapps_app_widgets_v3_settings WHERE site_id = ?i', (int)$POST['site_id']) ) {
				
				$this->MySQL->query('UPDATE yapps_app_widgets_v3_settings SET ?u WHERE site_id = ?i', $arIns, (int)$POST['site_id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_widgets_v3_settings SET ?u', $arIns);
			}
			
			$this->setSetsShutdowns( $POST['shutdown'], $arIns['site_id'] );
			
			return Helper::getRes(0);
		}
		
		public function delSettings( $id ) {
			
			$res = ($this->MySQL->query('DELETE FROM yapps_app_widgets_v3_settings WHERE site_id = ?i', (int)$id)) ? 0 : 41;
			$res = ($this->MySQL->query('DELETE FROM yapps_app_widgets_v3_recipients WHERE site_id = ?i', (int)$id)) ? 0 : 41;
			return Helper::getRes($res);
		}
		
		public function activateSets($id, $action = true) {
			
			$arIns['active'] = ( $action ) ? 1 : 0;
			return ( $id == 'all' ) ? $this->MySQL->query('UPDATE yapps_app_widgets_v3_settings SET ?u', $arIns) : $this->MySQL->query('UPDATE yapps_app_widgets_v3_settings SET ?u WHERE site_id = ?i', $arIns, (int)$id);
		}



        ///////////////////////////////////////////////////////////////////////////////////////////
        // Shutdowns //////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function setSetsShutdowns( $shuts, $site_id ) {
			
			$this->MySQL->query('DELETE FROM yapps_app_widgets_shutdowns WHERE site_id = ?i', $site_id);
			$arIns['site_id'] = $site_id;
			foreach ( $shuts as $sh ) {
				
				$shArr = explode(' - ', $sh);	
				$arIns['start'] = strtotime($shArr[0]);
				$arIns['end'] = strtotime($shArr[1]);
				
				$this->MySQL->query('INSERT INTO yapps_app_widgets_shutdowns SET ?u', $arIns);
			}
		}
		
		public function getShutdowns( $id ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_shutdowns WHERE site_id = ?i', (int)$id);
		}
		
		public function getSetsShutdowns( $id ) {
			
			$shuts = $this->getShutdowns($id);
			foreach( $shuts as $sh ) $res[] = date('Y-m-d H:i', $sh['start']).' - '.date('Y-m-d H:i', $sh['end']);
			
			return $res;
		}
		
		public function isShutdownBySite( $site_id ) {
			
			return $this->MySQL->getOne('SELECT id FROM yapps_app_widgets_shutdowns WHERE site_id = ?i AND start <= ?i AND end >= ?i', (int)$site_id, time(), time());	
		}



        ///////////////////////////////////////////////////////////////////////////////////////////
        // Goals //////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function setGoal( $arGoal, $site, $widget_id ) {
			
			$goal_id = $this->MySQL->getOne(
				'SELECT goal_id FROM yapps_goals WHERE site_id = ?i AND widget_id = ?i AND goal_name = ?s AND goal_js = ?s', 
				$site['id'], $widget_id, $arGoal['name'], $arGoal['goal']);
			
			if ( !$goal_id ) {
				
				$resGoal = Yandex::setGoal( $this->Yandex, $site['yandex_id'], $arGoal );
				$arInsGoal = [
					'site_id' => $site['id'],
					'app_id' => $this->AppInfo()->id,
					'widget_id' => $widget_id,
					'goal_id' => (string)$resGoal->goal->id,
					'goal_type' => ($resGoal->goal->type)?:'action',
					'goal_name' => $arGoal['name'],
					'goal_js' => $arGoal['goal']
				];
				$this->MySQL->query('INSERT INTO yapps_goals SET ?u', $arInsGoal);
			}
		}



        ///////////////////////////////////////////////////////////////////////////////////////////
        // Urls ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getUrls( $widget_id ) {
			
			return $this->MySQL->getCol('SELECT value FROM yapps_app_widgets_v3_urls WHERE widget_id = ?i', $widget_id);
		}
		
		public function setUrls( $urls, $widget_id ) {
			
			$this->MySQL->query('DELETE FROM yapps_app_widgets_v3_urls WHERE widget_id = ?i', $widget_id);
			foreach ( $urls as $url ) {
				if ( $url ) {

					// Helper::sp($url);
					// Helper::sp(Helper::parseHostLink($url));
					// Helper::sd($this->YApps_GetLandIdByUrl(Helper::parseHostLink($url)));
					// die;

					$this->MySQL->query(
						'INSERT INTO yapps_app_widgets_v3_urls SET ?u',
						[
							'widget_id' => $widget_id,
							'value' => ( $this->YApps_GetLandIdByUrl( Helper::parseHostLink($url) ) ) ? Helper::parseHostLink($url) : Helper::parseWidgetURL($url)
						]
					);
				}
			}
			
			return 1;
		}
        
        public function selectWidgetIDByUrl( $url, $type_id ) {

            $res = false;
            return $res;
        }




        ///////////////////////////////////////////////////////////////////////////////////////////
        // Widgets ////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getAllWidgets() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_v3');
		}
		
		public function getWidgetsByType( $type ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_v3 WHERE type_id = ?i AND site_id IN (?a)', (int)$type, $GLOBALS['USER_SITES']['sites_ids']);
		}
		
		public function getAllWidgetsByType( $type ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_v3 WHERE type_id = ?i', (int)$type);
		}

		public function getWidgetById( $id ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_v3 WHERE id = ?i', (int)$id);
			$res['recipients'] = $this->getWidgetRecipients( (int)$id );
			$res['url'] = $this->getUrls( (int)$id );
			
			return $res;
		}

		public function setWidget( $POST, $FILES = false ) {

			$arIns = $POST;
			unset($arIns['form'], $arIns['active'], $arIns['recipients'], $arIns['id'], $arIns['public_key'],$arIns['cb_url'],$arIns['lg_url'], $arIns['lg_timer_use']);
			$arIns['active'] = ( $POST['active'] == 'on' ) ? 1 : 0;

			$arIns['public_key'] = ( $POST['public_key'] ) ?: md5( $this->Conf->Secret.'_'.$POST['name'].'_'.time() );
			
			$arIns['type_id'] = (int)$POST['type_id'];
			$arIns['site_id'] = (int)$POST['site_id'];
			
			$arIns['lg_timer_use'] = ( $POST['lg_timer_use'] == 'on' ) ? 1 : 0;
			$arIns['lg_timer'] = strtotime($POST['lg_timer']);

			if ( $POST['id'] ) $widget = $this->getWidgetById( $POST['id'] );
			
			if ( $FILES && $FILES['lg_image_back']['error'] == 0 && $arIns['public_key'] ) {

				if ( !file_exists(__DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key']) ) 
					mkdir( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'] );
				if ( $POST['id'] ) unlink( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.explode('/', $widget['lg_image_back'])[6] );
				
				$arIns['lg_image_back'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.$FILES['lg_image_back']['name'];
				
				$file = __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.$FILES['lg_image_back']['name'];
				move_uploaded_file( $FILES['lg_image_back']['tmp_name'], $file );
			}
			if ( $FILES && $FILES['lg_image_front']['error'] == 0 && $arIns['public_key'] ) {

				if ( !file_exists(__DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key']) ) 
					mkdir( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'] );
				if ( $POST['id'] ) unlink( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.explode('/', $widget['lg_image_front'])[6] );
				
				$arIns['lg_image_front'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.$FILES['lg_image_front']['name'];
				
				$file = __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.$FILES['lg_image_front']['name'];
				move_uploaded_file( $FILES['lg_image_front']['tmp_name'], $file );
			}

			if ( $FILES && $FILES['cb_image_back']['error'] == 0 && $arIns['public_key'] ) {

				if ( !file_exists(__DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key']) ) 
					mkdir( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'] );
				if ( $POST['id'] ) unlink( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.explode('/', $widget['cb_image_back'])[6] );
				
				$arIns['cb_image_back'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.$FILES['cb_image_back']['name'];
				
				$file = __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.$FILES['cb_image_back']['name'];
				move_uploaded_file( $FILES['cb_image_back']['tmp_name'], $file );
			}
			if ( $FILES && $FILES['cb_image_front']['error'] == 0 && $arIns['public_key'] ) {

				if ( !file_exists(__DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key']) ) 
					mkdir( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'] );
				if ( $POST['id'] ) unlink( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.explode('/', $widget['cb_image_front'])[6] );
				
				$arIns['cb_image_front'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.$FILES['cb_image_front']['name'];
				
				$file = __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.$FILES['cb_image_front']['name'];
				move_uploaded_file( $FILES['cb_image_front']['tmp_name'], $file );
			}
			
			if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_app_widgets_v3 SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				$lastId = (int)$POST['id'];
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_widgets_v3 SET ?u', $arIns);
				$lastId = $this->MySQL->insertId();
			}
			
			$site = $this->YApps_GetSiteByID( $arIns['site_id'] );

			if ( $POST['recipients'] ) $this->setWidgetRecipients( $POST['recipients'], $lastId );
			if ( $POST['lg_url'] ) $this->setUrls( $POST['lg_url'], $lastId );
			if ( $POST['cb_url'] ) $this->setUrls( $POST['cb_url'], $lastId );

			if ( $site['yandex_id'] ) {
				
				switch ( $arIns['type_id'] ) {
					
					// CB, LG, QZ, CI, EH
					case 1:
					case 2:
						
						$arGoals = [
							[
								'name' => $this->AppInfo()->ru_name.'. ID: '.$lastId.'. '.$this->getTypeById( $arIns['type_id'] )['ru_name'] ,
								'goal' => 'YApps_Goals-Widgets_v3_'.$this->getTypeById( $arIns['type_id'] )['keyword']
							],
						];
						foreach ( $arGoals as $arGoal ) $this->setGoal( $arGoal, $site, $lastId );
						
						break;
					
					// NV	
					case 3:
						
						$arGoals = [
							[
								'name' => $this->AppInfo()->ru_name.'. ID: '.$lastId.'. '.$this->getTypeById( $arIns['type_id'] )['ru_name'].'. Маршрут.',
								'goal' => 'YApps_Goals-Widgets_v3_NV-Route'
							],
						];
						foreach ( $arGoals as $arGoal ) $this->setGoal( $arGoal, $site, $lastId );
						
						break;
					
					default: break;
				}
			}
			
			$return = Helper::getRes(0);
			$return->redirect = '/widgets_v3/'.mb_strtolower($this->getTypeById( $arIns['type_id'] )['keyword']).'/edit/'.$lastId.'/';

			return $return;
		}

		public function selectCB( $sets, $url ) {
			
			if ( $this->isShutdownBySite($sets['site_id']) ) return false;
			
			$id = false;
			if ( time() >= strtotime(date('Y-m-d').' '.$this->Conf->Defaults['CB']['work_start']) && time() <= strtotime(date('Y-m-d').' '.$this->Conf->Defaults['CB']['work_end']) ) {
				$ids = $this->MySQL->getCol(
					'SELECT widget_id FROM yapps_app_widgets_v3_urls WHERE value = ?s',
					( $this->YApps_GetLandIdByUrl( Helper::parseHostLink($url) ) ) ? Helper::parseHostLink($url) : Helper::parseWidgetURL($url)
				);
				if ( !$ids ) $ids = $this->MySQL->getCol('SELECT widget_id FROM yapps_app_widgets_v3_urls WHERE value = ?s', '/');
				$id = $this->MySQL->getOne('SELECT id FROM yapps_app_widgets_v3 WHERE site_id = ?i AND active = ?i AND type_id = ?i AND id IN (?a) ORDER BY id DESC', $sets['site_id'], 1, 1, $ids);
				if ( !$id ) $id = $this->MySQL->getOne('SELECT id FROM yapps_app_widgets_v3 WHERE site_id = ?i AND active = ?i AND type_id = ?i ORDER BY id DESC', $sets['site_id'], 1, 1);
			}
			
			return $id;
        }

		public function selectLG( $sets, $url ) {

			if ( $this->isShutdownBySite($sets['site_id']) ) return false;
			
			$ids = $this->MySQL->getCol(
				'SELECT widget_id FROM yapps_app_widgets_v3_urls WHERE value = ?s',
				( $this->YApps_GetLandIdByUrl( Helper::parseHostLink($url) ) ) ? Helper::parseHostLink($url) : Helper::parseWidgetURL($url)
			);
			if ( !$ids ) $ids = $this->MySQL->getCol('SELECT widget_id FROM yapps_app_widgets_v3_urls WHERE value = ?s', '/');
			return $this->MySQL->getOne('SELECT id FROM yapps_app_widgets_v3 WHERE site_id = ?i AND active = ?i AND type_id = ?i AND id IN (?a) ORDER BY id DESC', $sets['site_id'], 1, 2, $ids);
        }

		public function delWidget( $id ) {
			
			$widget = $this->getWidgetById( $id );
			
			$this->MySQL->query('DELETE FROM yapps_app_widgets_v3 WHERE id = ?i', (int)$id);
			$this->MySQL->query('DELETE FROM yapps_app_widgets_v3_recipients WHERE widget_id = ?i', (int)$id);
			$this->MySQL->query('DELETE FROM yapps_goals WHERE widget_id = ?i', (int)$id);
			$this->MySQL->query('DELETE FROM yapps_app_widgets_v3_urls WHERE widget_id = ?i', (int)$id);
			
			Helper::removeDirectory( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$widget['public_key'] );
			
			return Helper::getRes(0);
		}


        

        ///////////////////////////////////////////////////////////////////////////////////////////
        // API ////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function getScript( $user, $URL ) {
            
            // if ($_GET['s']) Helper::sp( $_GET );

			$host = parse_url( $URL )['host'];

			$land = $this->YApps_GetLandSettings( $this->YApps_GetLandIdByUrl('https://'.$host.'/') );
			if ( $land ) $host = $this->YApps_GetSiteByID( $land['site_id'] )['url'];
			$settings = $this->getSettingsByHost( $host );
			if ( !$settings ) $settings = $this->getSettingsByShowroom( $URL );

			$site = $this->YApps_GetSiteByID($settings['site_id']);

			if ( $land['calltouch_id'] ) $site['calltouch_id'] = $land['calltouch_id'];
			if ( $land['calltouch_sess'] ) $site['calltouch_sess'] = $land['calltouch_sess'];
			
			if ( $settings['active'] &&
				( !$land ||
					( $land && 
						( $land['use_lg'] || $land['use_cb'] || $land['use_nv'] || $land['use_cis'] )
					)
				)
			) {
				
				$id = $this->selectCB( $settings, $URL );
				$widget['CB'] = ( $id ) ? $this->getWidgetById( $id ) : false;
				
				$id = $this->selectLG( $settings, $URL );
				$widget['LG'] = ( $id ) ? $this->getWidgetById( $id ) : false;
                if ( $widget['LG']['lg_timer_use'] ) if ( $widget['LG']['lg_timer'] <= time() ) $widget['LG'] = false;
				
                $widget['NV'] = ( $settings['use_nv'] ) ? ['coords_lat'=>$settings['nv_coords_lat'], 'coords_lon'=>$settings['nv_coords_lon']] : false;
				$widget['CIS'] = ( $settings['use_cis'] ) ? ['link'=>$settings['cis_link']] : false;
				
				// HTML
				$html['res'] = file_get_contents(__DIR__.'/html/'.get_class($this).'.html');
				$html['buttons']['CB'] = ( $widget['CB'] && $settings['use_cb'] ) ? file_get_contents(__DIR__.'/html/'.get_class($this).'/Buttons/CB.html') : '';
				$html['buttons']['LG'] = ( $widget['LG'] && $settings['use_lg'] ) ? file_get_contents(__DIR__.'/html/'.get_class($this).'/Buttons/LG.html') : '';
				$html['buttons']['NV'] = ( $widget['NV'] && $settings['use_nv'] ) ? file_get_contents(__DIR__.'/html/'.get_class($this).'/Buttons/NV.html') : '';
				$html['buttons']['CIS'] = ( $widget['CIS'] && $settings['use_cis'] ) ? file_get_contents(__DIR__.'/html/'.get_class($this).'/Buttons/CIS.html') : '';
				$html['widgets']['CB'] = ( $widget['CB'] && $settings['use_cb'] ) ? file_get_contents(__DIR__.'/html/'.get_class($this).'/CB.html') : '';
				$html['widgets']['LG'] = ( $widget['LG'] && $settings['use_lg'] ) ? file_get_contents(__DIR__.'/html/'.get_class($this).'/LG.html') : '';

				// for LANDS
				if ( $land && !$land['use_cb'] ) $html['buttons']['CB'] = $html['widgets']['CB'] = '';
				if ( $land && !$land['use_lg'] ) $html['buttons']['LG'] = $html['widgets']['LG'] = '';
				if ( $land && !$land['use_av'] ) $html['buttons']['CIS'] = '';
				if ( $land && !$land['use_nv'] ) $html['buttons']['NV'] = '';

				$html['buttons']['CB'] = str_replace('%% CLUE %%', $settings['cb_clue'], $html['buttons']['CB']);
				$html['buttons']['LG'] = str_replace('%% CLUE %%', $settings['lg_clue'], $html['buttons']['LG']);
				$html['buttons']['NV'] = str_replace('%% COORDS_LON %%', $settings['nv_coords_lon'], $html['buttons']['NV']);
				$html['buttons']['NV'] = str_replace('%% COORDS_LAT %%', $settings['nv_coords_lat'], $html['buttons']['NV']);
				$html['buttons']['NV'] = str_replace('%% CLUE %%', $settings['nv_clue'], $html['buttons']['NV']);
				$html['buttons']['CIS'] = str_replace('%% CIS_LINK %%', $settings['cis_link'], $html['buttons']['CIS']);
				$html['buttons']['CIS'] = str_replace('%% CLUE %%', $settings['cis_clue'], $html['buttons']['CIS']);
                
				$html['widgets']['CB'] = str_replace('%% WIDGET_ID %%', $widget['CB']['id'], $html['widgets']['CB']);
				$html['widgets']['CB'] = str_replace('%% WIDGET_TITLE %%', addslashes($widget['CB']['cb_title']), $html['widgets']['CB']);
				$html['widgets']['CB'] = str_replace('%% WIDGET_TEXT %%', addslashes($widget['CB']['cb_text']), $html['widgets']['CB']);
				$html['widgets']['CB'] = str_replace('%% WIDGET_BUTTON %%', $widget['CB']['cb_button_text'], $html['widgets']['CB']);
				$html['widgets']['CB'] = str_replace('%% WIDGET_FORM_SUCCESS %%', $settings['form_success'], $html['widgets']['CB']);
				$html['widgets']['CB'] = str_replace('%% WIDGET_FORM_ERROR %%', $settings['form_error'], $html['widgets']['CB']);
				$html['widgets']['CB'] = str_replace('%% WIDGET_IMAGE_BACK %%', (($widget['CB']['cb_image_back'])?:$this->Conf->Defaults['CB']['image_back']), $html['widgets']['CB']);
				$html['widgets']['CB'] = str_replace('%% WIDGET_IMAGE_FRONT %%', (($widget['CB']['cb_image_front'])?:$this->Conf->Defaults['CB']['image_front']), $html['widgets']['CB']);
				$html['widgets']['CB'] = str_replace('%% WIDGET_TERM_PERSONAL %%', (($widget['CB']['term_personal'])?:$settings['term_personal']), $html['widgets']['CB']);
				$html['widgets']['CB'] = str_replace('%% WIDGET_TERM_COMMUNICATIONS %%', (($widget['CB']['term_politic'])?:$settings['term_communications']), $html['widgets']['CB']);
				$repl = '';
				for ( $i = date('H'); $i < 20; $i++ ) $repl .= "<option value='".$i."' '.(($i==date('H'))?'selected':'').'>".$i."</option>";
				$html['widgets']['CB'] = str_replace('%% WIDGET_HOUR_OPTIONS %%', $repl, $html['widgets']['CB']);
				
				$html['widgets']['LG'] = str_replace('%% WIDGET_ID %%', $widget['LG']['id'], $html['widgets']['LG']);
				$html['widgets']['LG'] = str_replace('%% WIDGET_TITLE %%', addslashes($widget['LG']['lg_title']), $html['widgets']['LG']);
				$html['widgets']['LG'] = str_replace('%% WIDGET_SUBTITLE %%', addslashes($widget['LG']['lg_subtitle']), $html['widgets']['LG']);
				$html['widgets']['LG'] = str_replace('%% WIDGET_TEXT %%', addslashes($widget['LG']['lg_text']), $html['widgets']['LG']);
				$html['widgets']['LG'] = str_replace('%% WIDGET_BUTTON %%', $widget['LG']['lg_button_text'], $html['widgets']['LG']);
				$html['widgets']['LG'] = str_replace('%% WIDGET_FORM_SUCCESS %%', $settings['form_success'], $html['widgets']['LG']);
				$html['widgets']['LG'] = str_replace('%% WIDGET_FORM_ERROR %%', $settings['form_error'], $html['widgets']['LG']);
				$html['widgets']['LG'] = str_replace('%% WIDGET_MARKING %%', addslashes($widget['LG']['lg_marking']), $html['widgets']['LG']);
				$html['widgets']['LG'] = str_replace('%% WIDGET_IMAGE_BACK %%', $widget['LG']['lg_image_back'], $html['widgets']['LG']);
				$html['widgets']['LG'] = str_replace('%% WIDGET_IMAGE_FRONT %%', $widget['LG']['lg_image_front'], $html['widgets']['LG']);
				$html['widgets']['LG'] = str_replace('%% WIDGET_TERM_PERSONAL %%', (($widget['LG']['term_personal'])?:$settings['term_personal']), $html['widgets']['LG']);
				$html['widgets']['LG'] = str_replace('%% WIDGET_TERM_COMMUNICATIONS %%', (($widget['LG']['term_politic'])?:$settings['term_communications']), $html['widgets']['LG']);
				$repl = '';
				if ( $widget['LG']['lg_timer_use'] ) {
					$timer = Helper::shortTimeout( $widget['LG']['lg_timer'] );
					$repl = file_get_contents(__DIR__.'/html/'.get_class($this).'/Widgets/Timer.html');
					$repl = str_replace('%% D %%', $timer['d'], $repl);
					$repl = str_replace('%% D_D %%', Helper::getWorld($timer['d'], 'd'), $repl);
					$repl = str_replace('%% M %%', $timer['m'], $repl);
					$repl = str_replace('%% M_D %%', Helper::getWorld($timer['m'], 'm'), $repl);
					$repl = str_replace('%% H %%', $timer['h'], $repl);
					$repl = str_replace('%% H_D %%', Helper::getWorld($timer['h'], 'h'), $repl);
					$repl = str_replace('%% S %%', $timer['s'], $repl);
					$repl = str_replace('%% S_D %%', Helper::getWorld($timer['s'], 's'), $repl);
				}
				$html['widgets']['LG'] = str_replace('%% WIDGET_TIMER %%', $repl, $html['widgets']['LG']);

				$html['res'] = str_replace('%% BUTTON_LG %%', $html['buttons']['LG'], $html['res']);
				$html['res'] = str_replace('%% BUTTON_CB %%', $html['buttons']['CB'], $html['res']);
				$html['res'] = str_replace('%% BUTTON_NV %%', $html['buttons']['NV'], $html['res']);
				$html['res'] = str_replace('%% BUTTON_CIS %%', $html['buttons']['CIS'], $html['res']);
				$html['res'] = str_replace('%% WIDGET_CB %%', $html['widgets']['CB'], $html['res']);
				$html['res'] = str_replace('%% WIDGET_LG %%', $html['widgets']['LG'], $html['res']);


				// CSS
				$css = file_get_contents(__DIR__.'/css/'.get_class($this).'.css');
				$css = str_replace('%% COLOR_BG %%', $settings['color_widget_bg'], $css);
				$css = str_replace('%% COLOR_TEXT %%', $settings['color_widget_text'], $css);
				$css = str_replace('%% COLOR_ICON_LIGHT %%', $settings['color_icon_light'], $css);
				$css = str_replace('%% COLOR_ICON_DARK %%', $settings['color_icon_dark'], $css);
				$css = str_replace('%% COLOR_ICON_HOVER_LIGHT %%', $settings['color_icon_hover_light'], $css);
				$css = str_replace('%% COLOR_ICON_HOVER_DARK %%', $settings['color_icon_hover_dark'], $css);
				$css = str_replace('%% COLOR_ICON_HOVER_SHADOW %%', implode(',', Helper::hexToRgb($settings['color_icon_hover_shadow'])), $css);
				$css = str_replace('%% COLOR_ICON_BUTTON %%', implode(',', Helper::hexToRgb($settings['color_icon_button'])), $css);
				$css = str_replace('%% COLOR_ICON_HOVER_BUTTON %%', $settings['color_icon_hover_button'], $css);
				$css = str_replace('%% COLOR_ICON_HOVER_BUTTON_SHADOW %%', implode(',', Helper::hexToRgb($settings['color_icon_hover_button_shadow'])), $css);
				$css = str_replace('%% COLOR_FIELD_BORDER %%', $settings['color_widget_field_border'], $css);
				$css = str_replace('%% COLOR_FIELD_BG %%', $settings['color_widget_field_bg'], $css);
				$css = str_replace('%% COLOR_BUTTON %%', $settings['color_widget_button'], $css);
				$css = str_replace('%% COLOR_BUTTON_TEXT %%', (($settings['color_widget_button_text'])?:$this->Conf->Defaults['Colors']['WidgetButtonText']), $css);
				$css = str_replace('%% COLOR_BUTTON_HOVER %%', $settings['color_widget_button_hover'], $css);
				$css = str_replace('%% COLOR_BUTTON_HOVER_TEXT %%', (($settings['color_widget_button_hover_text'])?:$this->Conf->Defaults['Colors']['WidgetButtonHoverText']), $css);
				$css = str_replace('%% COLOR_TERMS %%', $settings['color_widget_terms'], $css);
				$css = str_replace('%% COLOR_TIMER_BG %%', $settings['color_widget_timer_bg'], $css);
				$css = str_replace('%% COLOR_TIMER_TEXT %%', $settings['color_widget_timer_text'], $css);
				$css = str_replace('%% COLOR_ERROR %%', $settings['color_widget_error'], $css);
				$css = str_replace('%% MARGIN_BOTTOM %%', (($settings['margin_bottom'])?:$this->Conf->Defaults['Margins']['bottom']), $css);
				$css = str_replace('%% MARGIN_RIGHT %%', (($settings['margin_right'])?:$this->Conf->Defaults['Margins']['right']), $css);
				if ( file_exists( __DIR__.'/../../upload/Widgets3/AddStyles/'.$settings['site_id'].'.css' ) )
					$css .= PHP_EOL.file_get_contents( __DIR__.'/../../upload/Widgets3/AddStyles/'.$settings['site_id'].'.css' );

				
				// SCRIPT
				$script = '';
				if ( $settings['use_libs'] ) $script .= file_get_contents(__DIR__.'/../../pub/libs/jquery/3.7.1/jquery.min.js').PHP_EOL;
				// if ( $settings['site_id'] != 46) $script .= file_get_contents(__DIR__.'/../../pub/libs/jquery.inputmask/5.0.3/jquery.inputmask.min.js').PHP_EOL;
				$script .= file_get_contents(__DIR__.'/../../pub/libs/jquery-cookie/1.4.1/jquery.cookie.min.js').PHP_EOL;
				$script .= file_get_contents(__DIR__.'/js/'.get_class($this).'.js');

				$script = str_replace('%% CSS %%', JSMin::minifyCSS($css), $script);
				$script = str_replace('%% HTML %%', JSMin::minifyHTML($html['res']), $script);
				$script = str_replace('"%% FORM_TIMEOUT %%"', $settings['form_timeout'], $script);
				$script = str_replace('"%% CB_TIMEOUT %%"', $settings['cb_timeout'], $script);
				$script = str_replace('"%% LG_TIMEOUT_1 %%"', $settings['lg_timeout_1'], $script);
				$script = str_replace('"%% LG_TIMEOUT_2 %%"', $settings['lg_timeout_2'], $script);
				$script = str_replace('%% CT_ID %%', $site['calltouch_id'], $script);
				$script = str_replace('%% CT_S %%', $site['calltouch_sess'], $script);
				$script = str_replace('"%% YA_ID %%"', (($site['yandex_id'])?:'""'), $script);
				$script = str_replace('"%% CB %%"', (($settings['use_cb']&&$widget['CB'])?'true':'false'), $script);
				$script = str_replace('"%% LG %%"', (($settings['use_lg']&&$widget['LG'])?'true':'false'), $script);
				if ( file_exists( __DIR__.'/../../upload/Widgets3/AddStyles/'.$settings['site_id'].'.js' ) )
					$script .= PHP_EOL.file_get_contents( __DIR__.'/../../upload/Widgets3/AddStyles/'.$settings['site_id'].'.js' );
				
				return $script;
			}
		}

        

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Stat ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function pushStat( $POST, $user, $ip ) {
			
            $this->sendForm( $POST );
			$this->logForm( $POST );
			return Helper::getRes(0);
        }
        
        public function getStats( $user, $date1, $date2 ) {

			$sites = $this->MySQL->getCol('SELECT site_id FROM yapps_users_sites WHERE user_id = ?i', (int)$user->id);
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_widgets3_stat WHERE site_id IN (?a) AND timestamp >= ?i AND timestamp < ?i', $sites, strtotime($date1), strtotime($date2));

			return $res;
		}

		private function sendForm( $arIns ) {
			
			$this->Mailer->CharSet = 'utf-8';
			$this->Mailer->setFrom('widgets@apps.yug-avto.ru', 'Оповещения Юг-Авто Apps. Виджеты.');
			$this->Mailer->ClearAddresses();
			
			$widget = $this->getWidgetById($arIns['Id']);

			$site = $this->YApps_GetSiteByID($widget['site_id']);
			
			if ( !$widget['recipients'] ) $widget['recipients'] = [$this->Conf->Defaults['Recipients']];
			
			foreach ($widget['recipients'] as $email) $this->Mailer->addAddress($email, '');
			
			$this->Mailer->Subject = 'Сайт: '.$site['ru_name'].'. '.$arIns['EventName'];

			$message = '<h3>Сайт: '.$site['ru_name'].'. '.$arIns['EventName'].'</h3>';
			if ($arIns['yapps-widget-form-name']) $message .= 'Имя: '.$arIns['yapps-widget-form-name'].'<br />';
			$message .= 'Телефон: '.(($arIns['yapps-widget-form-phone'])?Helper::formatPhoneOut($arIns['yapps-widget-form-phone']):'').'<br />';
			if ($arIns['yapps-widget-form-time']) $message .= 'Время звонка: '.(($arIns['yapps-widget-form-time']!='now')?$arIns['yapps-widget-form-time']:'Сейчас').'<br />';
			
			
			$message .= '<br /><br />';
			$message .= 'Страница-источник: <a href="'.$arIns['Source'].'" target="_blank">'.(($arIns['source_title'])?:$arIns['Source']).'</a>';

			$this->Mailer->msgHTML($message);
			
			return $this->Mailer->Send();
		}

		private function logForm( $POST ) {

			$host = parse_url($POST['Source'])['host'];
			if ( !file_exists(__DIR__.'/Logs/Widgets3/'.date('Y')) ) mkdir(__DIR__.'/Logs/Widgets3/'.date('Y'));
			if ( !file_exists(__DIR__.'/Logs/Widgets3/'.date('Y').'/'.date('m')) ) mkdir(__DIR__.'/Logs/Widgets3/'.date('Y').'/'.date('m'));
			if ( file_exists(__DIR__.'/Logs/Widgets3/'.date('Y').'/'.date('m').'/'.date('d').'.json') ) $log = json_decode( file_get_contents(__DIR__.'/Logs/Widgets3/'.date('Y').'/'.date('m').'/'.date('d').'.json'), true);
			$log[$host][date('H:i:s')] = $POST;

			file_put_contents(__DIR__.'/Logs/Widgets3/'.date('Y').'/'.date('m').'/'.date('d').'.json', json_encode($log));
		}
		

	}
?>