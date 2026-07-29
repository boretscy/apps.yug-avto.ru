<?php
	class Route {
		
		function __construct( $arConf ) {
			
			$this->Path 	= (object)$arConf['modules']['Route']['Path'];
			$this->Conf		= (object)$arConf['modules'][get_class($this)];
		}
		
		public static function redirect($route) {
			
			header('Location: '.$route);
		}
		public static function jsRedirect($route) {
			
			return '<script>setTimeout( function() { window.location="'.$route.'"; }, 2000);</script>';
		}
		
		public static function getCurrentRoute($request) {
			
			$url = explode('/', $request);
			return (object)['section' => $url[1], 'view' => $url[2], 'action' => $url[3], 'id' => $url[4]];
		}

		public static function getAPIRoute($request) {

			$u = parse_url($request);
			$url = explode('/', $u['path']);
			$res = ['section' => $url[1], 'view' => $url[2], 'action' => $url[3], 'id' => $url[4]];
			if ( $url[5] )  $res['entity'] = $url[5];
			if ( $url[6] )  $res['item'] = $url[6];
			
			return (object)$res;
		}
		
		public function getCurRoute( $request ) {
			
			$f = $this->Conf->scheme.'://'.$this->Conf->host.$request;
			
			$url = explode('/', parse_url($f)['path']);
			$res = ['section' => $url[1], 'view' => $url[2], 'action' => $url[3], 'id' => $url[4]];
			if ( $url[5] )  $res['param05'] = $url[5];
			if ( $url[6] )  $res['param06'] = $url[6];
			if ( $url[7] )  $res['param07'] = $url[7];
			if ( $url[8] )  $res['param08'] = $url[8];
			if ( $url[9] )  $res['param09'] = $url[9];
			if ( $url[10] ) $res['param10'] = $url[10];
			if ( $url[11] ) $res['param11'] = $url[11];
			if ( $url[12] ) $res['param12'] = $url[12];
			if ( $url[13] ) $res['param13'] = $url[13];
			if ( $url[14] ) $res['param14'] = $url[14];
			if ( $url[15] ) $res['param15'] = $url[15];
			
			return (object)$res;
		}
		
		public function getRoute($request) {
			
            $url = $this->getCurrentRoute( $request );
			
			if ( $_SESSION['SSID'] ) {
				
				if ( $url->section ) {
                    
                    return ( $url->view ) ? '/'.$this->Path->views.'/'.$url->section.'/'.$this->Path->view.'_'.$url->view : '/'.$this->Path->views.'/'.$url->section.'/'.$this->Path->view.'_index';
					
				} else {
					
					return '/'.$this->Path->views.'/'.$this->Path->view.'_index';
				}
				
			} else {
				
				return '/'.$this->Path->views.'/_login';
			}
		}
		
		public static function getSubRoute($currentRoute) {
			
			switch ($currentRoute->action) {
				
				case 'new':
					return '/layouts/forms/_'.$currentRoute->view;
					break;
				
				case 'edit':
					return '/layouts/forms/_'.$currentRoute->view;
					break;
					
				case 'view':
					
					return '/layouts/view/_'.$currentRoute->view;
					break;
					
				case 'send':
					
					return '/layouts/send/_'.$currentRoute->view;
					break;
				
				default:
					
					return '/layouts/lists/_'.$currentRoute->view;
					break;
				
			}
		}
	}
?>