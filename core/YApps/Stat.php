<?php
	class Stat extends App {
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Init ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function __construct( $arConf = [], $mysql = false, $mssql = false, $mailer = false ) {

			$this->MySQL	= &$mysql;
			// $this->Conf		= (object)$arConf['modules'][get_class($this)];
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
		
		public function getStatTable( $app_id ) {
			
			
			$res = 'yapps_app_'.$this->MySQL->getOne('SELECT url_key FROM yapps_apps WHERE id = ?i', (int)$app_id).'_stat';
			if ( (int)$app_id == 11 ) $res = 'yapps_clients';
			
			return $res;
		}
		
		public function AppStat( $app_id ) {
			
		}
		
		public function AppStatByUser( $app_id, $user ) {
		}
		
		public function AppStatByFilter( $arF ) {
			
			$query = $this->MySQL->parse('SELECT * FROM ?n', $this->getStatTable($arF['app']));
			
			foreach ( $arF['params'] as $k => $v ) {
				
				if ( is_array($v) ) $w[] = $this->MySQL->parse('?n IN (?a)', $k, $v);
				if ( is_int($v) ) $w[] = $this->MySQL->parse('?n = ?i', $k, $v);
				if ( is_string($v) ) $w[] = $this->MySQL->parse('?n = ?s', $k, $v);
			}
			
			$w[] = $this->MySQL->parse('timestamp >= ?i', strtotime($arF['date1']));
			$w[] = $this->MySQL->parse('timestamp < ?i', strtotime($arF['date2'].' 23:59:59'));
			
            $where = 'WHERE '.implode(' AND ', $w);
            
            if ( $arF['app'] == 15 ) $where .= ' AND id > 52996';
			
			return $this->MySQL->getAll($query.' '.$where);
		}
	}
?>