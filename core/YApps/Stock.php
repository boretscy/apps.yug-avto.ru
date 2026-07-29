<?php

	class Stock extends App {
        

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Init ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, $mailer = false) {
  
			$this->MySQL	= &$mysql;
			$this->Conf		= (object)$arConf['modules'][get_class($this)];
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
        // Set ////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        public function saveFile( $id, $FILES = false ) {

            $res = 51;

            if ( $FILES && $FILES['stock']['error'] == 0 ) {

                $res = 52;
                $site = $this->YApps_GetDC( $id )['site_id'];

                $fArr = explode('.', $FILES['stock']['name']);

                if ( !file_exists( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$site ) ) mkdir( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$site );
                $file = __DIR__.'/../..'.$this->Conf->FileDir.'/'.$site.'/'.$id.'.'.$fArr[count($fArr)-1];

                if ( move_uploaded_file( $FILES['stock']['tmp_name'], $file )) $res = 0;
            }



            return Helper::getRes( $res );
        }
	}
?>