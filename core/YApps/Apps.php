<?php

	class Apps extends App {
		
		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, $mailer = false ) {
			
			$this->MySQL		= &$mysql;
			//$this->conf		= (object)$arConf['modules']['Apps'];
		}
		
		public function getAll( $user ) {
			
			foreach ( $this->MySQL->getAll('SELECT * FROM yapps_apps_users WHERE user_id = ?i', (int)$user->id) as $item ) $res[] = $this->get( $item['app_id'], $user );
			return $res;
		}
		
		public function getApps() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_apps ORDER BY sort ASC');
		}
		
		public function getAppById( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_apps WHERE id = ?i', (int)$id);
		}
		
		public function getString( $user ) {
			
			$res['apps'] = $this->getAll( $user );
			
			$res['apps_string'] = '';
			foreach ( $res['apps'] as $item ) {
				
				$res['apps_string'] .= $item['settings']['ru_name'].' / ';
				$res['apps_ids'][] = $item['settings']['id'];
			}
			$res['apps_string'] = mb_substr($res['apps_string'], 0, -3);
			
			return $res;
		}
		
		public function get( $id, $user ) {
			
			$res['settings'] = $this->MySQL->getRow('SELECT * FROM yapps_apps WHERE id = ?i', (int)$id);
			$res['menu'] = $this->MySQL->getAll('SELECT * FROM yapps_apps_menu WHERE app_id = ?i ORDER BY sort ASC', (int)$id);
			
			$res['add_menu'][] = [
				'url_key' => 'stat',
				'icon' => 'bar-chart',
				'name' => 'Статистика',
				'action' => 'view'
			];
			
			$res['add_menu'][] = [
				'url_key' => 'export',
				'icon' => 'download',
				'name' => 'Экспорт',
				'action' => 'view'
			];
			
			if ( (int)$user->role_id < 3 ) {
				
				$res['add_menu'][] = [
					'url_key' => 'settings',
					'icon' => 'cogs',
					'name' => 'Настройки',
					'action' => 'view'
				];
			}
			
			return $res;
        }
        
        public function getMenuPoints() {

            return $this->MySQL->getAll('SELECT * FROM yapps_apps_menu ORDER BY app_id');
        }

        public function getMenuPoint( $id ) {

            return $this->MySQL->getRow('SELECT * FROM yapps_apps_menu WHERE id = ?i', $id);
        }

        public function delMenuPoint( $id ) {

            return $this->MySQL->query('DELETE FROM yapps_apps_menu WHERE id = ?i', $id);
        }

        public function setMenuPoint( $POST ) {
            
            $arIns = $POST;
            unset($arIns['form']);
            $this->MySQL->query('REPLACE INTO yapps_apps_menu SET ?u', $arIns);

            return Helper::getRes(0);
        }

        public function setApp( $POST ) {

            $arIns = $POST;
            unset($arIns['form']);

            $arIns['view_in_menu'] = ( $POST['view_in_menu'] == 'on' ) ? 1 : 0;
            $arIns['hide_home'] = ( $POST['hide_home'] == 'on' ) ? 1 : 0;
            $arIns['hide_stat'] = ( $POST['hide_stat'] == 'on' ) ? 1 : 0;
            $arIns['hide_export'] = ( $POST['hide_export'] == 'on' ) ? 1 : 0;
            $arIns['maintenance'] = ( $POST['maintenance'] == 'on' ) ? 1 : 0;
            $arIns['activity'] = ( $POST['activity'] == 'on' ) ? 1 : 0;

            $this->MySQL->query('REPLACE INTO yapps_apps SET ?u', $arIns);

            if ( !$POST['id'] ) {

                if ( !file_exists( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'] ) ) 
                    mkdir( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'] );
                if ( !file_exists( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/inc' ) ) 
                    mkdir( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/inc' );
                if ( !file_exists( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/layout' ) ) 
                    mkdir( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/layouts' );
                if ( !file_exists( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/layout/lists' ) ) 
                    mkdir( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/layouts/lists' );
                if ( !file_exists( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/layout/forms' ) ) 
                    mkdir( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/layouts/forms' );
                if ( !file_exists( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/layout/view' ) ) 
                    mkdir( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/layouts/view' );
                if ( !file_exists( $_SERVER['DOCUMENT_ROOT'].'/upload/'.$POST['class'] ) ) 
                    mkdir( $_SERVER['DOCUMENT_ROOT'].'/upload/'.$POST['class'] );

                if ( !file_exists($_SERVER['DOCUMENT_ROOT'].'/core/YApps/Configs/Class/'.$POST['class'].'.php') ) {
                
                    $code = file_get_contents( $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/Conf.Class.php' );
                    $code = str_replace( '%%APP.CLASS%%', $POST['class'], $code );

                    file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/core/YApps/Configs/Class/Global/'.$POST['class'].'.php.dis', $code );
                }

                if ( !file_exists($_SERVER['DOCUMENT_ROOT'].'/core/YApps/Configs/App/'.$POST['class'].'.php') ) {
                
                    $code = file_get_contents( $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/Conf.App.php' );
                    $code = str_replace( '%%APP.CLASS%%', $POST['class'], $code );
                    $code = str_replace( '%%APP.NAME%%', $POST['ru_name'], $code );

                    file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/core/YApps/Configs/App/'.$POST['class'].'.php.dis', $code );
                }

                if ( !file_exists($_SERVER['DOCUMENT_ROOT'].'/core/YApps/'.$POST['class'].'.php') ) {
                
                    $code = file_get_contents( $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/Class.php' );
                    $code = str_replace( '%%APP.CLASS%%', $POST['class'], $code );
                    $code = str_replace( '%%APP.TABLE%%', mb_strtolower($POST['class']), $code );

                    file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/core/YApps/'.$POST['class'].'.php', $code );
                }
                
                if ( !file_exists($_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/view_index.php') ) {
                
                    $code = file_get_contents( $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/view_index.php' );
                    $code = str_replace( '%%APP.CLASS%%', $POST['class'], $code );

                    file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/view_index.php', $code );
                }

                if ( !file_exists($_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/view_settings.php') ) {
                
                    $code = file_get_contents( $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/view_settings.php' );
                    $code = str_replace( '%%APP.CLASS%%', $POST['class'], $code );

                    file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/view_settings.php', $code );
                }

                if ( !file_exists($_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/view_stat.php') ) {
                
                    $code = file_get_contents( $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/view_stat.php' );
                    $code = str_replace( '%%APP.CLASS%%', $POST['class'], $code );
                    $code = str_replace( '%%APP.URL%%', $POST['url_key'], $code );

                    file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/view_stat.php', $code );
                }

                if ( !file_exists($_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/view_export.php') ) {
                
                    $code = file_get_contents( $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/view_export.php' );
                    $code = str_replace( '%%APP.CLASS%%', $POST['class'], $code );
                    $code = str_replace( '%%APP.URL%%', $POST['url_key'], $code );

                    file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/views/'.$POST['url_key'].'/view_export.php', $code );
                }
            }
            
            return Helper::getRes(0);
        }
	}