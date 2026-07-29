<?php 
	class HumanResourses extends App {
		
		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, PHPMailer &$mailer ) {

			$this->MySQL	= &$mysql;
			$this->Mailer	= new PHPMailer();
			$this->Conf		= (object)$arConf['modules'][get_class($this)];
		}

		public function AppInfo() {
			
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_apps WHERE class = ?s', get_class($this));
		}

		public function getConf() {

			return $this->Conf;
        }
		
		private function getHtml( $html = 'CongratulationsV2' ) {
			
			return file_get_contents( $this->Conf->Direcrtories['html'].'/'.$html.'.html' );
		}
		
		private function prepareHtml( $arrQ, $html ) {
			
			foreach ( $arrQ as $key => $value ) $html = str_replace('%%'.$key.'%%', $value, $html);
			return JSMin::minifyHTML($html);
		}
		
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Send Area //////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function sendMessage( $id, $type = 'CongratulationsV2' ) {
			
			$POST = $this->getStat( $id );
            $res = Helper::getRes(0);
			
			if ( $POST['sent'] ) {
				
				$res = Helper::getRes(62);
				$res->description .= ' '.date('d.m.Y в H:i', $POST['sent_timestamp']);
				
			} else {
			
				$this->Mailer->CharSet = 'utf-8';
				$this->Mailer->setFrom('alert@apps.yug-avto.ru', 'Оповещения Юг-Авто Apps');
				$this->Mailer->ClearAddresses();
				
				$this->Mailer->addAddress($POST['email'], '');
				
				$this->Mailer->Subject = $this->Conf->Titles[$type];
				$this->Mailer->msgHTML( $POST['html'] );
				
				if ( !$this->Mailer->Send() ) return Helper::getRes(61);

				$sms = $this->Conf->SMS['text'];
				$sms = str_replace('%%HR.NAME%%', explode(' ', $POST['name'])[1], $sms);
				$sms = str_replace('%%HR.POSITION%%', $POST['position'], $sms);
				$sms = str_replace('%%HR.LINK%%', Helper::vgdShorten('https://apps.yug-avto.ru/upload/HumanResourses/Sent/'.$POST['hash'].'.html'), $sms);

				// Helper::sendBeelineSMS( $POST['phone'], $sms, [$this->Conf->SMS['login'], $this->Conf->SMS['password']] );
				Helper::sendQTSMS( $POST['phone'], $sms, [$this->Conf->SMS['login'], $this->Conf->SMS['password']] );
				
				$this->setStatSent( $POST['id'] );
			}
			
			return $res;
		}
		
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Stat Area //////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function pushStat( $POST ) {
			
//			Helper::sp( $POST );
			
			$arIns = $POST;
			unset( $arIns['form'], $arIns['start_date'], $arIns['salary_from'], $arIns['work_graph'], $arIns['id'], $arIns['sent'] );
			
			$user = (array)$this->YApps_GetUserById($POST['user_id']);
			
			$arrQ['name'] = explode(' ', $POST['name'])[1];
			$arrQ['position'] = $POST['position'];
			$arrQ['start_date'] = date( 'd.m.Y', strtotime($POST['start_date']) );
			$arrQ['start_time'] = date( 'H:i', strtotime($POST['start_date']) );
			
			$dc = $this->YApps_GetDC( $POST['dc_id'] );
			$brand = $this->YApps_GetSiteByID($this->MySQL->getOne('SELECT site_id FROM yapps_dcs WHERE id = ?i',$dc['id']))['brand_name'];
			
			$arrQ['start_dc'] = $brand.' по адресу '.$dc['address'];
			$arrQ['boss_name'] = $POST['boss_name'];
			$arrQ['boss_position'] = $POST['boss_position'];
			$arrQ['manager_name'] = $this->getManager($POST['manager_id'])['ru_name'];
			$arrQ['manager_desc'] = $POST['manager_desc'];
			$arrQ['salary'] = number_format((int)preg_replace('/[^0-9]/', '', $POST['salary']), 0, '', ' ');
			$arrQ['work'] = $POST['work_mode'];
			$arrQ['personal_phone_add'] = $user['add_phone'];
			$arrQ['personal_name'] = $user['name'];
			$arrQ['gender'] = $this->getGender($POST['gender_id'])['suffix'];
			$arrQ['dess'] = $this->getDress($POST['dress_id'])['ru_name'];
			$arrQ['work_add'] = $POST['work_add'];
			$arrQ['salary_add'] = $POST['salary_add'];
			$arrQ['salary_from'] = ( $POST['salary_from'] == 'on' ) ? 'от' : '';
			$arrQ['probation'] = $this->getProbation($POST['probation_id'])['ru_name'];
			
			$html = $this->prepareHtml( $arrQ, $this->getHtml() );
			
			$wg = '';
			foreach ( $POST['work_graph'] as $i ) $wg .= $this->getSchedule($i)['ru_name'].'<br />';
			$html = str_replace('{{work_graph}}', $wg, $html);
			
