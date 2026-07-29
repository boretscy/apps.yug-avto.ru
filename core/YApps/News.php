<?php

	class News extends App {
		
		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, $mailer = false ) {
			
			$this->MySQL		= &$mysql;
			$this->conf		= (object)$arConf['modules']['News'];
		}
		
		public function AppInfo() {
			
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_apps WHERE class = ?s', 'News');
		}
		
		public function getById( $id ) {

            return $this->MySQL->getRow('SELECT * FROM yapps_app_news WHERE id = ?i', (int)$id);
		}
		
		public function getLast() {
            
            return $this->MySQL->getRow('SELECT * FROM yapps_app_news WHERE MAX(timestamp)');
		}
		
		public function getAllLimit( $count = 10 ) {
            
            return $this->MySQL->getAll('SELECT * FROM yapps_app_news ORDER BY timestamp DESC LIMIT ?i', $count);
		}
		
		public function getUserAppNews( $count = 3 ) {
            
			$ids = array_merge([0], $this->MySQL->getCol('SELECT app_id FROM yapps_apps_users WHERE user_id = ?i', $GLOBALS['AUTH_USER']->id));
            return $this->MySQL->getAll('SELECT * FROM yapps_app_news WHERE app_id IN (?a) ORDER BY timestamp DESC LIMIT ?i', $ids, $count);
		}
		
		public function getAll() {
            
            return $this->MySQL->getAll('SELECT * FROM yapps_app_news');
		}
		
		public function setItem( $POST ) {
			
			$arIns = $POST;
			unset( $arIns['id'], $arIns['form'], $arIns['date'], $arIns['app_id'] );
			$arIns['timestamp'] = strtotime( $POST['date'] );
			$arIns['app_id'] = (int)$POST['app_id'];
			
			if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_app_news SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
			
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_news SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
		}
		
		public function delete( $id ) {
			
			$this->MySQL->query('DELETE FROM yapps_app_news WHERE id = ?i', (int)$id);
			return Helper::getRes(0);
		}
		
		public function getCountNew() {
			
			return $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_app_news WHERE timestamp > ?i', time()-7*24*3600);  
		}
		
		public function getCountNewApp( $id ) {
			
			return $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_app_news WHERE timestamp > ?i AND app_id = ?i', time()-7*24*3600, (int)$id);  
		}
	}