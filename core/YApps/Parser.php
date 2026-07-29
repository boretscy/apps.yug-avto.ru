<?php
	class Parser extends App {
		
		function __construct($arConf = array(), SafeMySQL &$mysql, $mssql = false, PHPMailer &$mailer) {
			
			$this->arParse 	= $arConf;
			$this->MySQL		= &$mysql;
			$this->Mailer	= &$mailer;
		}
		
		public function AppInfo() {
			
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_apps WHERE class = ?s', 'Parser');
		}
		
		public function getAdsAvito($brand) {
			
			foreach ($this->arParse['items']['avito']['urls'][$brand] as $url) {
	
				$str = file_get_contents($url);
						
				$doc = new DOMDocument();
				$doc->loadHTML($str);
				$doc->normalizeDocument();
				
				$finder = new DomXPath($doc);
				
				$nodes = $finder->query($this->arParse['items']['avito']['xpath']['query']);
				
				foreach ($nodes as $n) {
					
					$date = $n->childNodes[5]->childNodes[3]->childNodes[3]->textContent;
					
					$city = '';
					
					if ($date === NULL) {
						
						$date = $n->childNodes[5]->childNodes[3]->childNodes[1]->textContent;
						
					} else {
						
						$city = trim($n->childNodes[5]->childNodes[3]->childNodes[1]->textContent);
					}
					
					$date = trim($date);
					
					$tDar = explode(substr($date, -6, 1), $date);
					$tDar[0] = substr($tDar[0], 0, -1);
					
					$old = $tDar[0];
					
					foreach ($this->arParse['config']['Days'] as $k => $v) {
						
						$tDar[0] = str_replace($k, $v, $tDar[0]);
					}
					
					if ($tDar[0] == $old) {
						
						$t = explode(' ', $tDar[0]);
						$date = $this->arParse['config']['Month'][$t[1]].' '.$t[0].', '.$tDar[1];
					
					} else {
						
						$date = $tDar[0].' '.$tDar[1];
					}
					
					unset($t);
						
					$t['ad_id'] = $n->getAttribute('id');
					$t['link'] = 'https://www.avito.ru'.$n->childNodes[5]->childNodes[1]->childNodes[1]->childNodes[1]->attributes[1]->value;
					$t['name'] = explode(', ', trim( $n->childNodes[5]->childNodes[1]->childNodes[1]->nodeValue) )[0];
					$t['year'] = explode(', ', trim( $n->childNodes[5]->childNodes[1]->childNodes[1]->nodeValue) )[1];
					$desc = explode(' руб.', trim( $n->childNodes[5]->childNodes[1]->childNodes[3]->textContent) );
					$t['price'] = (int)str_replace(' ', '', trim($desc[0]) );
					$t['run'] = (int)str_replace(' ', '', trim(explode('км', $desc[1])[0]));
					$t['city'] = $city;
					$t['date'] = strtotime($date);
					$t['active'] = 1;
					
					$arN[] = $t;
					
					unset($t);
				}
			}
			
			return $arN;
		}
		
		
		public function getAdsAuto($brand) {
			
			$arN = [];
			
			$str = file_get_contents('/home/admin/web/apps.yug-avto.ru/public_html/upload/parser/str_'.$brand);
				
			$doc = new DOMDocument();
			$doc->loadHTML($str);
			$doc->normalizeDocument();
			
			$finder = new DomXPath($doc);
			$nodes = $finder->query($this->arParse['items']['auto']['xpath']['query']);
			
			foreach ($nodes as $n) {
				
				if ($n->childNodes[0]->childNodes[1]->childNodes[0]->childNodes[0]->attributes[2]->value !== NULL) {
				
					$id_temp = (array)json_decode($n->getAttribute('data-bem'));
					$t['ad_id'] = $id_temp['listing-item']->id;
					$t['link'] = $n->childNodes[0]->childNodes[1]->childNodes[0]->childNodes[0]->attributes[2]->value;
					$t['name'] = $n->childNodes[0]->childNodes[1]->childNodes[0]->childNodes[0]->textContent;
					$t['year'] = (int)$n->childNodes[0]->childNodes[3]->textContent;
					$t['price'] = (int)preg_replace("/[^0-9]/", '', $n->childNodes[0]->childNodes[2]->childNodes[0]->textContent);
					$t['run'] = (int)preg_replace("/[^0-9]/", '', $n->childNodes[0]->childNodes[4]->textContent);
					$t['city'] = $n->childNodes[1]->childNodes[0]->childNodes[0]->childNodes[0]->childNodes[0]->textContent;
					
					$date = $n->childNodes[1]->childNodes[0]->childNodes[0]->childNodes[0]->childNodes[2]->textContent;
					
					if ($date !== NULL) {
						
						$t['date'] = time()-(int)preg_replace("/[^0-9]/", '', $date)*3600;
						
					} else {
						
						$t['date'] = time()-24*3600;
					}
					
					$t['active'] = 1;
					$arN[] = $t;
					unset($t);
				}
			}
			
			return $arN;
		}
		
		public function getAutoSTR($brand) {
			
			$opts = [
				'http' => [
					'method' => "GET",
					'header' => "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8\r\n" .
								"Accept-Encoding:gzip, deflate, br\r\n" .
								"Accept-Language:ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4\r\n" .
								"Cache-Control:max-age=0\r\n" .
								"Connection:keep-alive\r\n" .
								"Cookie:suid=fd94357352a2aad0bf29a4fff74ec8d8.4e2afcb27c301d7b19bf03bc47cb94ff; af_lpdid=14:359822555; _ym_uid=1487583883404718247; fuid01=58de555d13f12ffe.EwO4BZ2K41A-j9V5Nm-zPnrJaq1FVMySxw3BntSks62gp8SZ-lTN8LFwJKjRvgtZmKIXiqGissKJFleHpbBEVsCt--Lq6a1YuG_b3nFPjlIhpgQGRSasZEtTpBE7wqet; _ga=GA1.2.1453791642.1498115213; yandexuid=5612369211488372866; geo_location=%7B%22city_id%22%3A%5B876%5D%2C%22region_id%22%3A%5B%5D%2C%22country_id%22%3A%5B%5D%7D; soc_reg=0%3A35; autoru_sid=21821332|1503565570839.7776000.294NlXCY5R8JPQL9aFaSjg.GdknvlCvYw2w8I1PPOh__n5vgBXH3spCKv3fN8nJf7o; autoruuid=g599d4c612d810ulgp6vaphkkrhrtepa.9b57f3911737c0f608d52b7ec0b0d91a; _ym_isad=2; gids=35; from_lifetime=1503646237110; from=direct; los=no\r\n" .
								"Host:auto.ru\r\n" .
								"Upgrade-Insecure-Requests:1\r\n" .
								"User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36\r\n"
				]
			];
			
			$context = stream_context_create($opts);
			
			$file = '';
			for ($i = 1; $i <= $this->arParse['items']['auto']['pages'][$brand]; $i++) $file .= file_get_contents($this->arParse['items']['auto']['url'][$brand].$i, false, $context);
			file_put_contents('/home/admin/web/apps.yug-avto.ru/public_html/upload/parser/str_'.$brand, $file);
			
			return true;
		}
		
		public function sendParserNotify($newAds, $changeAds, $site, $brand) {
			
			$this->Mailer->CharSet = 'utf-8';
			$this->Mailer->setFrom('alert@apps.yug-avto.ru', 'Система оповещений Юг-Авто Apps');
			
			$this->Mailer->addAddress('Boreckiy_A@adv.yug-avto.ru', '');
			$this->Mailer->addAddress('artem@pogorelyuk.ru', '');
			$this->Mailer->addAddress('igor.bortnik@premium.yug-avto.ru', '');
			
			$this->Mailer->Subject = 'Отчет парсера объявлений о продаже '.$brand.' c сайта '.$site.'. '.date('d.m.Y');
			
			$mss = 'Объявлений добавлено: <strong>'.count($newAds).'</strong>.<br />Объявлений изменено: <strong>'.count($changeAds).'</strong>.<br /><br /><br /><br />';
			
			if (count($changeAds)>0) {
				
				$mss .= 'Измененные объявления о продаже '.$brand.' на сайте '.$site.'('.count($changeAds).')<br /><br />';
				
				foreach ($changeAds as $Ad) {
					
					$mss .= '<strong>ID:</strong> '.$Ad['ad_id'].'<br />';
					$mss .= '<strong>Ссылка:</strong> <a href="'.$Ad['link'].'">'.$Ad['link'].'</a><br />';
					$mss .= '<strong>Автомобиль:</strong> '.$Ad['name'].'<br />';
					$mss .= '<strong>Год выпуска:</strong> '.$Ad['year'].'<br />';
					$mss .= '<strong>Цена (старая цена), руб.:</strong> '.number_format($Ad['price'], 0, '', ' ').' ('.number_format($Ad['old_price'], 0, '', ' ').')<br />';
					$mss .= '<strong>Пробег:</strong> '.number_format($Ad['run'], 0, '', ' ').'<br />';
					$mss .= '<strong>Город:</strong> '.$Ad['city'].'<br />';
					$mss .= '<strong>Дата размещения:</strong> '.date('d.m.Y H:i', $Ad['date']);
					$mss .= '<hr /><br /><br /><br />';
				}
			
			}
			
			if (count($newAds)>0) {
				
				$mss .= 'Новые объявления о продаже '.$brand.' на сайте '.$site.'('.count($newAds).')<br /><br />';
				
				foreach ($newAds as $Ad) {
					
					$mss .= '<strong>ID:</strong> '.$Ad['ad_id'].'<br />';
					$mss .= '<strong>Ссылка:</strong> <a href="'.$Ad['link'].'">'.$Ad['link'].'</a><br />';
					$mss .= '<strong>Автомобиль:</strong> '.$Ad['name'].'<br />';
					$mss .= '<strong>Год выпуска:</strong> '.$Ad['year'].'<br />';
					$mss .= '<strong>Цена, руб.:</strong> '.number_format($Ad['price'], 0, '', ' ').'<br />';
					$mss .= '<strong>Пробег:</strong> '.number_format($Ad['run'], 0, '', ' ').'<br />';
					$mss .= '<strong>Город:</strong> '.$Ad['city'].'<br />';
					$mss .= '<strong>Дата размещения:</strong> '.date('d.m.Y H:i', $Ad['date']);
					$mss .= '<hr /><br /><br /><br />';
				}
			
			}
			
			$this->Mailer->msgHTML($mss);
			return ($this->Mailer->Send()) ? true : false;
		}
	}

?>