//			Helper::sp( $html ); die;
			
			$arIns['user_id'] = (int)$POST['user_id'];
			$arIns['salary'] = (int)preg_replace('/[^0-9]/', '', $POST['salary']);
			$arIns['salary_from'] = ( $POST['salary_from'] == 'on' ) ? 1 : 0 ;
			$arIns['html'] = $html;
			$arIns['start_timestamp'] = strtotime($POST['start_date']);
			$arIns['timestamp'] = time();
			
			$arIns['phone'] = Helper::formatPhoneIn($arIns['phone']);
			
			$arIns['hash'] = md5($POST['name'].' '.$POST['email'].' '.$POST['phone']);
			Helper::sp( $this->Conf->Direcrtories['file'].'/'.$arIns['hash'].'.html' );
			file_put_contents($this->Conf->Direcrtories['file'].'/'.$arIns['hash'].'.html', $arIns['html']);
			
			if ( $POST['id'] ) {
				
				$arIns['sent'] = ( $POST['sent'] == 'on' ) ? 1 : 0 ;
				$this->MySQL->query('UPDATE yapps_app_humanresourses_stat SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				$lastId = $POST['id'];
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_humanresourses_stat SET ?u', $arIns);
				$lastId = $this->MySQL->insertId();
			}
			
			$this->setSchedulesByStat( $lastId, $POST['work_graph'] );
			
			$res = Helper::getRes(0);
			$res->id = $lastId;
			
			return $res;
		}
		
		public function setStatSent( $id ) {
			
			return $this->MySQL->query('UPDATE yapps_app_humanresourses_stat SET ?u WHERE id = ?i', ['sent'=>1, 'sent_timestamp'=>time()], (int)$id);
		}
		
		public function getStats() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_humanresourses_stat');
		}
		
		public function getStat( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_humanresourses_stat WHERE id = ?i', (int)$id);
		}
		
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Settings Area //////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getSettings() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_humanresourses_settings');
		}
		
		public function getSet( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_humanresourses_settings WHERE dc_id = ?i', (int)$id);
		}
		
		public function setSet( $POST ) {
			
			$arIns = $POST;
			unset( $arIns['form'], $arIns['active'] );
			$arIns['active'] = ( $POST['active'] == 'on' ) ? 1 : 0;
			
			if ( $this->MySQL->getOne('SELECT id FROM yapps_app_humanresourses_settings WHERE dc_id = ?i', (int)$POST['site_id']) ) {
				
				$this->MySQL->query('UPDATE yapps_app_humanresourses_settings SET ?u WHERE dc_id = ?i', $arIns, (int)$POST['site_id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_humanresourses_settings SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
		}
		
		public function delSet( $id ) {
			
			return $this->MySQL->getRow('DELETE FROM yapps_app_humanresourses_settings WHERE dc_id = ?i', (int)$id);
		}
		
		public function getDCs() {
			
			$ids = $this->MySQL->getCol('SELECT dc_id FROM yapps_app_humanresourses_settings');
			return $this->MySQL->getAll('SELECT * FROM yapps_dcs WHERE id IN (?a) ORDER BY ru_name ASC', $ids);
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Genders ////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getGenders() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_humanresourses_genders');
		}
		
		public function getGender( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_humanresourses_genders WHERE id = ?i', (int)$id);
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Managers ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getManagers() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_humanresourses_managers ORDER BY sort ASC');
		}
		
		public function getManager( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_humanresourses_managers WHERE id = ?i', (int)$id);
        }
        
        public function delManager( $id ) {

            return $this->MySQL->query('DELETE FROM yapps_app_humanresourses_managers WHERE id = ?i', (int)$id);
        }

        public function setManager( $POST ) {

            $arIns = $POST;
            unset($arIns['form']);
            
            $this->MySQL->query('REPLACE INTO yapps_app_humanresourses_managers SET ?u', $arIns);

            return Helper::getRes(0);
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Admins /////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function setAdmins( $POST ) {
			
			$this->MySQL->query('TRUNCATE yapps_app_humanresourses_admins');
			foreach ( $POST['admins'] as $a ) $this->MySQL->query('INSERT INTO yapps_app_humanresourses_admins SET ?u', ['user_id'=>(int)$a]);
			
			return Helper::getRes(0);
		}
		
		public function getAdmins() {
			
			return $this->MySQL->getCol('SELECT user_id FROM yapps_app_humanresourses_admins');
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Dresses ////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getDresses() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_humanresourses_dresses');
		}
		
		public function getDress( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_humanresourses_dresses WHERE id = ?i', (int)$id);
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Probations /////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getProbations() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_humanresourses_probations');
		}
		
		public function getProbation( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_humanresourses_probations WHERE id = ?i', (int)$id);
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Probations /////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getSchedules() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_humanresourses_schedules');
		}
		
		public function getSchedule( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_humanresourses_schedules WHERE id = ?i', (int)$id);
		}
		
		public function getScheduleByStat( $id ) {
			
			$ids = $this->MySQL->getCol('SELECT schedule_id FROM yapps_app_humanresourses_stat_schedules WHERE stat_id = ?i', (int)$id);
			return $this->MySQL->getAll('SELECT * FROM yapps_app_humanresourses_schedules WHERE id IN (?a)', $ids);
		}
		
		public function getScheduleIDsByStat( $id ) {
			
			$ids = $this->MySQL->getCol('SELECT schedule_id FROM yapps_app_humanresourses_stat_schedules WHERE stat_id = ?i', (int)$id);
			return $ids;
		}
		
		public function setSchedulesByStat( $id, $arr ) {
			
			$this->MySQL->query('DELETE FROM yapps_app_humanresourses_stat_schedules WHERE stat_id = ?i', (int)$id);
			foreach ( $arr as $a ) $this->MySQL->query('INSERT yapps_app_humanresourses_stat_schedules SET ?u', ['stat_id'=>(int)$id, 'schedule_id'=>(int)$a]);
			
			return true;
		}
	}
	
?>