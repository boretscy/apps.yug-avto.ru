<?php

	class Cis extends App {


        ////////////////////////////////////////////////////////////////
		// Consts  /////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////

        const URL_BASE              = 'https://autos.autocrm.ru/api/v1';

        const URL_DEALERSHIPS       = '/dealerships';
        const URL_BRANDS            = '/brands';
        const URL_MODELS            = '/models';
        const URL_MODELS_INFO       = '/vehicle/models-info';
        const URL_VEHICLES          = '/vehicles';
        const URL_VEHICLES_NEW      = '/vehicles/all';
        const URL_VEHICLES_USED     = '/tradein/vehicles';

        const METHOD_GET            = 'GET';
        const METHOD_POST           = 'POST';
        const METHOD_PUT            = 'PUT';
        const METHOD_DELETE         = 'DELETE';

        // const DOCUMENT_ROOT         = '/home/admin/web/apps.yug-avto.ru/public_html';

        const SITE_URL              = 'yug-avto.ru';


        ///////////////////////////////////////////////////////////////////////////////////////////
        // Init ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, /* PHPMailer &$mailer*/ $mailer = false ) {
			
			$this->MySQL	= &$mysql;
			$this->Conf		= (object)$arConf['modules'][get_class($this)];
			$this->Mailer	= &$mailer;

            $this->table    = json_decode(
                file_get_contents(YAPPS_DOCUMENT_ROOT.$this->Conf->DataDir.'/table.json')
            );
            
            // Динамически загружаем активную таблицу из БД (так как Go Cron переключает её в базе)
            $dbProdTable = $this->MySQL->getOne("SELECT value FROM yapps_app_cis_tables WHERE name = 'prod'");
            if ($dbProdTable) {
                $this->table->prod = $dbProdTable;
            }
		}

        public function AppInfo() {
	
			return (object)$this->MySQL->getRow('SELECT * FROM yapps_apps WHERE class = ?s', get_class($this));
		}

        ///////////////////////////////////////////////////////////////////////////////////////////
        // System /////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        public function pushStat( $POST ) {

            // Helper::sp( $POST );
            $res = ['status'=>false];

            $host = parse_url( (($POST[0]['value'])?:$POST['src']) )['host'];
			
			$this->Mailer->CharSet = 'utf-8';
			$this->Mailer->setFrom('formsender@yug-avto.ru', 'Юг-Авто');
			$this->Mailer->ClearAddresses();
			
			if ( $POST[2]['value'] == 'Оценим ваш автомобиль' || $POST['form'] == 'Оценим ваш автомобиль' || $POST[2]['value'] == 'Продать автомобиль' || $POST['form'] == 'Продать автомобиль' ) {
                // Helper::sp($POST);
                $this->Mailer->addAddress('galina.ribalko@yug-avto.ru', '');
                $this->Mailer->addAddress('ekaterina.ukrainec@yug-avto.ru', '');
                $this->Mailer->addAddress('anna.chumarina@yug-avto.ru', '');
            } else {
                $this->Mailer->addAddress('callcenter@yug-avto.ru', '');
            }
			
			// $this->Mailer->addAddress('anton.boreckiy@yug-avto.ru', '');
            // $this->Mailer->addAddress('callcenter@yug-avto.ru', '');
			
			$this->Mailer->Subject = (($POST[2]['value'])?:$POST['form']).' / '.$host;

			$message = '<h3>Сайт: '.$host.'. Форма: '.(($POST[2]['value'])?:$POST['form']).'</h3>';
			$message = '<p>Дата: '.date('d.m.Y в H:i:s').'</p>';
            foreach ( $POST as $k => $p ) {
                if ( $p['name'] == 'name' || $k == 'name' ) $message .= 'Имя: '.((is_array($p))?$p['value']:$p).'<br />';
                if ( $p['name'] == 'phone' || $k == 'phone' ) $message .= 'Телефон: '.((is_array($p))?$p['value']:$p).'<br />';

                if ( $p['name'] == 'vehicle' || $k == 'vehicle' ) {

                    $rs = $this->MySQL->getRow('SELECT ext_id, brand_id, model_id, type_id FROM ?n WHERE ext_id = ?i', $this->table->prod, (int)((is_array($p))?$p['value']:$p));
                    $b = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE id = ?i', (int)$rs['brand_id']);
                    $m = $this->MySQL->getRow('SELECT * FROM ?n WHERE id = ?i', 'yapps_app_cis_models_'.(($rs['type_id']==1)?'new':'used'), (int)$rs['model_id']);
                    $link = 'https://'.$host.'/cars/'.(($POST[3]['value'])?:$POST['mode']).'/'.$b['code'].'/'.$m['code'].'/'.$rs['ext_id'];

                    $message .= 'Интересующий автомобиль: <a href="'.$link.'" target="_blank">'.$b['name'].' '.$m['name'].'</a><br />';
                }
                
                if ( $p['name'] == 'car' || $k == 'car' ) $message .= 'Имеющийся автомобиль: '.((is_array($p))?$p['value']:$p).'<br />';
                if ( $p['name'] == 'year' || $k == 'year' ) $message .= 'Год выпуска: '.((is_array($p))?$p['value']:$p).'<br />';
                if ( $p['name'] == 'condition' || $k == 'condition' ) $message .= 'Состояние: '.((is_array($p))?$p['value']:$p).'<br />';
                if ( $p['name'] == 'dealership' || $k == 'dealership' ) $message .= 'Дилерский центр: '.((is_array($p))?$p['value']:$p).'<br />';

                if ( $p['value'] == 'testtesttest' || $p == 'testtesttest') {
                    $this->Mailer->ClearAddresses();
                    $this->Mailer->addAddress('anton.boreckiy@yug-avto.ru', '');
                }
            }
			
			$message .= '<br /><br />';
			$message .= 'Страница-источник: <a href="'.(($POST[0]['value'])?:$POST['src']) .'" target="_blank">'.(($POST[0]['value'])?:$POST['src']) .'</a>';

			$this->Mailer->msgHTML($message);
			if ( $this->Mailer->Send() ) $res['status'] = true;

            return $res;
		}

        public function getScript() {

			$script = '';
			foreach ( glob($_SERVER['DOCUMENT_ROOT'].$this->Conf->FrontendDir.'/dist/js/*.js') as $file ) {
				$script .= file_get_contents($file).PHP_EOL;
				$arF = explode('/', $file);
				$script .= '//@ sourceMappingURL='.$this->Conf->FrontendDir.'/dist/js/'.$arF[count($arF)-1].'.map';
			}
			return $script;
		}
		
		public function getConf() {
            
			return $this->Conf;
		}

        public function getTable() {
            
			return $this->table;
		}

        public function save( $new = true, $entity = 'brands', $data = [] ) {
            
            file_put_contents(
                YAPPS_DOCUMENT_ROOT.$this->Conf->DataDir.'/'.(($new)?'new':'used').'/'.$entity.'.json',
                json_encode($data)
            );
        }

        public function _save( $section = 'new', $entity = 'brands', $data = [] ) {
            
            file_put_contents(
                YAPPS_DOCUMENT_ROOT.$this->Conf->DataDir.'/'.$section.'/'.$entity.'.json',
                json_encode($data)
            );
        }

        public function read( $new = true, $entity = 'brands' ) {
            
            return json_decode(
                file_get_contents(YAPPS_DOCUMENT_ROOT.$this->Conf->DataDir.'/'.(($new)?'new':'used').'/'.$entity.'.json'),
                true
            );
        }

        public function _read( $section = 'new', $entity = 'brands' ) {
            
            return json_decode(
                file_get_contents(YAPPS_DOCUMENT_ROOT.$this->Conf->DataDir.'/'.$section.'/'.$entity.'.json'),
                true
            );
        }

        public function toggleTable() {
            
            $table = json_decode(
                file_get_contents(YAPPS_DOCUMENT_ROOT.$this->Conf->DataDir.'/table.json'),
                true
            );

            $n = $table['prod'];
            $table['prod'] = $table['cron'];
            $table['cron'] = $n;
            $table['updated'] = date('Y-m-d H:i:s');
            $table['hash'] = md5($table['updated']);
            $table['time'] = time();

            file_put_contents(
                YAPPS_DOCUMENT_ROOT.$this->Conf->DataDir.'/table.json',
                json_encode($table)
            );

            return $table;
        }

        public function send( $arSend, $recipients = [] ) {
			
			$this->Mailer->CharSet = 'utf-8';
			$this->Mailer->setFrom('cis@apps.yug-avto.ru', 'Оповещения Юг-Авто Apps. Витрина.');
			$this->Mailer->ClearAddresses();
			
			if ( empty($recipients) ) $recipients = [
                'natalya.kobeleva@yug-avto.ru',
                // 'anton.boreckiy@yug-avto.ru'
            ];
			
			foreach ($recipients as $email) $this->Mailer->addAddress($email, '');
			
			$this->Mailer->Subject = 'Оповещения Юг-Авто Apps. Витрина.';

            $message = $arSend['title'].PHP_EOL.PHP_EOL;
            $message .= $arSend['text'];

			$this->Mailer->msgHTML($message);
			
			return $this->Mailer->Send();
		}


        ////////////////////////////////////////////////////////////////
		// Private  ////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////

        private function request(
            $url = null,
            $params = [],
            $timeout = false
        )
        {
            if ( !$url ) return 'error';

            if ( !empty($params) ) $url .= '?'.http_build_query($params);
            
            $curl = curl_init($url);
            
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            if ( $timeout ) curl_setopt($curl, CURLOPT_TIMEOUT, $this->Conf->cURL_timeout);
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->Conf->token,
            ]);
            
            $response = curl_exec($curl);
            $info = curl_getinfo($curl);
            if ( curl_errno($curl) ) {
                echo "Ошибка cURL: " . curl_errno($curl).'. URL: '.$url.PHP_EOL;
                return false;
            }
            curl_close($curl);

            
            


            return json_decode($response, true);
        }

        private function getBrandComparisons( $q ) {

            $vars = $this->MySQL->getAll('SELECT * FROM yapps_app_cis_comparisons WHERE entity = ?s', 'brands');
            foreach ( $vars as $v ) if ( mb_strripos($q, $v['desired']) !== false ) return $this->yappsGetBrand($v['value']);

            return [
                'name' => 'Неизвестно',
                'code' => 'none'
            ];
        }

        private function getColor( $q ) {

            $vars = $this->MySQL->getAll('SELECT * FROM yapps_app_cis_comparisons WHERE entity = ?s', 'colors');
            foreach ( $vars as $v ) if ( mb_strripos($q, $v['desired']) !== false ) return $this->yappsGetColor($v['value']);

            return [
                'name' => 'Неизвестно',
                'code' => 'none',
                'rgb' => '#ffffff'
            ];
        }

        public function getTransmission( $q ) {

            $vars = $this->MySQL->getAll('SELECT * FROM yapps_app_cis_comparisons WHERE entity = ?s', 'transmissions');
            foreach ( $vars as $v ) if ( mb_strripos($q, $v['desired']) !== false ) return $this->yappsGetTransmission($v['value']);

            return [
                'name' => 'Неизвестно',
                'code' => 'none'
            ];
        }

        private function getEngine( $q ) {

            $vars = $this->MySQL->getAll('SELECT * FROM yapps_app_cis_comparisons WHERE entity = ?s', 'engines');
            foreach ( $vars as $v ) if ( mb_strripos($q, $v['desired']) !== false ) return $this->yappsGetEngine($v['value']);

            return [
                'name' => 'Неизвестно',
                'code' => 'none'
            ];

            return $q;
        }

        public function getBody( $q ) {

            $vars = $this->MySQL->getAll('SELECT * FROM yapps_app_cis_comparisons WHERE entity = ?s', 'bodies');
            foreach ( $vars as $v ) if ( mb_strripos($q, $v['desired']) !== false ) return $this->yappsGetBody($v['value']);

            return [
                'name' => 'Неизвестно',
                'code' => 'none'
            ];
        }

        public function getModelAlias( $q ) { // depricated, use generateModelAlias instead

            $q = trim($q);
            $q = str_ireplace(['Новый', 'Новая', 'Обновленный', 'Обновленная', 'Обновлённый', 'Обновлённая'], 'New', $q);
            $q = str_ireplace(['лифтбэк', 'лифтбек'], 'Liftback', $q);
            $q = str_ireplace(['хетчбэк', 'хэтчбек'], 'Hatchback', $q);
            $q = str_ireplace(['Кабриолет'], 'Cabriolet', $q);
            $q = str_ireplace(['Седан', 'седан'], 'Sedan', $q);
            $q = str_ireplace(['3-дв.', '3 дв.'], '3-door', $q);
            $q = str_ireplace(['5-дв.', '5 дв.'], '5-door', $q);
            $q = str_ireplace(['универсал', 'Универсал'], 'wagon', $q);
            $q = str_ireplace(['фургон', 'Фургон'], 'van', $q);
            $q = str_ireplace(['с увеличенной крышей'], 'big-roof', $q);
            $q = str_ireplace(['Бортовая платформа с тентом'], 'flatbed-van', $q);
            $q = str_ireplace(['х'], 'x', $q);
            $q = str_ireplace(['1 серия'], '1-series', $q);
            $q = str_ireplace(['2 серия'], '2-series', $q);
            $q = str_ireplace(['3 серия'], '3-series', $q);
            $q = str_ireplace(['4 серия'], '4-series', $q);
            $q = str_ireplace(['5 серия'], '5-series', $q);
            $q = str_ireplace(['6 серия'], '6-series', $q);
            $q = str_ireplace(['7 серия'], '7-series', $q);
            $q = str_ireplace(['8 серия'], '8-series', $q);
            $q = str_ireplace(['3110 Волга'], '3110-volga', $q);
            $q = str_ireplace(['31105 Волга'], '31105-volga', $q);
            $q = str_ireplace(['A-Класс'], 'a-class', $q);
            $q = str_ireplace(['C-Класс'], 'c-class', $q);
            $q = str_ireplace(['CLA-Класс'], 'cla-class', $q);
            $q = str_ireplace(['E-Класс'], 'e-class', $q);
            $q = str_ireplace(['G-Класс'], 'g-class', $q);
            $q = str_ireplace(['GLA-Класс'], 'gla-class', $q);
            $q = str_ireplace(['GLC-Класс'], 'glc-class', $q);
            $q = str_ireplace(['GLE-Класс'], 'gle-class', $q);
            $q = str_ireplace(['GLK-Класс'], 'glk-class', $q);
            $q = str_ireplace(['GLK-класс'], 'glk-class', $q);
            $q = str_ireplace(['GLB-Класс'], 'glb-class', $q);
            $q = str_ireplace(['GLB-класс'], 'glb-class', $q);
            $q = str_ireplace(['M-Класс'], 'm-class', $q);
            $q = str_ireplace(['S-Класс'], 's-class', $q);
            $q = str_ireplace(['V-Класс'], 'v-class', $q);
            $q = str_ireplace(['Класс'], 'class', $q);
            $q = str_ireplace(['класс'], 'class', $q);
            $q = str_ireplace(['SL-Класс'], 'sl-class', $q);
            $q = str_ireplace(['ГАЗель'], 'gazel', $q);
            $q = str_ireplace(['СГР'], 'sgr', $q);
            $q = str_ireplace(['Изотермический', 'изотермический'], 'isothermal', $q);
            $q = str_ireplace(['шасси', 'Шасси'], 'chassis', $q);
            $q = str_ireplace(['Бортовой', 'бортовой'], 'board', $q);
            $q = str_ireplace(['Борт', 'борт'], 'board', $q);
            $q = str_ireplace(['Пригород', 'пригород'], 'suburb', $q);
            $q = str_ireplace(['Турист', 'турист'], 'tourist', $q);
            $q = str_ireplace(['Евроборт', 'евроборт'], 'euroboard', $q);
            $q = str_ireplace(['Промтоварный', 'промтоварный'], 'goods', $q);
            $q = str_ireplace(['Минивэн', 'минивэн', 'Минивен', 'минивен'], 'minivan', $q);
            $q = str_ireplace(['Рефрижератор', 'рефрижератор'], 'refrigerator', $q);
            $q = str_ireplace(['Маршрутное такси', 'маршрутное такси'], 'taxi', $q);
            $q = str_ireplace(['Цельнометаллический', 'цельнометаллический'], 'cargo', $q);
            $q = str_ireplace(['Грузопассажирский', 'грузопассажирский'], 'cargo-passenger', $q);
            $q = str_ireplace(['Автобус', 'автобус'], 'bus', $q);
            $q = str_ireplace(['ПМ', 'пм'], 'pm', $q);
            $q = str_ireplace(['[рестайлинг]', 'рестайлинг'], 'restyling', $q);
            $q = str_ireplace(['Соболь'], 'sobol', $q);
            
            $q = mb_strtolower(str_replace(' ', '-', $q));

            return $q;
        }
        // public function getBrandAlias( $q ) {

        //     $q = trim($q);
        //     if ( $q == 'ГАЗ' ) $q = 'gaz';
        //     if ( $q == 'ЗАЗ' ) $q = 'zaz';
        //     if ( $q == 'УАЗ' ) $q = 'uaz';
        //     if ( $q == 'ТагАЗ' ) $q = 'tagaz';
        //     if ( $q == 'Москвич' ) $q = 'moskvich';
        //     if ( $q == 'Амберавто' ) $q = 'amberauto';
        //     if ( $q == 'LADA (ВАЗ)' ) $q = 'lada';
            
        //     $q = mb_strtolower(str_replace(' ', '-', $q));
        //     $q = mb_strtolower(str_replace(' & ', '-', $q));

        //     return $q;
        // }
        public function getBrandAlias(string $q): string 
        {
            $brands = [
                'ГАЗ'        => 'gaz',
                'ЗАЗ'        => 'zaz',
                'УАЗ'        => 'uaz',
                'ТагАЗ'      => 'tagaz',
                'Москвич'    => 'moskvich',
                'Амберавто'  => 'amberauto',
                'LADA (ВАЗ)' => 'lada',
            ];

            $q = trim($q);

            if (isset($brands[$q])) {
                return $brands[$q];
            }

            $q = mb_strtolower($q);

            // Сначала заменяем спецсимволы вроде амперсанда на пробел
            $q = preg_replace('/[^\p{L}\p{N}\s\-()]/u', ' ', $q);

            // Заменяем любые группы пробелов на один дефис
            $q = preg_replace('/[\s]+/u', '-', $q);

            // Удаляем дублирующиеся дефисы (включая дефисы вокруг скобок)
            $q = preg_replace('/-+/', '-', $q);

            // Убираем дефисы, которые прижались к скобкам изнутри: (- или -)
            $q = preg_replace('/(\(-|-\))/', '$1', $q); 
            $q = str_replace(['(-', '-)'], ['(', ')'], $q);

            return trim($q, '-');
        }


        // для PHP 7 ** AI
        public function generateModelAlias($text) {
            // 1. Предварительная очистка дублей: "Икс70 / X70" или "Икс70 (X70)"
            if (preg_match('/^(.*?)\s*[\/|\(]\s*(.*?)[ \)]*$/iu', $text, $matches)) {
                $part1 = trim($matches[1]);
                $part2 = trim($matches[2]);

                $temp = strtr(mb_strtolower($part1), [
                    // Фонетические
                    'эй'=>'a', 'ай'=>'a', 'би'=>'b', 'си'=>'c', 'це'=>'c', 'ди'=>'d',
                    'эф'=>'f', 'джи'=>'g', 'эйч'=>'h', 'джей'=>'j', 'кей'=>'k', 'ка'=>'k',
                    'эль'=>'l', 'эм'=>'m', 'эн'=>'n', 'пи'=>'p', 'ку'=>'q', 'кью'=>'q',
                    'эр'=>'r', 'эс'=>'s', 'ти'=>'t', 'ви'=>'v', 'дабл'=>'w', 'икс'=>'x',
                    'игрек'=>'y', 'уай'=>'y', 'зет'=>'z', 'зед'=>'z',
                    // Стандартная транслитерация
                    'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 'ё'=>'yo',
                    'ж'=>'zh', 'з'=>'z', 'и'=>'i', 'й'=>'y', 'к'=>'k', 'л'=>'l', 'м'=>'m',
                    'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u',
                    'ф'=>'f', 'х'=>'kh', 'ц'=>'ts', 'ч'=>'ch', 'ш'=>'sh', 'щ'=>'shch',
                    'ы'=>'y', 'э'=>'e', 'ю'=>'u', 'я'=>'ya', 'ъ'=>'', 'ь'=>''
                ]);

                $clean1 = preg_replace('/[^a-z0-9]/i', '', $temp);
                $clean2 = preg_replace('/[^a-z0-9]/i', '', mb_strtolower($part2));

                if ($clean1 !== '' && $clean1 === $clean2) {
                    $text = $part1;
                }
            }

            $text = mb_strtolower($text);

            // 2. Обработка поколений (например, "3 поколение" -> gen3)
            $generation = '';
            if (preg_match('/(\d+)\s*(поколение|пок|генерация|gen)/u', $text, $genMatches)) {
                $generation = 'gen' . $genMatches[1];
                $text = str_replace($genMatches[0], '', $text);
            }

            // 3. Суффиксы состояния (уходят в конец алиаса)
            $suffixMap = [
                'новый' => 'new',
                'рестайлинг' => 'restyling',
                'рестайл' => 'restyling'
            ];
            $foundSuffixes = [];
            foreach ($suffixMap as $ru => $en) {
                if (strpos($text, $ru) !== false) {
                    $foundSuffixes[] = $en;
                    $text = str_replace($ru, '', $text);
                }
            }

            // 4. Карта замен
            $map = [
                // Символы
                '+' => 'plus',
                '&' => 'and',
                '.' => '-',

                // Длинные фразы и грузовая специфика
                'бортовая платформа с тентом' => 'flatbed-van',
                'с увеличенной крышей' => 'big-roof',
                'цельнометаллический' => 'cargo',
                'грузопассажирский' => 'cargo-passenger',
                'изотермический' => 'isothermal',
                'маршрутное такси' => 'taxi',
                'рефрижератор' => 'refrigerator',
                'универсал' => 'wagon',
                'пригород' => 'suburb',
                'евроборт' => 'euroboard',
                'минивэн' => 'minivan', 'минивен' => 'minivan',
                'автобус' => 'bus', 'турист' => 'tourist',
                'серия' => 'series', 'шасси' => 'chassis',
                'фургон' => 'van', 'бортовой' => 'board', 'борт' => 'board',
                '3-дв.' => '3-door', '3-дв' => '3-door',
                '5-дв.' => '5-door', '5-дв' => '5-door',

                // Фонетические индексы (A-Z)
                'эй'=>'a', 'ай'=>'a', 'би'=>'b', 'си'=>'c', 'це'=>'c', 'ди'=>'d',
                'эф'=>'f', 'джи'=>'g', 'эйч'=>'h', 'джей'=>'j', 'кей'=>'k', 'ка'=>'k',
                'эль'=>'l', 'эм'=>'m', 'эн'=>'n', 'пи'=>'p', 'ку'=>'q', 'кью'=>'q',
                'эр'=>'r', 'эс'=>'s', 'ти'=>'t', 'ви'=>'v', 'дабл'=>'w', 'икс'=>'x',
                'игрек'=>'y', 'уай'=>'y', 'зет'=>'z', 'зед'=>'z',

                // Стандартная транслитерация
                'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 'ё'=>'yo',
                'ж'=>'zh', 'з'=>'z', 'и'=>'i', 'й'=>'y', 'к'=>'k', 'л'=>'l', 'м'=>'m',
                'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u',
                'ф'=>'f', 'х'=>'kh', 'ц'=>'ts', 'ч'=>'ch', 'ш'=>'sh', 'щ'=>'shch',
                'ы'=>'y', 'э'=>'e', 'ю'=>'u', 'я'=>'ya', 'ъ'=>'', 'ь'=>''
            ];

            $text = strtr($text, $map);

            // 5. Финальная чистка от мусора
            $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
            $text = trim($text, '-');

            // Схлопываем дефис между буквой и цифрой (bj-60 -> bj60)
            $text = preg_replace('/([a-z])-(\d+)/i', '$1$2', $text);
            $text = preg_replace('/(\d+)-([a-z])(?=[^a-z]|$)/i', '$1$2', $text);

            // 6. Сборка результата
            $resultParts = array_merge([$text], [$generation], $foundSuffixes);

            return implode('-', array_filter($resultParts, function($v) {
                return $v !== '' && $v !== null;
            }));
        }

        // для PHP 8 ** AI
        // public function generateModelAlias(string $text): string {
        //     // 1. Убираем дубли через разделители: "Икс70 / X70" или "Икс70 | X70"
        //     if (preg_match('/^(?P<part1>.*?)\s*[\/|]\s*(?P<part2>.*?)$/iu', $text, $m)) {
        //         // Упрощенный транслит первой части для сравнения со второй
        //         $temp = strtr(mb_strtolower($m['part1']), [
        //             'эй'=>'a', 'ай'=>'a', 'джи'=>'g', 'икс'=>'x', 'а'=>'a', 'б'=>'b', 'в'=>'v', 
        //             'г'=>'g', 'д'=>'d', 'е'=>'e', 'ё'=>'yo', 'ж'=>'zh', 'з'=>'z', 'и'=>'i', 
        //             'й'=>'y', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p', 
        //             'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'х'=>'kh', 'ц'=>'ts', 
        //             'ч'=>'ch', 'ш'=>'sh', 'щ'=>'shch', 'ы'=>'y', 'э'=>'e', 'ю'=>'yu', 'я'=>'ya'
        //         ]);

        //         $clean1 = preg_replace('/[^a-z0-9]/i', '', $temp);
        //         $clean2 = preg_replace('/[^a-z0-9]/i', '', mb_strtolower($m['part2']));

        //         // Если это одно и то же, оставляем только первую часть
        //         if ($clean1 === $clean2) {
        //             $text = $m['part1'];
        //         }
        //     }

        //     // 2. Убираем дубли в скобках: "ДжиИкс (GX)" -> "ДжиИкс"
        //     if (preg_match('/^(?P<before>.*?)\s*\((?P<inside>.*?)\)$/iu', $text, $m)) {
        //         $temp = strtr(mb_strtolower($m['before']), [
        //             'эй'=>'a', 'ай'=>'a', 'джи'=>'g', 'икс'=>'x', 'а'=>'a', 'б'=>'b', 'в'=>'v', 
        //             'г'=>'g', 'д'=>'d', 'е'=>'e', 'ё'=>'yo', 'ж'=>'zh', 'з'=>'z', 'и'=>'i', 
        //             'й'=>'y', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p', 
        //             'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'х'=>'kh', 'ц'=>'ts', 
        //             'ч'=>'ch', 'ш'=>'sh', 'щ'=>'shch', 'ы'=>'y', 'э'=>'e', 'ю'=>'yu', 'я'=>'ya'
        //         ]);
                
        //         $cleanBefore = preg_replace('/[^a-z0-9]/i', '', $temp);
        //         $cleanInside = preg_replace('/[^a-z0-9]/i', '', mb_strtolower($m['inside']));

        //         if ($cleanBefore === $cleanInside) {
        //             $text = $m['before'];
        //         }
        //     }

        //     $text = mb_strtolower($text);

        //     // 3. Выделяем поколение
        //     $generation = '';
        //     if (preg_match('/(?P<num>\d+)\s*(поколение|пок|генерация|gen)/u', $text, $genM)) {
        //         $generation = "gen{$genM['num']}";
        //         $text = str_replace($genM[0], '', $text);
        //     }

        //     // 4. Выносим суффиксы в конец
        //     $suffixMap = ['новый' => 'new', 'рестайлинг' => 'restyling', 'рестайл' => 'restyling'];
        //     $foundSuffixes = [];
        //     foreach ($suffixMap as $ru => $en) {
        //         if (str_contains($text, $ru)) {
        //             $foundSuffixes[] = $en;
        //             $text = str_replace($ru, '', $text);
        //         }
        //     }

        //     // 5. Основная карта замен
        //     $map = [
        //         'бортовая платформа с тентом' => 'flatbed-van',
        //         'с увеличенной крышей' => 'big-roof',
        //         'цельнометаллический' => 'cargo',
        //         'грузопассажирский' => 'cargo-passenger',
        //         'изотермический' => 'isothermal',
        //         'маршрутное такси' => 'taxi',
        //         'рефрижератор' => 'refrigerator',
        //         'универсал' => 'wagon',
        //         'пригород' => 'suburb',
        //         'евроборт' => 'euroboard',
        //         'минивэн' => 'minivan', 'минивен' => 'minivan',
        //         'автобус' => 'bus', 'турист' => 'tourist',
        //         'серия' => 'series', 'шасси' => 'chassis',
        //         'фургон' => 'van', 'бортовой' => 'board', 'борт' => 'board',
        //         '3-дв.' => '3-door', '3-дв' => '3-door',
        //         '5-дв.' => '5-door', '5-дв' => '5-door',

        //         // Фонетические индексы (A-Z)
        //         'эй'=>'a', 'ай'=>'a', 'би'=>'b', 'си'=>'c', 'це'=>'c', 'ди'=>'d', 
        //         'эф'=>'f', 'джи'=>'g', 'эйч'=>'h', 'джей'=>'j', 'кей'=>'k', 'ка'=>'k', 
        //         'эль'=>'l', 'эм'=>'m', 'эн'=>'n', 'пи'=>'p', 'ку'=>'q', 'кью'=>'q', 
        //         'эр'=>'r', 'эс'=>'s', 'ти'=>'t', 'ви'=>'v', 'дабл'=>'w', 'икс'=>'x', 
        //         'игрек'=>'y', 'уай'=>'y', 'зет'=>'z', 'зед'=>'z',

        //         // Алфавит
        //         'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 'ё'=>'yo',
        //         'ж'=>'zh', 'з'=>'z', 'и'=>'i', 'й'=>'y', 'к'=>'k', 'л'=>'l', 'м'=>'m',
        //         'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u',
        //         'ф'=>'f', 'х'=>'kh', 'ц'=>'ts', 'ч'=>'ch', 'ш'=>'sh', 'щ'=>'shch',
        //         'ы'=>'y', 'э'=>'e', 'ю'=>'yu', 'я'=>'ya', 'ъ'=>'', 'ь'=>''
        //     ];

        //     $text = strtr($text, $map);

        //     // 6. Очистка и сборка
        //     $text = trim(preg_replace('/[^a-z0-9]+/i', '-', $text), '-');
            
        //     return implode('-', array_filter([$text, $generation, ...$foundSuffixes]));
        // }

        // для PHP 7 ** AI
        public function transliterateBrandToRu($text = null) {
            // Защита от null и пустых значений
            if ($text === null || $text === '') {
                return '';
            }

            $map = [
                // --- Длинные названия и спец. написания (Приоритет 1) ---
                'Chevrolet Niva' => 'Шевроле Нива', 'chevrolet niva' => 'шевроле нива',
                'Chevrolet NAV'  => 'Шевроле НАВ',  'chevrolet nav'  => 'шевроле нав',
                'Mercedes-Benz'  => 'Мерседес-Бенц', 'mercedes-benz'  => 'мерседес-бенц',
                'Land Rover'     => 'Ланд Ровер',    'land rover'     => 'ланд ровер',
                'HAVAL City'     => 'ХАВЭИЛ Cити',   'haval city'     => 'хавэил сити',
                'HAVAL PRO'      => 'ХАВЭИЛ ПРО',    'haval pro'      => 'хавэил про',
                'Great Wall'     => 'Грейт Волл',    'great wall'     => 'грейт волл',
                'Alfa Romeo'     => 'Альфа Ромео',   'alfa romeo'     => 'альфа ромео',
                'Lynk & Co'      => 'Линк энд Ко',   'lynk & co'      => 'линк энд ко',
                'Renault Samsung'=> 'Рено Самсунг',  'renault samsung'=> 'рено самсунг',
                'Polar Stone (Jishi)' => 'Поляр стоун', 'polar stone' => 'поляр стоун',
                'LiXiang (Li Auto)'   => 'Ли авто', 'lixiang' => 'ли авто',
                'Nordcross (Lynk & Co)'   => 'Нордкросс',

                // --- Бренды из списка (Приоритет 2) ---
                'Volkswagen' => 'Фольксваген', 'Mitsubishi' => 'Мицубиси',
                'Hyundai'    => 'Хендай',      'Jaguar'     => 'Ягуар',
                'ŠKODA'      => 'Шкода',       'Suzuki'     => 'Сузуки',
                'Genesis'    => 'Дженезис',    'Cadillac'   => 'Кадиллак',
                'Chevrolet'  => 'Шевроле',     'Brilliance' => 'Брилионс',
                'SsangYong'  => 'Ссангйонг',   'Chrysler'   => 'Крайслер',
                'Infiniti'   => 'Инфинити',    'Daihatsu'   => 'Дайхатсу',
                'SKYWELL'    => 'Скайвол',     'SOLARIS'    => 'СОЛАРИС',
                'XCITE'      => 'Икссайт',     'Knewstar'   => 'Ньюстар',
                'Xiaomi'     => 'Сяоми',       'Ambertruck' => 'Амбертрак',
                'Maserati'   => 'Мазерати',    'Pontiac'    => 'Понтиак',
                'Exeed_d'    => 'Эксид',       'EXEED'      => 'Эксид',
                'CHERY'      => 'Чери',        'Chery'      => 'Чери',
                'LADA (ВАЗ)' => 'ЛАДА (ВАЗ)',  'LADA'       => 'ЛАДА',
                'Opel'       => 'Опель',       'Peugeot'    => 'Пежо',
                'Citroen'    => 'Cитроен',     'Datsun'     => 'Датсун',
                'Dacia'      => 'Дация',       'Daewoo'     => 'Дэу',
                'Vortex'     => 'Вортекс',     'Ravon'      => 'Равон',
                'Isuzu'      => 'Исузу',       'Zotye'      => 'Зоти',
                'Hawtai'     => 'Хотай',       'Haima'      => 'Хайма',
                'Soueast'    => 'Соуис',       'Venucia'    => 'Венуция',
                'Eonyx'      => 'Ионикс',      'Tesla'      => 'Тесла',
                'Smart'      => 'Смарт',       'smart'      => 'Смарт',
                'JAECOO'     => 'Джейку',      'RAM'        => 'Рэм',

                // --- Фонетика и общие правила (Приоритет 3) ---
                'Ch'=>'Ч', 'ch'=>'ч', 'Sh'=>'Ш', 'sh'=>'ш', 'Yo'=>'Ё', 'yo'=>'ё',
                'Zh'=>'Ж', 'zh'=>'ж', 'Kh'=>'Х', 'kh'=>'х', 'Ts'=>'Ц', 'ts'=>'ц',
                'Ph'=>'Ф', 'ph'=>'ф', 'Ya'=>'Я', 'ya'=>'я', 'Yu'=>'Ю', 'yu'=>'ю',

                // --- Одиночные буквы ---
                'A'=>'А', 'B'=>'Б', 'V'=>'В', 'G'=>'Г', 'D'=>'Д', 'E'=>'Е', 'Z'=>'З', 
                'I'=>'И', 'J'=>'Дж', 'K'=>'К', 'L'=>'Л', 'M'=>'М', 'N'=>'Н', 'O'=>'О', 
                'P'=>'П', 'R'=>'Р', 'S'=>'С', 'T'=>'Т', 'U'=>'У', 'F'=>'Ф', 'H'=>'Х', 
                'W'=>'В', 'X'=>'Кс', 'Y'=>'Й', 'Q'=>'К',
                'a'=>'а', 'b'=>'б', 'v'=>'в', 'g'=>'г', 'd'=>'д', 'e'=>'е', 'z'=>'з', 
                'i'=>'и', 'j'=>'дж', 'k'=>'к', 'l'=>'л', 'm'=>'м', 'n'=>'н', 'o'=>'о', 
                'p'=>'п', 'r'=>'р', 's'=>'с', 't'=>'т', 'u'=>'у', 'f'=>'ф', 'h'=>'х', 
                'w'=>'в', 'x'=>'кс', 'y'=>'й', 'q'=>'к'
            ];

            // strtr корректно обработает сначала длинные фразы из вашего списка
            return strtr($text, $map);
        }




        private function getDrive( $q ) {

            $vars = $this->MySQL->getAll('SELECT * FROM yapps_app_cis_comparisons WHERE entity = ?s', 'drives');
            foreach ( $vars as $v ) if ( mb_strripos($q, $v['desired']) !== false ) return $this->yappsGetDrive($v['value']);

            return [
                'name' => 'Неизвестно',
                'code' => 'none'
            ];
        }

        private function buildDBQuery( $GET ) {

            // Helper::sp($GET);

            $res = false;
            $w = [];

            if ( $GET['type'] && $GET['type'] !== 'all' && (int)$GET['type'] > 0 ) $w[] = $this->MySQL->parse('type_id = ?i', (int)$GET['type']);
            if ( $GET['brand'] ) $w[] = $this->MySQL->parse( 'brand_id IN (?a)', $this->MySQL->getCol('SELECT id FROM yapps_app_cis_brands WHERE code IN (?a)', explode(',', $GET['brand'])) );
            if ( $GET['!brand'] ) $w[] = $this->MySQL->parse( 'brand_id NOT IN (?a)', $this->MySQL->getCol('SELECT id FROM yapps_app_cis_brands WHERE code IN (?a)', explode(',', $GET['!brand'])) );
            if ( !$GET['brand'] ) $w[] = $this->MySQL->parse( 'brand_id != ?i', 0);
            if ( $GET['model'] ) $w[] = $this->MySQL->parse( 'model_id IN (?a)', $this->MySQL->getCol('SELECT id FROM ?n WHERE code IN (?a)', 'yapps_app_cis_models_'.(((int)$GET['type']==1)?'new':'used'), explode(',', $GET['model'])) );
            if ( $GET['!model'] ) $w[] = $this->MySQL->parse( 'model_id NOT IN (?a)', $this->MySQL->getCol('SELECT id FROM ?n WHERE code IN (?a)', 'yapps_app_cis_models_'.(((int)$GET['type']==1)?'new':'used'), explode(',', $GET['!model'])) );
            if ( !$GET['model'] ) $w[] = $this->MySQL->parse( 'model_id != ?i', 0);
            if ( $GET['transmission'] ) {
                $codes = explode(',', $GET['transmission']);
                $lookup = $this->MySQL->getAll('SELECT id, code FROM yapps_app_cis_transmissions WHERE code IN (?a)', $codes);
                foreach ($lookup as $row) $codes[] = 't_'.$row['id'];
                $w[] = $this->MySQL->parse('transmission IN (?a)', array_unique($codes));
            }
            if ( $GET['!transmission'] ) {
                $codes = explode(',', $GET['!transmission']);
                $lookup = $this->MySQL->getAll('SELECT id, code FROM yapps_app_cis_transmissions WHERE code IN (?a)', $codes);
                foreach ($lookup as $row) $codes[] = 't_'.$row['id'];
                $w[] = $this->MySQL->parse('transmission NOT IN (?a)', array_unique($codes));
            }
            if ( $GET['engine'] ) {
                $codes = explode(',', $GET['engine']);
                $lookup = $this->MySQL->getAll('SELECT id, code FROM yapps_app_cis_engines WHERE code IN (?a)', $codes);
                foreach ($lookup as $row) $codes[] = 'e_'.$row['id'];
                $w[] = $this->MySQL->parse('engine IN (?a)', array_unique($codes));
            }
            if ( $GET['!engine'] ) {
                $codes = explode(',', $GET['!engine']);
                $lookup = $this->MySQL->getAll('SELECT id, code FROM yapps_app_cis_engines WHERE code IN (?a)', $codes);
                foreach ($lookup as $row) $codes[] = 'e_'.$row['id'];
                $w[] = $this->MySQL->parse('engine NOT IN (?a)', array_unique($codes));
            }
            if ( $GET['drive'] ) {
                $codes = explode(',', $GET['drive']);
                $lookup = $this->MySQL->getAll('SELECT id, code FROM yapps_app_cis_drives WHERE code IN (?a)', $codes);
                foreach ($lookup as $row) $codes[] = 'd_'.$row['id'];
                $w[] = $this->MySQL->parse('drive IN (?a)', array_unique($codes));
            }
            if ( $GET['!drive'] ) {
                $codes = explode(',', $GET['!drive']);
                $lookup = $this->MySQL->getAll('SELECT id, code FROM yapps_app_cis_drives WHERE code IN (?a)', $codes);
                foreach ($lookup as $row) $codes[] = 'd_'.$row['id'];
                $w[] = $this->MySQL->parse('drive NOT IN (?a)', array_unique($codes));
            }
            if ( $GET['body'] ) {
                $codes = explode(',', $GET['body']);
                $lookup = $this->MySQL->getAll('SELECT id, code FROM yapps_app_cis_bodies WHERE code IN (?a)', $codes);
                foreach ($lookup as $row) $codes[] = 'b_'.$row['id'];
                $w[] = $this->MySQL->parse('body IN (?a)', array_unique($codes));
            }
            if ( $GET['!body'] ) {
                $codes = explode(',', $GET['!body']);
                $lookup = $this->MySQL->getAll('SELECT id, code FROM yapps_app_cis_bodies WHERE code IN (?a)', $codes);
                foreach ($lookup as $row) $codes[] = 'b_'.$row['id'];
                $w[] = $this->MySQL->parse('body NOT IN (?a)', array_unique($codes));
            }
            if ( $GET['dealership'] ) $w[] = $this->MySQL->parse('dealership_id IN (?a)', explode(',', $GET['dealership']));
            if ( $GET['!dealership'] ) $w[] = $this->MySQL->parse('dealership_id NOT IN (?a)', explode(',', $GET['!dealership']));
            if ( $GET['id'] ) $w[] = $this->MySQL->parse('ext_id IN (?a)', explode(',', $GET['id']));
            if ( $GET['!id'] ) $w[] = $this->MySQL->parse('ext_id NOT IN (?a)', explode(',', $GET['!id']));
            if ( $GET['color'] ) {
                $codes = explode(',', $GET['color']);
                $lookup = $this->MySQL->getAll('SELECT id, code FROM yapps_app_cis_colors WHERE code IN (?a)', $codes);
                foreach ($lookup as $row) $codes[] = 'c_'.$row['id'];
                $w[] = $this->MySQL->parse('color IN (?a)', array_unique($codes));
            }
            if ( $GET['!color'] ) {
                $codes = explode(',', $GET['!color']);
                $lookup = $this->MySQL->getAll('SELECT id, code FROM yapps_app_cis_colors WHERE code IN (?a)', $codes);
                foreach ($lookup as $row) $codes[] = 'c_'.$row['id'];
                $w[] = $this->MySQL->parse('color NOT IN (?a)', array_unique($codes));
            }
            if ( $GET['vin'] ) $w[] = $this->MySQL->parse('vin IN (?a)', explode(',', $GET['vin']));
            if ( $GET['!vin'] ) $w[] = $this->MySQL->parse('vin NOT IN (?a)', explode(',', $GET['!vin']));

            if ( $GET['price'] ) {
                $arQ = explode(',', $GET['price']);
                if ( (int)$arQ[1] ) $w[] = $this->MySQL->parse('min_price >= ?i AND min_price <= ?i', (int)$arQ[0], (int)$arQ[1]);
            }
            if ( $GET['volume'] ) {
                $arQ = explode(',', $GET['volume']);
                if ( (int)$arQ[1] ) $w[] = $this->MySQL->parse('volume >= ?i AND volume <= ?i', (int)$arQ[0], (int)$arQ[1]);
            }
            if ( $GET['power'] ) {
                $arQ = explode(',', $GET['power']);
                if ( (int)$arQ[1] ) $w[] = $this->MySQL->parse('power >= ?i AND power <= ?i', (int)$arQ[0], (int)$arQ[1]);
            }
            if ( $GET['mileage'] ) {
                $arQ = explode(',', $GET['mileage']);
                if ( (int)$arQ[1] ) $w[] = $this->MySQL->parse('mileage >= ?i AND mileage <= ?i', (int)$arQ[0], (int)$arQ[1]);
            }
            if ( $GET['year'] ) {
                $arQ = explode(',', $GET['year']);
                if ( (int)$arQ[1] ) $w[] = $this->MySQL->parse('year >= ?i AND year <= ?i', (int)$arQ[0], (int)$arQ[1]);
            }

            if ( $GET['is_discount'] ) $w[] = $this->MySQL->parse('discount = 1');
            if ( $GET['is_instock'] ) $w[] = $this->MySQL->parse('instock = 1');
            if ( $GET['is_onway'] ) $w[] = $this->MySQL->parse('onway = 1');

            if ( count($w) ) $res = "WHERE ".implode(' AND ',$w);
            
            switch ( $GET['tag'] ) {
                case 'instock': $res .= $this->MySQL->parse(' AND instock = ?i', 1); break;
                case 'onway': $res .= $this->MySQL->parse(' AND onway = ?i', 1); break;
                case 'discount': $res .= $this->MySQL->parse(' AND discount = ?i', 1); break;
            }
            switch ( $GET['sort'] ) {

                case 'instock': $res .= $this->MySQL->parse(' AND instock = ?i', 1); break;
                case 'onway': $res .= $this->MySQL->parse(' AND onway = ?i', 1); break;
                case 'discount': $res .= $this->MySQL->parse(' AND discount = ?i', 1); break;

                case 'price_up': $res .= $this->MySQL->parse(' ORDER BY min_price ASC'); break;
                case 'price_down': $res .= $this->MySQL->parse(' ORDER BY min_price DESC'); break;

                case 'datetime_up': $res .= $this->MySQL->parse(' ORDER BY ext_id DESC'); break;
                case 'datetime_down': $res .= $this->MySQL->parse(' ORDER BY ext_id ASC'); break;

                case 'year_up': $res .= $this->MySQL->parse(' ORDER BY year ASC'); break;
                case 'year_down': $res .= $this->MySQL->parse(' ORDER BY year DESC'); break;

                case 'mileage_up': $res .= $this->MySQL->parse(' ORDER BY mileage ASC'); break;
                case 'mileage_down': $res .= $this->MySQL->parse(' ORDER BY mileage DESC'); break;

                case 'random': $res .= $this->MySQL->parse(' ORDER BY RAND()'); break;
                
                default: $res .= $this->MySQL->parse(' ORDER BY ext_id DESC'); break;
            }
            if ( (int)$GET['page'] && !$GET['limit'] ) {
                $c_a = intdiv( (((int)$GET['perpage']?:$this->Conf->PerPage)), 16);
                $res .= $this->MySQL->parse(
                    ' LIMIT ?i,?i',
                     ((($GET['perpage'])?(int)$GET['perpage']:$this->Conf->PerPage)-$c_a)*(int)$GET['page']-((($GET['perpage'])?(int)$GET['perpage']:$this->Conf->PerPage)-$c_a), 
                     ((($GET['perpage'])?(int)$GET['perpage']:$this->Conf->PerPage)-$c_a));
            }
            if ( $GET['limit'] ) $res .= $this->MySQL->parse(' LIMIT ?i', (int)$GET['limit']);

            return $res;
        }



        ////////////////////////////////////////////////////////////////
		// Yapps  //////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////

        public function yappsGetBrandsForSeo( $sort = false ) {

            $query = 'SELECT * FROM yapps_app_cis_brands';
            if ( $sort ) $query .= ' ORDER BY '.$sort.' ASC';

            $res = $this->MySQL->getAll($query);
            return $res;
        }
        public function yappsGetModelsForSeo( $entity = 'new' ) {

            return $this->MySQL->getAll('SELECT * FROM yapps_app_cis_models_'.$entity);
        }

        public function yappsGetBrand( $id ) {

            return $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE id = ?i', $id);
        }


        public function yappsGetBodies() {

            return $this->MySQL->getAll('SELECT * FROM yapps_app_cis_bodies');
        }
        public function yappsGetBody( $id ) {

            return $this->MySQL->getRow('SELECT * FROM yapps_app_cis_bodies WHERE id = ?i', $id);
        }
        public function yappsSetBody( $POST ) {

            $arIns = $POST;
            unset($arIns['form'], $arIns['meta']);

            $this->MySQL->query('REPLACE INTO yapps_app_cis_bodies SET ?u', $arIns);
			return Helper::getRes(0);
        }
        public function yappsDelBody( $id ) {

            $this->MySQL->query('DELETE FROM yapps_app_cis_bodies WHERE id = ?i', $id);
			return Helper::getRes(0);
        }


        public function yappsGetColors() {

            return $this->MySQL->getAll('SELECT * FROM yapps_app_cis_colors');
        }
        public function yappsGetColor( $id ) {

            return $this->MySQL->getRow('SELECT * FROM yapps_app_cis_colors WHERE id = ?i', $id);
        }
        public function yappsSetColor( $POST ) {

            $arIns = $POST;
            unset($arIns['form'], $arIns['meta']);

            $this->MySQL->query('REPLACE INTO yapps_app_cis_colors SET ?u', $arIns);
			return Helper::getRes(0);
        }
        public function yappsDelColor( $id ) {

            $this->MySQL->query('DELETE FROM yapps_app_cis_colors WHERE id = ?i', $id);
			return Helper::getRes(0);
        }

        public function yappsGetEquipments() {

            $res = $this->MySQL->getAll('SELECT * FROM yapps_app_cis_equipments');
            foreach ( $res as $k => $i ) {
                $res[$k]['brand'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE id = ?i', $i['brand_id']);
                $res[$k]['model'] = $this->MySQL->getRow('SELECT * FROM ?n WHERE id = ?i', 'yapps_app_cis_models_'.(((int)$i['type_id']==1)?'new':'used'), $i['model_id']);
            }
            return $res;
        }
        public function yappsGetEquipment( $id ) {

            $res = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_equipments WHERE id = ?i', $id);
            $res['brand'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE id = ?i', $res['brand_id']);
            $res['model'] = $this->MySQL->getRow('SELECT * FROM ?n WHERE id = ?i', 'yapps_app_cis_models_'.(((int)$res['type_id']==1)?'new':'used'), $res['model_id']);
            return $res;
        }
        public function yappsGetEquipmentRuName( $brand_id, $model_id, $name ) {

            $res = $this->MySQL->getOne('SELECT ru_name FROM yapps_app_cis_equipments WHERE brand_id = ?i AND model_id = ?i AND name = ?s', $brand_id, $model_id, $name);
            return $res;
        }
        public function yappsSetEquipment( $POST ) {

            $arIns = $POST;
            unset($arIns['form']);

            $this->MySQL->query('REPLACE INTO yapps_app_cis_equipments SET ?u', $arIns);
			return Helper::getRes(0);
        }
        public function yappsDelEquipment( $id ) {

            $this->MySQL->query('DELETE FROM yapps_app_cis_equipments WHERE id = ?i', $id);
			return Helper::getRes(0);
        }


        public function yappsGetDrives() {

            return $this->MySQL->getAll('SELECT * FROM yapps_app_cis_drives');
        }
        public function yappsGetDrive( $id ) {

            return $this->MySQL->getRow('SELECT * FROM yapps_app_cis_drives WHERE id = ?i', $id);
        }
        public function yappsSetDrive( $POST ) {

            $arIns = $POST;
            unset($arIns['form']);

            $this->MySQL->query('REPLACE INTO yapps_app_cis_drives SET ?u', $arIns);
			return Helper::getRes(0);
        }
        public function yappsDelDrive( $id ) {

            $this->MySQL->query('DELETE FROM yapps_app_cis_drives WHERE id = ?i', $id);
			return Helper::getRes(0);
        }


        public function yappsGetEngines() {

            return $this->MySQL->getAll('SELECT * FROM yapps_app_cis_engines');
        }
        public function yappsGetEngine( $id ) {

            return $this->MySQL->getRow('SELECT * FROM yapps_app_cis_engines WHERE id = ?i', $id);
        }
        public function yappsSetEngine( $POST ) {

            $arIns = $POST;
            unset($arIns['form'], $arIns['meta']);

            $this->MySQL->query('REPLACE INTO yapps_app_cis_engines SET ?u', $arIns);
			return Helper::getRes(0);
        }
        public function yappsDelEngine( $id ) {

            $this->MySQL->query('DELETE FROM yapps_app_cis_engines WHERE id = ?i', $id);
			return Helper::getRes(0);
        }


        public function yappsGetTransmissions() {

            return $this->MySQL->getAll('SELECT * FROM yapps_app_cis_transmissions');
        }
        public function yappsGetTransmission( $id ) {

            return $this->MySQL->getRow('SELECT * FROM yapps_app_cis_transmissions WHERE id = ?i', $id);
        }
        public function yappsSetTransmission( $POST ) {

            $arIns = $POST;
            unset($arIns['form']);

            $this->MySQL->query('REPLACE INTO yapps_app_cis_transmissions SET ?u', $arIns);
			return Helper::getRes(0);
        }
        public function yappsDelTransmission( $id ) {

            $this->MySQL->query('DELETE FROM yapps_app_cis_transmissions WHERE id = ?i', $id);
			return Helper::getRes(0);
        }


        public function yappsGetComparisons() {

            $res = $this->MySQL->getAll('SELECT * FROM yapps_app_cis_comparisons');
            foreach ( $res as $k => $i ) $res[$k]['name'] = $this->MySQL->getOne('SELECT name FROM ?n WHERE id = ?i', 'yapps_app_cis_'.$i['entity'], $i['value']);
            return $res;
        }
        public function yappsGetComparison( $id ) {

            return $this->MySQL->getRow('SELECT * FROM yapps_app_cis_comparisons WHERE id = ?i', $id);
        }
        public function yappsSetComparison( $POST ) {

            $arIns = $POST;
            unset($arIns['form']);

            $this->MySQL->query('REPLACE INTO yapps_app_cis_comparisons SET ?u', $arIns);
			return Helper::getRes(0);
        }
        public function yappsDelComparison( $id ) {

            $this->MySQL->query('DELETE FROM yapps_app_cis_comparisons WHERE id = ?i', $id);
			return Helper::getRes(0);
        }
        public function yappsGetComparisonsLists() {
            
            return [
                'brands' => $this->MySQL->getAll('SELECT * FROM yapps_app_cis_brands'),
                'bodies' => $this->MySQL->getAll('SELECT * FROM yapps_app_cis_bodies'),
                'colors' => $this->MySQL->getAll('SELECT * FROM yapps_app_cis_colors'),
                'drives' => $this->MySQL->getAll('SELECT * FROM yapps_app_cis_drives'),
                'engines' => $this->MySQL->getAll('SELECT * FROM yapps_app_cis_engines'),
                'transmissions' => $this->MySQL->getAll('SELECT * FROM yapps_app_cis_transmissions'),
            ];
        }


        public function yappsGetSeos() {

            return $this->MySQL->getAll('SELECT id, site, entity, level, phone, custom FROM yapps_app_cis_seo');
        }
        public function yappsGetSeo( $id ) {
            
            return $this->MySQL->getRow('SELECT * FROM yapps_app_cis_seo WHERE id = ?i', $id);
        }
        public function yappsSetSeo( $POST ) {

            $arIns = $POST;
            unset($arIns['form']);
            $arIns['phone'] = Helper::formatPhoneIn($POST['phone']);

            $this->MySQL->query('REPLACE INTO yapps_app_cis_seo SET ?u', $arIns);
			return Helper::getRes(0);
        }
        public function yappsDelSeo( $id ) {

            $this->MySQL->query('DELETE FROM yapps_app_cis_seo WHERE id = ?i', $id);
			return Helper::getRes(0);
        }

        public function yappsGetSeoFilters() {
            return $this->MySQL->getAll('SELECT * FROM yapps_app_cis_seo_filters');
        }
        public function yappsGetSeoFilter( $id ) {

            return $this->MySQL->getRow('SELECT * FROM yapps_app_cis_seo_filters WHERE id = ?i', $id);
        }
        public function yappsSetSeoFilter( $POST ) {

            if ( $POST['id'] ) $arIns['id'] = $POST['id'];
            if ( $POST['site'] ) $arIns['site'] = $POST['site'];
            if ( $POST['entity'] ) $arIns['entity'] = $POST['entity'];
            if ( $POST['meta_h1'] ) $arIns['meta_h1'] = $POST['meta_h1'];
            if ( $POST['meta_title'] ) $arIns['meta_title'] = $POST['meta_title'];
            if ( $POST['meta_description'] ) $arIns['meta_description'] = $POST['meta_description'];
            if ( $POST['seo_title'] ) $arIns['seo_title'] = $POST['seo_title'];
            if ( $POST['seo_text'] ) $arIns['seo_text'] = $POST['seo_text'];
            if ( $POST['brand'] ) {
                sort($POST['brand']);
                $arIns['brand'] = implode(',', $POST['brand']);
            }
            if ( $POST['model'] ) {
                sort($POST['model']);
                $arIns['model'] = implode(',', $POST['model']);
            }
            if ( $POST['transmission'] ) {
                sort($POST['transmission']);
                $arIns['transmission'] = implode(',', $POST['transmission']);
            }
            if ( $POST['engine'] ) {
                sort($POST['engine']);
                $arIns['engine'] = implode(',', $POST['engine']);
            }
            if ( $POST['drive'] ) {
                sort($POST['drive']);
                $arIns['drive'] = implode(',', $POST['drive']);
            }
            if ( $POST['body'] ) {
                sort($POST['body']);
                $arIns['body'] = implode(',', $POST['body']);
            }
            if ( $POST['color'] ) {
                sort($POST['color']);
                $arIns['color'] = implode(',', $POST['color']);
            }
            if ( $POST['dealership'] ) {
                sort($POST['dealership']);
                $arIns['dealership'] = implode(',', $POST['dealership']);
            }
            if ( (int)$POST['price'][1] ) {
                sort($POST['price']);
                $arIns['price'] = implode(',', $POST['price']);
            }
            if ( (int)$POST['volume'][1] ) {
                sort($POST['volume']);
                $arIns['volume'] = implode(',', $POST['volume']);
            }
            if ( (int)$POST['power'][1] ) {
                sort($POST['power']);
                $arIns['power'] = implode(',', $POST['power']);
            }
            if ( (int)$POST['year'][1] ) {
                sort($POST['year']);
                $arIns['year'] = implode(',', $POST['year']);
            }

            $this->MySQL->query('REPLACE INTO yapps_app_cis_seo_filters SET ?u', $arIns);
			return Helper::getRes(0);
        }
        public function yappsDelSeoFilter( $id ) {

            $this->MySQL->query('DELETE FROM yapps_app_cis_seo_filters WHERE id = ?i', $id);
			return Helper::getRes(0);
        }

        public function yappsGetSeo404s() {
            return $this->MySQL->getAll('SELECT * FROM yapps_app_cis_seo_404');
        }
        public function yappsGetSeo404( $id ) {

            return $this->MySQL->getRow('SELECT * FROM yapps_app_cis_seo_404 WHERE id = ?i', $id);
        }
        public function yappsSetSeo404( $POST ) {

            $arIns['site'] = parse_url($POST['site'])['host'];
            $arIns['uri'] = parse_url($POST['site'])['path'];
            if ( $q = parse_url($POST['site'])['query'] ) $arIns['uri'] .= '?'.$q;

            $this->MySQL->query('REPLACE INTO yapps_app_cis_seo_404 SET ?u', $arIns);
			return Helper::getRes(0);
        }
        public function yappsDelSeo404( $id ) {

            $this->MySQL->query('DELETE FROM yapps_app_cis_seo_404 WHERE id = ?i', $id);
			return Helper::getRes(0);
        }


        public function yappsGetVehicles( $entity = 'new' ) {

            $res = $this->MySQL->getAll(
                'SELECT * FROM ?n WHERE type_id = ?i', 
                $this->table->prod,
                (($entity=='new')?1:2)
            );

            return $res;
        }
        public function yappsGetVehicle( $id ) {

            $res = $this->MySQL->getRow('SELECT * FROM ?n WHERE ext_id = ?i', $this->table->prod, $id);
            $res['images'] = $this->getInternalImages($res['ext_id']);

            return $res;
        }
        public function yappsSetVehicle( $POST ) {
            
            $vehicle = json_decode($this->MySQL->getOne('SELECT raw FROM ?n WHERE ext_id = ?i', $this->table->prod, $POST['ext_id']), true);
            $arIns = $POST;
            unset($arIns['form']);
            if ( $arIns['update_images'] == 'on' ) $arIns['update_images'] = 1;

            if ( $arIns['body'] ) {
                
                $comparison = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_bodies WHERE id = ?i', $POST['body']);
                $arIns['body'] = $comparison['code'];
                $desired = $vehicle['body_type'];
                if ( !$desired ) $desired = $vehicle['model_name'];
                if ( !$desired ) $desired = $vehicle['ref_model_name'];
                $comparisons[] = [
                    'entity' => 'bodies',
                    'desired' => $desired,
                    'value' => (int)$POST['body']
                ];
                $vehicle['body'] = $comparison;
            }
            if ( $arIns['color'] && $vehicle['general'][2]['value'] ) {
                
                $comparison = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_colors WHERE id = ?i', $POST['color']);
                $arIns['color'] = $comparison['code'];
                $comparisons[] = [
                    'entity' => 'colors',
                    'desired' => $vehicle['general'][2]['value'],
                    'value' => (int)$POST['color']
                ];  
                $vehicle['color'] = $comparison;
            }
            if ( $arIns['drive'] && $vehicle['specifications'][11]['value'] ) {
                
                $comparison = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_drives WHERE id = ?i', $POST['drive']);
                $arIns['drive'] = $comparison['code'];
                $comparisons[] = [
                    'entity' => 'drives',
                    'desired' => $vehicle['specifications'][11]['value'],
                    'value' => (int)$POST['drive']
                ];  
                $vehicle['drive'] = $comparison;
            }
            if ( $arIns['engine'] && $vehicle['general'][0]['value'] ) {
                
                $comparison = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_engines WHERE id = ?i', $POST['engine']);
                $arIns['engine'] = $comparison['code'];
                $comparisons[] = [
                    'entity' => 'engines',
                    'desired' => $vehicle['general'][0]['value'],
                    'value' => (int)$POST['engine']
                ];  
                $vehicle['engine'] = $comparison;
            }
            if ( $arIns['transmission'] && $vehicle['general'][1]['value'] ) {
                
                $comparison = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_transmissions WHERE id = ?i', $POST['transmission']);
                $arIns['transmission'] = $comparison['code'];
                $comparisons[] = [
                    'entity' => 'transmissions',
                    'desired' => $vehicle['general'][1]['value'],
                    'value' => (int)$POST['transmission']
                ];  
                $vehicle['transmition'] = $comparison;
            }

            if ( $arIns ) {
                if ($comparisons) $arIns['raw'] = json_encode($vehicle);
                $this->MySQL->query('UPDATE ?n SET ?u WHERE ext_id = ?i', $this->table->prod, $arIns, $POST['ext_id']);
            }
            if ( $comparisons ) {
                foreach ( $comparisons as $item ) {
                    $this->MySQL->query('REPLACE INTO yapps_app_cis_comparisons SET ?u', $item);
                }
            }

            return Helper::getRes(0);
        }


        public function yappsGetDealerships() {

            return $this->MySQL->getAll('SELECT * FROM yapps_app_cis_dealerships');
        }
        public function yappsGetDealership( $code ) {

            $res = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_dealerships WHERE code = ?i', $code);
            $res['coords'] = [
                'lat' => $res['coords_lat'],
                'lon' => $res['coords_lon']
            ];
            unset($res['coords_lat'],$res['coords_lon']);
            return $res;
        }
        public function yappsSetDealership( $POST ) {

            $arIns = $POST;
            unset($arIns['form']);

            $arIns['phone'] =  Helper::formatPhoneIn($POST['phone']);
            $this->MySQL->query('REPLACE INTO yapps_app_cis_dealerships SET ?u', $arIns);
			return Helper::getRes(0);
        }
        public function yappsDelDealership( $id ) {

            $this->MySQL->query('DELETE FROM yapps_app_cis_dealerships WHERE id = ?i', $id);
			return Helper::getRes(0);
        }


        public function yappsGetTypes() {

            return $this->MySQL->getAll('SELECT * FROM yapps_app_cis_types');
        }


        public function yappsGetTags() {

            return $this->MySQL->getAll('SELECT * FROM yapps_app_cis_tags');
        }
        public function yappsGetTag( $id ) {

            return $this->MySQL->getRow('SELECT * FROM yapps_app_cis_tags WHERE id = ?i', $id);
        }
        public function yappsSetTag( $POST, $FILES ) {

            $arIns = $POST;
            unset($arIns['form'], $arIns['meta']);
            if ( $POST['id'] ) {
                $t = $this->MySQL->getRow('SELECT icon FROM yapps_app_cis_tags WHERE id = ?i', $POST['id']);
                $arIns['icon'] = $t['icon'];
            }

            if ( $FILES && $FILES['icon']['error'] == 0 ) {

                if ( $POST['id'] ) {
                    unlink(__DIR__.'/../..'.$this->Conf->FileDir.'/tags/'.explode('/', $this->MySQL->getOne('SELECT icon FROM yapps_app_cis_tags WHERE id = ?i', $POST['id']))[6]);
                } else {

                }
				
				$arIns['icon'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$this->Conf->FileDir.'/tags/'.md5($arIns['name']).'.svg';
				
				$file = __DIR__.'/../..'.$this->Conf->FileDir.'/tags/'.md5($arIns['name']).'.svg';
				move_uploaded_file( $FILES['icon']['tmp_name'], $file );
			}

            $this->MySQL->query('REPLACE INTO yapps_app_cis_tags SET ?u', $arIns);
			return Helper::getRes(0);
        }
        public function yappsDelTag( $id ) {

            unlink(__DIR__.'/../..'.$this->Conf->FileDir.'/tags/'.explode('/', $this->MySQL->getOne('SELECT icon FROM yapps_app_cis_tags WHERE id = ?i', $id))[6]);
            $this->MySQL->query('DELETE FROM yapps_app_cis_tags WHERE id = ?i', $id);
			return Helper::getRes(0);
        }

        public function yappsGetBrandsModels( $entity = 'new' ) {
            // $d = ['BAIC','Chery','Geely','HAVAL','HAVAL PRO','JAC','JAECOO','KAIYI','LADA','Livan','OMODA','ORA','SOLARIS','SOLLERS','TANK','WEY','XCITE','Москвич'];
            // $b = $this->MySQL->getCol(
            //     'SELECT id FROM yapps_app_cis_brands WHERE name IN (?a)',
            //     $d
            // );
            // Helper::sp($d);
            // Helper::sp($b);
            // Helper::sp(implode(',',$b));
            
            // $this->MySQL->query(
            //     'DELETE FROM ?n WHERE brand_id NOT IN (?a)',
            //     'yapps_app_cis_models_'.$entity,
            //     $this->MySQL->getCol(
            //         'SELECT id FROM yapps_app_cis_brands WHERE name NOT IN (?a)',
            //         $d
            //     )
            // );

            $res = $this->MySQL->getAll(
                'SELECT * FROM yapps_app_cis_brands WHERE id IN(?a) ORDER BY name',
                $this->MySQL->getCol(
                    'SELECT DISTINCT brand_id FROM ?n WHERE id IN (?a)',
                    'yapps_app_cis_models_'.$entity,
                    $this->MySQL->getCol(
                        'SELECT DISTINCT model_id FROM ?n WHERE type_id = ?i',
                        $this->table->prod,
                        ( $entity == 'new' ) ? 1 : 2
                    )
                )
            );
            foreach ( $res as $k => $item ) {
                $res[$k]['models'] = $this->MySQL->getAll('SELECT * FROM ?n WHERE brand_id = ?i ORDER BY name', 'yapps_app_cis_models_'.$entity, $item['id']);
                $res[$k]['dealerships'] = $this->MySQL->getAll('SELECT * FROM yapps_app_cis_dealerships WHERE brand_id = ?i', $item['id']);
            }

            return $res;
        }
        public function yappsActivateModel( $id, $dc, $activate = 1, $entity = 'new' ) {

            // $this->MySQL->query(
            //     'UPDATE ?n SET ?u WHERE id = ?i',
            //     'yapps_app_cis_models_'.$entity,
            //     ['use_additional_equipment_in_price' => (int)$activate],
            //     $id
            // );
            if ( $activate ) {
                $this->MySQL->query(
                    'INSERT INTO yapps_app_cis_models_dealerships_uaep SET ?u', 
                    [
                        'model_id' => $id,
                        'dealership_id' => $dc
                    ]
                );
            } else {
                $this->MySQL->query('DELETE FROM yapps_app_cis_models_dealerships_uaep WHERE model_id = ?i AND dealership_id = ?i', $id, $dc);
            }

        }
        public function yappsActivateBrand( $id, $dc, $activate = 1, $entity = 'new' ) {

            // $this->MySQL->query(
            //     'UPDATE ?n SET ?u WHERE brand_id = ?i',
            //     'yapps_app_cis_models_'.$entity,
            //     ['use_additional_equipment_in_price' => (int)$activate],
            //     $id
            // );
            $models = $this->MySQL->getCol('SELECT id FROM ?n WHERE brand_id = ?i', 'yapps_app_cis_models_'.$entity, $id);
            if ( $activate ) {
                foreach ( $models as $model ) {
                    $this->MySQL->query(
                        'INSERT INTO yapps_app_cis_models_dealerships_uaep SET ?u', 
                        [
                            'model_id' => $model,
                            'dealership_id' => $dc
                        ]
                    );
                }
            } else {
                foreach ( $models as $model ) $this->MySQL->query('DELETE FROM yapps_app_cis_models_dealerships_uaep WHERE model_id = ?i AND dealership_id = ?i', $model, $dc);
            }
        }
        public function isActiveModel( $id, $dc ) {
            $res = $this->MySQL->getRow( 'SELECT * FROM yapps_app_cis_models_dealerships_uaep WHERE model_id = ?i AND dealership_id = ?i', $id, $dc );
            return ( $res ) ? true : false;
        }

        ////////////////////////////////////////////////////////////////
		// Images  /////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////

        public function makeImg($url, $size = 'full', $w_in = 634, $h_in = 500){

            // $info   = getimagesize($url);
            $arrContextOptions = array(
                "ssl" => array(
                    "verify_peer" => false,
                    "verify_peer_name" => false,
                ),
            );
            stream_context_set_default($arrContextOptions); 
            $imgString = file_get_contents($url);  
            $info = getimagesizefromstring( $imgString );

            $width  = $info[0];
            $height = $info[1];
            $type   = $info[2];

            if ( $size == 'preview' ) {
                $w = 307;
                $h = 236;
            } else {
                $w = $w_in;
                $h = $h_in;
            }

            if ( $width/$height >= $w/$h ) {
                $h1 = $h;
                $ch = 0;
                $w1 = ceil( $width*$h/$height );
                $cw = ceil( ($w1-$w)/2 );
            } else {
                $w1 = $w;
                $cw = 0;
                $h1 = ceil( $height*$w/$width );
                $ch = ceil( ($h1-$h)/2 );
            }
            
            switch ($type) { 
                case 1: 
                    return null;
                    break;					
                case 2: 
                    // $img = imageCreateFromJpeg($url);     
                    $img = imagecreatefromstring($imgString);     

                    if (empty($w)) {
                        $w = ceil($h / ($height / $width));
                    }
                    if (empty($h)) {
                        $h = ceil($w / ($width / $height));
                    }
                     
                    $tmp_ = imageCreateTrueColor($w1, $h1);
                    $tw = ceil($h1 / ($height / $width));
                    $th = ceil($w1 / ($width / $height));
                    if ($tw < $w1) {
                        imageCopyResampled($tmp_, $img, ceil(($w1 - $tw) / 2), 0, 0, 0, $tw, $h1, $width, $height);        
                    } else {
                        imageCopyResampled($tmp_, $img, 0, ceil(($h1 - $th) / 2), 0, 0, $w1, $th, $width, $height);    
                    }            
                     
                    $img = $tmp_;
                    $img = imagecrop($img, ['x' => $cw, 'y' => $ch, 'width' => $w, 'height' => $h]);
        
                    break;
        
                case 3: 
                    // $tmp = imageCreateFromPng($url);
                    $tmp = imagecreatefromstring($imgString); 
        
                    if (empty($w)) {
                        $w = ceil($h / ($height / $width));
                    }
                    if (empty($h)) {
                        $h = ceil($w / ($width / $height));
                    }
                     
                    $tmp_ = imageCreateTrueColor($w, $h);
                    imagealphablending($tmp_, true); 
                    imageSaveAlpha($tmp_, true);
                    $transparent = imagecolorallocatealpha($tmp_, 0, 0, 0, 127); 
                    imagefill($tmp_, 0, 0, $transparent); 
                    imagecolortransparent($tmp_, $transparent);
                     
                    $tw = ceil($h / ($height / $width));
                    $th = ceil($w / ($width / $height));
                    if ($tw < $w) {
                        imageCopyResampled($tmp_, $tmp, ceil(($w - $tw) / 2), 0, 0, 0, $tw, $h, $width, $height);        
                    } else {
                        imageCopyResampled($tmp_, $tmp, 0, ceil(($h - $th) / 2), 0, 0, $w, $th, $width, $height);    
                    }

                    $tmp = $tmp_;
        
                    $img = imagecreatetruecolor(imagesx($tmp), imagesy($tmp));
                    imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));
                    imagealphablending($img, TRUE);
                    imagecopy($img, $tmp, 0, 0, 0, 0, imagesx($tmp), imagesy($tmp));
                    imagedestroy($tmp);
                    
                    break;
            }

            return $img;
        }

        private function getInternalImages( $id ) {

            if ( $images = $this->MySQL->getAll('SELECT * FROM yapps_app_cis_images WHERE ext_id = ?i ORDER BY detail', $id) ) {
                foreach ($images as $i) {
                    $res[] = [
                        'id' => $i['id'],
                        'detail' => 'https://apps.yug-avto.ru' . $i['detail'],
                        'preview' => 'https://apps.yug-avto.ru' . ($i['preview'] ?: $i['detail']),
                        'big' => 'https://apps.yug-avto.ru' . $i['detail'],
                        'thumb' => 'https://apps.yug-avto.ru' . ($i['preview'] ?: $i['detail']),
                    ];
                }
            } else {
                $scan = scandir( __DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$id);
                if ($scan === false) return $res ?? [];
                $files = array_slice($scan, 2);
                array_pop($files);
                foreach ( $files as $k => $file )
                    $res[] = [
                        'id' => $k+1,
                        'big' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/vehicles/'.$id.'/'.$file, //.'?'.md5_file(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$id.'/'.$file),
                        'thumb' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/vehicles/'.$id.'/'.$file,
                        'detail' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/vehicles/'.$id.'/'.$file, //.'?'.md5_file(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$id.'/'.$file),
                        'preview' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/vehicles/'.$id.'/'.$file
                    ];
            }

            return $res;
        }

        private function getInternalImage( $id ) {

            if ( $image = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_images WHERE ext_id = ?i ORDER BY detail', $id) ) {
                return 'https://apps.yug-avto.ru' . ($image['preview'] ?: $image['detail']);
            } else {
                return ( file_exists(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$id.'/0.jpg') ) ? 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/vehicles/'.$id.'/0.jpg' : null;
            }
        }

        private function getExternalImages( $id ) {
            
            $files = json_decode($this->MySQL->getRow('SELECT * FROM ?n WHERE ext_id = ?i', $this->table->prod, (int)$id)['raw'], true)['images'];
            foreach ( $files as $k => $file )
                $res[] = [
                    'id' => $k+1,
                    'big' => $file['full'],
                    'thumb' => $file['full'],
                    'detail' => $file['full'],
                    'preview' => $file['preview_large']
                ];

            return $res;
        }

        private function getExternalImage( $id ) {

            $files = json_decode($this->MySQL->getRow('SELECT * FROM ?n WHERE ext_id = ?i', $this->table->prod, (int)$id)['raw'], true)['images'];
            return $files[0]['preview_large'];
        }
        


        ////////////////////////////////////////////////////////////////
		// Cities  /////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////

        public function buildCity( $q ) {

            $in = is_array($q) ? $q : explode(',', $q);

            if ( in_array('Краснодар', $in) || in_array('Яблоновский', $in) ) $res = ['Краснодар', 'Яблоновский'];
            foreach ( $in as $i ) $res[] = $i;

            return implode(',', array_unique($res));
        }

        

        ////////////////////////////////////////////////////////////////
		// Data  ///////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////

        public function getBrandsNew( $params = null ) {
            $rs = $this->request(
                static::URL_BASE.static::URL_BRANDS
            );

            foreach ( $rs['items'] as $k => $item ) {

                if ( $item['name'] == 'Chevrolet' ) $item['name']  = 'Chevrolet NAV';
                if ( $item['name'] == 'Chevrolet Auto' ) $item['name']  = 'Chevrolet';
                if ( $item['name'] == 'HAVAL Pro' ) $item['name']  = 'HAVAL PRO';
                if ( $item['name'] == 'Nordcross' ) $item['name']  = 'Nordcross (Lynk & Co)';
                $item['alias'] = $this->getBrandAlias($item['name']);
                if ( $item['name'] == 'Москвич' ) $item['alias']  = 'moskvich';
                $res[] = $item;
                
                $arIns = [
                    'ext_id' => $item['id'],
                    'name' => $item['name'],
                    'ru_name' => $this->transliterateBrandToRu($item['name']),
                    'code' => $item['alias'],
                ];
                if ( $arIns['code'] == 'skoda' ) $arIns['name'] = 'ŠKODA';
                // $this->MySQL->query('INSERT INTO yapps_app_cis_brands SET ?u ON DUPLICATE KEY UPDATE ?u', $arIns, $arIns);
                if ( $b_id = $this->MySQL->getOne('SELECT id FROM yapps_app_cis_brands WHERE ext_id = ?i', $arIns['ext_id']) ) {
                    $this->MySQL->query('UPDATE yapps_app_cis_brands SET ?u WHERE id = ?i', $arIns, $b_id);
                } else {
                    $this->MySQL->query('INSERT INTO yapps_app_cis_brands SET ?u', $arIns);
                }
            };
            
            return $res;
        }
        public function getBrandAutoCRM( $id ) {
            $rs = $this->request(
                static::URL_BASE.static::URL_BRANDS.'/'.$id.'/info'
            );
            return $res;
        }

        public function getModels( $id ) {

            $res = $this->request(
                static::URL_BASE.static::URL_BRANDS.'/'.$id.'/models',
                [
                    'expand' => 'statistics'
                ]
            )['items'];
            foreach ($res as $k => $r) {
                $res[$k]['alias'] =  $this->generateModelAlias( $r['name'] );
                $res[$k]['body'] =  $this->getBody( $r['body_type'] );
                if ( $r['id'] == 2016 ) $res[$k]['alias'] = 'new-tugella';
                
                $arIns = [
                    'ext_id' => $r['id'],
                    'brand_id' => $this->MySQL->getOne('SELECT id FROM yapps_app_cis_brands WHERE ext_id = ?i', $id),
                    'name' => $r['name'],
                    'image' => $r['image'],
                    'code' => $res[$k]['alias'],
                    'body_id' => $this->MySQL->getOne('SELECT id FROM yapps_app_cis_bodies WHERE code = ?s', $res[$k]['body']['code']),
                ];
                // $this->MySQL->query('INSERT INTO yapps_app_cis_models_new_1 SET ?u ON DUPLICATE KEY UPDATE ?u', $arIns, $arIns);
                if ( $m_id = $this->MySQL->getOne('SELECT id FROM yapps_app_cis_models_new WHERE ext_id = ?i', $arIns['ext_id']) ) {
                    $this->MySQL->query('UPDATE yapps_app_cis_models_new SET ?u WHERE id = ?i', $arIns, $m_id);
                } else {
                    $this->MySQL->query('INSERT INTO yapps_app_cis_models_new SET ?u', $arIns);
                }
            }

            return $res;
        }

        public function getModelInfo( $id ) {

            $res['modifications'] = $this->request(
                static::URL_BASE.static::URL_MODELS.'/'.$id.'/modifications'
            )['items'];
            $res['equipments'] = $this->request(
                static::URL_BASE.static::URL_MODELS.'/'.$id.'/equipments'
            )['items'];
            $res['colors'] = $this->request(
                static::URL_BASE.static::URL_MODELS.'/'.$id.'/colors'
            )['items'];

            return $res;
        }
        
        public function getVehicles( $entity = 'new' ) {

            $SECTION = ($entity == 'new') ? static::URL_VEHICLES_NEW : static::URL_VEHICLES_USED;

            $time = time();
            $res = $this->request(
                static::URL_BASE.$SECTION ,
                [
                    'page' => 1,
                    'per-page' => 50
                ]
            );
            if ( !$res ) {
                $diff = time() - $time;
                echo 'Запрос сброшен и выполнялся '.$diff.'с'.PHP_EOL; 
            }
            
            foreach ( $res['items'] as $k => $is ) {
                if ( (int)$is['dealership']['id'] == 1514 ) unset($res['items'][$k]);
                // if ( (int)$is['brand_id'] == 125 || (int)$is['brand_id'] == 2743 ) unset($res['items'][$k]); // Исключить LADA и XCITE
                // if ( (int)$is['status']['id']==1 && stripos($is['images'][0]['full'], 'catalog')!==false ) unset($res[$k]);  // Исключить В НАЛИЧИИ без фото
            }

            // for ( $i=2; $i<=10; $i++ ) {
            for ( $i=2; $i<=(int)$res['_meta']['pageCount']; $i++ ) {

                $time = time();
                $r = $this->request(
                    static::URL_BASE.$SECTION ,
                    [
                        'page' => $i,
                        'per-page' => 50
                    ]
                );
                if ( !$r ) {
                    $diff = time() - $time;
                    echo 'Запрос сброшен и выполнялся '.$diff.'с'.PHP_EOL; 
                }
                $rs = $r['items'];

                if (count($rs)) foreach ( $rs as $k => $is ) {
                    if ( (int)$is['dealership']['id'] == 1514 ) unset($rs[$k]);
                    // if ( (int)$is['brand_id'] == 125 || (int)$is['brand_id'] == 2743 ) unset($rs[$k]); // Исключить LADA и XCITE
                    // if ( (int)$is['status']['id']==1 && stripos($is['images'][0]['full'], 'catalog')!==false ) unset($rs[$k]);  // Исключить В НАЛИЧИИ без фото
                }
                
                if (count($rs)) $res['items'] = array_merge( $res['items'], $rs);
            }

            return $res['items'];
        }

        public function getVehiclesUsed() {

            $res = $this->request(
                static::URL_BASE.static::URL_VEHICLES_USED ,
                [
                    'page' => 1,
                    'per-page' => 50
                ]
            );

            $items = $res['items'];

            for ( $i=2; $i<=(int)$res['_meta']['pageCount']; $i++ ) {

                $rs = $this->request(
                    static::URL_BASE.static::URL_VEHICLES_USED ,
                    [
                        'page' => $i,
                        'per-page' => 50
                    ]
                )['items'];
                
                // !LADA !XCITE ! Москвич
                // if (count($rs)) foreach ( $rs as $k => $is ) {
                //     if ( (int)$is['brand_id'] == 125 || (int)$is['brand_id'] == 132 || (int)$is['brand_id'] == 2743 ) unset($rs[$k]);
                // }
                
                if ( count($rs) ) $items = array_merge( $items, $rs);
                // Helper::sp( count($rs) );
                // Helper::sp( count($items) );
            }

            $res['items'] = $items;
            return $res;
        }

        public function getVehicle( $id, $type_id = 1 ) {
            
            $res = $this->request(
                static::URL_BASE.static::URL_VEHICLES.'/'.$id,
                [],
                true // включить сброс по таймауту
            );

            if ( !$res ) return false; // Сброс по таймауту

            if ( $res['status']['id'] == 1 || $res['status']['id'] == 2 ) {

                ////// !!!!!! Костыль !!!!!!!
                if ( $type_id == 1 ) {
                    if ( (int)$res['model_id'] == 91 ) $res['model_id'] = 7364;
                    if ( (int)$res['model_id'] == 92 ) $res['model_id'] = 7367;
                    if ( (int)$res['model_id'] == 280 ) $res['model_id'] = 276;
                    if ( (int)$res['model_id'] == 282 ) $res['model_id'] = 277;
                    // LADA XCITE -> XCITE
                    if ( (int)$res['model_id'] == 2537 ) {
                        $res['model_id'] = 2464;
                        $res['brand_id'] = 2743;
                    }
                    if ( (int)$res['model_id'] == 2538 ) {
                        $res['model_id'] = 2580;
                        $res['brand_id'] = 2743;
                    }
                }
                if ( $type_id == 2 ) {
                    if ( (int)$arIns['dealership_id'] == 1328 ) {
                        $arIns['dealership_id'] = 1502;
                        $res['dealership']['id'] = 1502;
                        $arIns['raw'] = json_encode($res);
                    }
                }

                unset($res['description']);
                $brand = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE ext_id = ?i', (int)$res['brand_id']);
                $model = $this->MySQL->getRow('SELECT * FROM ?n WHERE ext_id = ?i', 'yapps_app_cis_models_'.(($type_id==1)?'new':'used'), ($type_id == 1) ? (int)$res['model_id'] : (int)$res['ref_model_id']);
                $dealership = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_dealerships WHERE code = ?i', $res['dealership']['id']);

                $res['body'] = $this->getBody($res['body_type']);
                if ( $res['body']['code'] == 'none' ) $res['body'] = $this->getBody($res['model_name']);
                if ( $res['body']['code'] == 'none' ) $res['body'] = $this->getBody($res['ref_model_name']);
                if ( $res['body']['code'] == 'none' ) $res['body'] = $this->getBody($res['modification_name']);

                $res['color'] = $this->getColor($res['general'][2]['value']);
                $res['transmition'] = $res['transmission'] = ( $this->getTransmission($res['general'][1]['value']) ) ?: ['name'=>'Неизвестно', 'code'=>'none'];
                $res['engine'] = $this->getEngine($res['general'][0]['value']);
                $res['drive'] = $this->getDrive($res['specifications'][11]['value']);
                foreach ( $res['specifications'] as $k => $i ) {
                    $i['value'] = str_replace(['(', ')'], '', $i['value']);
                    $i['value'] = str_replace(',', '.', $i['value']);
                    $a = explode(' ', $i['value']);
                    if ( !((float)$a[0] < 1000) ) $a[0] = Helper::formatNumber($a[0]);
                    $res['specifications'][$k]['value'] = implode(' ', $a);
                }
                
                $arIns = [
                    'ext_id' => (int)$res['id'],
                    'type_id' => $type_id,
                    'brand_id' => $brand['id'],
                    'model_id' => $model['id'],
                    'vin' => $res['vin'],
                    'name' => $brand['name'].' '.$model['name'].(($res['equipment']?' '.$res['equipment']:'')),
                    'price' => $res['price'] - (($type_id==1&&!$this->isActiveModel($model['id'], $dealership['id']))?(int)$res['additional_equipment_price']:0),
                    'min_price' => $res['min_price'] - (($type_id==1&&!$this->isActiveModel($model['id'], $dealership['id']))?(int)$res['additional_equipment_price']:0),
                    'transmission' => $res['transmission']['code'],
                    'engine' => $res['engine']['code'],
                    'drive' => $res['drive']['code'],
                    'body' => $res['body']['code'],
                    'color' => $res['color']['code'],
                    'dealership_id' => $res['dealership']['id'],
                    'volume' => (float)str_replace(',', '.', explode(' ', $res['general'][0]['value'])[0])*1000,
                    'power' => (float)str_replace(',', '.', explode('(',explode(' ', $res['general'][0]['value'])[1])[1]),
                    'year' => $res['general'][4]['value'],
                    'mileage' => ( $type_id == 2 ) ? (int)$res['general'][5]['value'] : 0,
                    'instock' => ( $res['status']['id'] == 1 ),
                    'onway' => ( $res['status']['id'] == 2 ),
                    'discount' => ( $res['price'] > $res['min_price'] ),
                    'update_images' => 0,
                    'raw' => json_encode($res),
                    'created' => strtotime( (($res['vehicle_entry_date'])?:$res['vehicle_receipt_date']) )
                ];

                if ( $vehicle = $this->MySQL->getRow('SELECT * FROM ?n WHERE ext_id = ?i', $this->table->prod, (int)$res['id']) ) {
                    $o_images = json_decode( $vehicle['raw'], true )['images'];
                    
                    if ( count($o_images) != count($res['images']) ) {
                        $arIns['update_images'] = 1;
                    } else {
                        for ( $i=0; $i<count($o_images); $i++ ) {
                            if ( $o_images[$i]['full'] != $res['images'][$i]['full'] ) {
                                $arIns['update_images'] = 1;
                                break;
                            } 
                        }
                    }
                }
                if ( $arIns['update_images'] ) {
                    Helper::sp( 'Обновляем фотки для '.$arIns['ext_id'].' | '.$arIns['vin'] );
                    $res['update_images'] = true;
                }

                if ( $this->MySQL->getOne('SELECT ext_id FROM ?n WHERE ext_id = ?i', $this->table->cron, (int)$arIns['ext_id']) ) $this->MySQL->query('DELETE FROM ?n WHERE ext_id = ?i', $this->table->cron, (int)$arIns['ext_id'] );
                if ( $arIns['brand_id'] && $arIns['model_id'] && ((int)$res['status']['id'] == 1 || (int)$res['status']['id'] == 2) ) {
                    $this->MySQL->query('INSERT INTO ?n SET ?u', $this->table->cron, $arIns);
                    if ( $type_id == 1 && !preg_match('/[а-яё]/iu', $res['equipment']) && !$this->yappsGetEquipmentRuName($arIns['brand_id'], $arIns['model_id'], $res['equipment']) ) {
                        $res['eq'] = [
                            'brand' => $brand['name'],
                            'model' => $model['name'],
                            'equipment' => $res['equipment']
                        ];
                    }
                    $res['log'] = true;
                } else {
                    if ( ((int)$res['status']['id'] == 1 || (int)$res['status']['id'] == 2) ) {
                        $res['log'] = false;
                    }
                }

            }

            return $res;
        }


        
        public function makeImages() {

            $res = false;

            $vehicle = $this->MySQL->getRow('SELECT * FROM ?n WHERE use_internal_images = ?i OR update_images = ?i', $this->table->prod, 0, 1);
            
            if ( $vehicle ) {

                Helper::sp( $vehicle['ext_id'].': '. $this->table->prod);

                if ( file_exists(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id']) && !(int)$vehicle['update_images'] ) {

                    

                    $this->MySQL->query('UPDATE ?n SET ?u WHERE ext_id = ?i', $this->table->prod, ['use_internal_images'=>1], $vehicle['ext_id'] );
                    if ( !$this->MySQL->getCol('SELECT * FROM yapps_app_cis_images WHERE ext_id = ?i', (int)$vehicle['ext_id']) ) {
                        $images = array_slice( scandir(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id']), 2);
                        array_pop($images);
                        foreach ($images as $i) {
                            $arIns = [
                                'ext_id' => $vehicle['ext_id'],
                                'detail' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'].'/'.$i.'?'.md5_file(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'].'/'.$i),
                                'preview' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'].'/sm/'.$i.'?'.md5_file(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'].'/sm/'.$i),
                            ];
                            $this->MySQL->query('INSERT INTO yapps_app_cis_images SET ?u', $arIns);
                        }
                    }
                    $res = true;

                } else {

                    if ( file_exists(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id']) ) Helper::removeDirectory( __DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'] );
                    $this->MySQL->query('DELETE FROM yapps_app_cis_images WHERE ext_id = ?i', (int)$vehicle['ext_id']);

                    mkdir( __DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'] );
                    mkdir( __DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'].'/sm' );

                    foreach ( json_decode($vehicle['raw'], true)['images'] as $k => $url ) {

                        $arIns = ['ext_id' => $vehicle['ext_id']];
                        
                        $image = $this->makeImg($url['full'], 'full');
                        if ( $image ) {
                            imageJpeg($image, __DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'].'/'.(($k<10)?'0':'').$k.'.jpg', 80);
                            imagedestroy($image);
                            $res = true;
                        }
                        if ( !$res ) continue;
                        $arIns['detail'] = 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'].'/'.(($k<10)?'0':'').$k.'.jpg'.'?'.md5_file(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'].'/'.(($k<10)?'0':'').$k.'.jpg');

                        $res = false;
                        $image = $this->makeImg($url['preview_large'], 'preview');
                        if ( $image ) {
                            imageJpeg($image, __DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'].'/sm/'.(($k<10)?'0':'').$k.'.jpg', 80);
                            imagedestroy($image);
                            $res = true;
                        }
                        if ( !$res ) continue;
                        $arIns['preview'] = 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'].'/sm/'.(($k<10)?'0':'').$k.'.jpg'.'?'.md5_file(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'].'/sm/'.(($k<10)?'0':'').$k.'.jpg');

                        $this->MySQL->query('INSERT INTO yapps_app_cis_images SET ?u', $arIns);
                    }
                    $this->MySQL->query('UPDATE ?n SET ?u WHERE ext_id = ?i', $this->table->prod, ['use_internal_images'=>1,'update_images'=>0], $vehicle['ext_id'] );
                    if ( !$res ) Helper::removeDirectory( __DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'] );
                }
            
            } else {

                $vv = $this->MySQL->getCol('SELECT ext_id FROM ?n', $this->table->prod);
                $dirs = array_slice( scandir(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles'), 2);

                foreach ( $dirs as $dir ) {
                    if ( !in_array((int)$dir, $vv) ) {
                        Helper::removeDirectory( __DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$dir );
                        $this->MySQL->query('DELETE FROM yapps_app_cis_images WHERE ext_id = ?i', (int)$dir);
                    }
                }
                    
            }
        }

        public function setImages() {

            $vv = $this->MySQL->getCol('SELECT ext_id FROM ?n', $this->table->cron);
            $dirs = array_slice( scandir(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles'), 2);

            foreach ( $vv as $v ) {
                if ( in_array($v, $dirs) ) {
                    $sets[] = $v;
                    if ( !$this->MySQL->getCol('SELECT * FROM yapps_app_cis_images WHERE ext_id = ?i', (int)$v) ) {
                        $images = array_slice( scandir(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$v), 2);
                        array_pop($images);
                        foreach ($images as $i) {
                            $arIns = [
                                'ext_id' => $v,
                                'detail' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/vehicles/'.$v.'/'.$i.'?'.md5_file(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$v.'/'.$i),
                                'preview' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/vehicles/'.$v.'/sm/'.$i.'?'.md5_file(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$v.'/sm/'.$i),
                            ];
                            $this->MySQL->query('INSERT INTO yapps_app_cis_images SET ?u', $arIns);
                        }
                    }
                }
            }
            $this->MySQL->query('UPDATE ?n SET ?u WHERE ext_id IN (?a)', $this->table->cron, ['use_internal_images'=>1], $sets );
        }

        public function isOk_cron() {

            $c1 = $this->MySQL->getOne('SELECT COUNT(*) FROM ?n WHERE type_id = ?i', $this->table->cron, 1);
            $c2 = $this->MySQL->getOne('SELECT COUNT(*) FROM ?n WHERE type_id = ?i', $this->table->cron, 2);

            return ( $c1 && $c2 );
        }



        ////////////////////////////////////////////////////////////////
		// AutoCRM Raw  ////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////

        public function getBrandsAutoCRM( $params = null ) {
            $rs = $this->request(
                static::URL_BASE.static::URL_BRANDS
            );
            return $rs['items'];
        }

        public function getModelsAutoCRM( $id ) {

            $res = $this->request(
                static::URL_BASE.static::URL_BRANDS.'/'.$id.'/models',
                [
                    'expand' => 'statistics'
                ]
            );
            $res['url'] = static::URL_BASE.static::URL_BRANDS.'/'.$id.'/models';

            return $res;
        }

        public function getVehiclesAutoCRM( $brand, $model ) {

            $arBrand = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE ext_id = ?i', (int)$brand);
            $arModel = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_models_new WHERE brand_id = ?i', $arBrand['id'] );

            $res = $this->apiDBGetVehicles(
                'new',
                [
                    'brand' => $arBrand['code'],
                    'model' => $arModel['code']
                ]
            );

            foreach ( $res['items'] as $k => $r ) 
                if ( $r['type'] == 'random_cta' ) 
                    unset( $res['items'][$k] );

            return $res['items'];
        } 

        public function getVehicleAutoCRM($id) {

            $res = $this->request(
                static::URL_BASE.static::URL_VEHICLES.'/'.$id,
                [],
                true // включить сброс по таймауту
            );
            return $res;
        }

        public function getDealershipsAutoCRM( $params = null ) {
            $rs = $this->request(
                static::URL_BASE.static::URL_DEALERSHIPS
            );
            return $rs;
        }



        ////////////////////////////////////////////////////////////////
		// LOG  ////////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////

        public function Log( $d, $f ) {

            if ( !file_exists(__DIR__.'/Logs/Cis/'.date('Y')) ) mkdir(__DIR__.'/Logs/Cis/'.date('Y'));
			if ( !file_exists(__DIR__.'/Logs/Cis/'.date('Y').'/'.date('m')) ) mkdir(__DIR__.'/Logs/Cis/'.date('Y').'/'.date('m'));
			if ( !file_exists(__DIR__.'/Logs/Cis/'.date('Y').'/'.date('m').'/'.date('d')) ) mkdir(__DIR__.'/Logs/Cis/'.date('Y').'/'.date('m').'/'.date('d'));

            file_put_contents( __DIR__.'/Logs/Cis/'.date('Y').'/'.date('m').'/'.date('d').'/'.$f.'.txt', $d );
        }

        
        ////////////////////////////////////////////////////////////////
		// Dealerships  ////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////

        public function getDBDealerships( $entity = 'new', $GET = [] ) {

            $w[] = $this->MySQL->parse('type_id = ?i', (($entity=='new')?1:2));
            if ( $GET['city'] ) $w[] = $this->MySQL->parse( 'city IN (?a)', explode(',', $GET['city']) );
            if ( $GET['!dealership'] ) $w[] = $this->MySQL->parse( 'code NOT IN (?a)', explode(',', $GET['!dealership']) );
            if ( $GET['!brand'] ) $w[] = $this->MySQL->parse( 'brand_id NOT IN (?a)', $this->MySQL->getCol('SELECT id FROM yapps_app_cis_brands WHERE code IN (?a)', explode(',', $GET['!brand'])) );
            if ( $GET['dealership'] ) {
                $w[] = $this->MySQL->parse( 'code IN (?a)', explode(',', $GET['dealership']) );
            } else {
                $w[] = $this->MySQL->parse(
                    'code IN (?a)',
                    $this->MySQL->getCol('SELECT DISTINCT dealership_id FROM ?n', $this->table->prod)
                );
            }

            $query = 'WHERE '.implode(' AND ',$w).' ORDER BY name ASC';

            return $this->MySQL->getAll('SELECT * FROM yapps_app_cis_dealerships ?p', $query);
        }
        public function getDBDealership( $code, $brand ) {

            // Helper::sp( [$code, $brand] );

            return $this->MySQL->getRow('SELECT * FROM yapps_app_cis_dealerships WHERE code = ?i AND brand_id = ?i', $code, $brand);
        }

        public function getDBDealershipsIDs( $entity = 'new', $GET = [] ) {

            // Helper::sp($GET);

            $w[] = $this->MySQL->parse('type_id = ?i', (($entity=='new')?1:2));
            if ( $GET['city'] ) $w[] = $this->MySQL->parse( 'city IN (?a)', ((is_array($GET['city']))?$GET['city']:explode(',', $GET['city'])) );
            if ( $GET['brand'] && $entity=='new' ) $w[] = $this->MySQL->parse(
                'brand_id IN (?a)', 
                $this->MySQL->getCol(
                    'SELECT id FROM yapps_app_cis_brands WHERE code IN (?a)',
                    explode(',', $GET['brand'])
                )
            );
            if ( $GET['!dealership'] ) $w[] = $this->MySQL->parse( 'code NOT IN (?a)', explode(',', $GET['!dealership']) );
            if ( $GET['dealership'] ) {
                $w[] = $this->MySQL->parse( 'code IN (?a)', explode(',', $GET['dealership']) );
            } else {
                $w[] = $this->MySQL->parse(
                    'code IN (?a)',
                    $this->MySQL->getCol('SELECT DISTINCT dealership_id FROM ?n', $this->table->prod)
                );
            }
            // Helper::sp($w);
            $query = 'WHERE '.implode(' AND ',$w).' ORDER BY code ASC';

            // Helper::sp($query);

            return $this->MySQL->getCol('SELECT code FROM yapps_app_cis_dealerships ?p', $query);
        }



        public function getDBDealershipsCities( $entity = 'new', $GET = [] ) {

            // Helper::sp($GET);

            $w[] = $this->MySQL->parse('type_id = ?i', (($entity=='new')?1:2));
            if ( $GET['city'] ) $w[] = $this->MySQL->parse( 'city IN (?a)', explode(',', $GET['city']) );
            if ( $GET['dealership'] ) $w[] = $this->MySQL->parse( 'code IN (?a)', explode(',', $GET['dealership']) );
            if ( $GET['!dealership'] ) $w[] = $this->MySQL->parse( 'code NOT IN (?a)', explode(',', $GET['!dealership']) );

            $query = 'WHERE '.implode(' AND ',$w);

            // Helper::sp($query);

            $cities = $this->MySQL->getCol('SELECT DISTINCT in_city FROM yapps_app_cis_dealerships ?p', $query);
            if ( in_array('Краснодаре', $cities) || in_array('Яблоновском', $cities) ) array_push( $cities, 'Краснодаре', 'Яблоновском' );
            $cities = array_unique($cities);
            sort($cities);

            if ( count($cities) == 1 ) return $cities[0];

            $res = array_chunk($cities, count($cities)-1);
            return implode(', ', $res[0]).' и '.$res[1][0];
            
        }

        public function getDBDealershipsFromInStock( $entity = 'new' ) {

            $res = $this->MySQL->getCol('SELECT DISTINCT dealership_id FROM ?n WHERE type_id = ?i',$this->table->prod ,($entity=='new')?1:2);
            $res = $this->MySQL->getAll(
                'SELECT * FROM yapps_app_cis_dealerships WHERE code IN (?a) ORDER BY name',
                $this->MySQL->getCol(
                    'SELECT DISTINCT dealership_id FROM ?n WHERE type_id = ?i',
                    $this->table->prod ,
                    ($entity=='new')?1:2
                )
            );
            
            return $res;
        }




        ////////////////////////////////////////////////////////////////
		// CACHE  //////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////

        private function getQueryCache( $query ) {

            $result = false;
            if ( file_exists(__DIR__.'/Cache/'.get_class($this).'/'.md5($query).'.json') ) {
                $res = json_decode( file_get_contents(__DIR__.'/Cache/'.get_class($this).'/'.md5($query).'.json'), true );
                if ( $res['expire'] > time() ) {
                    $result = $res['cache'];
                } else {
                    unlink(__DIR__.'/Cache/'.get_class($this).'/'.md5($query).'.json');
                }
            }
            return $result;
        }

        private function setQueryCache( $query = '', $data = [] ) {
            
            if ( !empty($data) ) {
                $res = [
                    'expire' => time()+60*60,
                    'query' => $query,
                    'hash' => md5($query),
                    'cache' => $data,
                    'count' => count($data)
                ];
                file_put_contents(__DIR__.'/Cache/'.get_class($this).'/'.md5($query).'.json', json_encode($res));
            }

            return true;
        }

        public function getQCaches() {
            $res = [];
            $cache = array_splice(scandir(__DIR__.'/Cache/'.get_class($this)), 2);
            foreach ( $cache as $file ) {
                $tmp = json_decode( file_get_contents(__DIR__.'/Cache/'.get_class($this).'/'.$file), true );
                if ( $tmp ) 
                    $res[] = [
                        'hash' => $tmp['hash'],
                        'query' => $tmp['query'],
                        'expire' => $tmp['expire'],
                        'count' => $tmp['count']
                    ]; 
            }
            return $res;
        }
        public function delQCahce( $q ) {

            unlink(__DIR__.'/Cache/'.get_class($this).'/'.$q.'.json');
            return true;
        }
        public function clearQCaches() {

            foreach (glob(__DIR__.'/Cache/'.get_class($this).'/*') as $file) unlink($file);
            return true;
        }

        ////////////////////////////////////////////////////////////////
		// API  ////////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////
        
        public function apiDBGetCount( $entity = 'new', $GET = [] ) {
            
            $GET['type'] = ( $entity == 'new' ) ? 1 : 2;
            if ( $GET['city'] ) $GET['city'] = $this->buildCity($GET['city']);


            if ( $GET['dealership'] ) {
                foreach ( explode(',',$GET['dealership']) as $i ) {
                    if ( (int)$i ) {
                        $d_get[] = $i;
                    } else {
                        if ( $tmp = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_dealerships WHERE url = ?s', $i) ) {
                            $d_get[] = $tmp['code'];
                            if ( $t = $this->MySQL->getOne('SELECT code FROM yapps_app_cis_brands WHERE id = ?i', $tmp['brand_id']) ) $b_get[] = $t;
                        }
                    }
                    if ( $d_get ) $GET['dealership'] = implode(',', $d_get);
                    if ( $b_get ) $GET['brand'] = implode(',', $b_get);
                }
            }

            
            $GET['dealership'] = implode(',', $this->getDBDealershipsIDs( $entity, $GET ) );
            // Helper::sp($GET);
            if ( !$GET['dealership'] ) return 0;
            
            $query = $this->buildDBQuery( $GET );
            // Helper::sp($query);
            return (int)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p', $this->table->prod, $query);
        }

        public function apiDBGetModelsCount( $entity = 'new', $GET = [] ) {

            $query = 'SELECT COUNT(ext_id) as vehicles, model_id FROM ?n WHERE type_id = ?i';
            if ( $GET['model'] ) {
                
                $models = $this->MySQL->getAll(
                    'SELECT id, ext_id, code FROM ?n WHERE code IN (?a)',
                    'yapps_app_cis_models_'.$entity, 
                    explode(',', $GET['model'])
                );
                
                $query .= $this->MySQL->parse(
                    ' AND model_id IN (?a)',
                    $this->MySQL->getCol(
                        'SELECT id FROM ?n WHERE code IN (?a)',
                        'yapps_app_cis_models_'.$entity, 
                        explode(',', $GET['model'])
                    )
                );
            }
            $query .= ' GROUP BY model_id';
            $res = $this->MySQL->getInd('model_id', $query, $this->table->prod, ($entity == 'new') ? 1 : 2);

            foreach ( $models as $i ) if ( (int)$res[$i['id']]['vehicles'] ) $result[$i['code']] = $res[$i['id']];

            return $result;
        }

        public function apiDBGetModel( $entity = 'new', $item = null, $GET = [] ) {

            $GET['type'] = ( $entity == 'new' ) ? 1 : 2;
            if ( $item ) $GET['model'] = $item;
            if ( $GET['city'] && !$GET['dealership'] ) {
                $GET['city'] = $this->buildCity($GET['city']);
                $GET['dealership'] = implode(',', $this->getDBDealershipsIDs( $entity, $GET ) );
                // $j = json_decode( file_get_contents('https://yug-avto.ru/api/dealerships?mode='.$entity.'&city='.$GET['city']), true );
                // foreach ($j as $c) $d[] = $c['code'];
                // // $res['in_city'] = $j[0]['city'];
                // $GET['dealership'] = implode(',', $d);
            }
            $query = $this->buildDBQuery( $GET );

            $rs = $this->MySQL->getAll('SELECT * FROM ?n ?p', $this->table->prod, $query);
            $res['brand'] = $this->MySQL->getRow(
                'SELECT * FROM yapps_app_cis_brands WHERE id IN (?a)',
                $this->MySQL->getCol(
                    'SELECT DISTINCT brand_id FROM ?n ?p',
                    $this->table->prod,
                    $query
                )
            );
            $res['model'] = $this->MySQL->getRow(
                'SELECT * FROM ?n WHERE id IN (?a)',
                'yapps_app_cis_models_'.$entity, 
                $this->MySQL->getCol(
                    'SELECT DISTINCT model_id FROM ?n ?p',
                    $this->table->prod,
                    $query
                )
            );

            foreach ( $rs as $i ) {

                $item = json_decode($i['raw'], true);
                $item['brand'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE id = ?i', $i['brand_id']);
                $item['model'] = $this->MySQL->getRow('SELECT * FROM ?n WHERE id = ?i', 'yapps_app_cis_models_'.$entity, $i['model_id']);
                $item['Discount'] = ( $item['min_price'] < $item['price'] );
                $item['InStock'] = ( $item['status']['id'] == 1 );
                $item['OnWay'] = ( $item['status']['id'] == 2 );
                $item['power'] = $i['power'];
                $item['volume'] = $i['volume'];
                $item['use_internal_images'] = (int)$i['use_internal_images'];
                $item['image'] = ( (int)$i['use_internal_images'] ) ? $this->getInternalImage($i['ext_id']) : $item['images'][0]['preview_large'];
                $res['items'][] = $item;
            }

            return $res;

        }

        public function apiDBGetBrands( $entity, $GET ) {

            $GET['type'] = ( $entity == 'new' ) ? 1 : 2;
            
            if ( $GET['city'] ) $GET['city'] = $this->buildCity($GET['city']);

            if ( $GET['dealership'] ) {
                foreach ( explode(',',$GET['dealership']) as $i ) {
                    if ( (int)$i ) {
                        $d_get[] = $i;
                    } else {
                        if ( $tmp = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_dealerships WHERE url = ?s', $i) ) {
                            $d_get[] = $tmp['code'];
                            if ( $t = $this->MySQL->getOne('SELECT code FROM yapps_app_cis_brands WHERE id = ?i', $tmp['brand_id']) ) $b_get[] = $t;
                        }
                    }
                    if ( $d_get ) $GET['dealership'] = implode(',', $d_get);
                    if ( $b_get ) $GET['brand'] = implode(',', $b_get);
                }
            }

            
            $GET['dealership'] = implode(',', $this->getDBDealershipsIDs( $entity, $GET ) );
            $res['in_city'] = $this->getDBDealershipsCities( $entity, $GET );

            $query = $this->buildDBQuery( $GET );
            
            $res['dropLists']['bodies'] = $this->MySQL->getAll(
                'SELECT * FROM yapps_app_cis_bodies WHERE code IN (?a) ORDER BY name',
                $this->MySQL->getCol(
                    'SELECT DISTINCT body FROM ?n ?p',
                    $this->table->prod,
                    $this->buildDBQuery( array_merge($GET,['limit'=>6]) )
                )
            );
            foreach ( $res['dropLists']['bodies'] as $k => $b ) if ( $b['code'] == 'none' ) unset($res['dropLists']['bodies'][$k]);
            sort($res['dropLists']['bodies']);
            if ( count($res['dropLists']['bodies']) == 6 ) array_pop($res['dropLists']['bodies']);

            $res['dropLists']['brands'] = $this->MySQL->getAll(
                'SELECT * FROM yapps_app_cis_brands WHERE id IN (?a) ORDER BY name ASC',
                $this->MySQL->getCol(
                    'SELECT DISTINCT brand_id FROM ?n ?p',
                    $this->table->prod,
                    $query
                )
            );
            foreach ( $res['dropLists']['brands'] as $k => $i ) {
                $get_ = $GET;
                $get_['brand'] = $i['code']; $query = $this->buildDBQuery( $get_ );
                $res['dropLists']['brands'][$k]['vehicles'] = (int)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p', $this->table->prod, $query);
                $res['dropLists']['brands'][$k]['min'] = (int)$this->MySQL->getOne('SELECT MIN(min_price) FROM ?n ?p', $this->table->prod, $query);
                $res['dropLists']['brands'][$k]['max'] = (int)$this->MySQL->getOne('SELECT MAX(min_price) FROM ?n ?p', $this->table->prod, $query);
                $res['dropLists']['brands'][$k]['path'] = '/cars/'.$entity.'/'.$i['code'];
                $res['dropLists']['brands'][$k]['indx'] = $k;
            }

            $get_ = $GET;
            unset($get_['price']);
            $res['ranges']['price'] = $this->MySQL->getRow(
                'SELECT MIN(min_price) as min, MAX(min_price) as max FROM ?n ?p',
                $this->table->prod,
                $this->buildDBQuery($get_)
            );
            $res['ranges']['price']['value'] = [
                ( $res['ranges']['price']['min'] > (int)explode(',', $GET['price'])[0] ) ? (int)$res['ranges']['price']['min'] : (int)explode(',', $GET['price'])[0],
                ( $res['ranges']['price']['max'] < (($GET['price'])?(int)explode(',', $GET['price'])[1]:99999999) ) ? (int)$res['ranges']['price']['max'] : (int)explode(',', $GET['price'])[1],
            ];
            $res['ranges']['price']['min'] = (int)$res['ranges']['price']['min'];
            $res['ranges']['price']['max'] = (int)$res['ranges']['price']['max'];

            $res['totalCount'] = (int)$this->MySQL->getOne(
                'SELECT COUNT(*) FROM ?n ?p',
                $this->table->prod,
                $this->buildDBQuery($get_)
            );

            return $res;
        }

        public function apiDBGetBrand($entity, $code, $GET = []) {

            $res = $this->MySQL->getRow(
                'SELECT * FROM yapps_app_cis_brands WHERE code = ?s',
                $code
            );
            $GET['brand'] = $code;
            $GET['type'] = ( $entity == 'new' ) ? 1 : 2;
            if ( $GET['city'] && !$GET['dealership'] ) {
                $GET['city'] = $this->buildCity($GET['city']);
                $GET['dealership'] = implode(',', $this->getDBDealershipsIDs( $entity, $GET ) );
                // $j = json_decode( file_get_contents('https://yug-avto.ru/api/dealerships?mode='.$entity.'&city='.$GET['city']), true );
                // foreach ($j as $c) $d[] = $c['code'];
                // $res['in_city'] = $j[0]['city'];
                // $GET['dealership'] = implode(',', $d);
            }
            $query = $this->buildDBQuery( $GET );
            $d = $this->MySQL->getRow('SELECT COUNT(*) as vehicles, MIN(min_price) as min, MAX(min_price) as max FROM ?n ?p', $this->table->prod, $query);
            

            return array_merge($res, $d);
        }

        public function apiDBGetBrandsModels( $entity, $GET ) {

            $GET['type'] = ( $entity == 'new' ) ? 1 : 2;
            if ( $GET['city'] ) $GET['city'] = $this->buildCity($GET['city']);

            if ( $GET['dealership'] ) {
                foreach ( explode(',',$GET['dealership']) as $i ) {
                    if ( (int)$i ) {
                        $d_get[] = $i;
                    } else {
                        if ( $tmp = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_dealerships WHERE url = ?s', $i) ) {
                            $d_get[] = $tmp['code'];
                            if ( $t = $this->MySQL->getOne('SELECT code FROM yapps_app_cis_brands WHERE id = ?i', $tmp['brand_id']) ) $b_get[] = $t;
                        }
                    }
                    if ( $d_get ) $GET['dealership'] = implode(',', $d_get);
                    if ( $b_get ) $GET['brand'] = implode(',', $b_get);
                }
            }
            
            $GET['dealership'] = implode(',', $this->getDBDealershipsIDs( $entity, $GET ) );
            $res['in_city'] = $this->getDBDealershipsCities( $entity, $GET );

            $query = $this->buildDBQuery( $GET );

            $res = $this->MySQL->getAll(
                'SELECT * FROM yapps_app_cis_brands WHERE id IN (?a) ORDER BY name ASC',
                $this->MySQL->getCol(
                    'SELECT DISTINCT brand_id FROM ?n ?p',
                    $this->table->prod,
                    $query
                )
            );
            foreach ( $res as $k => $i ) {
                
                $GET['brand'] = $i['code']; $query = $this->buildDBQuery( $GET );
                $res[$k]['vehicles'] = (int)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p', $this->table->prod, $query);
                $res[$k]['min'] = (int)$this->MySQL->getOne('SELECT MIN(min_price) FROM ?n ?p', $this->table->prod, $query);
                $res[$k]['max'] = (int)$this->MySQL->getOne('SELECT MAX(min_price) FROM ?n ?p', $this->table->prod, $query);

                $res[$k]['_models'] = $this->MySQL->getAll(
                    'SELECT * FROM ?n WHERE id IN (?a)',
                    'yapps_app_cis_models_'.$entity, 
                    $this->MySQL->getCol(
                        'SELECT DISTINCT model_id FROM ?n ?p',
                        $this->table->prod,
                        $query
                    )
                );
                foreach ( $res[$k]['_models'] as $km => $im ) {

                    $get_models = $GET;
                    $get_models['model'] = $im['code']; $query = $this->buildDBQuery( $get_models );
                    // Helper::sp($query);
                    $res[$k]['_models'][$km]['vehicles'] = (int)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p', $this->table->prod, $query);
                    $res[$k]['_models'][$km]['body'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_bodies WHERE id = ?i', $im['body_id']);
                    $res[$k]['_models'][$km]['_colors'] = count( $this->MySQL->getCol('SELECT DISTINCT color FROM ?n ?p', $this->table->prod, $query));
                    
                    $get_models['sort'] = 'discount';
                    $res[$k]['_models'][$km]['Discount'] = (bool)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p AND discount = 1', $this->table->prod, $this->buildDBQuery( $get_models ));
                    $get_models['sort'] = 'instock';
                    $res[$k]['_models'][$km]['InStock'] = (bool)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p AND instock = 1', $this->table->prod, $this->buildDBQuery( $get_models ));
                    $get_models['sort'] = 'onway';
                    $res[$k]['_models'][$km]['OnWay'] = (bool)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p AND onway = 1', $this->table->prod, $this->buildDBQuery( $get_models ));

                    $res[$k]['_models'][$km]['min_price'] = (int)$this->MySQL->getOne('SELECT MIN(min_price) FROM ?n ?p', $this->table->prod, $query);

                    $res[$k]['_models'][$km]['min'] = (int)$this->MySQL->getOne('SELECT MIN(min_price) FROM ?n ?p', $this->table->prod, $query);
                    $res[$k]['_models'][$km]['max'] = (int)$this->MySQL->getOne('SELECT MAX(min_price) FROM ?n ?p', $this->table->prod, $query);

                    if ( $GET['sort'] == 'discount' && !$res[$k]['_models'][$km]['Discount'] ) unset( $res[$k]['_models'][$km] );
                    if ( $GET['sort'] == 'instock' && !$res[$k]['_models'][$km]['InStock'] ) unset( $res[$k]['_models'][$km] );
                    if ( $GET['sort'] == 'onway' && !$res[$k]['_models'][$km]['OnWay'] ) unset( $res[$k]['_models'][$km] );
                }
                sort($res[$k]['_models']);
            }

            return $res;
        }
        public function apiDBGetBrandsForFooters( $entity, $GET ) {

            $GET['type'] = ( $entity == 'new' ) ? 1 : 2;
            if ( $GET['city'] ) $GET['city'] = $this->buildCity($GET['city']);

            if ( $GET['dealership'] ) {
                foreach ( explode(',',$GET['dealership']) as $i ) {
                    if ( (int)$i ) {
                        $d_get[] = $i;
                    } else {
                        if ( $tmp = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_dealerships WHERE url = ?s', $i) ) {
                            $d_get[] = $tmp['code'];
                            if ( $t = $this->MySQL->getOne('SELECT code FROM yapps_app_cis_brands WHERE id = ?i', $tmp['brand_id']) ) $b_get[] = $t;
                        }
                    }
                    if ( $d_get ) $GET['dealership'] = implode(',', $d_get);
                    if ( $b_get ) $GET['brand'] = implode(',', $b_get);
                }
            }
            
            $GET['dealership'] = implode(',', $this->getDBDealershipsIDs( $entity, $GET ) );

            $query = $this->buildDBQuery( $GET );

            $res = $this->MySQL->getAll(
                'SELECT yapps_app_cis_brands.*, COUNT('.$this->table->prod.'.ext_id) AS vehicles
                    FROM yapps_app_cis_brands, ?n
                    WHERE id IN (?a) 
                    AND yapps_app_cis_brands.id = '.$this->table->prod.'.brand_id
                    GROUP BY yapps_app_cis_brands.name ',
                $this->table->prod,
                $this->MySQL->getCol(
                    'SELECT DISTINCT brand_id FROM ?n ?p',
                    $this->table->prod,
                    $query
                )
            );

            return $res;
        }


        public function apiDBGetInStockBrandsModels( $entity, $GET ) {

            $GET['type'] = ( $entity == 'new' ) ? 1 : 2;
            $query = $this->buildDBQuery( $GET );

            $res = $this->MySQL->getAll(
                'SELECT * FROM yapps_app_cis_brands WHERE id IN (?a) ORDER BY name ASC',
                $this->MySQL->getCol(
                    'SELECT DISTINCT brand_id FROM ?n ?p',
                    $this->table->prod,
                    $query
                )
            );
            foreach ( $res as $k => $i ) {
                
                $GET['brand'] = $i['code']; $query = $this->buildDBQuery( $GET );
                $res[$k]['vehicles'] = (int)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p', $this->table->prod, $query);
                $res[$k]['min'] = (int)$this->MySQL->getOne('SELECT MIN(min_price) FROM ?n ?p', $this->table->prod, $query);
                $res[$k]['max'] = (int)$this->MySQL->getOne('SELECT MAX(min_price) FROM ?n ?p', $this->table->prod, $query);

                $res[$k]['_models'] = $this->MySQL->getAll(
                    'SELECT * FROM ?n WHERE id IN (?a)',
                    'yapps_app_cis_models_'.$entity, 
                    $this->MySQL->getCol(
                        'SELECT DISTINCT model_id FROM ?n ?p',
                        $this->table->prod,
                        $query
                    )
                );
                foreach ( $res[$k]['_models'] as $km => $im ) {

                    $get_models = $GET;
                    $get_models['model'] = $im['code']; $query = $this->buildDBQuery( $get_models );
                    // Helper::sp($query);
                    $res[$k]['_models'][$km]['vehicles'] = (int)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p', $this->table->prod, $query);
                    $res[$k]['_models'][$km]['body'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_bodies WHERE id = ?i', $im['body_id']);
                    $res[$k]['_models'][$km]['_colors'] = count( $this->MySQL->getCol('SELECT DISTINCT color FROM ?n ?p', $this->table->prod, $query));
                    
                    $get_models['sort'] = 'discount';
                    $res[$k]['_models'][$km]['Discount'] = (bool)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p AND discount = 1', $this->table->prod, $this->buildDBQuery( $get_models ));
                    $get_models['sort'] = 'instock';
                    $res[$k]['_models'][$km]['InStock'] = (bool)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p AND instock = 1', $this->table->prod, $this->buildDBQuery( $get_models ));
                    $get_models['sort'] = 'onway';
                    $res[$k]['_models'][$km]['OnWay'] = (bool)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p AND onway = 1', $this->table->prod, $this->buildDBQuery( $get_models ));

                    $res[$k]['_models'][$km]['min_price'] = (int)$this->MySQL->getOne('SELECT MIN(min_price) FROM ?n ?p', $this->table->prod, $query);

                    $res[$k]['_models'][$km]['min'] = (int)$this->MySQL->getOne('SELECT MIN(min_price) FROM ?n ?p', $this->table->prod, $query);
                    $res[$k]['_models'][$km]['max'] = (int)$this->MySQL->getOne('SELECT MAX(min_price) FROM ?n ?p', $this->table->prod, $query);

                    if ( $GET['sort'] == 'discount' && !$res[$k]['_models'][$km]['Discount'] ) unset( $res[$k]['_models'][$km] );
                    if ( $GET['sort'] == 'instock' && !$res[$k]['_models'][$km]['InStock'] ) unset( $res[$k]['_models'][$km] );
                    if ( $GET['sort'] == 'onway' && !$res[$k]['_models'][$km]['OnWay'] ) unset( $res[$k]['_models'][$km] );
                }
                sort($res[$k]['_models']);
            }

            return $res;
        }

        public function apiDBGetModels( $entity, $GET ) {

            $GET['type'] = ( $entity == 'new' ) ? 1 : 2;

            if ( $GET['city'] ) $GET['city'] = $this->buildCity($GET['city']);

            if ( $GET['dealership'] ) {
                foreach ( explode(',',$GET['dealership']) as $i ) {
                    if ( (int)$i ) {
                        $d_get[] = $i;
                    } else {
                        if ( $tmp = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_dealerships WHERE url = ?s', $i) ) {
                            $d_get[] = $tmp['code'];
                            if ( $t = $this->MySQL->getOne('SELECT code FROM yapps_app_cis_brands WHERE id = ?i', $tmp['brand_id']) ) $b_get[] = $t;
                        }
                    }
                    if ( $d_get ) $GET['dealership'] = implode(',', $d_get);
                    if ( $b_get ) $GET['brand'] = implode(',', $b_get);
                }
            } else {
                $GET['dealership'] = implode(',', $this->getDBDealershipsIDs( $entity, $GET ) );
            }
            $res['in_city'] = $this->getDBDealershipsCities( $entity, $GET );
            
            $query = $this->buildDBQuery( $GET );

            $res = $this->MySQL->getAll(
                'SELECT * FROM ?n WHERE id IN (?a) ORDER BY name ASC',
                'yapps_app_cis_models_'.$entity, 
                $this->MySQL->getCol(
                    'SELECT DISTINCT model_id FROM ?n ?p',
                    $this->table->prod,
                    $query
                )
            );

            foreach ( $res as $km => $im ) {

                $get_models = $GET;
                $get_models['model'] = $im['code']; $query = $this->buildDBQuery( $get_models );
                $res[$km]['vehicles'] = (int)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p', $this->table->prod, $query);
                $res[$km]['body'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_bodies WHERE id = ?i', $im['body_id']);
                $res[$km]['_colors'] = count( $this->MySQL->getCol('SELECT DISTINCT color FROM ?n ?p', $this->table->prod, $query));
                    
                $get_models['sort'] = 'discount';
                $res[$km]['Discount'] = (bool)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p AND discount = 1', $this->table->prod, $this->buildDBQuery( $get_models ));
                $get_models['sort'] = 'instock';
                $res[$km]['InStock'] = (bool)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p AND instock = 1', $this->table->prod, $this->buildDBQuery( $get_models ));
                $get_models['sort'] = 'onway';
                $res[$km]['OnWay'] = (bool)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p AND onway = 1', $this->table->prod, $this->buildDBQuery( $get_models ));
                
                $res[$km]['min_price'] = (int)$this->MySQL->getOne('SELECT MIN(min_price) FROM ?n ?p', $this->table->prod, $query);
                $res[$km]['brand'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE id = ?i', (int)$im['brand_id']);

                $res[$km]['min'] = (int)$this->MySQL->getOne('SELECT MIN(min_price) FROM ?n ?p', $this->table->prod, $query);
                $res[$km]['max'] = (int)$this->MySQL->getOne('SELECT MAX(min_price) FROM ?n ?p', $this->table->prod, $query);

                $res[$km]['path'] = '/cars/'.$entity.'/'.$res[$km]['brand']['code'].'/'.$im['code'];
            }
            sort($res);
            foreach ( $res as $k => $item ) $res[$k]['indx'] = $k;

            return $res;
        }

        public function apiDBGetFilter( $entity, $GET ) {

            $GET['type'] = ( $entity == 'new' ) ? 1 : 2;
            if ( $GET['city'] ) $GET['city'] = $this->buildCity($GET['city']);

            if ( $GET['dealership'] ) {
                foreach ( explode(',',$GET['dealership']) as $i ) {
                    if ( (int)$i ) {
                        $d_get[] = $i;
                    } else {
                        if ( $tmp = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_dealerships WHERE url = ?s', $i) ) {
                            $d_get[] = $tmp['code'];
                            if ( $t = $this->MySQL->getOne('SELECT code FROM yapps_app_cis_brands WHERE id = ?i', $tmp['brand_id']) ) $b_get[] = $t;
                        }
                    }
                    if ( $d_get ) $GET['dealership'] = implode(',', $d_get);
                    if ( $b_get ) $GET['brand'] = implode(',', $b_get);
                }
            } else {
                $GET['dealership'] = implode(',', $this->getDBDealershipsIDs( $entity, $GET ) );
            }

            $res['in_city'] = $this->getDBDealershipsCities( $entity, $GET );

            // Helper::sp($GET);
            
            $query = $this->buildDBQuery( $GET );

            $res['totalCount'] = (int)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p', $this->table->prod, $query);

            $res['dropLists']['mode'] = [
                [
                    'name' => 'Новые автомобили',
                    'code' => 'new'
                ],
                [
                    'name' => 'Автомобили с пробегом',
                    'code' => 'used'
                ]
            ];
            $res['dropLists']['brands'] = $this->apiDBGetBrands($entity, $GET)['dropLists']['brands'];
            $res['dropLists']['models'] = [];
            if ( $GET['brand'] ) {
                $get_models = $GET;
                unset($get_models['model']);
                $res['dropLists']['models'] = $this->apiDBGetModels($entity, $get_models);
            }
            $res['dropLists']['transmissions'] = $this->MySQL->getAll(
                'SELECT * FROM yapps_app_cis_transmissions WHERE code IN (?a) ORDER BY name',
                $this->MySQL->getCol(
                    'SELECT DISTINCT transmission FROM ?n ?p',
                    $this->table->prod,
                    $query
                )
            );
            $res['dropLists']['engines'] = $this->MySQL->getAll(
                'SELECT * FROM yapps_app_cis_engines WHERE code IN (?a) ORDER BY name',
                $this->MySQL->getCol(
                    'SELECT DISTINCT engine FROM ?n ?p',
                    $this->table->prod,
                    $query
                )
            );
            $res['dropLists']['drives'] = $this->MySQL->getAll(
                'SELECT * FROM yapps_app_cis_drives WHERE code IN (?a) ORDER BY name',
                $this->MySQL->getCol(
                    'SELECT DISTINCT drive FROM ?n ?p',
                    $this->table->prod,
                    $query
                )
            );
            $res['dropLists']['bodies'] = $this->MySQL->getAll(
                'SELECT * FROM yapps_app_cis_bodies WHERE code IN (?a) ORDER BY name',
                $this->MySQL->getCol(
                    'SELECT DISTINCT body FROM ?n ?p',
                    $this->table->prod,
                    $query
                )
            );
            // $url = 'https://yug-avto.ru/api/dealerships?mode='.$entity.'&city='.$this->buildCity($GET['city']);
            // if ( $GET['dealership'] ) $url .= '&code='.$GET['dealership'];
            // Helper::sp($url);
            // if ( $GET['brand'] && $entity == 'new' ) $url .= '&brand='.$GET['brand'];
            // $res['dropLists']['dealerships'] = json_decode(file_get_contents($url), true);
            $dc_get = ['city'=>$this->buildCity($GET['city'])];
            if ( $GET['!dealership'] ) $dc_get['!dealership'] = $GET['!dealership'];
            if ( $GET['!brand'] ) $dc_get['!brand'] = $GET['!brand'];
            $res['dropLists']['dealerships'] = $this->getDBDealerships($entity, $dc_get);
            // $res['dropLists']['dealerships'] = $this->getDBDealershipsFromInStock($entity);

            $res['dropLists']['colors'] = $this->MySQL->getAll(
                'SELECT * FROM yapps_app_cis_colors WHERE code IN (?a) ORDER BY name',
                $this->MySQL->getCol(
                    'SELECT DISTINCT color FROM ?n ?p',
                    $this->table->prod,
                    $query
                )
            );

            $get_ = $GET;
            unset($get_['price']);
            $res['ranges']['price'] = $this->MySQL->getRow(
                'SELECT MIN(min_price) as min, MAX(min_price) as max FROM ?n ?p',
                $this->table->prod,
                $this->buildDBQuery($get_)
            );
            $res['ranges']['price']['value'] = [
                ( $res['ranges']['price']['min'] > (int)explode(',', $GET['price'])[0] ) ? (int)$res['ranges']['price']['min'] : (int)explode(',', $GET['price'])[0],
                ( $res['ranges']['price']['max'] < (($GET['price'])?(int)explode(',', $GET['price'])[1]:99999999) ) ? (int)$res['ranges']['price']['max'] : (int)explode(',', $GET['price'])[1],
            ];
            $res['ranges']['price']['min'] = (int)$res['ranges']['price']['min'];
            $res['ranges']['price']['max'] = (int)$res['ranges']['price']['max'];

            $get_ = $GET;
            unset($get_['volume']);
            $res['ranges']['volume'] = $this->MySQL->getRow(
                'SELECT MIN(volume) as min, MAX(volume) as max FROM ?n ?p',
                $this->table->prod,
                $this->buildDBQuery($get_)
            );
            $res['ranges']['volume']['value'] = [
                ( $res['ranges']['volume']['min'] > (int)explode(',', $GET['volume'])[0] ) ? (int)$res['ranges']['volume']['min'] : (int)explode(',', $GET['volume'])[0],
                ( $res['ranges']['volume']['max'] < (($GET['volume'])?(int)explode(',', $GET['volume'])[1]:99999999) ) ? (int)$res['ranges']['volume']['max'] : (int)explode(',', $GET['volume'])[1],
            ];
            $res['ranges']['volume']['min'] = (int)$res['ranges']['volume']['min'];
            $res['ranges']['volume']['max'] = (int)$res['ranges']['volume']['max'];

            $get_ = $GET;
            unset($get_['power']);
            $res['ranges']['power'] = $this->MySQL->getRow(
                'SELECT MIN(power) as min, MAX(power) as max FROM ?n ?p',
                $this->table->prod,
                $this->buildDBQuery($get_)
            );
            $res['ranges']['power']['value'] = [
                ( $res['ranges']['power']['min'] > (int)explode(',', $GET['power'])[0] ) ? (int)$res['ranges']['power']['min'] : (int)explode(',', $GET['power'])[0],
                ( $res['ranges']['power']['max'] < (($GET['power'])?(int)explode(',', $GET['power'])[1]:99999999) ) ? (int)$res['ranges']['power']['max'] : (int)explode(',', $GET['power'])[1],
            ];
            $res['ranges']['power']['min'] = (int)$res['ranges']['power']['min'];
            $res['ranges']['power']['max'] = (int)$res['ranges']['power']['max'];

            $get_ = $GET;
            unset($get_['year']);
            $res['ranges']['year'] = $this->MySQL->getRow(
                'SELECT MIN(year) as min, MAX(year) as max FROM ?n ?p',
                $this->table->prod,
                $this->buildDBQuery($get_)
            );
            $res['ranges']['year']['value'] = [
                ( $res['ranges']['year']['min'] > (int)explode(',', $GET['year'])[0] ) ? (int)$res['ranges']['year']['min'] : (int)explode(',', $GET['year'])[0],
                ( $res['ranges']['year']['max'] < (($GET['year'])?(int)explode(',', $GET['year'])[1]:99999999) ) ? (int)$res['ranges']['year']['max'] : (int)explode(',', $GET['year'])[1],
            ];
            $res['ranges']['year']['min'] = (int)$res['ranges']['year']['min'];
            $res['ranges']['year']['max'] = (int)$res['ranges']['year']['max'];

            if ( $res['in_city'] ) $GET['in_city'] = $res['in_city'];
            $res['meta'] = $this->getDirectMeta( $entity, $GET );

            if ( $entity == 'used' ) {
                
                $dcs = $this->getDBDealershipsIDs($entity, ['city'=>$GET['city']]);
                if ( !empty($dcs) ) {

                    $res['counts']['pass'] = 0;
                    $k = array_search(1489, $dcs);
                    if ( $k !== false ) unset( $dcs[$k] );
                    $res['counts']['pass'] = (int)$this->MySQL->getOne(
                        'SELECT COUNT(*) FROM ?n ?p', 
                        $this->table->prod, 
                        $this->buildDBQuery(
                            [  
                                'type' => 2,
                                'dealership' => implode(',', $dcs)
                            ]
                        )
                    );

                    $res['counts']['comm'] = 0;
                    if ( $k !== false ) {
                        $res['counts']['comm'] = (int)$this->MySQL->getOne(
                            'SELECT COUNT(*) FROM ?n ?p', 
                            $this->table->prod, 
                            $this->buildDBQuery(
                                [  
                                    'type' => 2,
                                    'dealership' => 1489
                                ]
                            )
                        );
                    }
                    $res['counts']['prem'] = 0;
                    if ( in_array(1502, $dcs) ) {
                        $res['counts']['prem'] = (int)$this->MySQL->getOne(
                            'SELECT COUNT(*) FROM ?n ?p', 
                            $this->table->prod, 
                            $this->buildDBQuery(
                                [  
                                    'type' => 2,
                                    'dealership' => 1502
                                ]
                            )
                        );
                    }
                }
            }
            if ($GET['nometa']) unset($res['meta']);
            
            return $res;
        }
        
        
        public function apiDBGetVehicle($entity, $id, $brand = null, $GET = null) {

            // Helper::sd( $GET );
            
            $type_id = 0;
            if ( $entity == 'new' ) $type_id = 1;
            if ( $entity == 'used' ) $type_id = 2;

            $rs = $this->MySQL->getRow('SELECT * FROM ?n WHERE ext_id = ?i', $this->table->prod, (int)$id);
            if ( !$rs || $rs['type_id'] != $type_id ) {
                header("HTTP/1.0 404 Not Found");
                return null;
            }
            $res = json_decode($rs['raw'], true);
            // Helper::sp($res);
            $res['brand'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE id = ?i', $rs['brand_id']);
            $res['model'] = $this->MySQL->getRow('SELECT * FROM ?n WHERE id = ?i', 'yapps_app_cis_models_'.$entity, $rs['model_id']);
            if ( $ru_eq = $this->yappsGetEquipmentRuName($res['brand']['id'], $res['model']['id'], $res['equipment']) ) $res['equipment'] = $ru_eq;
            $res['price'] = (int)$rs['price'];
            $res['min_price'] = (int)$rs['min_price'];
            $res['_updated'] = date('d.m.Y');
            if (empty($res['discounts']) && $res['price'] > $res['min_price']) {
                $res['discounts'] = [
                    [
                        'name' => 'Скидка',
                        'description' => 'Специальное предложение',
                        'sum' => $res['price'] - $res['min_price'],
                        'active' => true
                    ]
                ];
            }
            if (!empty($res['discounts']) && is_array($res['discounts'])) {
                foreach ($res['discounts'] as $k => $i) {
                    $res['discounts'][$k]['active'] = true;
                    $discountType = (isset($i['types']) && is_array($i['types']) && isset($i['types'][0])) ? $i['types'][0] : '';
                    switch ($discountType) {
                        case 'trade_in': $res['discounts'][$k]['description'] = 'За Трейд-ин'; break;
                        case 'credit': $res['discounts'][$k]['description'] = 'За кредит'; break;
                        case 'leasing': $res['discounts'][$k]['description'] = 'За лизинг'; break;
                        case 'insurance': $res['discounts'][$k]['description'] = 'За страховку'; break;
                        case 'other': $res['discounts'][$k]['description'] = $i['name']; break;
                    }
                }
            }
            $res['_additional_equipment_description'] = explode(PHP_EOL, $res['additional_equipment_description']);
            foreach ($res['_additional_equipment_description'] as $k => $i) if (!$i) unset($res['_additional_equipment_description'][$k]); 
            $res['_additional_options'] = explode(PHP_EOL, $res['additional_options']);
            foreach ($res['_additional_options'] as $k => $i) if (!$i) unset($res['_additional_options'][$k]); 
            $res['_specifications'] = array_chunk(!empty($res['specifications']) && is_array($res['specifications']) ? $res['specifications'] : [], 6);
            $res['general'][] = [
                'name' => 'Двигатель',
                'value' => explode(' / ', $res['general'][0]['value'])[0]
            ];
            $res['general'][] = [
                'name' => 'Топливо',
                'value' => explode(' / ', $res['general'][0]['value'])[1]
            ];
            $res['_additional'] = [];
            foreach ($res['side_options'] as $o) $res['_additional'][] = $o['name'];
            $res['_additional'] = array_merge($res['_additional'], $res['_additional_equipment_description']);
            if ( $rs['use_internal_images'] ) {
                $res['_images'] = $this->getInternalImages($rs['ext_id']);
                if ( empty($res['_images']) ) {
                    $res['_images'][] = [
                        'id' => 1,
                        'big' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$res['body']['code'].'.jpg',
                        'thumb' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$res['body']['code'].'_sm.jpg',
                        'detail' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$res['body']['code'].'.jpg',
                        'preview' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$res['body']['code'].'_sm.jpg',
                    ];
                }
            } else {
                foreach ( $res['images'] as $k => $i ) {
                    $res['_images'][] = [
                        'id' => $k+1,
                        'big' => $i['full'],
                        'detail' => $i['full'],
                        'thumb' => $i['preview_small'],
                        'preview' => $i['preview_small'],
                    ];
                }
                if ( empty($res['_images']) ) {
                    $res['_images'][] = [
                        'id' => 1,
                        'big' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$res['body']['code'].'.jpg',
                        'thumb' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$res['body']['code'].'_sm.jpg',
                        'detail' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$res['body']['code'].'.jpg',
                        'preview' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$res['body']['code'].'_sm.jpg',
                    ];
                }
            }

            $res['power'] = (int)$rs['power'];
            $res['volume'] = (int)$rs['volume'];
            
            foreach ($res['options'] as $k => $i) $res['options'][$k]['view'] = false;

            
            // $res['dealership'] = $this->yappsGetDealership($res['dealership']['id']); $res['dealership']['id'] = $res['dealership']['code'];
            $res['dealership'] = ( $type_id == 1 ) ? $this->getDBDealership($res['dealership']['id'],$res['brand']['id']) : $this->yappsGetDealership($res['dealership']['id']);
            $res['dealership']['id'] = $res['dealership']['code'];
            
            if ( !empty($d) && is_array($d) && count($d) > 1 ) foreach ( $d as $i ) if ( $i['brand'] == $res['brand']['code'] ) $res['dealership']['name'] = $i['name'];

            $res['recomended'] = $this->apiDBGetRecomended( $res['id'], $brand );
            $res['others'] = $this->apiDBGetOther( $res['id'] );

            $res['meta'] = $this->getVehicleMeta($entity, $res, (($GET['site'])?:'yug-avto.ru'));

            foreach ( $res['tags'] as $t ) if ( $tag = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_tags WHERE name = ?s', $t) ) $res['_tags'][] = $tag; 

            return $res;
        }

        public function apiDBGetRecomended( $id, $brand = null ) {
            
            $res = [];

            $v = $this->MySQL->getRow('SELECT * FROM ?n WHERE ext_id = ?i', $this->table->prod, (int)$id);
            $min = $max = $v['min_price'];
            
            $query = $this->MySQL->parse(
                'SELECT * FROM ?n
                WHERE min_price >= ?i
                AND min_price <= ?i
                AND type_id = ?i 
                AND model_id != ?i',
                $this->table->prod,
                $min-0.1*$min,
                $max+0.1*$max,
                $v['type_id'],
                $v['model_id']
            );

            if ( $brand ) $query .= $this->MySQL->parse(
                ' AND brand_id = ?i', $this->MySQL->getOne('SELECT id FROM yapps_app_cis_brands WHERE code = ?s', $brand)
            );
            if ( $v['type_id'] == 2 ) {
                if ( $v['dealership_id'] != 1489 ) $query .= $this->MySQL->parse( ' AND dealership_id NOT IN (?a)', [1489] );
                if ( $v['dealership_id'] == 1489 ) $query .= $this->MySQL->parse( ' AND dealership_id IN (?a)', [1489] );
            }
            if ( $v['type_id'] == 1 ) {
                if ( $v['dealership_id'] != 1650 && $v['dealership_id'] != 1853 ) $query .= $this->MySQL->parse( ' AND dealership_id NOT IN (?a)', [1650, 1853] );
                if ( $v['dealership_id'] == 1650 || $v['dealership_id'] == 1853 ) $query .= $this->MySQL->parse( ' AND dealership_id IN (?a)', [1650, 1853] );
            }

            $query .= $this->MySQL->parse( ' AND ext_id != ?i', (int)$id );

            $query .= ' ORDER BY RAND() LIMIT 12';

            $rs = $this->MySQL->getAll($query);
            foreach ( $rs as $item ) {

                $b = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE id = ?i', $item['brand_id']);
                $m = $this->MySQL->getRow('SELECT * FROM ?n WHERE id = ?i', 'yapps_app_cis_models_'.(($v['type_id']==1)?'new':'used'), $item['model_id']);

                $raw = json_decode($item['raw'], true);
                
                $tmp = [
                    'ext_id' => $item['ext_id'],
                    'entity' => (($v['type_id']==1)?'new':'used'),
                    'brand_alias' => $b['code'],
                    'brand' => $b,
                    'model' => $m,
                    'tags' => $raw['tags'],
                    'model_alias' => $m['code'],
                    'name' => $b['name'].' '.$m['name'].' '.(($this->yappsGetEquipmentRuName($b['id'], $m['id'], $raw['equipment'])&&$v['type_id']==1)?$this->yappsGetEquipmentRuName($b['id'], $m['id'], $raw['equipment']):$raw['equipment']),
                    'link' => '/'.$b['code'].'/'.$m['code'].'/'.$item['ext_id'].'/',
                    'image' => ( (int)$item['use_internal_images'] ) ? $this->getInternalImage($item['ext_id']) : $raw['images'][0]['preview_large'],
                    'price' => (int)$item['price'],
                    'min_price' => (int)$item['min_price'],
                    'status' => $raw['status'],
                    'body' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_bodies WHERE code = ?s', $item['body']),
                    'engine' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_engines WHERE code = ?s', $item['engine']),
                    'transmission' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_transmissions WHERE code = ?s', $item['transmission']),
                    'drive' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_drives WHERE code = ?s', $item['drive']),
                    'discount' => ( $item['min_price'] < $item['price'] ),
                    'dealership' => $raw['dealership'],
                    'images' => $this->safeGetImages((int)$item['use_internal_images'], $item['ext_id'], $raw['images'])
                ];
                // if ( $ru_eq = $this->yappsGetEquipmentRuName($b['id'], $m['id'], $raw['equipment']) ) $raw['equipment'] = $ru_eq;
                // (($this->yappsGetEquipmentRuName($b['id'], $m['id'], $raw['equipment']))?:$raw['equipment'])
                foreach ( $raw['tags'] as $t ) if ( $tag = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_tags WHERE name = ?s', $t) ) $tmp['_tags'][] = $tag; 

                if ( !$tmp['image'] ) $tmp['image'] = 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg';
                if ( !$tmp['images'][0] ) {
                    $tmp['images'] = [];
                    $tmp['images'][] = [
                        'detail' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                        'preview' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                        'big' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                        'thumb' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                    ];
                }
                if ( count($tmp['images']) < 4 ) {
                    for ( $ii = count($tmp['images']); $ii < 4; $ii++ ) {
                        $tmp['images'][] = $tmp['images'][count($tmp['images'])-1];
                    }
                }

                $tmp['general'][] = (($raw['general'][4]['value'])?:date('Y')).' г.в.';
                $tmp['general'][] = ($item['type_id']==1)?$tmp['body']['name']:number_format((int)$raw['general'][5]['value'], 0, ',', ' ').' км';
                $tmp['general'][] = number_format($item['volume']/1000, 1, ',', ' ').' ('.  $item['power'].' л.с.)';
                $tmp['general'][] = ($tmp['engine']['code']!='none')?$tmp['engine']['name']:null;
                $tmp['general'][] = ($tmp['transmission']['code']!='none')?$tmp['transmission']['name']:null;
                $tmp['general'][] = ($tmp['drive']['code']!='none')?$tmp['drive']['name']:null;

                if ($raw) $res[] = $tmp;
            }

            return $res;
        }

        public function apiDBGetOther( $id, $brand = null ) {

            // $res = [];

            $v = $this->MySQL->getRow('SELECT * FROM ?n WHERE ext_id = ?i', $this->table->prod, (int)$id);
            $query = 'SELECT * FROM ?n WHERE min_price >= ?i AND min_price <= ?i ';

            $rs = $this->MySQL->getAll(
                'SELECT * FROM ?n
                WHERE min_price >= ?i
                AND min_price <= ?i
                AND body = ?s 
                AND type_id = ?i 
                ORDER BY ext_id DESC
                LIMIT 12',
                $this->table->prod,
                $v['min_price']-0.1*$v['min_price'],
                $v['min_price']+0.1*$v['min_price'],
                $v['body'],
                ( $v['type_id']==1 ) ? 2 : 1
            );
            foreach ( $rs as $item ) {

                $b = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE id = ?i', $item['brand_id']);
                $m = $this->MySQL->getRow('SELECT * FROM ?n WHERE id = ?i', 'yapps_app_cis_models_'.(($v['type_id']==1)?'used':'new'), $item['model_id']);

                $raw = json_decode($item['raw'], true);
                
                $tmp = [
                    'ext_id' => $item['ext_id'],
                    'entity' => (($v['type_id']==1)?'used':'new'),
                    'brand_alias' => $b['code'],
                    'brand' => $b,
                    'model' => $m,
                    'tags' => $raw['tags'],
                    'model_alias' => $m['code'],
                    'name' => $b['name'].' '.$m['name'].' '.(($this->yappsGetEquipmentRuName($b['id'], $m['id'], $raw['equipment'])&&$v['type_id']==1)?$this->yappsGetEquipmentRuName($b['id'], $m['id'], $raw['equipment']):$raw['equipment']),
                    'link' => '/'.$b['code'].'/'.$m['code'].'/'.$item['ext_id'].'/',
                    'image' => ( (int)$item['use_internal_images'] ) ? $this->getInternalImage($item['ext_id']) : $raw['images'][0]['preview_large'],
                    'price' => (int)$item['price'],
                    'min_price' => (int)$item['min_price'],
                    'status' => $raw['status'],
                    'body' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_bodies WHERE code = ?s', $item['body']),
                    'engine' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_engines WHERE code = ?s', $item['engine']),
                    'transmission' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_transmissions WHERE code = ?s', $item['transmission']),
                    'drive' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_drives WHERE code = ?s', $item['drive']),
                    'discount' => ( $item['min_price'] < $item['price'] ),
                    'dealership' => $raw['dealership'],
                    'images' => $this->safeGetImages((int)$item['use_internal_images'], $item['ext_id'], $raw['images'])
                ];
                foreach ( $raw['tags'] as $t ) if ( $tag = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_tags WHERE name = ?s', $t) ) $tmp['_tags'][] = $tag; 

                if ( !$tmp['image'] ) $tmp['image'] = 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg';
                if ( !$tmp['images'][0] ) {
                    $tmp['images'] = [];
                    $tmp['images'][] = [
                        'detail' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                        'preview' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                        'big' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                        'thumb' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                    ];
                }
                if ( count($tmp['images']) < 4 ) {
                    for ( $ii = count($tmp['images']); $ii < 4; $ii++ ) {
                        $tmp['images'][] = $tmp['images'][count($tmp['images'])-1];
                    }
                }

                $tmp['general'][] = (($raw['general'][4]['value'])?:date('Y')).' г.в.';
                $tmp['general'][] = ($item['type_id']==1)?$tmp['body']['name']:number_format((int)$raw['general'][5]['value'], 0, ',', ' ').' км';
                $tmp['general'][] = number_format($item['volume']/1000, 1, ',', ' ').' ('.  $item['power'].' л.с.)';
                $tmp['general'][] = ($tmp['engine']['code']!='none')?$tmp['engine']['name']:null;
                $tmp['general'][] = ($tmp['transmission']['code']!='none')?$tmp['transmission']['name']:null;
                $tmp['general'][] = ($tmp['drive']['code']!='none')?$tmp['drive']['name']:null;

                if ( $raw ) if ( $tmp['brand'] && $tmp['model'] ) $res[] = $tmp;
            }

            return $res;
        }

        public function apiDBGetLimitVehicles( $entity = 'new', $GET = [] ) {

            $GET['type'] = ( $entity == 'new' ) ? 1 : 2;
            
            if ( $GET['city'] ) $GET['city'] = $this->buildCity($GET['city']);

            if ( $GET['dealership'] ) {
                foreach ( explode(',',$GET['dealership']) as $i ) {
                    if ( (int)$i ) {
                        $d_get[] = $i;
                    } else {
                        if ( $tmp = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_dealerships WHERE url = ?s', $i) ) {
                            $d_get[] = $tmp['code'];
                            if ( $t = $this->MySQL->getOne('SELECT code FROM yapps_app_cis_brands WHERE id = ?i', $tmp['brand_id']) ) $b_get[] = $t;
                        }
                    }
                    if ( $d_get ) $GET['dealership'] = implode(',', $d_get);
                    if ( $b_get ) $GET['brand'] = implode(',', $b_get);
                }
            }

            
            $GET['dealership'] = implode(',', $this->getDBDealershipsIDs( $entity, $GET ) );
            
            $query = $this->buildDBQuery( $GET );

            $rs = $this->MySQL->getAll('SELECT * FROM ?n ?p', $this->table->prod, $query);

            foreach ( $rs as $item ) {

                $b = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE id = ?i', $item['brand_id']);
                $m = $this->MySQL->getRow('SELECT * FROM ?n WHERE id = ?i', 'yapps_app_cis_models_'.$entity, $item['model_id']);

                $raw = json_decode($item['raw'], true);
                
                $tmp = [
                    'ext_id' => $raw['id'],
                    'brand_alias' => $b['code'],
                    'model_alias' => $m['code'],
                    'brand' => $b,
                    'model' => $m,
                    'name' => $b['name'].' '.$m['name'].' '.(($this->yappsGetEquipmentRuName($b['id'], $m['id'], $raw['equipment'])&&$v['type_id']==1)?$this->yappsGetEquipmentRuName($b['id'], $m['id'], $raw['equipment']):$raw['equipment']),
                    'link' => '/cars/'.(($item['type_id']==1)?'new':'used').'/'.$b['code'].'/'.$m['code'].'/'.$item['ext_id'],
                    'image' => ( (int)$item['use_internal_images'] ) ? $this->getInternalImage($item['ext_id']) : $raw['images'][0]['preview_large'],
                    'price' => (int)$item['price'],
                    'min_price' => (int)$item['min_price'],
                    'status' => $raw['status'],
                    'body' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_bodies WHERE code = ?s', $item['body']),
                    'engine' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_engines WHERE code = ?s', $item['engine']),
                    'transmission' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_transmissions WHERE code = ?s', $item['transmission']),
                    'drive' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_drives WHERE code = ?s', $item['drive']),
                    'discount' => ( $item['min_price'] < $item['price'] ),
                    'dealership' => $raw['dealership'],
                    'images' => $this->safeGetImages((int)$item['use_internal_images'], $item['ext_id'], $raw['images'])
                ];
                foreach ( $raw['tags'] as $t ) if ( $tag = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_tags WHERE name = ?s', $t) ) $tmp['_tags'][] = $tag; 

                if ( !$tmp['image'] ) $tmp['image'] = 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg';
                if ( !$tmp['images'][0] ) {
                    $tmp['images'] = [];
                    $tmp['images'][] = [
                        'detail' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                        'preview' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                        'big' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                        'thumb' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                    ];
                }
                if ( count($tmp['images']) < 4 ) {
                    for ( $ii = count($tmp['images']); $ii < 4; $ii++ ) {
                        $tmp['images'][] = $tmp['images'][count($tmp['images'])-1];
                    }
                }

                $tmp['general'][] = (($raw['general'][4]['value'])?:date('Y')).' г.в.';
                $tmp['general'][] = ($item['type_id']==1)?$tmp['body']['name']:number_format((int)$raw['general'][5]['value'], 0, ',', ' ').' км';
                $tmp['general'][] = number_format($item['volume']/1000, 1, ',', ' ').' ('.  $item['power'].' л.с.)';
                $tmp['general'][] = ($tmp['engine']['code']!='none')?$tmp['engine']['name']:null;
                $tmp['general'][] = ($tmp['transmission']['code']!='none')?$tmp['transmission']['name']:null;
                $tmp['general'][] = ($tmp['drive']['code']!='none')?$tmp['drive']['name']:null;

                $res[] = $tmp;
            }

            return $res;
        }

        public function apiDBGetRandomVehicles( $entity = 'new', $GET = [] ) {
            
            $res = [];
            foreach ( $this->Conf->Random['price'] as $i )
                $res = array_merge($res, $this->apiDBGetLimitVehicles($entity, [
                    'limit' => $this->Conf->Random['limit'],
                    'price' => $i
                ]));
            if ( $entity == 'used' ) 
                $res = array_merge($res, $this->apiDBGetLimitVehicles($entity, [
                    'limit' => $this->Conf->Random['limit'],
                    'dealership' => '1489,1533'
                ]));
            shuffle($res);

            return $res;
        }
        public function apiDBGetRandomVehicles_NEW( $entity = 'new', $GET = [] ) {
            
            $GET['type'] = ( $entity == 'new' ) ? 1 : 2;
            
            if ( $GET['city'] ) $GET['city'] = $this->buildCity($GET['city']);
            if ( $GET['dealership'] ) {
                foreach ( explode(',',$GET['dealership']) as $i ) {
                    if ( (int)$i ) {
                        $d_get[] = $i;
                    } else {
                        if ( $tmp = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_dealerships WHERE url = ?s', $i) ) {
                            $d_get[] = $tmp['code'];
                            if ( $t = $this->MySQL->getOne('SELECT code FROM yapps_app_cis_brands WHERE id = ?i', $tmp['brand_id']) ) $b_get[] = $t;
                        }
                    }
                    if ( $d_get ) $GET['dealership'] = implode(',', $d_get);
                    if ( $b_get ) $GET['brand'] = implode(',', $b_get);
                }
            }

            $GET['dealership'] = implode(',', $this->getDBDealershipsIDs( $entity, $GET ) );
            // Helper::sp($GET);
            $GET['sort'] = 'random';
            $res['in_city'] = $this->getDBDealershipsCities( $entity, $GET );
            
            $query = $this->buildDBQuery( $GET );

            $rs = $this->MySQL->getAll('SELECT * FROM ?n ?p', $this->table->prod, $query);
            
            $get_total = $GET;
            unset( $get_total['page'] );
            $res['totalCount'] = (int)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p', $this->table->prod, $this->buildDBQuery($get_total));

            $get_ = $GET;
            $res['ranges']['price'] = $this->MySQL->getRow(
                'SELECT MIN(min_price) as min, MAX(min_price) as max FROM ?n ?p',
                $this->table->prod,
                $this->buildDBQuery(['type'=>$GET['type']])
            );
            $res['ranges']['price']['value'] = $this->MySQL->getRow(
                'SELECT MIN(min_price) as min, MAX(min_price) as max FROM ?n ?p',
                $this->table->prod,
                $this->buildDBQuery($get_)
            );
            $res['ranges']['price']['min'] = (int)$res['ranges']['price']['min'];
            $res['ranges']['price']['max'] = (int)$res['ranges']['price']['max'];
            $res['ranges']['price']['value']['min'] = (int)$res['ranges']['price']['value']['min'];
            $res['ranges']['price']['value']['max'] = (int)$res['ranges']['price']['value']['max'];

            // Helper::sp($this->buildDBQuery($get_));

            foreach ( $rs as $item ) {

                $b = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE id = ?i', $item['brand_id']);
                $m = $this->MySQL->getRow('SELECT * FROM ?n WHERE id = ?i', 'yapps_app_cis_models_'.$entity, $item['model_id']);

                $raw = json_decode($item['raw'], true);
                
                $tmp = [
                    'ext_id' => $raw['id'],
                    'brand_alias' => $b['code'],
                    'model_alias' => $m['code'],
                    'vin' => $raw['vin'],
                    'brand' => $b,
                    'model' => $m,
                    'name' => $b['name'].' '.$m['name'].' '.(($this->yappsGetEquipmentRuName($b['id'], $m['id'], $raw['equipment'])&&$v['type_id']==1)?$this->yappsGetEquipmentRuName($b['id'], $m['id'], $raw['equipment']):$raw['equipment']),
                    'link' => '/cars/'.(($item['type_id']==1)?'new':'used').'/'.$b['code'].'/'.$m['code'].'/'.$item['ext_id'],
                    'image' => ( (int)$item['use_internal_images'] ) ? $this->getInternalImage($item['ext_id']) : $raw['images'][0]['preview_large'],
                    'price' => (int)$item['price'],
                    'min_price' => (int)$item['min_price'],
                    'status' => $raw['status'],
                    'body' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_bodies WHERE code = ?s', $item['body']),
                    'engine' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_engines WHERE code = ?s', $item['engine']),
                    'transmission' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_transmissions WHERE code = ?s', $item['transmission']),
                    'drive' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_drives WHERE code = ?s', $item['drive']),
                    'discount' => ( $item['min_price'] < $item['price'] ),
                    'dealership' => $raw['dealership'],
                    'images' => $this->safeGetImages((int)$item['use_internal_images'], $item['ext_id'], $raw['images'])
                ];
                foreach ( $raw['tags'] as $t ) if ( $tag = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_tags WHERE name = ?s', $t) ) $tmp['_tags'][] = $tag; 

                if ( !$tmp['image'] ) $tmp['image'] = 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg';
                if ( !$tmp['images'][0] ) {
                    $tmp['images'] = [];
                    $tmp['images'][] = [
                        'detail' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                        'preview' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                        'big' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                        'thumb' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$tmp['body']['code'].'.jpg',
                    ];
                }
                if ( count($tmp['images']) < 4 ) {
                    for ( $ii = count($tmp['images']); $ii < 4; $ii++ ) {
                        $tmp['images'][] = $tmp['images'][count($tmp['images'])-1];
                    }
                }

                $tmp['general'][] = (($raw['general'][4]['value'])?:date('Y')).' г.в.';
                $tmp['general'][] = ($item['type_id']==1)?$tmp['body']['name']:number_format((int)$raw['general'][5]['value'], 0, ',', ' ').' км';
                $tmp['general'][] = number_format($item['volume']/1000, 1, ',', ' ').' ('.  $item['power'].' л.с.)';
                $tmp['general'][] = ($tmp['engine']['code']!='none')?$tmp['engine']['name']:null;
                $tmp['general'][] = ($tmp['transmission']['code']!='none')?$tmp['transmission']['name']:null;
                $tmp['general'][] = ($tmp['drive']['code']!='none')?$tmp['drive']['name']:null;

                $res['items'][] = $tmp;
            }

            return $res;
        }


        public function apiDBGetVehicles( $entity = 'new', $GET = [] ) {

            if ( !$GET['site'] ) $GET['site'] = parse_url($_SERVER['HTTP_REFERER'])['host'];
            if ( $this->get404($GET) ) return ['force_404' => true];

            if ( !isset($GET['type']) || !in_array($GET['type'], ['new', 'used', 'all']) ) {
                $GET['type'] = ( $entity == 'new' ) ? 'new' : 'used';
            }
            if ( $GET['city'] ) $GET['city'] = $this->buildCity($GET['city']);
            $token = 'ef6541490c8bb9d481d37020b6a1953e';

            // Определяем порт: avatr (тест) работает на 8081, yug-avto (прод) на 8080
            $port = (strpos($_SERVER['HTTP_HOST'], 'avatr') !== false) ? 8081 : 8080;
            
            $params = $GET;
            unset($params['site']);
            unset($params['token']);

            if ( $GET['brand'] ) {
                $b_arr = explode(',', $GET['brand']);
                if ( in_array('chery', $b_arr) && $GET['type'] == 'new' ) $params['brand'] = implode(',', array_unique(array_merge($b_arr, ['tenet'])));
                if ( in_array('tank', $b_arr) && $GET['type'] == 'new' ) $params['brand'] = implode(',', array_unique(array_merge($b_arr, ['wey'])));
            }
            if ( $GET['dealership'] ) {
                $b_arr = explode(',', $GET['dealership']);
                if ( in_array('chery-yablonovskiy', $b_arr) && $GET['type'] == 'new' ) $params['dealership'] = implode(',', array_unique(array_merge($b_arr, ['tenet-yablonovskiy'])));
                if ( in_array('chery-maykop', $b_arr) && $GET['type'] == 'new' ) $params['dealership'] = implode(',', array_unique(array_merge($b_arr, ['tenet-maykop'])));
            }

            if ( !$params['dealership'] && empty($GET['id']) ) {
                $params['dealership'] = implode(',', $this->getDBDealershipsIDs( $entity, $GET ) );
            }

            $url = 'http://127.0.0.1:' . $port . '/api/v1/cis/vehicles?' . http_build_query($params);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $token
            ]);
            $response = curl_exec($ch);
            curl_close($ch);

            $res = [];
            if ($response) {
                $res = json_decode($response, true);
            }

            if (!is_array($res) || !isset($res['items'])) {
                $res = ['items' => [], 'totalCount' => 0];
            }

            if ( !empty($res['items']) ) {
                foreach ($res['items'] as &$vItem) {
                    if ( isset($vItem['general']) && empty($GET['id']) ) {
                        unset($vItem['general']);
                    }
                }
                unset($vItem);
            }

            if ( !$GET['id'] ) {
                $c_a = intdiv( (((int)$GET['perpage']?:$this->Conf->PerPage)), 16);

                if ( !empty($res['items']) ) {
                    array_splice(
                        $res['items'],
                        rand(4,11),
                        0,
                        [$this->Conf->CTA[mt_rand(0,count($this->Conf->CTA)-1)]]
                    );
                }
                if ( $c_a > 1 && !empty($res['items']) && count($res['items'])>20 ) {
                    array_splice(
                        $res['items'],
                        rand(20,27),
                        0,
                        [$this->Conf->CTA[mt_rand(0,count($this->Conf->CTA)-1)]]
                    );
                }
                if ( $c_a > 2 && !empty($res['items']) && count($res['items'])>36 ) {
                    array_splice(
                        $res['items'],
                        rand(36,43),
                        0,
                        [$this->Conf->CTA[mt_rand(0,count($this->Conf->CTA)-1)]]
                    );
                }
                if ( $c_a > 3 && !empty($res['items']) && count($res['items'])>52 ) {
                    array_splice(
                        $res['items'],
                        rand(52,59),
                        0,
                        [$this->Conf->CTA[mt_rand(0,count($this->Conf->CTA)-1)]]
                    );
                }
            }

            $res['in_city'] = $this->getDBDealershipsCities( $entity, $GET );
            $res['meta'] = $this->getDirectMeta( $entity, array_merge($GET, ['in_city'=>$res['in_city']]) );
            if ($GET['nometa']) unset($res['meta']);

            return $res;
        }
        public function __apiDBGetVehicles( $entity = 'new', $GET = [] ) {

            if ( !$GET['site'] ) $GET['site'] = parse_url($_SERVER['HTTP_REFERER'])['host'];
            if ( $this->get404($GET) ) return ['force_404' => true];

            $GET['type'] = ( $entity == 'new' ) ? 1 : 2;
            if ( $GET['city'] ) $GET['city'] = $this->buildCity($GET['city']);

            $CGET = $GET;
            // Helper::sp($GET);
            if ( $GET['brand'] ) { // костыль для chery и tank
                $b_arr = explode(',', $GET['brand']);
                if ( in_array('chery', $b_arr) && (int)$GET['type'] == 1 ) $GET['brand'] = implode(',', array_unique(array_merge($b_arr, ['tenet'])));
                if ( in_array('tank', $b_arr) && (int)$GET['type'] == 1 ) $GET['brand'] = implode(',', array_unique(array_merge($b_arr, ['wey'])));
            }
            if ( $GET['dealership'] ) { // костыль для chery и tank
                $b_arr = explode(',', $GET['dealership']);
                if ( in_array('chery-yablonovskiy', $b_arr) && (int)$GET['type'] == 1 ) $GET['dealership'] = implode(',', array_unique(array_merge($b_arr, ['tenet-yablonovskiy'])));
                if ( in_array('chery-maykop', $b_arr) && (int)$GET['type'] == 1 ) $GET['dealership'] = implode(',', array_unique(array_merge($b_arr, ['tenet-maykop'])));
            }

            if ( $GET['dealership'] ) {
                foreach ( explode(',',$GET['dealership']) as $i ) {
                    if ( (int)$i ) {
                        $d_get[] = $i;
                    } else {
                        if ( $tmp = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_dealerships WHERE url = ?s', $i) ) {
                            $d_get[] = $tmp['code'];
                            if ( $t = $this->MySQL->getOne('SELECT code FROM yapps_app_cis_brands WHERE id = ?i', $tmp['brand_id']) ) $b_get[] = $t;
                        }
                    }
                    if ( $d_get ) {
                        $CGET['dealership'] = $GET['dealership'] = implode(',', $d_get);
                    }
                    if ( $b_get ) $CGET['brand'] = $GET['brand'] = implode(',', $b_get);
                }

            } else {
                
                $CGET['dealership'] = $GET['dealership'] = implode(',', $this->getDBDealershipsIDs( $entity, $GET ) );
            }

            // $CGET['dealership'] = $GET['dealership'] = implode(',', $this->getDBDealershipsIDs( $entity, $GET ) );
            // if ( !$GET['dealership'] ) return null;
            $res['in_city'] = $this->getDBDealershipsCities( $entity, $GET );
            if ( $entity == 'all' ) unset($GET['type'], $GET['dealership']);

            $query = $this->buildDBQuery( $GET );
            Helper::sp($query);

            // /////////// CACHE //////////////
            if ( $QCache = $this->getQueryCache($query) ) {
                
                // /////////// CACHE //////////////
                $res['items'] = $QCache;
            
            } else {

                $rs = $this->MySQL->getAll('SELECT * FROM ?n ?p', $this->table->prod, $query);

                $get_total = $GET;
                unset( $get_total['page'] );
                $res['totalCount'] = (int)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p', $this->table->prod, $this->buildDBQuery($get_total));
                $get_tags = $get_total;
                unset( $get_tags['tag'] );

                $get_tags['is_discount'] = true;
                $res['Discount'] = (bool)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p', $this->table->prod, $this->buildDBQuery($get_tags));
                unset( $get_tags['is_discount'] );

                $get_tags['is_instock'] = true;
                $res['InStock'] = (bool)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p', $this->table->prod, $this->buildDBQuery($get_tags));
                unset( $get_tags['is_instock'] );

                $get_tags['is_onway'] = true;
                $res['OnWay'] = (bool)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p', $this->table->prod, $this->buildDBQuery($get_tags));
                unset( $get_tags['is_onway'] );

                $res['next_page'] = (int)$GET['page'] + 1;
                if ( (int)$GET['page']*($this->Conf->PerPage-1) >= $res['totalCount'] ) $res['next_page'] = false;

                foreach ( $rs as $i ) {

                    $r = [];
                    // Helper::sp($i);

                    $item = json_decode($i['raw'], true);
                    // Helper::sp($item);
                    $r['id'] = (int)$i['ext_id'];
                    $r['entity'] = (($i['type_id']==1)?'new':'used');
                    $r['general'] = $item['general'];
                    $r['vin'] = $item['vin'];
                    $r['specifications'] = $item['specifications'];
                    $r['status'] = $item['status'];
                    $r['dealership'] = $item['dealership'];
                    $r['min_price'] = (int)$i['min_price'];
                    $r['body_type'] = $item['body_type'];
                    $r['price'] = (int)$i['price'];
                    $r['brand'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE id = ?i', $i['brand_id']);
                    $r['model'] = $this->MySQL->getRow('SELECT * FROM ?n WHERE id = ?i', 'yapps_app_cis_models_'.(((int)$i['type_id']==1)?'new':'used'), $i['model_id']);
                    $r['equipment'] = (($this->yappsGetEquipmentRuName($r['brand']['id'], $r['model']['id'], $item['equipment']))?:$item['equipment']);
                    $r['body'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_bodies WHERE code = ?s', $i['body']);
                    $r['engine'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_engines WHERE code = ?s', $i['engine']);
                    $r['color'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_colors WHERE code = ?s', $i['color']);
                    $r['drive'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_drives WHERE code = ?s', $i['drive']);
                    $r['transmission'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_transmissions WHERE code = ?s', $i['transmission']);
                    $r['Discount'] = ( $item['min_price'] < $item['price'] );
                    $r['InStock'] = ( $item['status']['id'] == 1 );
                    $r['OnWay'] = ( $item['status']['id'] == 2 );
                    $r['power'] = $i['power'];
                    $r['volume'] = $i['volume'];
                    $r['use_internal_images'] = (int)$i['use_internal_images'];
                    $r['image'] = ( (int)$i['use_internal_images'] ) ? $this->getInternalImage($i['ext_id']) : (!empty($item['images']) && is_array($item['images']) ? $item['images'][0]['preview_large'] : '');
                    $r['images'] = $this->safeGetImages((int)$i['use_internal_images'], $i['ext_id'], $item['images']);
                    $r['type'] = 'vehicle';
                    $r['created'] = $i['created'];
                    $r['_general'][] = (($item['general'][4]['value'])?:date('Y')).' г.в.';
                    $r['_general'][] = ($i['type_id']==1)?$r['body']['name']:number_format((int)$item['general'][5]['value'], 0, ',', ' ').' км';
                    $r['_general'][] = number_format($i['volume']/1000, 1, ',', ' ').' ('.  $i['power'].' л.с.)';
                    $r['_general'][] = ($r['engine']['code']!='none')?$r['engine']['name']:null;
                    $r['_general'][] = ($r['transmission']['code']!='none')?$r['transmission']['name']:null;
                    $r['_general'][] = ($r['drive']['code']!='none')?$r['drive']['name']:null;

                    foreach ( $item['tags'] as $t ) if ( $tag = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_tags WHERE name = ?s', $t) ) $r['_tags'][] = $tag; 

                    foreach ($r['discounts'] as $k => $i) {
                        $r['discounts'][$k]['active'] = true;
                        switch ( $i['types'][0] ) {
                            case 'trade_in': $r['discounts'][$k]['description'] = 'За Трейд-ин'; break;
                            case 'credit': $r['discounts'][$k]['description'] = 'За кредит'; break;
                            case 'leasing': $r['discounts'][$k]['description'] = 'За лизинг'; break;
                            case 'insurance': $r['discounts'][$k]['description'] = 'За страховку'; break;
                        }
                    }

                    if ( !$r['image'] ) {
                        $r['images'] = [
                            [
                                'id' => 1,
                                'big' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$r['body']['code'].'.jpg',
                                'thumb' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$r['body']['code'].'_sm.jpg',
                                'detail' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$r['body']['code'].'.jpg',
                                'preview' => 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/bodies/'.$r['body']['code'].'_sm.jpg',
                            ]
                        ];
                    }

                    if ( count($r['images']) < 4 ) {
                        for ( $ii = count($r['images']); $ii < 4; $ii++ ) {
                            $r['images'][] = $r['images'][count($r['images'])-1];
                        }
                    }

                    if ( $r['brand'] && $r['model'] ) $res['items'][] = $r;
                }

                //////////// CACHE //////////////
                // $this->setQueryCache( $query, $res['items'] );
            }

            if ( !$GET['id'] ) {
                $c_a = intdiv( (((int)$GET['perpage']?:$this->Conf->PerPage)), 16);

                if ( !empty($res['items']) ) {
                    array_splice(
                        $res['items'],
                        rand(4,11),
                        0,
                        [$this->Conf->CTA[mt_rand(0,count($this->Conf->CTA)-1)]]
                    );

                }
                
                if ( $c_a > 1 && !empty($res['items']) && count($res['items'])>20 ) {
                    array_splice(
                        $res['items'],
                        rand(20,27),
                        0,
                        [$this->Conf->CTA[mt_rand(0,count($this->Conf->CTA)-1)]]
                    );
                }
                if ( $c_a > 2 && !empty($res['items']) && count($res['items'])>36 ) {
                    array_splice(
                        $res['items'],
                        rand(36,43),
                        0,
                        [$this->Conf->CTA[mt_rand(0,count($this->Conf->CTA)-1)]]
                    );
                }
                if ( $c_a > 3 && !empty($res['items']) && count($res['items'])>52 ) {
                    array_splice(
                        $res['items'],
                        rand(52,59),
                        0,
                        [$this->Conf->CTA[mt_rand(0,count($this->Conf->CTA)-1)]]
                    );
                }
            }

            // if ( !$res['meta'] ) $res['meta'] = $this->getDirectMeta( $entity, array_merge($GET, ['in_city'=>$res['in_city']]) );
            $res['meta'] = $this->getDirectMeta( $entity, array_merge($CGET, ['in_city'=>$res['in_city']]) );
            if ($GET['nometa']) unset($res['meta']);
            
            return $res;
        }



        public function apiDBGetDealerships( $entity = 'new', $GET = [] ) {

            $GET['type'] = ( $entity == 'new' ) ? 1 : 2;
            $query = $this->buildDBQuery( $GET );

            $rs = $this->MySQL->getAll('SELECT * FROM ?n ?p', $this->table->prod, $query);
            foreach ( $rs as $item ) {
                
                // Helper::sp( json_decode($item['raw'], true) ); die;
                $tmp = json_decode($item['raw'], true);
                $res[$tmp['dealership']['id']] = $tmp['dealership'];
            }

            return $res;
        }

        public function apiDBQRFeed( $entity, $mode = 'object' ) {

            $type_id = 0;
            if ( $entity == 'new' ) $type_id = 1;
            if ( $entity == 'used' ) $type_id = 2;

            $rs = $this->MySQL->getAll('SELECT ext_id, brand_id, model_id, vin FROM ?n WHERE type_id = ?i', $this->table->prod, $type_id);
            foreach ( $rs as $i ) {

                $url = 'https://'.(($type_id==2)?'yug-avto-expert':'yug-avto').'.ru/cars/'.(($type_id==2)?'used':'new').'/';
                $url .= $this->MySQL->getOne('SELECT code FROM ?n WHERE id = ?i', 'yapps_app_cis_brands', $i['brand_id']).'/';
                $url .= $this->MySQL->getOne('SELECT code FROM ?n WHERE id = ?i', 'yapps_app_cis_models_'.$entity, $i['model_id']).'/';
                $url .= $i['ext_id'];

                if ( $mode == 'object' ) $res[] = ['vin'=>$i['vin'], 'url'=>$url];
                if ( $mode == 'line' ) $res[][$i['vin']] = $url;
            }

            return $res;
            

        }



        public function getStory( $vin = null ) {

            $res = ['status' => false, 'description'=>'Что-то пошло не так.'];

            if ( $vin ) {

                if ( $vehicle = $this->MySQL->getRow('SELECT * FROM ?n WHERE vin = ?s', $this->table->prod, $vin) ) {

                    // $res = $vehicle;
                    $raw = json_decode( $vehicle['raw'], true);
                    $res = [
                        'status' => true,
                        'type' => $vehicle['type_id'],
                        'brand' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE id = ?i', $vehicle['brand_id']),
                        'model' => $this->MySQL->getRow('SELECT * FROM ?n WHERE id = ?i', 'yapps_app_cis_models_'.(($vehicle['type_id']==2)?'used':'new'), $vehicle['model_id']),
                        'equipment' => $raw['equipment'],
                        'price' => $vehicle['price'],
                        'min_price' => $vehicle['min_price'],
                        'transmission' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_transmissions WHERE code = ?s', $vehicle['transmission']),
                        'engine' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_engines WHERE code = ?s', $vehicle['engine']),
                        'color' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_colors WHERE code = ?s', $vehicle['color']),
                        'drive' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_drives WHERE code = ?s', $vehicle['drive']),
                        'body' => $this->MySQL->getRow('SELECT * FROM yapps_app_cis_bodies WHERE code = ?s', $vehicle['body']),
                        'volume' => $vehicle['volume'],
                        'power' => $vehicle['power'],
                        'year' => $vehicle['year'],
                        'mileage' => $vehicle['mileage'],
                        'image' => $raw['images'][0]['full'],
                        'dealership' => $vehicle['dealership_id']
                    ];
                    if ( !file_exists(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id']) ) mkdir( __DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'] );
                    $image = $this->makeImg($raw['images'][0]['full'], 'full', 990, 782);
                    if ( $image ) {
                        imageJpeg($image, __DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'].'/story.jpg', 80);
                        imagedestroy($image);
                        $res['image'] = 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'].'/story.jpg'.'?'.md5_file(__DIR__.'/../..'.$this->Conf->FileDir.'/vehicles/'.$vehicle['ext_id'].'/story.jpg');
                    }

                } else {
                    $res['description'] = 'Такой автомобиль не найден';
                }
            }

            return $res;
        }

        
        ////////////////////////////////////////////////////////////////
		// SEO  ////////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////


        public function getMeta( $q = null ) {

            if ( !$q ) return ['status' => 404];
        }

        public function getFilterMeta( $GET = [] ) {

            if ( !$GET['site'] ) $GET['site'] = parse_url($_SERVER['HTTP_REFERER'])['host'];
            if ( $GET['type'] == 1 ) $GET['entity'] = 'new';
            if ( $GET['type'] == 2 ) $GET['entity'] = 'used';

            unset( $GET['page'], $GET['filter'], $GET['token'], $GET['type'], $GET['city'] );

            foreach ( $GET as $k => $v ) {
                $tmp = explode(',', $v);
                sort($tmp);
                $GET[$k] = implode(',', $tmp);
            }
            if ( $GET['dealership'] ) $GET['dealership'] = implode(',', $this->MySQL->getCol('SELECT code FROM yapps_app_cis_dealerships WHERE url IN(?a)', explode(',', $GET['dealership'])));

            $w = [];
            $w[] = $this->MySQL->parse('site = ?s', (($GET['site'])?:''));
            $w[] = $this->MySQL->parse('entity = ?s', (($GET['entity'])?:''));
            $w[] = $this->MySQL->parse('brand = ?s', (($GET['brand'])?:''));
            $w[] = $this->MySQL->parse('model = ?s', (($GET['model'])?:''));
            $w[] = $this->MySQL->parse('transmission = ?s', (($GET['transmission'])?:''));
            $w[] = $this->MySQL->parse('engine = ?s', (($GET['engine'])?:''));
            $w[] = $this->MySQL->parse('drive = ?s', (($GET['drive'])?:''));
            $w[] = $this->MySQL->parse('body = ?s', (($GET['body'])?:''));
            $w[] = $this->MySQL->parse('color = ?s', (($GET['color'])?:''));
            $w[] = $this->MySQL->parse('dealership = ?s', (($GET['dealership'])?:''));
            $w[] = $this->MySQL->parse('price = ?s', (($GET['price'])?:''));
            $w[] = $this->MySQL->parse('volume = ?s', (($GET['volume'])?:''));
            $w[] = $this->MySQL->parse('power = ?s', (($GET['power'])?:''));
            $w[] = $this->MySQL->parse('year = ?s', (($GET['year'])?:''));

            if ( count($w) ) $query = "WHERE ".implode(' AND ',$w);

            $res = $this->MySQL->getRow(
                'SELECT id, meta_h1, meta_title, meta_description, seo_title, seo_text FROM yapps_app_cis_seo_filters ?p ORDER BY id DESC',
                $query
            );
            if (!empty($res)) {
                $res['entity'] = $GET['entity'];
                $res['site'] = $GET['site'];
            }
            return ( !empty($res) ) ? $res : false;
        }
        public function get404( $GET ) {

            $res = $this->MySQL->getRow(
                'SELECT * FROM yapps_app_cis_seo_404 WHERE site = ?s AND uri = ?s',
                $GET['site'],
                $GET['uri']
            );

            return ( !empty($res) ) ? true : false;
        }

        public function getDirectMeta( $entity = 'new', $GET = [] ) {

            // Helper::sp( $GET ); die;
            unset( $GET['page'] );

            if ( !$GET['site'] ) $GET['site'] = parse_url($_SERVER['HTTP_REFERER'])['host'];

            if ( $GET['brand'] == 'favorites' || $GET['brand'] == 'compare' ) {

                $res['status'] = 200;
                // $res['meta'] = $this->Conf->newMeta[$GET['site']][$entity][$GET['brand']];
                $res['meta'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_seo WHERE entity = ?s AND site = ?s AND level = ?s', $entity, $GET['site'], $GET['brand']);
                $res['meta']['h1'] = $res['meta']['meta_h1']; unset($res['meta']['meta_h1']);
                $res['meta']['title'] = $res['meta']['meta_title']; unset($res['meta']['meta_title']);
                $res['meta']['description'] = $res['meta']['meta_description']; unset($res['meta']['meta_description']);
                
                return $res;
            }

            if ( !isset($GET['type']) || !in_array($GET['type'], [1, 2, 'new', 'used', 'all']) ) {
                $GET['type'] = ( $entity == 'new' ) ? 1 : 2;
            } else {
                if ($GET['type'] === 'new') $GET['type'] = 1;
                elseif ($GET['type'] === 'used') $GET['type'] = 2;
            }
            $res['in_city'] = $GET['in_city'];
            if ( $GET['city'] && !$GET['dealership'] ) {
                $GET['city'] = $this->buildCity($GET['city']);
                $GET['dealership'] = implode(',', $this->getDBDealershipsIDs( $entity, $GET ) );
                // $j = json_decode( file_get_contents('https://yug-avto.ru/api/dealerships?mode='.$entity.'&city='.$GET['city']), true );
                // foreach ($j as $c) if ( $GET['!dealership'] && $GET['!dealership'] !== $c['code'] ) $d[] = $c['code'];
                // $res['in_city'] = $j[0]['city'];
                // $GET['dealership'] = implode(',', $d);
            }
            // Helper::sp( $GET ); die;

            $res['level'] = 'brands';
            $res['status'] = 200;
            if ( $GET['brand'] && count(explode(',',$GET['brand'])) == 1 ) {
                $res['level'] = 'brand';
                $res['custom'] = $GET['brand'];
                if ( $GET['model'] && count(explode(',',$GET['model'])) == 1 ) {
                    $res['level'] = 'model';
                    $res['custom'] = $GET['model'];
                    if ( $GET['id'] && count(explode(',',$GET['id'])) == 1 ) {
                        $res['level'] = 'vehicle';
                        $res['custom'] = $GET['id'];
                    }
                }
            }

            // $metaQuery = $this->buildDBQuery($GET);
            $res['count'] = (int)$this->MySQL->getOne('SELECT COUNT(*) FROM ?n ?p', $this->table->prod, $this->buildDBQuery($GET));
            $res['GET'] = $GET;
            $res['query'] = $this->buildDBQuery($GET);
            if ( !$res['count'] ) {
                $res['status'] = 404;
                if ( 
                    ($res['level'] == 'brand' && $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE code = ?s', $GET['brand'])) ||
                    ($res['level'] == 'model' && $this->MySQL->getRow('SELECT * FROM ?n WHERE code = ?s', 'yapps_app_cis_models_'.$entity, $GET['model']))
                    ) $res['status'] = '404_vehicles';
                return $res;
            }

            if ( $GET['city'] && !$GET['in_city'] && !$GET['dealership'] ) {
                foreach ( $this->apiDBGetDealerships( $entity, $GET ) as $d ) $dd[] = $i['id'];
                if ( count($dd) ) {
                    foreach( json_decode(file_get_contents('https://yug-avto.ru/api/dealerships?code='.implode(',',$dd)), true) as $d ) $cities[] = $d['city'];
                    $cities = array_values(array_unique($cities));
                    $res['in_city'] = implode(' и ', [implode(', ',array_chunk($cities, count($cities)-1)[0]), array_chunk($cities, count($cities)-1)[1][0]]);
                }
            }

            // $res['meta'] = $this->Conf->newMeta[$GET['site']][$entity][$res['level']];

            
            $res['meta'] = $this->getFilterMeta($GET);
            // Helper::sp($res);
            if ( !$res['meta'] ) {
                $res['meta'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_seo WHERE entity = ?s AND site = ?s AND level = ?s AND custom = ?s', $entity, $GET['site'], $res['level'], $res['custom']);
                if ( !$res['meta'] ) $res['meta'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_seo WHERE entity = ?s AND site = ?s AND level = ?s', $entity, $GET['site'], $res['level']);
            }
            // $res['meta'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_seo WHERE entity = ?s AND site = ?s AND level = ?s AND custom = ?s', $entity, $GET['site'], $res['level'], $res['custom']);
            // if ( !$res['meta'] ) $res['meta'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_seo WHERE entity = ?s AND site = ?s AND level = ?s', $entity, $GET['site'], $res['level']);
            $res['meta']['h1'] = $res['meta']['meta_h1']; unset($res['meta']['meta_h1']);
            $res['meta']['title'] = $res['meta']['meta_title']; unset($res['meta']['meta_title']);
            $res['meta']['description'] = $res['meta']['meta_description']; unset($res['meta']['meta_description']);
            
            $months = ['январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь'];

            $brand = null; $vehicle = null; $model = null;
            if ( $res['level'] == 'brand' ) {
                $brand = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE code = ?s', $GET['brand']);
                $min_price = $this->MySQL->getOne('SELECT MIN(min_price) FROM ?n ?p', $this->table->prod, $this->buildDBQuery($GET) );
            }
            if ( $res['level'] == 'model' ) {
                $brand = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_brands WHERE code = ?s', $GET['brand']);
                $model = $this->MySQL->getRow('SELECT * FROM ?n WHERE code = ?s', 'yapps_app_cis_models_'.$entity, $GET['model']);
                $min_price = $this->MySQL->getOne('SELECT MIN(min_price) FROM ?n ?p', $this->table->prod, $this->buildDBQuery($GET) );
            }
            if ( $res['level'] == 'vehicle' ) {
                $vehicle = $this->apiDBGetVehicle($entity, (int)$GET['id']);
                $brand = $vehicle['brand'];
                $model = $vehicle['model'];
                $transmission_code = (isset($vehicle['transmition']) && is_array($vehicle['transmition'])) ? $vehicle['transmition']['code'] : ($vehicle['transmition'] ?? '');
                $t = $transmission_code ? $this->MySQL->getOne('SELECT meta FROM yapps_app_cis_transmissions WHERE code = ?s', $transmission_code) : '';
                $drive_code = (isset($vehicle['drive']) && is_array($vehicle['drive'])) ? $vehicle['drive']['code'] : ($vehicle['drive'] ?? '');
                $d = $drive_code ? $this->MySQL->getOne('SELECT meta FROM yapps_app_cis_drives WHERE code = ?s', $drive_code) : '';
                $min_price = $vehicle['min_price'];
            }
            // Helper::sp($GET);
            foreach ( $res['meta'] as $k => $i ) {
                $year_val = (isset($vehicle['general'][4]) && is_array($vehicle['general'][4])) ? $vehicle['general'][4]['value'] : '';
                $i = str_replace('{%year%}', ($year_val ?: date('Y')), $i);
                $i = str_replace('{%count%}', Helper::formatNumber($res['count']), $i);
                $i = str_replace('{%cars%}', Helper::getWorld($res['count'], 'a'), $i);
                // $i = str_replace('{%tel%}', Helper::formatPhoneOut($this->Conf->newMeta[$GET['site']]['phone']), $i);
                $i = str_replace(
                    '{%tel%}', 
                    Helper::formatPhoneOut(
                        $this->MySQL->getRow('SELECT phone FROM yapps_app_cis_seo WHERE site = ?s AND entity = ?s', $GET['site'], 'phone')['phone']
                    ), 
                    $i
                );
                $i = str_replace('{%brand%}', $brand['name'], $i);
                $i = str_replace('{%brand_rus%}', $brand['ru_name'], $i);
                $i = str_replace('{%model%}', $model['name'], $i);
                $i = str_replace('{%model_rus%}', $model['ru_name'], $i);
                $i = str_replace('{%date%}', $months[date('n')-1].' '.date('Y'), $i);
                $i = str_replace('{%city%}', (($res['in_city'])?'в '.$res['in_city']:''), $i);
                $i = str_replace('{%ext_id%}', $vehicle['ext_id'], $i);
                $i = str_replace('{%tth%}', $vehicle['modification_name'], $i);
                $i = str_replace('{%complectation%}', $vehicle['equipment'], $i);
                $i = str_replace('{%price%}', Helper::formatNumber($min_price), $i);
                $mileage_val = (isset($vehicle['general'][5]) && is_array($vehicle['general'][5])) ? $vehicle['general'][5]['value'] : '';
                $i = str_replace('{%mileage%}', Helper::formatNumber($mileage_val), $i);

                // $i = str_replace('{%engine%}', $vehicle['general'][(($entity=='new')?5:8)]['value'], $i);
                if ( $res['level'] == 'vehicle' ) {
                    $engine_idx = ($entity == 'new') ? 5 : 8;
                    $engine_val = (isset($vehicle['general'][$engine_idx]) && is_array($vehicle['general'][$engine_idx])) ? $vehicle['general'][$engine_idx]['value'] : '';
                    $i = str_replace('{%engine%}', $engine_val, $i);
                } else {
                    if ( $GET['engine'] && count(explode(',',$GET['engine']))==1 ) {
                        $i = str_replace(
                            '{%engine%}',
                            'двигатель: '.mb_strtolower($this->MySQL->getOne('SELECT name FROM yapps_app_cis_engines WHERE code = ?s', $GET['engine'])), 
                            $i
                        );
                    } else {
                        $i = preg_replace('#\s+\{\%engine\%\}[\.\s,;]#', '', $i);
                    }
                }

                // $i = str_replace('{%color_processed%}', $vehicle['color']['name'], $i);
                if ( $res['level'] == 'vehicle' ) {
                    $color_name = is_array($vehicle['color']) ? $vehicle['color']['name'] : ($vehicle['color'] ?? '');
                    $i = str_replace('{%color_processed%}', $color_name, $i);
                } else {
                    if ( $GET['color'] && count(explode(',',$GET['color']))==1 ) {
                        $i = str_replace(
                            '{%color_processed%}',
                            'цвет: '.mb_strtolower($this->MySQL->getOne('SELECT name FROM yapps_app_cis_colors WHERE code = ?s', $GET['color'])), 
                            $i
                        );
                    } else {
                        $i = preg_replace('#\s+\{\%color_processed\%\}[\.\s,;]#', '', $i);
                    }
                }

                // $i = str_replace(' {%transmission%}', (($t)?' c '.$t.' коробкой':''), $i);
                if ( $res['level'] == 'vehicle' ) {
                    $i = str_replace(' {%transmission%}', (($t)?' c '.$t.' коробкой':''), $i);
                } else {
                    if ( $GET['transmission'] && count(explode(',',$GET['transmission']))==1 ) {
                        $i = str_replace(
                            '{%transmission%}',
                            'КПП: '.mb_strtolower($this->MySQL->getOne('SELECT name FROM yapps_app_cis_transmissions WHERE code = ?s', $GET['transmission'])), 
                            $i
                        );
                    } else {
                        $i = preg_replace('#\s+\{\%transmission\%\}[\.\s,;]#', '', $i);
                    }
                }
                
                // $i = str_replace('{%drive%}', $d, $i);
                if ( $res['level'] == 'vehicle' ) {
                    $i = str_replace('{%drive%}', $vehicle['drive']['name'], $i);
                } else {
                    if ( $GET['drive'] && count(explode(',',$GET['drive']))==1 ) {
                        $i = str_replace(
                            '{%drive%}',
                            'привод: '.mb_strtolower($this->MySQL->getOne('SELECT name FROM yapps_app_cis_drives WHERE code = ?s', $GET['drive'])), 
                            $i
                        );
                    } else {
                        $i = preg_replace('#\s+\{\%drive\%\}[\.\s,;]#', '', $i);
                    }
                }

                $filter = [];
                if ( $GET['engine'] && count(explode(',',$GET['engine']))==1 ) $filter[] = 'двигатель: '.mb_strtolower($this->MySQL->getOne('SELECT name FROM yapps_app_cis_engines WHERE code = ?s', $GET['engine']));
                if ( $GET['color'] && count(explode(',',$GET['color']))==1 ) $filter[] = 'цвет: '.mb_strtolower($this->MySQL->getOne('SELECT name FROM yapps_app_cis_colors WHERE code = ?s', $GET['color']));
                if ( $GET['transmission'] && count(explode(',',$GET['transmission']))==1 ) $filter[] = 'КПП: '.mb_strtolower($this->MySQL->getOne('SELECT name FROM yapps_app_cis_transmissions WHERE code = ?s', $GET['transmission']));
                if ( $GET['drive'] && count(explode(',',$GET['drive']))==1 ) $filter[] = 'привод: '.mb_strtolower($this->MySQL->getOne('SELECT name FROM yapps_app_cis_drives WHERE code = ?s', $GET['drive']));
                $i = str_replace('{%filter%}', implode(', ',$filter), $i);



                $i = str_replace('{%transmission_meta%}', $t, $i);
                $i = str_replace('{%power%}', Helper::formatNumber($vehicle['power']), $i);
                $i = str_replace('{%volume%}', $vehicle['volume'] / 1000, $i);

                $i = str_replace('""', '"', $i);

                $res['meta'][$k] = $i;
            }

            


            switch ( $res['level'] ) {

                case 'brands':
                    break;

                case 'brand':
                    $res['meta']['brand'] = $brand['code'];
                    break;

                case 'model':
                    $res['meta']['brand_code'] = $brand['code'];
                    $res['meta']['model_code'] = $model['code'];
                    break;

                case 'vehicle':
                    $res['meta']['brand'] = $vehicle['brand']['name'];
                    $res['meta']['price'] = $vehicle['min_price'];
                    $res['meta']['image'] = $vehicle['_images'][0]['thumb'];
                    break;
            }


            if ( $brand['code'] == 'moskvich' && $model == null && $vehicle == null ) {

            }
            

            // unset($res['level']);
            return $res;
        }


        private function getVehicleMeta( $entity, $vehicle, $site = null ) {

            // Helper::sp($site);

            // $res['meta'] = $this->Conf->newMeta[parse_url($_SERVER['HTTP_REFERER'])['host']][$entity]['vehicle'];
            $res['status'] = 200;
            $res['meta'] = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_seo WHERE entity = ?s AND site = ?s AND level = ?s', $entity, $site, 'vehicle');
            $res['meta']['h1'] = $res['meta']['meta_h1']; unset($res['meta']['meta_h1']);
            $res['meta']['title'] = $res['meta']['meta_title']; unset($res['meta']['meta_title']);
            $res['meta']['description'] = $res['meta']['meta_description']; unset($res['meta']['meta_description']);
            $res['meta']['image'] = $vehicle['_images'][0]['detail'];
            $res['meta']['brand'] = $vehicle['brand']['name'];


            $brand = $vehicle['brand'];
            $model = $vehicle['model'];
            $transmission_code = (isset($vehicle['transmition']) && is_array($vehicle['transmition'])) ? $vehicle['transmition']['code'] : ($vehicle['transmition'] ?? '');
            $t = $transmission_code ? $this->MySQL->getOne('SELECT meta FROM yapps_app_cis_transmissions WHERE code = ?s', $transmission_code) : '';
            $drive_code = (isset($vehicle['drive']) && is_array($vehicle['drive'])) ? $vehicle['drive']['code'] : ($vehicle['drive'] ?? '');
            $d = $drive_code ? $this->MySQL->getOne('SELECT meta FROM yapps_app_cis_drives WHERE code = ?s', $drive_code) : '';

            $months = ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];

            foreach( $res['meta'] as $k => $i ) {
                $year_val = (isset($vehicle['general'][4]) && is_array($vehicle['general'][4])) ? $vehicle['general'][4]['value'] : '';
                $i = str_replace('{%year%}', ($year_val ?: date('Y')), $i);
                $i = str_replace('{%count%}', Helper::formatNumber($res['count']), $i);
                $i = str_replace('{%cars%}', Helper::getWorld($res['count'], 'a'), $i);
                // $i = str_replace('{%tel%}', Helper::formatPhoneOut($this->Conf->newMeta[$GET['site']]['phone']), $i);
                $i = str_replace(
                    '{%tel%}', 
                    Helper::formatPhoneOut(
                        $this->MySQL->getRow('SELECT phone FROM yapps_app_cis_seo WHERE site = ?s AND entity = ?s', $site, 'phone')['phone']
                    ), 
                    $i
                );
                $i = str_replace('{%brand%}', $brand['name'], $i);
                $i = str_replace('{%brand_rus%}', $brand['ru_name'], $i);
                $i = str_replace('{%model%}', $model['name'], $i);
                $i = str_replace('{%model_rus%}', $model['ru_name'], $i);
                $i = str_replace('{%date%}', $months[date('n')-1].' '.date('Y'), $i);
                $in_city_val = (isset($vehicle['dealership']) && is_array($vehicle['dealership'])) ? ($vehicle['dealership']['in_city'] ?? '') : '';
                $i = str_replace('{%city%}', ($in_city_val ? 'в ' . $in_city_val : ''), $i);
                $general_2 = (isset($vehicle['general'][2]) && is_array($vehicle['general'][2])) ? $vehicle['general'][2]['value'] : '';
                $i = str_replace('{%color%}', $general_2, $i);
                $color_name = is_array($vehicle['color']) ? $vehicle['color']['name'] : ($vehicle['color'] ?? '');
                $i = str_replace('{%color_processed%}', $color_name, $i);
                $i = str_replace('{%ext_id%}', $vehicle['id'], $i);
                $i = str_replace('{%tth%}', $vehicle['modification_name'], $i);
                $i = str_replace('{%complectation%}', $vehicle['equipment'], $i);
                $i = str_replace('{%price%}', Helper::formatNumber($vehicle['min_price']), $i);
                $general_5 = (isset($vehicle['general'][5]) && is_array($vehicle['general'][5])) ? $vehicle['general'][5]['value'] : '';
                $i = str_replace('{%mileage%}', Helper::formatNumber($general_5), $i);
                $engine_idx = ($entity == 'new') ? 5 : 8;
                $general_engine = (isset($vehicle['general'][$engine_idx]) && is_array($vehicle['general'][$engine_idx])) ? $vehicle['general'][$engine_idx]['value'] : '';
                $i = str_replace('{%engine%}', $general_engine, $i);
                $i = str_replace(' {%transmission%}', (($t)?' c '.$t.' коробкой':''), $i);
                $i = str_replace('{%transmission_meta%}', $t, $i);
                $i = str_replace('{%drive%}', $d, $i);
                $i = str_replace('{%power%}', Helper::formatNumber($vehicle['power']), $i);
                $i = str_replace('{%volume%}', $vehicle['volume'] / 1000, $i);

                $i = str_replace('""', '"', $i);

                $res['meta'][$k] = $i;
            }

            foreach( $res['meta'] as $k => $i ) {

            }

            // Helper::sp($res);

            return $res;
        }

        

        ////////////////////////////////////////////////////////////////
		// SEARCH  /////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////

        // private function searchModel ( $q, $brands ) {
            
        //     foreach ($brands as $item) $brand[] = $item['id'];
        //     $res = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_models_new WHERE LOWER(`name`) LIKE LOWER(?s) OR LOWER(`ru_name`) LIKE LOWER(?s) AND brand_id IN (?a)', "%$q%", "%$q%", $brand);
        //     if ( !$res ) $res = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_models_used WHERE LOWER(`name`) LIKE LOWER(?s) OR LOWER(`ru_name`) LIKE LOWER(?s) AND brand_id IN (?a)', "%$q%", "%$q%", $brand);
        //     return ( !$res || $res['code'] == 'none' ) ? false : $res;
        // }
        // private function searchBrand ( $q ) {
            
        //     $current_brands = $this->MySQL->getCol('SELECT DISTINCT brand_id FROM ?n', $this->table->prod);
        //     $res = $this->MySQL->getRow(
        //         'SELECT * FROM yapps_app_cis_brands WHERE LOWER(`name`) LIKE LOWER(?s) OR LOWER(`ru_name`) LIKE LOWER(?s) AND id IN (?a)',
        //         "%$q%",
        //         "%$q%",
        //         $current_brands
        //     );
        //     if ( !$res ) $res = $this->getBrandComparisons($q);
        //     return ( !$res || $res['code'] == 'none' || !in_array($res['id'], $current_brands) ) ? false : $res;
        // }
        // private function searchColor ( $q ) {
            
        //     $res = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_colors WHERE LOWER(`name`) LIKE LOWER(?s) OR LOWER(`code`) LIKE LOWER(?s)', "%$q%", "%$q%");
        //     if ( !$res ) $res = $this->getColor($q);
        //     return ( !$res || $res['code'] == 'none' ) ? false : $res;
        // }
        // private function searchBody ( $q ) {
            
        //     $res = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_bodies WHERE LOWER(`name`) LIKE LOWER(?s) OR LOWER(`code`) LIKE LOWER(?s)', "%$q%", "%$q%");
        //     if ( !$res ) $res = $this->getBody($q);
        //     return ( !$res || $res['code'] == 'none' ) ? false : $res;
        // }
        // private function searchTransmission ( $q ) {
            
        //     $res = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_transmissions WHERE LOWER(`name`) LIKE LOWER(?s) OR LOWER(`code`) LIKE LOWER(?s)', "%$q%", "%$q%");
        //     if ( !$res ) $res = $this->getTransmission($q);
        //     return ( !$res || $res['code'] == 'none' ) ? false : $res;
        // }
        // private function searchEngine ( $q ) {
            
        //     $res = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_engines WHERE LOWER(`name`) LIKE LOWER(?s) OR LOWER(`code`) LIKE LOWER(?s)', "%$q%", "%$q%");
        //     if ( !$res ) $res = $this->getEngine($q);
        //     return ( !$res || $res['code'] == 'none' ) ? false : $res;
        // }
        // private function searchDrive ( $q ) {
            
        //     $res = $this->MySQL->getRow('SELECT * FROM yapps_app_cis_drives WHERE LOWER(`name`) LIKE LOWER(?s) OR LOWER(`code`) LIKE LOWER(?s)', "%$q%", "%$q%");
        //     if ( !$res ) $res = $this->getDrive($q);
        //     return ( !$res || $res['code'] == 'none' ) ? false : $res;
        // }
        // private function makeSearchQuery( $query ) {

        //     foreach( $query as $k => $q ) $res[$k] = implode(',', $q);
        //     return ( $res ) ?: [];
        // }

        // public function searchVehicles( $GET ) {
            
        //     if ( !!$GET['query'] ) {

        //         $arrQ = explode(' ', $GET['query']);

        //         foreach ( $arrQ as $k => $q ) {

        //             switch ($q) {

        //                 case 'или':
        //                 case 'и':
        //                 case 'на':
        //                 case 'до':
        //                 case 'от':
        //                 case 'не':
        //                 case 'с':
        //                 case 'без':
        //                 case 'or':
        //                 case 'and':
        //                     break;
                        
        //                 default:

        //                     /***** Brand *****/
        //                     $rawRes = $this->searchBrand( $q );
        //                     if ( $rawRes ) {
        //                         if ( !$GET['!brand'] || !in_array($rawRes['code'], explode(',', $GET['!brand'])) ) {
        //                             $res['parser']['brand'][] = $rawRes;
        //                             $res['query']['brand'][] = $rawRes['code'];
        //                         }
        //                     }
        //                     if ( $res['parser']['brand'] ) {
                                
        //                         /***** Model *****/
        //                         $rawRes = $this->searchModel( $q, $res['parser']['brand'] );
        //                         if ( $rawRes ) {
        //                             $res['parser']['model'][] = $rawRes;
        //                             $res['query']['model'][] = $rawRes['code'];
        //                         }
        //                     }

        //                     /***** Color *****/
        //                     $rawRes = $this->searchColor( $q );
        //                     if ( $rawRes ) {
        //                         $res['parser']['color'][] = $rawRes;
        //                         $res['query']['color'][] = $rawRes['code'];
        //                     }

        //                     /***** Body *****/
        //                     $rawRes = $this->searchBody( $q );
        //                     if ( $rawRes ) {
        //                         $res['parser']['body'][] = $rawRes;
        //                         $res['query']['body'][] = $rawRes['code'];
        //                     }

        //                     /***** Transmission *****/
        //                     $rawRes = $this->searchTransmission( $q );
        //                     // Helper::sp($rawRes);
        //                     if ( $rawRes ) {
        //                         $res['parser']['transmission'][] = $rawRes;
        //                         $res['query']['transmission'][] = $rawRes['code'];
        //                     }

        //                     /***** Engines *****/
        //                     $rawRes = $this->searchEngine( $q );
        //                     if ( $rawRes ) {
        //                         $res['parser']['engine'][] = $rawRes;
        //                         $res['query']['engine'][] = $rawRes['code'];
        //                     }

        //                     /***** Drives *****/
        //                     $rawRes = $this->searchDrive( $q );
        //                     if ( $rawRes ) {
        //                         $res['parser']['drive'][] = $rawRes;
        //                         $res['query']['drive'][] = $rawRes['code'];
        //                     }

        //                     break;
        //             }
        //             $res['link'] = $this->makeSearchQuery($res['query']);
        //         }
                

        //         foreach ( $res['parser'] as $k => $items ) {
        //             $tmp = [];
        //             foreach ( $items as $item ) $tmp[] = $item['name'];
        //             switch ($k) {
        //                 case 'brand': $res['pseudo'][$k]['name'] = 'Бренд'; break;
        //                 case 'model': $res['pseudo'][$k]['name'] = 'Модель'; break;
        //                 case 'color': $res['pseudo'][$k]['name'] = 'Цвет'; break;
        //                 case 'drive': $res['pseudo'][$k]['name'] = 'Привод'; break;
        //                 case 'engine': $res['pseudo'][$k]['name'] = 'Двигатель'; break;
        //                 case 'body': $res['pseudo'][$k]['name'] = 'Кузов'; break;
        //                 case 'transmission': $res['pseudo'][$k]['name'] = 'КПП'; break;
        //             }
        //             $res['pseudo'][$k]['query'] = implode(', ', $tmp);
        //         }

        //         $res['query']['sort'][] = 'price_up';
        //         $res['query']['limit'][] = '3';
        //         if ( $GET['!brand'] ) $res['query']['!brand'] = explode(',', $GET['!brand']);
                
        //         $res['raw']['new'] = $this->apiDBGetVehicles('new', $this->makeSearchQuery($res['query']))['items'];
        //         foreach ( $res['raw']['new'] as $k => $item ) if ( $item['type'] == 'random_cta' ) unset( $res['raw']['new'][$k] );
        //         $res['raw']['used'] = $this->apiDBGetVehicles('used', $this->makeSearchQuery($res['query']))['items'];
        //         foreach ( $res['raw']['used'] as $k => $item ) if ( $item['type'] == 'random_cta' ) unset( $res['raw']['used'][$k] );
        //     }

        //     return $res;
        // }
        
        /* ******** AI **/
        private function searchModel($q, array $brands) {
            $brandIds = array_column($brands, 'id');
            $sql = 'SELECT * FROM %s WHERE (name LIKE ?s OR ru_name LIKE ?s) AND brand_id IN (?a) LIMIT 1';
            
            $res = $this->MySQL->getRow(sprintf($sql, 'yapps_app_cis_models_new'), "%$q%", "%$q%", $brandIds);
            if (!$res) {
                $res = $this->MySQL->getRow(sprintf($sql, 'yapps_app_cis_models_used'), "%$q%", "%$q%", $brandIds);
            }

            return ($res && ($res['code'] ?? '') !== 'none') ? $res : false;
        }
        private function searchBrand($q) {
            $current_brands = $this->MySQL->getCol('SELECT DISTINCT brand_id FROM ?n', $this->table->prod);
            $res = $this->MySQL->getRow(
                'SELECT * FROM yapps_app_cis_brands WHERE (name LIKE ?s OR ru_name LIKE ?s) AND id IN (?a) LIMIT 1',
                "%$q%", "%$q%", $current_brands
            );
            
            if (!$res) $res = $this->getBrandComparisons($q);
            
            if (!$res || ($res['code'] ?? '') === 'none' || !in_array($res['id'], $current_brands)) {
                return false;
            }
            return $res;
        }
        // Универсальный метод для простых справочников (Color, Body, и т.д.), 
        // чтобы не дублировать код searchColor, searchBody...
        private function searchDictionary($table, $q, $fallbackMethod) {
            $res = $this->MySQL->getRow("SELECT * FROM $table WHERE name LIKE ?s OR code LIKE ?s LIMIT 1", "%$q%", "%$q%");
            if (!$res) $res = $this->$fallbackMethod($q);
            return ($res && ($res['code'] ?? '') !== 'none') ? $res : false;
        }
        public function searchVehicles($GET) {
            $queryStr = $GET['query'] ?? '';
            if (empty($queryStr)) return [];

            $res = ['parser' => [], 'query' => [], 'pseudo' => []];
            $stopWords = ['или', 'и', 'на', 'до', 'от', 'не', 'с', 'без', 'or', 'and'];
            $arrQ = explode(' ', $queryStr);

            foreach ($arrQ as $q) {
                if (in_array(mb_strtolower($q), $stopWords)) continue;

                // Поиск бренда
                if ($rawRes = $this->searchBrand($q)) {
                    $excludedBrands = explode(',', $GET['!brand'] ?? '');
                    if (!in_array($rawRes['code'], $excludedBrands)) {
                        $res['parser']['brand'][] = $rawRes;
                        $res['query']['brand'][] = $rawRes['code'];
                    }
                }

                // Поиск модели (только если есть бренды)
                if (!empty($res['parser']['brand'])) {
                    if ($rawRes = $this->searchModel($q, $res['parser']['brand'])) {
                        $res['parser']['model'][] = $rawRes;
                        $res['query']['model'][] = $rawRes['code'];
                    }
                }

                // Остальные параметры через обобщенный метод
                $map = [
                    'color'        => ['table' => 'yapps_app_cis_colors', 'fallback' => 'getColor'],
                    'body'         => ['table' => 'yapps_app_cis_bodies', 'fallback' => 'getBody'],
                    'transmission' => ['table' => 'yapps_app_cis_transmissions', 'fallback' => 'getTransmission'],
                    'engine'       => ['table' => 'yapps_app_cis_engines', 'fallback' => 'getEngine'],
                    'drive'        => ['table' => 'yapps_app_cis_drives', 'fallback' => 'getDrive'],
                ];

                foreach ($map as $key => $params) {
                    if ($rawRes = $this->searchDictionary($params['table'], $q, $params['fallback'])) {
                        $res['parser'][$key][] = $rawRes;
                        $res['query'][$key][] = $rawRes['code'];
                    }
                }
                $res['link'] = $this->makeSearchQuery($res['query']);
            }

            // Формирование человекопонятных имен (pseudo)
            $labels = [
                'brand' => 'Бренд', 'model' => 'Модель', 'color' => 'Цвет',
                'drive' => 'Привод', 'engine' => 'Двигатель', 'body' => 'Кузов', 'transmission' => 'КПП'
            ];

            foreach ($res['parser'] as $k => $items) {
                $res['pseudo'][$k] = [
                    'name'  => $labels[$k] ?? $k,
                    'query' => implode(', ', array_column($items, 'name'))
                ];
            }

            // Финальный запрос
            $res['query']['sort'][] = 'price_up';
            $res['query']['limit'][] = '3';
            if (!empty($GET['!brand'])) $res['query']['!brand'] = explode(',', $GET['!brand']);

            $searchQuery = $this->makeSearchQuery($res['query']);
            
            foreach (['new', 'used'] as $type) {
                $apiRes = $this->apiDBGetVehicles($type, $searchQuery)['items'] ?? [];
                $res['raw'][$type] = array_filter($apiRes, function($item) {
                    return ($item['type'] ?? '') !== 'random_cta';
                });
            }

            return $res;
        }
        private function makeSearchQuery($query) {
            if (empty($query)) return [];
            return array_map(function($q) {
                return is_array($q) ? implode(',', $q) : $q;
            }, $query);
        }
        private function safeGetImages( $useInternal, $extID, $rawImages ) {
            if ( $useInternal ) {
                $imgs = $this->getInternalImages($extID);
                return (!empty($imgs) && is_array($imgs)) ? array_chunk($imgs, 4)[0] : [];
            }
            return (!empty($rawImages) && is_array($rawImages)) ? array_chunk($rawImages, 4)[0] : [];
        }
        /* ************ **/
    }
?>