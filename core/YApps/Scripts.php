<?php

	class Scripts extends App {
		
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////// PRIVATE AREA //////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////

		private function getJQuery( $sites ) {
			
			return '
			if (typeof window.jQuery == \'undefined\') {
				let script = document.createElement(\'script\');
				script.type = \'text/javascript\';
				script.src = \'https://apps.yug-avto.ru/pub/libs/jquery/3.4.1/jquery.min.js\';
				document.getElementsByTagName(\'head\')[0].appendChild(script);
			}'.PHP_EOL;
		}
		
		private function getCSSLink( $user ) {
			
			return '$(\'head\').append(\'<link href="https://apps.yug-avto.ru/API/get/css/?token='.$user->public_key.'" rel="stylesheet">\');'.PHP_EOL;
		}
		
		/*
		public function getConf() {

			return $this->Conf;
        }
		*/

/////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// PUBLIC AREA //////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		
		
		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, $mailer = false ) {
			
			$this->MySQL		= &$mysql;
			$this->conf		= (object)$arConf['modules']['Scripts'];
        }
        
        public function getConf() {
	
			return $this->Conf;
		}
		
		public function getAppSrcript( $user, $URL ) {
            
            header('Content-Type: application/javascript');

            $host = parse_url( $URL )['host'];
            $land = $this->YApps_GetLandSettings( $this->YApps_GetLandIdByUrl($URL) );
			if ( $land ) $host = $this->YApps_GetSiteByID( $land['site_id'] )['url'];
            $site = (array)$this->YApps_GetSiteByHost($host);
			
			if ( !$site ) $site = $this->YApps_GetSiteByID( $this->YApps_GetSiteIdByShowroom($URL) );
			if ( !$site ) return ''.PHP_EOL;
			
			$script = '';
			if ( $host == 'kia.yug-avto.ru' ) $script .= file_get_contents( __DIR__.'/js/Core.js' ).PHP_EOL;

			if ( $host == 'jaguar.yug-avto.ru' ) $script .= file_get_contents(__DIR__.'/../../pub/libs/jquery/3.5.1/jquery.min.js').PHP_EOL;
			if ( $host == 'landrover.yug-avto.ru' ) $script .= file_get_contents(__DIR__.'/../../pub/libs/jquery/3.5.1/jquery.min.js').PHP_EOL;


			$script .= PHP_EOL.file_get_contents( __DIR__.'/js/'.get_class($this).'.js' ).PHP_EOL;
			$script .= PHP_EOL.file_get_contents('/home/admin/web/apps.yug-avto.ru/public_html/upload/Scripts/'.$site['id'].'.js' ).PHP_EOL;
            $script = str_replace( '%%MATOMO.CODE%%', (($this->YApps_UseSiteScripts($site['id'])->use_matomo)?file_get_contents( __DIR__.'/js/Matomo.js' ):''), $script );
            $script = str_replace( '%%SITE.JQUERY%%', (($this->YApps_UseSiteScripts($site['id'])->use_jquery)?file_get_contents( '/home/admin/web/apps.yug-avto.ru/public_html/pub/libs/jquery/3.4.1/jquery.min.js' ):''), $script );
			$script = str_replace( '%%SITE.ID%%', $site['id'], $script );
			$script = str_replace( '%%SITE.PIWIKID%%', (($land['piwik_id'])?:$site['piwik_id']), $script );
			$script = str_replace( '%%SITE.YANDEXID%%', (($land['yandex_id'])?:$site['yandex_id']), $script );
			$script = str_replace( '%%SITE.GOOGLEID%%', (($land['google_id'])?:$site['google_id']), $script );
			$script = str_replace( '%%SITE.CT_NODE%%', (($land['calltouch_node'])?:$site['calltouch_node']), $script );
			$script = str_replace( '%%SITE.CT_ID%%', (($land['calltouch_id'])?:$site['calltouch_id']), $script );
			$script = str_replace( '%%SITE.CT_SESS%%', (($land['calltouch_sess'])?:$site['calltouch_sess']), $script );
			$script = str_replace( '%%USER.TOKEN%%', $user->public_key, $script );
			$script = str_replace( '%%YAPPS.SVG%%', JSMin::minifyHTML( file_get_contents(__DIR__.'/svg/'.get_class($this).'.php') ), $script );
			$script = str_replace( '%%SITE.START_SCRIPT%%', $site['start_script'], $script );
			$script = str_replace( '%%SITE.END_SCRIPT%%', $site['end_script'], $script );
			
			header('Content-Type: application/javascript');
			return $script.PHP_EOL;
        }
        
        public function getEndScript( $user, $URL ) {

            $host = parse_url( $URL )['host'];
			$land = $this->YApps_GetLandSettings( $this->YApps_GetLandIdByUrl($URL) );
			if ( $land ) $host = $this->YApps_GetSiteByID( $land['site_id'] )['url'];
			$site = (array)$this->YApps_GetSiteByHost($host);
			
			if ( !$site ) $site = $this->YApps_GetSiteByID( $this->YApps_GetSiteIdByShowroom($URL) );
			
            if ( !$site ) return ''.PHP_EOL;
            
            $script = '';
            $script .= file_get_contents( __DIR__.'/js/End.js' ).PHP_EOL;

            return $script.PHP_EOL;
        }
		
		public function getAppCSS( $user, $URL ) {
			
			header('Content-Type: text/css');
			$res .= file_get_contents( __DIR__.'/css/'.get_class($this).'.css' );
			
			return $res;
		}
		
		public function getAppSVG() {
			
			return  JSMin::minifyHTML( file_get_contents(__DIR__.'/svg/'.get_class($this).'.php') );
		}
	}

?>