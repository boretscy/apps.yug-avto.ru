<?php

	class Foots extends App {
  
		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, $mailer = false) {
  
			$this->MySQL	= &$mysql;
			$this->Conf		= (object)$arConf['modules'][get_class($this)];
		}
  
		public function AppInfo() {
	
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_apps WHERE class = ?s', get_class($this));
		}
	
		public function getConf() {
	
			return $this->Conf;
		}
		
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Users Area /////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getUser( $id ) {
			
			$res = $this->YApps_GetUserById( $id );
			$res->dcs = $this->MySQL->getCol('SELECT dc_id FROM yapps_app_foots_users_dcs WHERE user_id = ?i', (int)$id);
			
			
			return $res;
		}
		
		public function setUser( $POST ) {
			
			$this->MySQL->query('DELETE FROM yapps_app_foots_users_dcs WHERE user_id = ?i', (int)$POST['user_id']);
			foreach ( $POST['dc_id'] as $dc ) $this->MySQL->query('INSERT INTO yapps_app_foots_users_dcs SET ?u', ['user_id'=>(int)$POST['user_id'], 'dc_id'=>(int)$dc]);
			
			return Helper::getRes(0);
		}
		
		public function delUser( $id ) {
		}
		
		public function getUserDCNames( $id ) {
			
			return $this->MySQL->getCol('SELECT ru_name FROM yapps_dcs WHERE id IN (?a)', $this->getUser($id)->dcs);
		}
		
		public function getUserDCs( $id ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_dcs WHERE id IN (?a)', $this->getUser($id)->dcs);
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Hostess Area ///////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getHostesses() {
			
			
		}
		
		public function getHostess( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_users WHERE role_id = ?i AND id = ?i', 4, (int)$id);
		}
		
		public function getHostessByKey( $id ) {
		}
		
		public function getHostessByUser( $id ) {
			
			
		}
		
		public function setHostess( $POST ) {
		}
		
		
		public function authHostess( $POST ) {
			
			if ( $POST['email'] == preg_replace('/[^a-zA-Z0-9а-яА-Я@._\-\s]/', '', $POST['email']) ) {
				
				$user = (object)$this->MySQL->getRow('SELECT * FROM yapps_users WHERE role_id = ?i AND email = ?s', 4, $POST['email']);
				
				if ( $user && $user->active == 1 ) {
					
					if ( password_verify($POST['passwd'], $user->passwd) ) {
						
						$res = $user;
						$res->dcs = $this->getUserDCNames($user->id);
						
					} else {
						
						$res = Helper::getRes(13);
					}
					
				} else {
					
					$res = Helper::getRes(12);
				}
				
			} else {
				
				$res = Helper::getRes(11);
			}
			
			return (object)$res;
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Managers Area //////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getManagers() {
		}
		
		public function getManagersByUser( $user ) {
			
			$dc_ids = $this->MySQL->getCol('SELECT dc_id FROM yapps_app_foots_users_dcs WHERE user_id = ?i', (int)$user->id);
			return $this->getManagersByDCs( $dc_ids );
		}
		
		public function getManagersAPI( $user ) {
			
			$dc_ids = $this->MySQL->getCol('SELECT dc_id FROM yapps_app_foots_users_dcs WHERE user_id = ?i', (int)$user->id);
			$m_ids = $this->MySQL->getCol('SELECT manager_id FROM yapps_app_foots_managers_dcs WHERE dc_id IN (?a)', $dc_ids);
			$s_ids = $this->MySQL->getCol('SELECT id FROM yapps_app_foots_schedules WHERE start < CAST(CURTIME() AS time) AND end > CAST(CURTIME() AS time)');
			$res_ids = $this->MySQL->getCol('SELECT manager_id FROM yapps_app_foots_managers_schedules WHERE schedule_id IN (?a) AND manager_id IN (?a)', $s_ids, $m_ids);
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_foots_managers WHERE id IN (?a)', $res_ids);
		}
		
		public function getManagersByHostessKey( $key ) {
		}
		
		public function getManagersByDCs( $ids ) {
			
			$m_ids = $this->MySQL->getCol('SELECT manager_id FROM yapps_app_foots_managers_dcs WHERE dc_id IN (?a)', $ids);
			return $this->MySQL->getInd('id', 'SELECT * FROM yapps_app_foots_managers WHERE id IN (?a)', $m_ids);
		}
		
		public function getManager( $id ) {
			
			$n = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_foots_managers WHERE id = ?i', (int)$id);
			$res['dcs'] = $this->MySQL->getCol('SELECT dc_id FROM yapps_app_foots_managers_dcs WHERE manager_id = ?i', (int)$id);
			$res['schedules'] = $this->MySQL->getInd('date', 'SELECT * FROM yapps_app_foots_managers_schedules WHERE DATE(date) BETWEEN ?s AND ?s', date('Y-m-'.'01'), date('Y-m-'.$n));
			
			return $res;
		}
		
		public function getManagerCurCount( $id ) {
			
			return $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_app_foots_stat WHERE manager_id = ?i AND DATE(date) = CURDATE()', (int)$id);
		}
		
		public function getManagerDCNames( $id ) {
			
			return $this->MySQL->getCol('SELECT ru_name FROM yapps_dcs WHERE id IN (?a)', $this->getManager($id)['dcs']);
		}
		
		public function setManager( $POST ) {
			
			$arIns['ru_name'] = $POST['ru_name'];
			
			if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_app_foots_managers SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				$lastId = (int)$POST['id'];
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_foots_managers SET ?u', $arIns);
				$lastId = $this->MySQL->insertId();
			}
			
			$this->MySQL->query('DELETE FROM yapps_app_foots_managers_dcs WHERE manager_id = ?i', $lastId);
			foreach ( $POST['dc_id'] as $dc ) $this->MySQL->query('INSERT INTO yapps_app_foots_managers_dcs SET ?u', ['manager_id'=>$lastId, 'dc_id'=>(int)$dc]);
			
			$this->MySQL->query('DELETE FROM yapps_app_foots_managers_schedules WHERE manager_id = ?i', $lastId);
			foreach ( $POST['schedule_id'] as $k => $s ) if ( $s ) $this->MySQL->query('INSERT INTO yapps_app_foots_managers_schedules SET ?u', ['date'=>$POST['date'][$k], 'manager_id'=>$lastId, 'schedule_id'=>(int)$s]);
			
			return Helper::getRes(0);
		}
		
		public function delManager( $id ) {
			
			$this->MySQL->query('DELETE FROM yapps_app_foots_managers WHERE id = ?i', (int)$id);
			$this->MySQL->query('DELETE FROM yapps_app_foots_managers_dcs WHERE manager_id = ?i', (int)$id);
			
			return Helper::getRes(0);
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Schedules Area /////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getSchedules() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_foots_schedules');
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Targets Area ///////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getTargets() {
			
			for ( $i=1; $i<=$this->getConf()->TargetsRoles; $i++ ) $res[$i] = $this->MySQL->getAll('SELECT * FROM yapps_app_foots_targets WHERE role = ?i', $i);
			return $res;
		}
		
		public function getAllTargets() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_foots_targets ORDER BY role ASC');
		}
		
		public function getTarget( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_foots_targets WHERE id = ?i', (int)$id);
		}
		
		public function getSteps() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_foots_steps');	
		}
		public function getStep( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_foots_steps WHERE id = ?i', (int)$id);	
		}
		
		public function setTarget( $POST ) {
			
			$arIns = $POST;
			unset( $arIns['id'], $arIns['form'] );
			
			if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_app_foots_targets SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_foots_targets SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
		}
		
		public function delTarget( $id ) {
			
			return $this->MySQL->query('DELETE FROM yapps_app_foots_targets WHERE id = ?i', (int)$id);
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Stats Area /////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getCurCount( $dc_id ) {
			
			return $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_app_foots_stat WHERE dc_id = ?i AND DATE(date) = CURDATE()', (int)$dc_id);	
		}
		
		public function getHostessStat( $id ) {
			
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_foots_stat WHERE hostess_id = ?i AND DATE(date) = CURDATE()', (int)$id);
			foreach ( $res as $k => $r) {
				
				$res[$k]['manager_name'] = $this->getManager($r['manager_id'])['ru_name'];
				$res[$k]['target_name'] = $this->getTarget($r['target_id'])['ru_name'];
				$res[$k]['time'] = date('H:i', $r['timestamp']);
			}
			
			return $res;
		}
		
		public function setWorkList( $POST ) {
			
			$this->MySQL->query('UPDATE yapps_app_foots_stat SET ?u WHERE id = ?i', ['work_list'=>$POST['work_list']], (int)$POST['id']);
			return Helper::getRes(0);
		}
		
		private function addStat( $data ) {
            
            return $this->MySQL->query('INSERT INTO yapps_app_foots_stat SET ?u', $data);
        }
		
		public function pushStat( $POST ) {
			
			$arIns = $POST;
			unset( $arIns['hostess_token'], $arIns['dc_name'] );
			
			$arIns['hostess_id'] = $this->YApps_GetUserByToken( $POST['hostess_token'] )->id;
			$arIns['dc_id'] = $this->YApps_GetDCByName( $POST['dc_name'] )['id'];
			$arIns['timestamp'] = time();
			$arIns['date'] = date('Y-m-d');
			
			$res = ( $this->addStat($arIns) ) ? 0 : 102;
			
			return Helper::getRes($res);
		}
	}
?>
