<?php

	class Goals extends App {
		
		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, $mailer = false ) {

			$this->MySQL		= &$mysql;
			$this->Yandex	= (object)$arConf['App']['Yandex'];
		}
		
		
		// Get Area
		
		public function getAll() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_goals');
		}
		
		public function getByUser( $user ) {
		}
		
		public function getByIds( $ids ) {
		}
		
		public function getById( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_goals WHERE id = ?i', (int)$id);
		}
		
		public function getAllByApp( $id ) {
		}
		
		public function getByApp( $user, $id ) {
			
			$sites = $this->YApps_GetUserSiteIDs( $user );
			$res = $this->MySQL->getAll('SELECT * FROM yapps_goals WHERE site_id IN (?a) AND app_id = ?i', $sites, (int)$id);
			
			return $res;
		}
		
		
		
		
		
		public function set( $POST ) {
			
			$arIns = $POST;
			unset( $arIns['form'] );
			
			$arGoal = [
				'name' => $arIns['goal_name'],
				'url' => ( parse_url($arIns['goal_url'])['path'] ) ? parse_url($arIns['goal_url'])['path'] : $arIns['goal_url'],
				'goal' => $arIns['goal_js'],
			];
			
			if ( $POST['goal_id'] ) $arGoal['id'] = (int)$POST['goal_id'];
			$resGoal = Yandex::setGoal( $this->Yandex, $this->getSite($POST['site_id'])['yandex_id'], $arGoal );
			
			$arIns['goal_id'] = ( $POST['goal_id'] ) ? $POST['goal_id'] : (string)$resGoal->goal->id;
			$arIns['goal_type'] = $resGoal->goal->type;
			
			if ( $POST['goal_id'] ) {
				
				$this->MySQL->query('UPDATE yapps_goals SET ?u WHERE goal_id = ?s', $arIns, $POST['goal_id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_goals SET ?u', $arIns);
				
			} //if
			
			return Helper::getRes(0);
		}
		
		
		
		public function del( $id ) {
			
			$goal = $this->getById( $id );
			//Yandex::del();
			return $this->MySQL->query('DELETE FROM yapps_goals WHERE id = ?i', (int)$id);
		}
	}