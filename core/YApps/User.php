<?php 
	
	class User extends App {
		
		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, PHPMailer &$mailer ) {
			
			$this->MySQL		= &$mysql;
			$this->Conf		= (object)$arConf['modules']['User'];
			$this->secret	= $arConf['App']['secret'];
			$this->Mailer	= new PHPMailer();
		}
		
		public function AppInfo() {
			
			return (object)['url_key'=>'user', 'class'=>'User', 'ru_name'=>'Пользователь'];
		}
		
		// Get User Area 
		public function getById( $id ) {
			
			$res = $this->MySQL->getRow( 'SELECT * FROM yapps_users WHERE id = ?i', (int)$id );
			$res['role'] = $this->getRole($res['role_id']);
			
			return (object)$res;
		}
		
		public function getByEmail( $str ) {
			
			$res = $this->MySQL->getRow( 'SELECT * FROM yapps_users WHERE email = ?s', (string)$str );
			$res['role'] = $this->getRole($res['role_id']);
			
			return (object)$res;
		}
		
		public function getBySSId( $str ) {
			
			$res = $this->MySQL->getRow( 'SELECT * FROM yapps_users WHERE ssid = ?s', (string)$str );
			$res['role'] = $this->getRole($res['role_id']);
			
			return (object)$res;
		}
		
		public function getByKey( $str ) {
			
			$res = $this->MySQL->getRow( 'SELECT * FROM yapps_users WHERE public_key = ?s', (string)$str );
			$res['role'] = $this->getRole($res['role_id']);
			
			return (object)$res;
		}
		
		public function get($arrQ) {
			
			$key = array_keys($arrQ);
			
			if ($key[0] == 'id') {
				
				$res = $this->MySQL->getRow('SELECT * FROM yapps_users WHERE '.$key[0].' = '.(int)$arrQ[$key[0]]);
				
			} else {
				
				$query = 'SELECT * FROM yapps_users WHERE ';
				
				foreach ($arrQ as $key => $value) {
					
					$query .= $key.' = "'.$value.'" AND ';
				}
				$query = substr($query, 0, -5);
				
				$res = $this->MySQL->getRow($query);
			}
			
			$res['role'] = $this->getRole($res['role_id']);
			
			return (object)$res;
		}
		
		public function getAll($arrQ) {

			if ($arrQ == 'all') {
				
				$query = 'SELECT * FROM yapps_users';
				if ( $GLOBALS['AUTH_USER']->role_id>1 ) $query .= ' WHERE hidden = 0';
				$tmp = $this->MySQL->getAll( $query );
				
				foreach ($tmp as $v) {
					
					$v['role'] = $this->getRole($v['role_id']);
					$res[] = (object)$v;
				}
				
			} else {
			
				$key = array_keys($arrQ);
				
				if ($key[0] == 'ids') {
					
					foreach ($arrQ['ids'] as $id) $res[] = $this->get(['id' => $id]);
					
				} else {
					
					foreach ($arrQ[$key[0]] as $q) $res[] = $this->get([$key[0] => $q]);
				}
			}
			
			return $res;
		}
		
		
		
		
		// Add / Update Area
		public function add($POST) {
			
			if ( $POST['email'] == preg_replace('/[^a-zA-Z0-9а-яА-Я@._\-\s]/', '', $POST['email']) ) {
				
				if ( !$this->getByEmail( mb_strtolower($POST['email']) )->email ) {
					
					if ( $POST['passwd'] == preg_replace('/[^a-zA-Z0-9!@#$%_\s]/', '', $POST['passwd']) ) {
						
						if ( Helper::checkNewPass($POST['passwd'], $POST['confim_passwd']) ) {
							
							$arIns['name'] = Helper::validate($POST['name']);
							$arIns['email'] = preg_replace("/[^a-zA-Z0-9а-яА-Я@._\-\s]/", "", mb_strtolower($POST['email']));
							$arIns['passwd'] = password_hash($POST['passwd'], PASSWORD_DEFAULT);
							$arIns['role_id'] = 3;
							$arIns['active'] = 1;
							$arIns['ssid'] = md5( $this->secret.$arIns['email'].date('Y-m-d, H:i:s') );
							$arIns['directory'] = md5( $arIns['email'].date('Y-m-d, H:i:s') );
							$arIns['public_key'] = md5( $arIns['name'].$arIns['email'].$arIns['ssid'].$arIns['directory'] );
							$arIns['register_timestamp'] = time();
							
							if ( !file_exists($this->Conf->PUB_DIR.'/'.$arIns['directory']) ) mkdir( $this->Conf->PUB_DIR.'/'.$arIns['directory'] );
							if ( !file_exists($this->Conf->USERS_DIR.'/'.$arIns['directory']) ) mkdir( $this->Conf->USERS_DIR.'/'.$arIns['directory'] );
							
							$this->MySQL->query('INSERT INTO yapps_users SET ?u', $arIns);
							$_SESSION['SSID'] = $arIns['ssid'];
							
							$res = [
								'status' => 'success',
								'error_code' => 0,
								'description' => 'Пользователь успешно зарегистирован и авторизован'
							];
							
						} else { 
						
							$res = [
								'status' => 'error',
								'error_code' => 23,
								'description' => Helper::getError(23)
							];
						}
						
					} else { 
					
						$res = [
							'status' => 'error',
							'error_code' => 22,
							'description' => Helper::getError(23)
						];
					}
					
				} else {
					
					$res = [
						'status' => 'error',
						'error_code' => 21,
						'description' => Helper::getError(21)
					];
				}
				
			} else { 
			
				$res = [
					'status' => 'error',
					'error_code' => 11,
					'description' => Helper::getError(11)
				];
			}
			
			return (object)$res;
		}
		
		public function update($POST, $FILES = [], $ssid, $isAdmin = false) {
			
			$user = $this->getById( $POST['id'] );
			
			$arIns['name'] = $POST['name'];
			$arIns['phone'] = $POST['phone'];
			$arIns['add_phone'] = $POST['add_phone'];
            if ( $isAdmin && $POST['role_id'] ) $arIns['role_id'] = (int)$POST['role_id'];
			if ( $isAdmin ) $arIns['active'] = ( $POST['active'] == 'on' ) ? 1 : 0;
			
			if ( $FILES && $FILES['avatar']['error'] == 0 ) {
				
				$arIns['avatar'] = $this->Conf->USERS_DIR.'/'.$user->directory.'/'.$FILES['avatar']['name'];
				$file = $_SERVER['DOCUMENT_ROOT'].$this->Conf->USERS_DIR.'/'.$user->directory.'/'.$FILES['avatar']['name'];

				if ( file_exists($file) ) unlink( $file );
                move_uploaded_file( $FILES['avatar']['tmp_name'], $file );
			}
			
			$res = Helper::getRes(0);
			
			if ( $POST['passwd'] ) {
				
				if ( password_verify($POST['old_passwd'], $user->passwd) ) {
					
					if ($POST['passwd'] == preg_replace('/[^a-zA-Z0-9!@#$%_\s]/', '', $POST['passwd'])) {
						
						if (Helper::checkNewPass($POST['passwd'], $POST['confim_passwd'])) {
							
							$arIns['passwd'] = password_hash($POST['passwd'], PASSWORD_DEFAULT);
							
						} else {
							
							$res = Helper::getRes(23);
						}
						
					} else {
						
						$res = Helper::getRes(23);
					}
					
				} else {
					
					$res = Helper::getRes(13);
				}
				
			}
			
			if ( $isAdmin ) {
				
				$this->MySQL->query('DELETE FROM yapps_users_sites WHERE user_id = ?i', $user->id);
				foreach ( $POST['sites'] as $id ) $this->MySQL->query('INSERT INTO yapps_users_sites SET ?u', ['user_id'=>$user->id, 'site_id'=>(int)$id]);
				
				$this->MySQL->query('DELETE FROM yapps_apps_users WHERE user_id = ?i', $user->id);
				foreach ( $POST['apps'] as $id ) $this->MySQL->query('INSERT INTO yapps_apps_users SET ?u', ['user_id'=>$user->id, 'app_id'=>(int)$id]);
			}
			
			if ( $res->status == 'success' ) $this->MySQL->query('UPDATE yapps_users SET ?u WHERE id = ?i', $arIns, $user->id);
			
			return (object)$res;
		}
		
		public function delete( $id ) {
			
			$user = $this->getById( (int)$id );
			
			Helper::removeDirectory( __DIR__.'/../..'.$this->Conf->USERS_DIR.'/'.$user->directory );
			Helper::removeDirectory( __DIR__.'/../..'.$this->Conf->PUB_DIR.'/'.$user->directory );
			
			$this->MySQL->query('DELETE FROM yapps_users WHERE id = ?i', (int)$id);
			$this->MySQL->query('DELETE FROM yapps_users_sites WHERE user_id = ?i', (int)$id);
			$this->MySQL->query('DELETE FROM yapps_apps_users WHERE user_id = ?i', (int)$id);
			
			return Helper::getRes(0);
		}
		
		
		/////////////////////////////////
		public function isAdmin($ssid) {
			
			return ($this->MySQL->getOne('SELECT role_id FROM yapps_users WHERE ssid = ?s', $ssid) == 1) ? true : false;
		}
		
		public function isRoot($ssid) {
			
			return ( $this->MySQL->getOne('SELECT role_id FROM yapps_users WHERE ssid = ?s', $ssid) == 1 ) ? true : false;
		}
		
		public function isCorporate($ssid) {
			
			$role_id = $this->MySQL->getOne('SELECT role_id FROM yapps_users WHERE ssid = ?s', $ssid);
			
			return ( $role_id < 3 ) ? true : false;
		}
		
		public function isAdministrator($ssid) {
			
			return ( $this->MySQL->getOne('SELECT role_id FROM yapps_users WHERE ssid = ?s', $ssid) < 3 ) ? true : false;
		}
		
		public function isOperator($ssid) {
			
			$role_id = $this->MySQL->getOne('SELECT role_id FROM yapps_users WHERE ssid = ?s', $ssid);
			
			return ( $role_id == 4 ) ? true : false;
		}
		
		public function issetUser($arrQ) {
			
			$key = array_keys($arrQ);
			
			if ($key[0] == 'id') {
				
				return ($this->MySQL->getRow('SELECT * FROM yapps_users WHERE '.$key[0].' = '.(int)$arrQ[$key[0]])) ? true : false;
				
			} else {
				
				$query = 'SELECT * FROM yapps_users WHERE ';
				
				foreach ($arrQ as $key => $value) {
					
					$query .= $key.' = "'.$value.'" AND ';
				}
				$query = substr($query, 0, -5);
				
				return ($this->MySQL->getRow($query)) ? true : false;
			}
		}
		
		
		public function isRootUser( $user ) {
			
			return ( $user->role->id == 1 ) ? true : false;
		}
		
		public function isAdminUser( $user ) {
			
			return ( $user->role->id <= 2 ) ? true : false;
		}
		
		/////////////////////////////////
		
		public function getRoles() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_user_roles');
		}
		
		public function getRole( $id ) {
			
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_user_roles WHERE id = ?i', (int)$id);
		}
		
		public function getAvailRoles( $user ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_user_roles where id >= ?i', (int)$user->role->id);
		}
		
		
		
		// Auth Area
		public function checkAUth() {
			
			return ($_SESSION['SSID']) ? true : false;
		}
		
		public function unAUth() {
			
			return session_destroy();
		}
		
		public function AUth($POST) {
				
			if ( $POST['email'] == preg_replace('/[^a-zA-Z0-9а-яА-Я@._\-\s]/', '', $POST['email']) ) {
				
				$user = $this->getByEmail( mb_strtolower($POST['email']) );
				
				if ( $user && $user->active == 1 ) {
					
					if ( password_verify($POST['passwd'], $user->passwd) ) {
						
						$_SESSION['SSID'] = $user->ssid;
                        $this->MySQL->query('UPDATE yapps_users SET ?u WHERE id = ?i', ['current_login'=>time(), 'last_login'=>$user->current_login], $user->id);
                        
						$res = Helper::getRes(0);
						
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
		
		private function sendRecovery( $id, $pass = false ) {

			$this->Mailer->CharSet = 'utf-8';
			$this->Mailer->setFrom('alert@apps.yug-avto.ru', 'Оповещения Юг-Авто Apps.');
			$this->Mailer->ClearAddresses();
			
			$user = $this->getById( $id );
			$this->Mailer->addAddress( $user->email, $user->name);
			
			if ( $pass ) {
				
				$this->Mailer->Subject = 'Восстановление пароля Юг-Авто Apps';
				$message = 'Новый пароль для Юг-Авто Apps:<br /><br />';
				$message .= '<strong>'.$pass.'</strong>';
				
			} else {
				
				$this->Mailer->Subject = 'Восстановление пароля Юг-Авто Apps';
				$message = 'Ваша ссылка для восстановления пароля:<br /><br />';
				$message .= '<a href="https://apps.yug-avto.ru/?action=recovery&recovery_string='.$user->recovery_string.'" target="_blank">https://apps.yug-avto.ru/?action=recovery&recovery_string='.$user->recovery_string.'</a><br /><br />';
				$message .= 'Ссылка действительна до <strong>'.date('d.m.Y H:i:s', (int)$user->recovery_time).'</strong>.';
				
			}
			
			$this->Mailer->msgHTML($message);
			return $this->Mailer->Send();
		}
		
		public function Recovery( $POST ) {
			
			if ( $POST['recovery_string'] ) {
				
				if ( $user = $this->MySQL->getRow('SELECT * FROM yapps_users WHERE recovery_string = ?s', $POST['recovery_string']) ) {
					
					if ( time() <= $user['recovery_time'] ) {
						
						$pass = Helper::newPass(10);
						
						$arIns['passwd'] = password_hash( $pass, PASSWORD_DEFAULT );
						$this->MySQL->query('UPDATE yapps_users SET ?u WHERE id = ?i', $arIns, $user['id']);
						$this->sendRecovery( $user['id'], $pass );
						
						$res = Helper::getRes(17);
						$res->status = 'success';
						
					} else {
						
						$res = Helper::getRes(15);
					}
					
				} else {
					
					$res = Helper::getRes(12);
				}
				
			} else {
			
				if ( $user = $this->getByEmail($POST['email']) ) {
				
					$arIns = [
						'recovery_string' => md5($user->email.$user->public_key.$user->name.time()),
						'recovery_time' => time()+$this->Conf->RecoveryTime
					];
					$this->MySQL->query('UPDATE yapps_users SET ?u WHERE id = ?i', $arIns, $user->id);
					
					$this->sendRecovery( $user->id );
					
					$res = Helper::getRes(16);
					$res->status = 'success';
					
				} else {
					
					$res = Helper::getRes(12);
				}
			}
			
			return $res;
		}
		
		
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Billing ////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
	}
?>