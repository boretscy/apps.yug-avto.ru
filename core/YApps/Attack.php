<?php 
	class Attack extends App {
		
		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, PHPMailer &$mailer ) {
			
			$this->MySQL	= &$mysql;
			$this->Mailer	= &$mailer;
			$this->Conf		= (object)$arConf['modules']['Attack'];
		}
		
		public function AppInfo() {
			
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_apps WHERE class = ?s', 'Attack');
		}
		
		public function getTypeAttack( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_attack_types WHERE id = ?i', (int)$id);
		}
		
		public function pushStat( $POST ) {
			
			$host = parse_url( $POST['source_url'] )['host'];
			$arIns['site_id'] = $this->MySQL->getOne('SELECT id FROM yapps_sites WHERE url = ?s', $host);
			
			$arIns['type_id'] = 1;
			$arIns['source_url'] = $POST['source_url'];
			$arIns['hackerIP'] = $POST['ip'];
			$arIns['raw_base64'] = $POST['raw'];
			$arIns['raw_hash'] = md5( $POST['raw'] );
			$arIns['timestamp'] = time();
			
			$this->MySQL->query('INSERT INTO yapps_app_attack_stat SET ?u', $arIns);
			
			return $arIns;
		}
		
		public function isAttack( $POST ) {
			
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_attack_log WHERE hacker_ip = ?s AND timestamp >= ?i', $POST['hacker_ip'], time()-$this->Conf->Period);
			return ( count($res) >= $this->Conf->Count ) ? $res : false;
		}
		
		public function CFBanIP( $POST ) {
			
			$site = $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE id = ?i', (int)$POST[0]['site_id']);
			$type = $this->getTypeAttack( $POST[0]['type_id'] );
			
			// Add CF API Ban ip
			
			$this->Mailer->CharSet = 'utf-8';
			$this->Mailer->setFrom('alert@apps.yug-avto.ru', 'Оповещения Юг-Авто Apps');
			$this->Mailer->ClearAddresses();
			
			foreach ( $this->Conf->Recipients as $email ) $this->Mailer->addAddress($email, '');
			
			$this->Mailer->Subject = 'Внимание! Обнаружена атака на сайт: '.$site['ru_name'];
			
			$message = '<strong>Сайт</strong>: '.$site['ru_name'].'<br /><br />';
			$message .= '<strong>Начало атаки</strong>: '.date('d/m/Y H:i:s', $POST[0]['timestamp']).'<br />';
			$message .= '<strong>Последнее действие</strong>: '.date('d/m/Y H:i:s', $POST[count($POST)-1]['timestamp']).'<br />';
			$message .= '<strong>IP Злоумышленника</strong>: '.$POST[count($POST)-1]['hacker_ip'].'<br />';
			$message .= '<strong>Страница-источник</strong>: <a href="'.$POST[count($POST)-1]['source_url'].'">'.$POST[count($POST)-1]['source_url'].'</a><br /><br />';
			$message .= '<strong>Зафиксировано попыток</strong>: '.count($POST);
			
			$this->Mailer->msgHTML($message);
			return $this->Mailer->Send();
		}
		
		// Statisctics area
		
		public function getStats( $user, $date1, $date2 ) {
			
			$sites = $this->MySQL->getCol('SELECT site_id FROM yapps_users_sites WHERE user_id = ?i', (int)$user->id);
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_attack_log WHERE site_id IN (?a) AND timestamp >= ?i AND timestamp < ?i', $sites, strtotime($date1), strtotime($date2));
		}
	}	