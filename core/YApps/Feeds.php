<?php

	class Feeds extends App {
        

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
        
        public function set( $POST ) {

            $arIns = $POST;
            unset( $arIns['form'], $arIns['id'], $arIns['phone'], $arIns['feeds_active']);

            $arIns['feeds_phone'] = Helper::formatPhoneIn( $POST['phone'] );
            $arIns['feeds_active'] = ( $POST['feeds_active'] == 'on' ) ? 1 : 0;

            $this->MySQL->query('UPDATE yapps_dcs SET ?u WHERE id = ?i', $arIns, $POST['id']);
            
            $this->pub();
            
            return Helper::getRes(0);
        }

        public function get() {

            return $this->MySQL->getAll('SELECT * FROM yapps_dcs WHERE feeds_active = ?i', 1);
        }

        public function getURLsWithUTM( $dc ) {

            $res['yandex']['name'] = 'Яндекс Карточки';
            $site = $this->getSite($dc['site_id'])['url'].(($dc['site_id']==31)?'/genesis.html':'');
            $showroom = $this->YApps_GetShowroomBySite($dc['site_id'])['url'];
            $url = 'https://'.$site.((!strripos($site , '?'))?'?':'&');
            if ( $dc['feeds_utm_source'] ) $url .= 'utm_source='.$dc['feeds_utm_source'];
            if ( $dc['feeds_utm_medium'] ) $url .= '&utm_medium='.$dc['feeds_utm_medium'];
            if ( $dc['feeds_utm_campaign'] ) $url .= '&utm_campaign='.$dc['feeds_utm_campaign'];
            if ( $dc['feeds_utm_content'] ) $url .= '&utm_content='.$dc['feeds_utm_content'];
            $res['yandex']['items'][] = $url;
            if ( $showroom ) {
                $url = $showroom.((!strripos($showroom , '?'))?'?':'&');
                if ( $dc['feeds_utm_source'] ) $url .= 'utm_source='.$dc['feeds_utm_source'].'_cars';
                if ( $dc['feeds_utm_medium'] ) $url .= '&utm_medium='.$dc['feeds_utm_medium'];
                if ( $dc['feeds_utm_campaign'] ) $url .= '&utm_campaign='.$dc['feeds_utm_campaign'];
                if ( $dc['feeds_utm_content'] ) $url .= '&utm_content='.$dc['feeds_utm_content'];
                $res['yandex']['items'][] = $url;
            }

            $res['yandex_2']['name'] = 'Яндекс Приоритет';
            $site = $this->getSite($dc['site_id'])['url'].(($dc['site_id']==31)?'/genesis.html':'');
            $url = 'https://'.$site.((!strripos($site , '?'))?'?':'&');
            if ( $dc['feeds_utm_source'] ) $url .= 'utm_source='.$dc['feeds_utm_source'];
            if ( $dc['feeds_utm_medium'] ) $url .= '&utm_medium=cpc';
            if ( $dc['feeds_utm_campaign'] ) $url .= '&utm_campaign=prioritet';
            if ( $dc['feeds_utm_content'] ) $url .= '&utm_content='.$dc['feeds_utm_content'];
            $res['yandex_2']['items'][] = $url;

            $res['google']['name'] = 'Google';
            $url = 'https://'.$site.((!strripos($site , '?'))?'?':'&');
            if ( $dc['feeds_utm_source'] ) $url .= 'utm_source=google_catalog';
            if ( $dc['feeds_utm_medium'] ) $url .= '&utm_medium='.$dc['feeds_utm_medium'];
            if ( $dc['feeds_utm_campaign'] ) $url .= '&utm_campaign='.$dc['feeds_utm_campaign'];
            if ( $dc['feeds_utm_content'] ) $url .= '&utm_content='.$dc['feeds_utm_content'];
            $res['google']['items'][] = $url;
            if ( $showroom ) {
                $url = $showroom.((!strripos($showroom , '?'))?'?':'&');
                if ( $dc['feeds_utm_source'] ) $url .= 'utm_source=google_catalog_cars';
                if ( $dc['feeds_utm_medium'] ) $url .= '&utm_medium='.$dc['feeds_utm_medium'];
                if ( $dc['feeds_utm_campaign'] ) $url .= '&utm_campaign='.$dc['feeds_utm_campaign'];
                if ( $dc['feeds_utm_content'] ) $url .= '&utm_content='.$dc['feeds_utm_content'];
                $res['google']['items'][] = $url;
            }

            $res['2gis']['name'] = '2Гис';
            $url = 'https://'.$site.((!strripos($site , '?'))?'?':'&');
            if ( $dc['feeds_utm_source'] ) $url .= 'utm_source=2gis_catalog';
            if ( $dc['feeds_utm_medium'] ) $url .= '&utm_medium='.$dc['feeds_utm_medium'];
            if ( $dc['feeds_utm_campaign'] ) $url .= '&utm_campaign='.$dc['feeds_utm_campaign'];
            if ( $dc['feeds_utm_content'] ) $url .= '&utm_content='.$dc['feeds_utm_content'];
            $res['2gis']['items'][] = $url;
            if ( $showroom ) {
                $url = $showroom.((!strripos($showroom , '?'))?'?':'&');
                if ( $dc['feeds_utm_source'] ) $url .= 'utm_source=2gis_catalog_cars';
                if ( $dc['feeds_utm_medium'] ) $url .= '&utm_medium='.$dc['feeds_utm_medium'];
                if ( $dc['feeds_utm_campaign'] ) $url .= '&utm_campaign='.$dc['feeds_utm_campaign'];
                if ( $dc['feeds_utm_content'] ) $url .= '&utm_content='.$dc['feeds_utm_content'];
                $res['2gis']['items'][] = $url;
            }

            return $res;
        }

        public function pub() {

            $head = ['name', 'country', 'address', 'address-add', 'phone', 'url', 'rubric-id', 'working-time', 'lat', 'lon'];

            foreach ( $this->get() as $item ) {

                $row = [];

                $row[] = trim($item['feeds_name']);
                $row[] = 'Россия';
                $row[] = trim($item['feeds_address']);
                $row[] = '';
                $row[] = Helper::formatPhoneOut( $item['feeds_phone'] );

                $url = 'https://'.$this->getSite($item['site_id'])['url'];
                if ( $item['feeds_utm_source'] ) $url .= '/?utm_source='.$item['feeds_utm_source'];
                if ( $item['feeds_utm_medium'] ) $url .= '&utm_medium='.$item['feeds_utm_medium'];
                if ( $item['feeds_utm_campaign'] ) $url .= '&utm_campaign='.$item['feeds_utm_campaign'];
                if ( $item['feeds_utm_content'] ) $url .= '&utm_content='.$item['feeds_utm_content'];
                $row[] = $url;

                $row[] = trim($item['feeds_rubric']);
                $row[] = trim($item['feeds_working']);
                $row[] = str_replace(',', '.', trim($item['coords_lat']));
                $row[] = str_replace(',', '.', trim($item['coords_lon']));

                $res[] = $row;
            }

            $handle = new SplFileObject($_SERVER['DOCUMENT_ROOT'].'/upload/Feeds/yug-avto_yandex.csv', 'w');
            $handle->fputcsv($head);
            foreach ( $res as $item ) $handle->fputcsv($item);

            return Helper::getRes(1);
        }
	}
?>