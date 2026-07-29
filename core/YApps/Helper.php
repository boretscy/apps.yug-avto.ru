<?php
	class Helper {
        
        
		///////////////////////////////////////////////////////////////////////////////////////////
        // Dev Helpers ////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public static function sp( $q, $hide = false, $title = false ) {
			
			echo '<pre '.(($hide)?'style="display:none;"':'').'>';
			if ( $title ) echo $title.'<br />-------------------------------<br />';
			print_r( $q );
			echo '</pre>'.PHP_EOL;
        }
        
        public static function sd( $q, $hide = false, $title = false ) {
			
			echo '<pre '.(($hide)?'style="display:none;"':'').'>';
			if ( $title ) echo $title.'<br />-------------------------------<br />';
			var_dump( $q );
			echo '</pre>'.PHP_EOL;
        }
		public static function sp_h($q, $t = false) {
			
			echo '<pre style="display: none;">'; if ($t) echo $t.'<br />'; print_r($q); echo '</pre>'.PHP_EOL;
		}
		public static function sd_h($q, $t = false) {
			
			echo '<pre style="display: none;">'; if ($t) echo $t.'<br />'; var_dump($q); echo '</pre>'.PHP_EOL;
		}
		
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // App Helpers ////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public static function validate($data) {
		
			$data = trim($data);
			$data = stripslashes($data);
			$data = strip_tags($data);
			$data = htmlspecialchars($data);
			
			return $data;
		}
		
		public static function removeDirectory($dir) {
	
			if ($objs = glob($dir."/*")) {
				foreach($objs as $obj) {
					is_dir($obj) ? self::removeDirectory($obj) : unlink($obj);
				}
			}
			rmdir($dir);
		}
		
		public static function rmDir($dir) {
	
			if ($objs = glob($dir."/*")) {
				foreach($objs as $obj) {
					is_dir($obj) ? self::rmDir($obj) : unlink($obj);
				}
			}
			rmdir($dir);
		}
		
		public static function clearDir($dir) {
	
			if ($objs = glob($dir."/*")) {
				foreach($objs as $obj) {
					is_dir($obj) ? self::rmDir($obj) : unlink($obj);
				}
			}
		}
        
        


		///////////////////////////////////////////////////////////////////////////////////////////
        // Errors Area ////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public static function getError(int $q) {
			
			// TODO: How get this from $arConfig
			
			$errors = [
				
				0 => 'Успешно',
				1 => 'Экспорт успешно завершен',
				
				// AUth errors
				11 => 'Недопустимый e-mail',
				12 => 'Пользователь заблокирован или не существует',
				13 => 'Неправильный пароль',
				14 => 'Неправильная контрольная строка для восстановления пароля',
				15 => 'Контрольная строка для восстановления пароля просрочена',
				16 => 'Ссылка для восстановления пароля отправлена на почту',
				17 => 'Новый пароль отправлен на почту',
				18 => '<small>Неизвестно</small>',
				
				//Sign Up errors
				21 => 'Такой пользователь существует',
				22 => 'Недопустимый пароль. Пароль дожен быть не менее 8 символов длиной и состоять из латинских букв, цифр и символов !@#$%_',
				23 => 'Пароль не прошел валидацию.<br />Пароль дожен быть не менее 8 символов длиной и состоять из латинских букв, цифр и символов !@#$%_',
				24 => 'Неправильный номер телефона',
				25 => 'Истек срок верификации, проверочный код отправлен повторно',
				26 => 'Верификация уже пройдена',
				27 => 'Неправильный проверочный код',
				
				//DB Errors
				41 => 'Ошибка обращения к базе данных',
				
				//FILE Errors
                51 => 'Неверный тип файла',
                52 => 'Не удалось записать файл',
				
				//EMAIL Errors
				61 => 'Ошибка отправки письма',
				62 => 'Это письмо уже было отправлено',
				
				//SEARCH ERRORS
				71 => 'Не найдено',
				
				//API Errors
				101 => 'Получение данных по API запрещено. У вас нет доступа.',
                102 => 'Что-то пошло не так',
                

                // APPs Sale
                200 => 'Попытка передать данные на лендинг закончилась неудачей.',
			];
			
			return $errors[$q];
		}
		
		public static function getRes( int $id ) {
			
			$res = [
				'status' => ( ( $id > 10 ) ? 'error' : 'success' ),
				'error_code' => $id,
				'description' => Helper::getError($id)
			];
			
			return (object)$res;
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Password Area //////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public static function newPass($length) {
	
			$chars = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP!@#$%_?(){}[]';
			
			$size = strlen($chars) - 1;
			$pass = '';
			
			while ($length--)
				$pass .= $chars[rand(0, $size)];
			
			return $pass;
		}
		
		public static function checkNewPass($newPasswd, $confimPasswd, $length = 8) {
	
			return ($newPasswd == $confimPasswd && mb_strlen($newPasswd) >= $length) ? true : false;
		}
        
        


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Phones Area ////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public static function formatPhoneIn( $phone ) {
			
			// +7 (111) 111-11-11 -> 71111111111
			
			$phone = preg_replace('/[^0-9]/', '', $phone);
			$phone = mb_substr($phone, 0, 11);
			
			if ( mb_strlen($phone) == 10 ) $phone = '7'.$phone;
			if ( mb_strlen($phone) == 7 ) $phone = '7861'.$phone;
			if ( $phone[0] == '8' ) $phone = '7'.mb_substr($phone, 1);
			
			return $phone;
		}
		
		public static function formatPhoneOut( $str ) {
			
			// 71111111111 -> +7 (111) 111-11-11
			
			$str = self::formatPhoneIn( $str );
			
			for ($k = 0; $k < mb_strlen((string)$str); $k++) $phone[] = mb_substr($str, $k, 1);
			return '+'.$phone[0].' ('.$phone[1].$phone[2].$phone[3].') '.$phone[4].$phone[5].$phone[6].'-'.$phone[7].$phone[8].'-'.$phone[9].$phone[10];
		}
        
        public static function isFakePhone( $phone ) {

            $phone = self::formatPhoneIn( $phone );
			
			$checkArr[] = ''; $checkArr[] = false; $checkArr[] = NULL; $checkArr[] = 'null'; $checkArr[] = 'NULL';
            for ($i=1; $i<=9; $i++) {
				
				$checkArr[] = '7'.$i.$i.$i.$i.$i.$i.$i.$i.$i.$i;
				for ($k=0; $k<=9; $k++) $checkArr[] = '7'.$i.$k.$k.$k.$k.$k.$k.$k.$k.$k;
			}
            $checkArr[] = '71234567890';
            $checkArr[] = '71234567899';
            $checkArr[] = '71234567898';
            $checkArr[] = '71234567891';
            $checkArr[] = '70123456789';
            $checkArr[] = '79876543210';
            $checkArr[] = '79876543211';
            $checkArr[] = '79876543212';
            $checkArr[] = '71112223344';
            $checkArr[] = '71231231231';
            $checkArr[] = '71231231232';
            $checkArr[] = '71231231233';
            $checkArr[] = '71231231234';
            $checkArr[] = '74564564564';
            $checkArr[] = '74564564565';
            $checkArr[] = '74564564566';
            $checkArr[] = '77897897897';
            $checkArr[] = '77897897898';
            $checkArr[] = '77897897899';
            $checkArr[] = '79879879879';
            $checkArr[] = '79879879878';
            $checkArr[] = '79879879877';
            $checkArr[] = '72223334455';
            $checkArr[] = '71111231231';
            $checkArr[] = '71111231232';
            $checkArr[] = '71111231233';
            $checkArr[] = '74444564564';
            $checkArr[] = '74444564565';
            $checkArr[] = '74444564566';
            $checkArr[] = '77777897897';
            $checkArr[] = '77777897898';
            $checkArr[] = '77777897899';
            $checkArr[] = '73216549870';
            $checkArr[] = '73216549877';
            $checkArr[] = '73216549878';
            $checkArr[] = '73216549879';
            $checkArr[] = '77894561230';
            $checkArr[] = '77894561233';
            $checkArr[] = '77894561232';
            $checkArr[] = '77894561231';

            return in_array($phone, $checkArr);
        }
		
        public static function isNotFakePhone( $phone ) {

            return !self::isFakePhone($phone);
        }
		
		public static function sendBeelineSMS( $phone, $message, $params = ['KRD_Yug_Avto1', '9654633580'], $sender = 'Yug-Avto1' ) {
			
			$sms = new Beesms($params[0], $params[1]);
			$r = $sms->post_message($message, $phone, $sender);
			
			return self::getRes(0);
        }

		public static function sendQTSMS( $phone, $message, $params = ['KRD_Yug_Avto1', '9654633580'], $sender = 'Yug-Avto1' ) {
			
			$sms = new QTSMS($params[0], $params[1]);
			$r = $sms->post_message($message, $phone, $sender);
			
			// return self::getRes(0);
			return $r;
        }
        





		///////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public static function VPost( $POST ) {
			
			foreach ( $POST as $k => $v ) $POST[stripslashes( htmlspecialchars( strip_tags( trim( $k ))))] = stripslashes( htmlspecialchars( strip_tags( trim( $v ))));
			return $POST;
		}
		
		public static function checkImageSize( $file, $width, $height ) {
			
			return ( getimagesize($file)[0] <= $width && getimagesize($file)[1] <= $height ) ? true : false;
		}
		
		public static function parseURL( $q ) {
			
			return (object)parse_url($q);
		}
		
		public static function parseHostLink( $q ) {
			
			$res = parse_url($q)['scheme'];
			$res .= '://'.parse_url($q)['host'].'/';
			
			return $res;
		}
		
		public static function parseHostPathLink( $q ) {
			
			$res = parse_url($q)['scheme'];
			$res .= '://'.parse_url($q)['host'].parse_url($q)['path'];
			
			return $res;
		}
		
		public static function parseWidgetURL( $q ) {
			
			$res = parse_url($q)['scheme'];
			$res .= '://'.parse_url($q)['host'];
			$res .= parse_url($q)['path'];
			//if ( parse_url($q)['query'] ) $res .='?'.parse_url($q)['query'];
			
			if ( !parse_url($q)['path'] || parse_url($q)['path'] == '/' ) $res = '/';
			
			return $res;
		}

		public static function getShortLink( $URL ) {

			return file_get_contents( 'https://is.gd/create.php?format=simple&url='.urlencode( $URL ) );
		}

		public static function vgdShorten($url,$shorturl = null)
		{
			//$url - The original URL you want shortened
			//$shorturl - Your desired short URL (optional)

			//This function returns an array giving the results of your shortening
			//If successful $result["shortURL"] will give your new shortened URL
			//If unsuccessful $result["errorMessage"] will give an explanation of why
			//and $result["errorCode"] will give a code indicating the type of error

			//See https://v.gd/apishorteningreference.php#errcodes for an explanation of what the
			//error codes mean. In addition to that list this function can return an
			//error code of -1 meaning there was an internal error e.g. if it failed
			//to fetch the API page.

			$url = urlencode($url);
			$basepath = "https://v.gd/create.php?format=simple";
			//if you want to use is.gd instead, just swap the above line for the commented out one below
			//$basepath = "https://is.gd/create.php?format=simple";
			$result = array();
			$result["errorCode"] = -1;
			$result["shortURL"] = null;
			$result["errorMessage"] = null;

			//We need to set a context with ignore_errors on otherwise PHP doesn't fetch
			//page content for failure HTTP status codes (v.gd needs this to return error
			//messages when using simple format)
			$opts = array("http" => array("ignore_errors" => true));
			$context = stream_context_create($opts);

			if($shorturl)
				$path = $basepath."&shorturl=$shorturl&url=$url";
			else
				$path = $basepath."&url=$url";

			$response = @file_get_contents($path,false,$context);

			if(!isset($http_response_header))
			{
				$result["errorMessage"] = "Local error: Failed to fetch API page";
				return($result);
			}

			//Hacky way of getting the HTTP status code from the response headers
			if (!preg_match("{[0-9]{3}}",$http_response_header[0],$httpStatus))
			{
				$result["errorMessage"] = "Local error: Failed to extract HTTP status from result request";
				return($result);
			}

			$errorCode = -1;
			switch($httpStatus[0])
			{
				case 200:
					$errorCode = 0;
					break;
				case 400:
					$errorCode = 1;
					break;
				case 406:
					$errorCode = 2;
					break;
				case 502:
					$errorCode = 3;
					break;
				case 503:
					$errorCode = 4;
					break;
			}

			if($errorCode==-1)
			{
				$result["errorMessage"] = "Local error: Unexpected response code received from server";
				return($result);
			}

			$result["errorCode"] = $errorCode;
			if($errorCode==0)
				$result["shortURL"] = $response;
			else
				$result["errorMessage"] = $response;

			return($result['shortURL']);
		}
		
		public static function ArrToString( $arr, $key = false ) {
			
			$res = '';
			foreach ( $arr as $r ) $res .= (($key)?$r[$key]:$r).' / ';
			$res = mb_substr($res, 0, -3);
			
			return $res;
		}

		public static function formatNumber( $q ) {

			return number_format((float)$q, 0, '', ' ');
		}
		
		public static function findEmails( $text ) {
			
			preg_match_all( '/\b([a-z0-9._-]+@[a-z0-9.-]+)\b/i', $text, $res );
			return $res[0];
		}
		
		public static function getWorld( $q = 0, $flag = 'd' ) {
			
			$res = [
				'd' => ['день', 'дня', 'дней'],
				'h' => ['час', 'часа', 'часов'],
				'm' => ['минута', 'минуты', 'минут'],
				's' => ['секунда', 'секунды', 'секунд'],
				'a' => ['автомобиль', 'автомобиля', 'автомобилей'],
				'hot' => ['горячее предложение', 'горячих предложения', 'горячих предложений'],
                'offer' => ['предложение', 'предложения', 'предложений'],
                'record' => ['запись', 'записи', 'записей'],
                'feedback' => ['отзыв', 'отзыва', 'отзывов'],
				'files' => ['файл', 'файла', 'файлов'],
			];
			
			$t1 = [1];
			$t2 = [2,3,4];
            
            for ( $i=20; $i<=5000; $i+=10 ) array_push( $t1, $i+1 );
			for ( $i=20; $i<=5000; $i+=10 ) foreach ( [2,3,4] as $k ) if ( $i % 100 != 10 ) array_push( $t2, $k+$i );
			
			$test = [$t1, $t2];
			
			if ( in_array( (int)$q, $test[0] ) ) return $res[$flag][0];
			if ( in_array( (int)$q, $test[1] ) ) return $res[$flag][1];
			return $res[$flag][2];
		}

		public static function toPrepositional($str) {


			if (in_array( substr($str, -1), ['и','о','е','ё','э'])) return $str;
			if (in_array( substr($str, -3), ['ово','ево','ино','ыно'])) return $str;
		
			$custom_cities = [
				'Ростов-на-дону' => 'Ростове-на-дону',
				'Сочи' => 'Сочи'
			];
			if (isset($custom_cities[$str])) return $custom_cities[$str];
		
			$replace = array();
			$replace['2'][] = array('ия','ии');
			$replace['2'][] = array('ия','ии');
			$replace['2'][] = array('ий','ом');
			$replace['2'][] = array('ое','ом');
			$replace['2'][] = array('ая','ой');
			$replace['2'][] = array('ль','ле');
			$replace['1'][] = array('а','е');
			$replace['1'][] = array('о','е');
			$replace['1'][] = array('и','ах');
			$replace['1'][] = array('ы','ах');
			$replace['1'][] = array('ь','и');
		
			foreach ($replace as $length => $replacement) {
				$str_length = mb_strlen($str, 'UTF-8');
				$find = mb_substr($str, $str_length - $length, $str_length, 'UTF-8');
				foreach($replacement as $try) {
					if ( $find == $try[0] ) {
						$str = mb_substr($str, 0, $str_length - $length, 'UTF-8');
						$str .= $try['1'];
						return $str;
					}
				}
			}
			if ($find == 'е') {
				return $str;
			} else {
				return $str.'е';
			}
		
		}

		public static function getCalendar() {
			
			# Последний день месяца
			$month = date('m',time()) + 1;
			$lastday = date('d',mktime(0, 0, 0, $month, 0, date('Y',time())));
			$lastday = $lastday + 1;
			 
			# 1. Первая неделя
			$num = '0';
			for($i = 0; $i < 7; $i++)
			{
				# Вычисляем номер дня недели для числа
				$dayofweek = date('w',mktime(0, 0, 0, $month, $day_count, $year));
				# Приводим к числа к формату 1 - понедельник, ..., 6 - суббота
				$dayofweek = $dayofweek - 1;
				if($dayofweek == -1) $dayofweek = 6;
				if($dayofweek == $i)
				{	
				# Если дни недели совпадают,заполняем массив $week числами месяца
				$week[$num][$i] = $day_count;
				$day_count++;
				}else{
				$week[$num][$i] = "";
				}
			}
			 
			# 2. Последующие недели месяца
			while(true)
			{
				$num++;
				for($i = 0; $i < 7; $i++)
				{
				$week[$num][$i] = $day_count;
				$day_count++;
				# Если достигли конца месяца - выходим из цикла
				if($day_count > $lastday) $week[$num][$i] = "";
				}
			 
			   # Если достигли конца месяца - выходим из цикла
				if($day_count > $lastday) break;
			}
			
			return $week;
		}
		
		public static function getWeek() {
			
			return ['ПН', 'ВТ', 'СР', 'ЧТ', 'ПТ', 'СБ', 'ВС'];
		}

		public static function getMean( $q ) {

			return array_sum($q) / sizeof($q);
		}

		public static function getMedian( $q ) {

			sort($q);
			$c = sizeof($q);

			return ( !($c & 1) ) ? ($q[intval($c/2)]+$q[intval($c/2)+1])/2 : $q[intval($c/2)+1];
		}

		public static function MBUcfirst($str, $encoding='UTF-8') {
			/**
			* MBUcfirst - преобразует первый символ в верхний регистр
			* @param string $str - строка
			* @param string $encoding - кодировка, по-умолчанию UTF-8
			* @return string
			*/
			$str = mb_ereg_replace('^[\ ]+', '', $str);
			$str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding).
				mb_substr($str, 1, mb_strlen($str), $encoding);
			return $str;
		}


		public static function hexToRgb($color) {
			// проверяем наличие # в начале,
			// если есть, то отрезаем ее
			if ($color[0] == '#') {
				$color = substr($color, 1);
			}
			
			// разбираем строку на массив
			if (strlen($color) == 6) { 
				// если hex цвет в полной форме - 6 символов
				list($red, $green, $blue) = [
					$color[0] . $color[1],
					$color[2] . $color[3],
					$color[4] . $color[5]
				];
			} elseif (strlen($color) == 3) { 
				// если hex цвет в сокращенной 
				// форме - 3 символа
				list($red, $green, $blue) = [
					$color[0]. $color[0],
					$color[1]. $color[1],
					$color[2]. $color[2]
				];
			} else {
				return false; 
			}
		  
			// переводим шестнадцатеричные числа в десятичные
			$red = hexdec($red); 
			$green = hexdec($green);
			$blue = hexdec($blue);
			  
			// вернем результат
			return [$red, $green, $blue];
		}
		
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Geo Area ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        public static function getGeo( $ip ) {
            
            $Geo = json_decode( file_get_contents('https://api.sypexgeo.net/json/'.$ip) );

			if ( $Geo->country->name_ru ) $res['country'] = $Geo->country->name_ru;
			if ( $Geo->region->name_ru ) $res['region'] = $Geo->region->name_ru;
            if ( $Geo->city->name_ru ) $res['city'] = $Geo->city->name_ru;
            
            return $res;
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // UTM Area ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        public static function getUtm( $url, $prefix = false ) {
            
            $GET = explode( '&', parse_url($url)['query'] );
			foreach ( $GET as $g ) {

				$t = explode( '=', $g );
				if ( explode('_', $t[0])[0] == 'utm' ) {
                    
                    $key = (($prefix)?$prefix.'_':'').$t[0];
                    $res[$key] = $t[1];
                }
            }
            
            return ( $res ) ? $res : false;
        }
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // User Id & Client Id ////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public static function newGlobalID( $arr ) {
			
			$salt = '3db760d8b585e19445db905558b35dc8';
			$str = $arr['name'].$arr['phone'].time().$salt;
			
			return md5( $str );
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Timeouts ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public static function Timeout( $time ) {
			
			$res['Days'] = intdiv($time, 24*60*60);
			$res['Hours'] = intdiv($time - $res['Days']*24*60*60, (60*60));
			$res['Minuts'] = intdiv($time - $res['Days']*24*60*60 - $res['Hours']*60*60, 60);
			$res['Seconds'] = $time - $res['Days']*24*60*60 - $res['Hours']*60*60 - $res['Minuts']*60;
			
			return $res;
		}

		public static function shortTimeout( $q ) {

			$time = $q - time();

			$res['d'] = intdiv($time, 24*60*60);
			$res['h'] = intdiv($time - $res['d']*24*60*60, (60*60));
			$res['m'] = intdiv($time - $res['d']*24*60*60 - $res['h']*60*60, 60);
			$res['s'] = $time - $res['d']*24*60*60 - $res['h']*60*60 - $res['m']*60;

			
			return $res;
		}
		
		public static function TimeoutObj( $time ) {
			
			return (object)self::Timeout( $time );
		}


		public static function parseCSS( $css ){
			
			preg_match_all( '/(?ims)([a-z0-9\s\.\:#_\-@,]+)\{([^\}]*)\}/', $css, $arr);
			$result = [];
			foreach ( $arr[0] as $i => $x ){

				$selector = trim($arr[1][$i]);
				$rules = explode(';', trim($arr[2][$i]));
				$rules_arr = [];
				foreach ( $rules as $strRule ) {

					if ( !empty($strRule) ) {

						$rule = explode(":", $strRule);
						$rules_arr[trim($rule[0])] = trim($rule[1]);
					}
				}
				
				$selectors = explode(',', trim($selector));
				foreach ( $selectors as $strSel ) $result[$strSel] = $rules_arr;
			}
			return $result;
		}
	}
?>