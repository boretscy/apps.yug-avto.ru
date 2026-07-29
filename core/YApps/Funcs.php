<?php

	class Funcs {
		
		public function __construct( SafeMySQL &$mysql/*, SafeMSSQL &$mssql */ ) {

			$this->MySQL		= &$mysql;
//			$this->MSSQL		= &$mssql;
		}
		
		public function getUserSites( $user ) {
			
			return $this->MySQL->getCol('SELECT site_id FROM yapps_users_sites WHERE user_id = ?i', $user->id);
		}
		
		public function getSite( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE id = ?i', (int)$id);
		}
	}