<?php

	class Analytics extends App {

        ////////////////////////////////////////////////////////////////
		// Consts  /////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////

        const AUTODEALER_URL_BASE           = 'https://yug-avto.autocrm.ru/yii/api';
        const AUTODEALER_RETAIL_CASE        = '/retailCase';
        const AUTODEALER_USED_CASE          = '/saleUsedCase';
        const AUTODEALER_DEALERSHIPS        = '/autosalon';
        const AUTODEALER_CASE_STAGE         = '/stage';
        const AUTODEALER_EVENT              = '/event';

        const CALLTOUCH_URL_BASE            = 'https://api.calltouch.ru/calls-service/RestAPI/';
        const CALLTOUCH_CALLS                = '/calls-diary/calls';
        const CALLTOUCH_BIDS                = '/requests';

        // const DOCUMENT_ROOT                 = '/home/admin/web/apps.yug-avto.ru/public_html';
        

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


        ////////////////////////////////////////////////////////////////
		// Private  ////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////

        private function Request( $url = null, $params = [], $auth = false ) {

            if ( !$url ) return 'error';

            if ( !empty($params) ) $url .= '?'.http_build_query($params);
            
            $curl = curl_init($url);
            
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            if ( $auth ) {
                curl_setopt($curl, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->Conf->Autodealer['token'],
                ]);
            }
            
            $response = curl_exec($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);

            // Helper::sp( $url );

            return json_decode($response, true);
        }

        
        ///////////////////////////////////////////////////////////////////////////////////////////
        // CRON ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        public function getARetailCases( $params = [] ) {

            $res = $this->Request(
                static::AUTODEALER_URL_BASE.static::AUTODEALER_RETAIL_CASE,
                $params,
                true
            );

            for ( $i=2; $i<=(int)$res['meta']['pagination']['page_count']; $i++ ) {
                $rs = $this->Request(
                    static::AUTODEALER_URL_BASE.static::AUTODEALER_RETAIL_CASE,
                    array_merge($params, ['RetailCaseSearch_page'=>$i]),
                    true
                )['result'];
                if (count($rs)) $res['result'] = array_merge( $res['result'], $rs);
            }
            Helper::sp('Р/л новые получены '.date('d-m-Y в H:i:s') );
            $err_dc = 0;
            foreach ( $res['result'] as $r ) {
                $tmp = [
                    'ext_id' => $r['id'],
                    'timestamp' => date('Y-m-d H:i:s', strtotime($r['createdAt'])),
                    'phone' => (string)Helper::formatPhoneIn($r['primaryPerson']['phones'][0]['number']),
                    'contract' => 0,
                    'issuance' => 0,

                ];
                if ( (int)$r['state'] == 1 || (int)$r['state'] == 2 ) {
                    $stages = $this->Request(
                        static::AUTODEALER_URL_BASE.static::AUTODEALER_CASE_STAGE,
                        ['caseIds' => $r['id']],
                        true
                    )['result'];
                    foreach ( $stages as $stage ) {
                        if ( (int)$stage['type'] == 11 && !!$stage['closedAt'] && (int)$stage['state'] == 3 ) {
                            $tmp['contract'] = 1;
                            $tmp['contract_date'] = $stage['closedAt'];
                        }
                        if ( (int)$stage['type'] == 12 && !!$stage['closedAt'] && (int)$stage['state'] == 3 ) {
                            $tmp['issuance'] = 1;
                            $tmp['issuance_date'] = $stage['closedAt'];
                        }
                    }
                }
                

                // if ( $r['state'] == 1 || $r['state'] == 2 ) {
                //     $events = $this->Request(
                //         static::AUTODEALER_URL_BASE.static::AUTODEALER_EVENT,
                //         ['caseId' => $r['id']],
                //         true
                //     )['result'];
                //     foreach ( $events as $e ) {
                //         if ( (int)$e['type'] == 6 && strripos($e['comment'], 'акрыт автоматически по Заказу') !== false ) {
                //             $tmp['contract'] = 1;
                //             $tmp['contract_date'] = $r['contract']['updated_at'];
                //         }
                //         if ( (int)$e['type'] == 6 && strripos($e['comment'], 'акрыт автоматически по Реализации') !== false ) {
                //             $tmp['issuance'] = 1;
                //             $tmp['issuance_date'] = $e['createdAt'];
                //         }
                //     }
                // }

                $tmp['dealership_id'] = $this->selectDealership($r);
                $tmp['cabinet'] = 0;
                if ( $tmp['dealership_id'] == 0 ) $err_dc++;
                if ( $tmp['dealership_id'] > 0 ) $tmp['cabinet'] = $this->MySQL->getOne('SELECT ct_id FROM yapps_app_cis_dealerships WHERE id = ?i', $tmp['dealership_id']);
                $tmp['channel'] = $this->selectChannel($r);

                $result[] = $tmp;
            }
            Helper::sp('Р/л новые обработаны '.date('d-m-Y в H:i:s') );
            if ( $err_dc ) Helper::sp('Р/л с не распознанным ДЦ: '.$err_dc );

            return $result;
        }
        public function getAUsedCases( $params = [] ) {

            $res = $this->Request(
                static::AUTODEALER_URL_BASE.static::AUTODEALER_USED_CASE,
                $params,
                true
            );

            for ( $i=2; $i<=(int)$res['meta']['pagination']['page_count']; $i++ ) {
                $rs = $this->Request(
                    static::AUTODEALER_URL_BASE.static::AUTODEALER_USED_CASE,
                    array_merge($params, ['RetailCaseSearch_page'=>$i]),
                    true
                )['result'];
                if (count($rs)) $res['result'] = array_merge( $res['result'], $rs);
            }

            Helper::sp('Р/л б/у получены '.date('d-m-Y в H:i:s') );
            foreach ( $res['result'] as $r ) {
                $tmp = [
                    'ext_id' => $r['id'],
                    'timestamp' => date('Y-m-d H:i:s', strtotime($r['createdAt'])),
                    'phone' => (string)Helper::formatPhoneIn($r['primaryPerson']['phones'][0]['number']),
                    'contract' => ( $r['state'] == 2 ) ? 1 : 0,
                    'issuance' => ( $r['state'] == 2 ) ? 1 : 0,

                ];
                if ( (int)$r['state'] == 1 || (int)$r['state'] == 2 ) {
                    $stages = $this->Request(
                        static::AUTODEALER_URL_BASE.static::AUTODEALER_CASE_STAGE,
                        ['caseIds' => $r['id']],
                        true
                    )['result'];
                    foreach ( $stages as $stage ) {
                        if ( (int)$stage['type'] == 11 && !!$stage['closedAt'] && (int)$stage['state'] == 3 ) {
                            $tmp['contract'] = 1;
                            $tmp['contract_date'] = $stage['closedAt'];
                        }
                        if ( (int)$stage['type'] == 12 && !!$stage['closedAt'] && (int)$stage['state'] == 3 ) {
                            $tmp['issuance'] = 1;
                            $tmp['issuance_date'] = $stage['closedAt'];
                        }
                    }
                }

                // if ( $r['state'] == 1 || $r['state'] == 2 ) {
                //     $events = $this->Request(
                //         static::AUTODEALER_URL_BASE.static::AUTODEALER_EVENT,
                //         ['caseId' => $r['id']],
                //         true
                //     )['result'];
                //     foreach ( $events as $e ) {
                //         if ( (int)$e['type'] == 6 && strripos($e['comment'], 'акрыт автоматически по Заказу') !== false ) {
                //             $tmp['contract'] = 1;
                //             $tmp['contract_date'] = $r['contract']['updated_at'];
                //         }
                //         if ( (int)$e['type'] == 6 && strripos($e['comment'], 'акрыт автоматически по Реализации') !== false ) {
                //             $tmp['issuance'] = 1;
                //             $tmp['issuance_date'] = $e['createdAt'];
                //         }
                //     }
                // }

                $tmp['dealership_id'] = $this->selectDealership($r);
                $tmp['channel'] = $this->selectChannel($r);
                $tmp['cabinet'] = $this->MySQL->getOne('SELECT ct_id FROM yapps_app_cis_dealerships WHERE id = ?i', $tmp['dealership_id']);

                $result[] = $tmp;
            }

            Helper::sp('Р/л б/у обработаны '.date('d-m-Y в H:i:s') );
            return $result;
        }

        public function getAEvents( $id ) {
            $events = $this->Request(
                static::AUTODEALER_URL_BASE.static::AUTODEALER_EVENT,
                ['caseId' => $id],
                true
            )['result'];
            return $events;
        }
        public function getACase( $id ) {

            $res = $this->Request(
                static::AUTODEALER_URL_BASE.static::AUTODEALER_RETAIL_CASE.'/'.$id,
                [],
                true
            );
            return $res['result'];
        }
        public function getAStages( $params = [] ) {

            $res = $this->Request(
                static::AUTODEALER_URL_BASE.static::AUTODEALER_CASE_STAGE,
                $params,
                true
            );
            for ( $i=2; $i<=(int)$res['meta']['pagination']['page_count']; $i++ ) {
                $rs = $this->Request(
                    static::AUTODEALER_URL_BASE.static::AUTODEALER_CASE_STAGE,
                    $params,
                    true
                )['result'];
                if (count($rs)) $res['result'] = array_merge( $res['result'], $rs);
            }
            return $res;
        }

        public function getCCalls( $ct_id = null, $params = null ) {

            $res = false;
            $url = static::CALLTOUCH_URL_BASE.$ct_id.static::CALLTOUCH_CALLS;

            if ( $ct_id ) {

                $res = $this->Request(
                    $url,
                    $params
                );
            }
            foreach ( $res as $k => $r ) {
                if ( $r['callerNumber'] ) {
                    $tmp = [
                        'timestamp' => date('Y-m-d H:i:s', strtotime( str_replace('/', '.', $r['date']) )),
                        'phone' => (string)Helper::formatPhoneIn($r['callerNumber']),
                        'channel' => ( $r['medium'] ) ?: '',
                        'source' => ( $r['source'] ) ?: '',
                        'utm_campaign' => ( $r['utmCampaign'] ) ?: '',
                        'utm_content' => ( $r['utmContent'] ) ?: '',
                        'utm_term' => ( $r['utmTerm'] ) ?: '',
                        'cabinet' => (string)$r['siteId'],
                        'type' => 'call',
                        'unique_flag' => ( $r['uniqueCall'] ) ? 1 : 0
                    ];
                    $result[] = $tmp;
                    // if (!$tmp['phone']) {
                    //     Helper::sp( $r );
                    //     Helper::sp( $url );
                    //     die;
                    // } 
                }
            }
            return $result;
        }
        public function getCBids( $params = null ) {

            $res = false;

            if ( $params ) {

                $res = $this->Request(
                    static::CALLTOUCH_URL_BASE.static::CALLTOUCH_BIDS,
                    $params
                );
            }
            foreach ( $res as $k => $r ) {
                if ( $r['client']['phones'][0]['phoneNumber'] ) {
                    $tmp = [
                        'timestamp' => date('Y-m-d H:i:s', strtotime( str_replace('/', '.', $r['dateStr']) )),
                        'phone' => (string)Helper::formatPhoneIn($r['client']['phones'][0]['phoneNumber']),
                        'channel' => ( $r['session']['medium'] ) ?: 'Без сессии',
                        'source' => ( $r['session']['source'] ) ?: '',
                        'utm_campaign' => ( $r['session']['utmCampaign'] ) ?: '',
                        'utm_content' => ( $r['session']['utmContent'] ) ?: '',
                        'utm_term' => ( $r['session']['utmTerm'] ) ?: '',
                        'cabinet' => (string)$r['siteId'],
                        'type' => 'bid',
                        'unique_flag' => ( $r['uniqueRequest'] ) ? 1 : 0
                    ];
                    $result[] = $tmp;
                }
            }
            return $result;
        }

        public function getADealerships() {

            $res = $this->Request(
                static::AUTODEALER_URL_BASE.static::AUTODEALER_DEALERSHIPS,
                [],
                true
            );
            return $res;
        }
        public function getYappsDealerships() {

            return $this->MySQL->getAll('SELECT * FROM yapps_app_cis_dealerships WHERE ct_id != ?i ORDER BY name ASC', 0);
        }

        public function selectDealership( $rl ) {

            switch ( (int)$rl['autosalon']['id'] ) {
                case 16: $res = 25; break; //Эксперт Новороссийск
                case 19: $res = 23; break; //Эксперт Яблоновский Y
                case 22: $res = 23; break; //Эксперт Яблоновский X
                case 30: //LADA XCITE Яблоновский 
                    if ( $rl['needs'][count($rl['needs'])-1]['refBrandId'] ) {
                        switch ( (int)$rl['needs'][count($rl['needs'])-1]['refBrandId'] ) {
                            case 125: $res = 4; break;
                            case 2743: $res = 52; break;
                            default: $res = 4; break;
                        }
                    } else {
                        $res = 4; break;
                    }
                    break;
                // case 'Эксперт Майкоп': $res = 000; break;
                case 44: $res = 13; break; //Haval Дзержинского
                case 46: //LADA XCITE Майкоп
                    if ( $rl['needs'][count($rl['needs'])-1]['refBrandId'] ) {
                        switch ( (int)$rl['needs'][count($rl['needs'])-1]['refBrandId'] ) {
                            case 125: $res = 9; break;
                            case 2743: $res = 53; break;
                            default: $res = 9; break;
                        }
                    } else {
                        $res = 9; break;
                    }
                    break;
                case 47: $res = 14; break; //Haval Яблоновский
                case 56: $res = 61; break; //Юг-Авто Холдинг
                case 61: $res = 15; break; //Haval Новороссийск
                case 78: $res = 26; break; //Эксперт Яблоновский Коммерческий
                case 95: $res = 29; break; //Эксперт Премиум
                case 99: $res = 31; break; //Sollers Яблоновский
                case 108: //GEELY KNEWSTAR Майкоп
                    if ( $rl['needs'][count($rl['needs'])-1]['refBrandId'] ) {
                        switch ( (int)$rl['needs'][count($rl['needs'])-1]['refBrandId'] ) {
                            case 46: $res = 33; break; // Geely
                            case 2771: $res = 59; break; // Knewstar
                            default: $res = 33; break;
                        }
                    } else {
                        $res = 46; break;
                    }
                    break;
                case 110: $res = 32; break; //MOSKVICH Яблоновский
                case 111: //TANK ORA WEY Яблоновский
                    if ( $rl['needs'][count($rl['needs'])-1]['refBrandId'] ) {
                        switch ( (int)$rl['needs'][count($rl['needs'])-1]['refBrandId'] ) {
                            case 2628: $res = 38; break;
                            case 2706: $res = 54; break;
                            case 2707: $res = 55; break;
                            default: $res = 38; break;
                        }
                    } else {
                        $res = 38; break;
                    }
                    break;
                case 112: $res = 36; break; //LIVAN Дзержинского
                case 113: $res = 34; break; //KAIYI Дзержинского
                case 114: $res = 35; break; //BAIC Дзержинского
                case 115: $res = 37; break; //OMODA Яблоновский
                case 116: //GEELY KNEWSTAR Яблоновский
                    if ( $rl['needs'][count($rl['needs'])-1]['refBrandId'] ) {
                        switch ( (int)$rl['needs'][count($rl['needs'])-1]['refBrandId'] ) {
                            case 46: $res = 44; break; // Geely
                            case 2771: $res = 60; break; // Knewstar
                            default: $res = 44; break;
                        }
                    } else {
                        $res = 44; break;
                    }
                    break;
                case 117: $res = 40; break; //MOSKVICH Новороссийск
                case 118: $res = 41; break; //CHERY Яблоновский
                case 119: $res = 45; break; //SOLLERS Дзержинского
                case 120: $res = 43; break; //JAECOO Яблоновский
                case 121: $res = 47; break; //SOLARIS Дзержинского
                case 122: $res = 48; break; //HAVAL PRO Дзержинского
                case 123: $res = 49; break; //SOLARIS Яблоновский
                case 124: $res = 51; break; //HAVAL PRO Яблоновский
                case 125: $res = 50; break; //JAC Яблоновский
                case 127: $res = 58; break; //BELGEE Яблоновский
                case 128: $res = 57; break; //GAC Дзержинского
                case 129: $res = 56; break; //CHANGAN Яблоновский
                case 130: $res = 62; break; //JETOUR Яблоновский
                case 131: //LADA XCITE Новороссийск 
                    if ( $rl['needs'][count($rl['needs'])-1]['refBrandId'] ) {
                        switch ( (int)$rl['needs'][count($rl['needs'])-1]['refBrandId'] ) {
                            case 125: $res = 63; break;
                            case 2743: $res = 64; break;
                            default: $res = 63; break;
                        }
                    } else {
                        $res = 4; break;
                    }
                    break;
                default: $res = 0; break;
            }

            return $res;
        }
        public function selectChannel( $rl ) {
            switch ( $rl['primaryContactType'] ) {
                case 5: $res = 'Звонок'; break;
                case 7: $res = 'Посещение'; break;
                case 11: $res = 'Интернет'; break;
                case 12: $res = 'Холодный звонок'; break;
                default: $res = '<none>'; break;
            }
            return $res;
        }

        public function pushAutodealer( $POST ) {

            if ( $cur_rl = $this->MySQL->getRow('SELECT * FROM yapps_app_analytics_autodealer WHERE ext_id = ?i', $POST['ext_id']) ) {
                if ( $cur_rl['contract'] != $POST['contract'] ) {
                    $this->MySQL->query(
                        'UPDATE yapps_app_analytics_autodealer SET ?u WHERE ext_id = ?i', 
                        [
                            'contract_date'=>$POST['contract_date'], 
                            'contract'=>$POST['contract'], 
                        ], 
                        $POST['ext_id']
                    );
                }
                if ( $cur_rl['issuance'] != $POST['issuance'] ) {
                    $this->MySQL->query(
                        'UPDATE yapps_app_analytics_autodealer SET ?u WHERE ext_id = ?i', 
                        [
                            'issuance_date'=>$POST['issuance_date'], 
                            'issuance'=>$POST['issuance'], 
                        ], 
                        $POST['ext_id']
                    );
                }
            } else {
                $this->MySQL->query('INSERT INTO yapps_app_analytics_autodealer SET ?u', $POST);
            }
        }
        public function pushCalltouch( $POST ) {

            $this->MySQL->query('INSERT INTO yapps_app_analytics_calltouch SET ?u', $POST);
        }


        ///////////////////////////////////////////////////////////////////////////////////////////
        // State //////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        public function getState() {
            
            return json_decode(file_get_contents(__DIR__.'/../../../_cron/Analytics/data/state.json'), true);
        }





        ///////////////////////////////////////////////////////////////////////////////////////////
        // API ////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        public function getAPIDealerships() {

            return $this->MySQL->getAll('SELECT * FROM yapps_app_cis_dealerships WHERE ct_id != ?i ORDER BY name ASC', 0);
        }

        private function makeQuery( $child_level = 0, $GET ) {

            $children = [ 'channel', 'source', 'utm_campaign', 'utm_content', 'utm_term'];

            $dateFrom = date('Y-m-d H:i:s', strtotime($GET['dateFrom']));
            $dateTo = date('Y-m-d H:i:s', strtotime($GET['dateTo'])+24*60*60);
            $dateFromCompare = date('Y-m-d H:i:s', strtotime($GET['dateFromCompare']));
            $dateToCompare = date('Y-m-d H:i:s', strtotime($GET['dateToCompare'])+24*60*60);
            
            $where = []; $where_compare = []; $items_where = [];
            $where['dateFrom'] = $this->MySQL->parse('timestamp >= ?s', $dateFrom);
            $where['dateTo'] = $this->MySQL->parse('timestamp < ?s', $dateTo);
            $where_compare['dateFrom'] = $this->MySQL->parse('timestamp >= ?s', $dateFromCompare);
            $where_compare['dateTo'] = $this->MySQL->parse('timestamp < ?s', $dateToCompare);

            if ( $GET['project'] ) {
                $where['cabinet'] = $where_compare['cabinet'] = $items_where['cabinet']= $this->MySQL->parse('cabinet IN (?a)', explode(',', $GET['ct']));
                $where_dealership = $this->MySQL->parse('dealership_id IN (?a)', explode(',', $GET['project']));
            }
            for ( $i = 0; $i < $child_level; $i++ ) $where[$children[$i]] = $where_compare[$children[$i]] =$items_where[$children[$i]] = $this->MySQL->parse($children[$i].' = ?s', $GET[$children[$i]]);

            $tmp_items['ad'] = $this->MySQL->getCol('SELECT DISTINCT '.$children[$child_level].' FROM yapps_app_analytics_autodealer WHERE '.implode(' AND ',array_values($where)).(($GET['project'])?' AND '.$where_dealership:''));
            $tmp_items['ct'] = $this->MySQL->getCol('SELECT DISTINCT '.$children[$child_level].' FROM yapps_app_analytics_calltouch WHERE '.implode(' AND ',array_values($where)));

            $items_where['dateFrom'] = $this->MySQL->parse('contract_date >= ?s', $dateFrom);
            $items_where['dateTo'] = $this->MySQL->parse('contract_date < ?s', $dateTo);
            $tmp_items['c'] = $this->MySQL->getCol('SELECT DISTINCT '.$children[$child_level].' FROM yapps_app_analytics_autodealer WHERE '.implode(' AND ',array_values($items_where)).(($GET['project'])?' AND '.$where_dealership:''));

            unset($items_where['dateFrom'], $items_where['dateTo']);
            $items_where['dateFrom'] = $this->MySQL->parse('issuance_date >= ?s', $dateFrom);
            $items_where['dateTo'] = $this->MySQL->parse('issuance_date < ?s', $dateTo);
            $tmp_items['i'] = $this->MySQL->getCol('SELECT DISTINCT '.$children[$child_level].' FROM yapps_app_analytics_autodealer WHERE '.implode(' AND ',array_values($items_where)).(($GET['project'])?' AND '.$where_dealership:''));

            $items = array_unique(
                array_merge(
                    ( is_array($tmp_items['ad']) ) ? $tmp_items['ad'] : [],
                    ( is_array($tmp_items['ct']) ) ? $tmp_items['ct'] : []
                )
            );
            sort($items);
            $ci_items = array_diff(
                array_unique(
                    array_merge(
                        ( is_array($tmp_items['c']) ) ? $tmp_items['c'] : [],
                        ( is_array($tmp_items['i']) ) ? $tmp_items['i'] : []
                    )
                ),
                $items
            );
            sort($ci_items);
            // if ( $GET['ct'] == '20621' ) {
            //     unset($where['cabinet'], $where_compare['cabinet'], $items_where['cabinet']);
            //     $where['phone'] = $where_compare['phone'] = $items_where['phone'] = $this->MySQL->parse( 'phone IN (?a)', $this->MySQL->getCol('SELECT DISTINCT phone FROM yapps_app_analytics_calltouch WHERE '.implode(' AND ',array_values($where))) );
            // }
            if( $GET['debug'] ) {
                Helper::sp($items, false, '$items');
                Helper::sp($ci_items, false, '$ci_items');
            }

            $ci_where = $where;

            foreach ( $items as $item ) {

                $where_parts = $where_parts_compare = [];

                $where['item'] = $where_compare['item'] = $this->MySQL->parse($children[$child_level].' = ?s', $item);

                unset( 
                    $where['contracts'],
                    $where_compare['contracts'],
                    $where['issuances'],
                    $where_compare['issuances'],
                    $where['type'],
                    $where_compare['type'],
                    $where['unique_flag'],
                    $where_compare['unique_flag']
                 );

                $where_parts['count'] = implode(' AND ',array_values($where));
                $where_parts_compare['count'] = implode(' AND ',array_values($where_compare));

                $where_contracts = $where; $where_contracts_compare = $where_compare;
                $where_contracts['dateFrom'] = $this->MySQL->parse('contract_date >= ?s', $dateFrom);
                $where_contracts['dateTo'] = $this->MySQL->parse('contract_date < ?s', $dateTo);
                $where_contracts_compare['dateFrom'] = $this->MySQL->parse('contract_date >= ?s', $dateFromCompare);
                $where_contracts_compare['dateTo'] = $this->MySQL->parse('contract_date < ?s', $dateToCompare);
                $where_contracts['contracts'] = $where_contracts_compare['contracts'] = $this->MySQL->parse('contract = ?i', 1);
                $where_parts['count_contracts'] = implode(' AND ',array_values($where_contracts));
                $where_parts_compare['count_contracts'] = implode(' AND ',array_values($where_contracts_compare));

                $where_issuances = $where; $where_issuances_compare = $where_compare;
                $where_issuances['dateFrom'] = $this->MySQL->parse('issuance_date >= ?s', $dateFrom);
                $where_issuances['dateTo'] = $this->MySQL->parse('issuance_date < ?s', $dateTo);
                $where_issuances_compare['dateFrom'] = $this->MySQL->parse('issuance_date >= ?s', $dateFromCompare);
                $where_issuances_compare['dateTo'] = $this->MySQL->parse('issuance_date < ?s', $dateToCompare);
                $where_issuances['issuances'] = $where_issuances_compare['issuances'] = $this->MySQL->parse('issuance = ?i', 1);
                $where_parts['count_issuances'] = implode(' AND ',array_values($where_issuances));
                $where_parts_compare['count_issuances'] = implode(' AND ',array_values($where_issuances_compare));

                unset($where['contracts'], $where['issuances'], $where_compare['contracts'], $where_compare['issuances']);

                $where['type'] = $where_compare['type'] = $this->MySQL->parse('type = ?s', 'call');
                $where_parts['count_calls'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_calls'] = implode(' AND ',array_values($where_compare));

                $where['type'] = $where_compare['type'] = $this->MySQL->parse('type = ?s', 'bid');
                $where_parts['count_bids'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_bids'] = implode(' AND ',array_values($where_compare));

                $where['unique_flag'] = $where_compare['unique_flag'] = $this->MySQL->parse('unique_flag = ?i', 1);
                $where_parts['count_unibids'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_unibids'] = implode(' AND ',array_values($where_compare));

                $where['type'] = $where_compare['type'] = $this->MySQL->parse('type = ?s', 'call');
                $where_parts['count_unicalls'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_unicalls'] = implode(' AND ',array_values($where_compare));

                $query = '
                    SELECT
                        '.$children[$child_level].' as item,

                        COUNT(*) as count,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count,
                        
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as count_issuances,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_issuances,
                        ';
                        if ($child_level<4)  $query .= '
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count'].' AND sub.'.$children[$child_level+1].' != \'\''.(($GET['project'])?' AND '.$where_dealership:'').') as children_ad,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count'].' AND sub.'.$children[$child_level+1].' != \'\') as children_ct,
                        
                        ';

                        $query .= '
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_calls'].') as count_calls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_calls'].') as compare_count_calls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_bids'].') as count_bids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_bids'].') as compare_count_bids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_unicalls'].') as count_unicalls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unicalls'].') as compare_count_unicalls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_unibids'].') as count_unibids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unibids'].') as compare_count_unibids

                    FROM yapps_app_analytics_autodealer as main
                    WHERE '.$where_parts['count'].(($GET['project'])?' AND '.$where_dealership:'').'
                    GROUP BY main.'.$children[$child_level].'

                    UNION ALL

                     SELECT
                        '.$children[$child_level].' as item,

                        0 as count,
                        0 as compare_count,
                        
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as count_issuances,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_issuances,
                        ';
                        if ($child_level<4)  $query .= '
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count'].' AND sub.'.$children[$child_level+1].' != \'\''.(($GET['project'])?' AND '.$where_dealership:'').') as children_ad,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count'].' AND sub.'.$children[$child_level+1].' != \'\') as children_ct,
                        
                        ';

                        $query .= '
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_calls'].') as count_calls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_calls'].') as compare_count_calls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_bids'].') as count_bids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_bids'].') as compare_count_bids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_unicalls'].') as count_unicalls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unicalls'].') as compare_count_unicalls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_unibids'].') as count_unibids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unibids'].') as compare_count_unibids

                    FROM yapps_app_analytics_calltouch as main
                    WHERE '.$where_parts['count'].'
                    GROUP BY main.'.$children[$child_level].'

                ';
                if( $GET['debug'] ) {
                    Helper::sp($query, false, '$query');
                }
                $tmp = $this->MySQL->getRow( $query );
                $tmp['children'] = ( (int)$tmp['children_ad'] || (int)$tmp['children_ct'] ) ? 1 : 0;
                $res[] = $tmp;

            }
            foreach ( $ci_items as $item ) {

                $where_parts = $where_parts_compare = []; $where = $ci_where;

                $where['item'] = $where_compare['item'] = $this->MySQL->parse($children[$child_level].' = ?s', $item);

                unset( 
                    $where['contracts'],
                    $where_compare['contracts'],
                    $where['issuances'],
                    $where_compare['issuances'],
                    $where['type'],
                    $where_compare['type'],
                    $where['unique_flag'],
                    $where_compare['unique_flag']
                );
                $w = $this->MySQL->parse(
                    '((contract_date >= ?s AND contract_date < ?s AND contract = ?i) OR (issuance_date >= ?s AND issuance_date < ?s AND issuance = ?i))',
                    $dateFrom, $dateTo, 1, $dateFrom, $dateTo, 1
                );
                $w2 = $where;
                unset(
                    $w2['dateFrom'],
                    $w2['dateTo']
                );

                $where_parts['count'] = $w.' AND '.implode(' AND ',array_values($w2));
                $where_parts_compare['count'] = implode(' AND ',array_values($where_compare));

                $where_contracts = $where; $where_contracts_compare = $where_compare;
                $where_contracts['dateFrom'] = $this->MySQL->parse('contract_date >= ?s', $dateFrom);
                $where_contracts['dateTo'] = $this->MySQL->parse('contract_date < ?s', $dateTo);
                $where_contracts_compare['dateFrom'] = $this->MySQL->parse('contract_date >= ?s', $dateFromCompare);
                $where_contracts_compare['dateTo'] = $this->MySQL->parse('contract_date < ?s', $dateToCompare);
                $where_contracts['contracts'] = $where_contracts_compare['contracts'] = $this->MySQL->parse('contract = ?i', 1);
                $where_parts['count_contracts'] = implode(' AND ',array_values($where_contracts));
                $where_parts_compare['count_contracts'] = implode(' AND ',array_values($where_contracts_compare));

                $where_issuances = $where; $where_issuances_compare = $where_compare;
                $where_issuances['dateFrom'] = $this->MySQL->parse('issuance_date >= ?s', $dateFrom);
                $where_issuances['dateTo'] = $this->MySQL->parse('issuance_date < ?s', $dateTo);
                $where_issuances_compare['dateFrom'] = $this->MySQL->parse('issuance_date >= ?s', $dateFromCompare);
                $where_issuances_compare['dateTo'] = $this->MySQL->parse('issuance_date < ?s', $dateToCompare);
                $where_issuances['issuances'] = $where_issuances_compare['issuances'] = $this->MySQL->parse('issuance = ?i', 1);
                $where_parts['count_issuances'] = implode(' AND ',array_values($where_issuances));
                $where_parts_compare['count_issuances'] = implode(' AND ',array_values($where_issuances_compare));

                unset($where['contracts'], $where['issuances'], $where_compare['contracts'], $where_compare['issuances']);

                $where['type'] = $where_compare['type'] = $this->MySQL->parse('type = ?s', 'call');
                $where_parts['count_calls'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_calls'] = implode(' AND ',array_values($where_compare));

                $where['type'] = $where_compare['type'] = $this->MySQL->parse('type = ?s', 'bid');
                $where_parts['count_bids'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_bids'] = implode(' AND ',array_values($where_compare));

                $where['unique_flag'] = $where_compare['unique_flag'] = $this->MySQL->parse('unique_flag = ?i', 1);
                $where_parts['count_unibids'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_unibids'] = implode(' AND ',array_values($where_compare));

                $where['type'] = $where_compare['type'] = $this->MySQL->parse('type = ?s', 'call');
                $where_parts['count_unicalls'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_unicalls'] = implode(' AND ',array_values($where_compare));
                
                
                // Helper::sp($where_parts);
                $query = '
                    SELECT
                        '.$children[$child_level].' as item,

                        0 as count,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count,
                        
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as count_issuances,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_issuances,
                        ';
                        if ($child_level<4)  $query .= '
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count'].' AND sub.'.$children[$child_level+1].' != \'\''.(($GET['project'])?' AND '.$where_dealership:'').') as children_ad,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.implode(' AND ',array_values($where)).' AND sub.'.$children[$child_level+1].' != \'\') as children_ct,
                        
                        ';

                        $query .= '
                        0 as count_calls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_calls'].') as compare_count_calls,
                        0 as count_bids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_bids'].') as compare_count_bids,
                        0 as count_unicalls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unicalls'].') as compare_count_unicalls,
                        0 as count_unibids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unibids'].') as compare_count_unibids

                    FROM yapps_app_analytics_autodealer as main
                    WHERE '.$where_parts['count'].(($GET['project'])?' AND '.$where_dealership:'').'
                    GROUP BY main.'.$children[$child_level].'

                    UNION ALL

                     SELECT
                        '.$children[$child_level].' as item,

                        0 as count,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as count_issuances,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_issuances,
                        ';
                        if ($child_level<4)  $query .= '
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count'].' AND sub.'.$children[$child_level+1].' != \'\''.(($GET['project'])?' AND '.$where_dealership:'').') as children_ad,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.implode(' AND ',array_values($where)).' AND sub.'.$children[$child_level+1].' != \'\') as children_ct,
                        
                        ';

                        $query .= '
                        0 as count_calls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_calls'].') as compare_count_calls,
                        0 as count_bids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_bids'].') as compare_count_bids,
                        0 as count_unicalls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unicalls'].') as compare_count_unicalls,
                        0 as count_unibids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unibids'].') as compare_count_unibids

                    FROM yapps_app_analytics_calltouch as main
                    WHERE '.implode(' AND ',array_values($where)).'
                    GROUP BY main.'.$children[$child_level].'

                ';
                if( $GET['debug'] ) {
                    Helper::sp($query, false, '$query');
                }
                $tmp = $this->MySQL->getRow( $query );
                $tmp['children'] = ( (int)$tmp['children_ad'] || (int)$tmp['children_ct'] ) ? 1 : 0;
                $res[] = $tmp;

            }


            return $res;
            
        }
        private function __makeQuery( $child_level = 0, $GET ) {

            $children = [ 'channel', 'source', 'utm_campaign', 'utm_content', 'utm_term'];

            $dateFrom = date('Y-m-d H:i:s', strtotime($GET['dateFrom']));
            $dateTo = date('Y-m-d H:i:s', strtotime($GET['dateTo'])+24*60*60);
            $dateFromCompare = date('Y-m-d H:i:s', strtotime($GET['dateFromCompare']));
            $dateToCompare = date('Y-m-d H:i:s', strtotime($GET['dateToCompare'])+24*60*60);
            
            $where = []; $where_compare = []; $items_where = [];
            $where['dateFrom'] = $this->MySQL->parse('timestamp >= ?s', $dateFrom);
            $where['dateTo'] = $this->MySQL->parse('timestamp < ?s', $dateTo);
            $where_compare['dateFrom'] = $this->MySQL->parse('timestamp >= ?s', $dateFromCompare);
            $where_compare['dateTo'] = $this->MySQL->parse('timestamp < ?s', $dateToCompare);

            if ( $GET['project'] ) {
                $where['cabinet'] = $where_compare['cabinet'] = $items_where['cabinet'] = $this->MySQL->parse('cabinet IN (?a)', explode(',', $GET['ct']));
                $where_dealership = $this->MySQL->parse('dealership_id IN (?a)', explode(',', $GET['project']));
                // if ( (int)$GET['ct'] == 20621 ) {
                //     unset($where['cabinet'], $where_compare['cabinet'], $items_where['cabinet']);
                // }
            }
            for ( $i = 0; $i < $child_level; $i++ ) $where[$children[$i]] = $where_compare[$children[$i]] =$items_where[$children[$i]] = $this->MySQL->parse($children[$i].' = ?s', $GET[$children[$i]]);

            $tmp_items['ad'] = $this->MySQL->getCol('SELECT DISTINCT '.$children[$child_level].' FROM yapps_app_analytics_autodealer WHERE '.implode(' AND ',array_values($where)).(($GET['project'])?' AND '.$where_dealership:''));
            $tmp_items['ct'] = $this->MySQL->getCol('SELECT DISTINCT '.$children[$child_level].' FROM yapps_app_analytics_calltouch WHERE '.implode(' AND ',array_values($where)));

            $items_where['dateFrom'] = $this->MySQL->parse('contract_date >= ?s', $dateFrom);
            $items_where['dateTo'] = $this->MySQL->parse('contract_date < ?s', $dateTo);
            $tmp_items['c'] = $this->MySQL->getCol('SELECT DISTINCT '.$children[$child_level].' FROM yapps_app_analytics_autodealer WHERE '.implode(' AND ',array_values($items_where)).(($GET['project'])?' AND '.$where_dealership:''));

            unset($items_where['dateFrom'], $items_where['dateTo']);
            $items_where['dateFrom'] = $this->MySQL->parse('issuance_date >= ?s', $dateFrom);
            $items_where['dateTo'] = $this->MySQL->parse('issuance_date < ?s', $dateTo);
            $tmp_items['i'] = $this->MySQL->getCol('SELECT DISTINCT '.$children[$child_level].' FROM yapps_app_analytics_autodealer WHERE '.implode(' AND ',array_values($items_where)).(($GET['project'])?' AND '.$where_dealership:''));

            $items = array_unique(
                array_merge(
                    ( is_array($tmp_items['ad']) ) ? $tmp_items['ad'] : [],
                    ( is_array($tmp_items['ct']) ) ? $tmp_items['ct'] : []
                )
            );
            sort($items);
            $ci_items = array_diff(
                array_unique(
                    array_merge(
                        ( is_array($tmp_items['c']) ) ? $tmp_items['c'] : [],
                        ( is_array($tmp_items['i']) ) ? $tmp_items['i'] : []
                    )
                ),
                $items
            );
            sort($ci_items);
            
            // if ( $GET['ct'] == '20621' ) {
            //     $h = true;
            //     // unset($where['cabinet'], $where_compare['cabinet'], $items_where['cabinet']);
            //     $where_phone = $this->MySQL->parse( 'phone IN (?a)', $this->MySQL->getCol('SELECT DISTINCT phone FROM yapps_app_analytics_calltouch WHERE '.implode(' AND ',array_values($where))) );
            // }
            if( $GET['debug'] ) {
                Helper::sp($items, false, '$items');
                Helper::sp($ci_items, false, '$ci_items');
            }

            $ci_where = $where;

            foreach ( $items as $item ) {

                $where_parts = $where_parts_compare = [];

                $where['item'] = $where_compare['item'] = $this->MySQL->parse($children[$child_level].' = ?s', $item);

                unset( 
                    $where['contracts'],
                    $where_compare['contracts'],
                    $where['issuances'],
                    $where_compare['issuances'],
                    $where['type'],
                    $where_compare['type'],
                    $where['unique_flag'],
                    $where_compare['unique_flag']
                 );

                $where_parts['count'] = implode(' AND ',array_values($where));
                $where_parts_compare['count'] = implode(' AND ',array_values($where_compare));

                $where_contracts = $where; $where_contracts_compare = $where_compare;
                $where_contracts['dateFrom'] = $this->MySQL->parse('contract_date >= ?s', $dateFrom);
                $where_contracts['dateTo'] = $this->MySQL->parse('contract_date < ?s', $dateTo);
                $where_contracts_compare['dateFrom'] = $this->MySQL->parse('contract_date >= ?s', $dateFromCompare);
                $where_contracts_compare['dateTo'] = $this->MySQL->parse('contract_date < ?s', $dateToCompare);
                $where_contracts['contracts'] = $where_contracts_compare['contracts'] = $this->MySQL->parse('contract = ?i', 1);
                $where_parts['count_contracts'] = implode(' AND ',array_values($where_contracts));
                $where_parts_compare['count_contracts'] = implode(' AND ',array_values($where_contracts_compare));

                $where_issuances = $where; $where_issuances_compare = $where_compare;
                $where_issuances['dateFrom'] = $this->MySQL->parse('issuance_date >= ?s', $dateFrom);
                $where_issuances['dateTo'] = $this->MySQL->parse('issuance_date < ?s', $dateTo);
                $where_issuances_compare['dateFrom'] = $this->MySQL->parse('issuance_date >= ?s', $dateFromCompare);
                $where_issuances_compare['dateTo'] = $this->MySQL->parse('issuance_date < ?s', $dateToCompare);
                $where_issuances['issuances'] = $where_issuances_compare['issuances'] = $this->MySQL->parse('issuance = ?i', 1);
                $where_parts['count_issuances'] = implode(' AND ',array_values($where_issuances));
                $where_parts_compare['count_issuances'] = implode(' AND ',array_values($where_issuances_compare));

                unset($where['contracts'], $where['issuances'], $where_compare['contracts'], $where_compare['issuances']);

                $where['type'] = $where_compare['type'] = $this->MySQL->parse('type = ?s', 'call');
                $where_parts['count_calls'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_calls'] = implode(' AND ',array_values($where_compare));

                $where['type'] = $where_compare['type'] = $this->MySQL->parse('type = ?s', 'bid');
                $where_parts['count_bids'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_bids'] = implode(' AND ',array_values($where_compare));

                $where['unique_flag'] = $where_compare['unique_flag'] = $this->MySQL->parse('unique_flag = ?i', 1);
                $where_parts['count_unibids'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_unibids'] = implode(' AND ',array_values($where_compare));

                $where['type'] = $where_compare['type'] = $this->MySQL->parse('type = ?s', 'call');
                $where_parts['count_unicalls'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_unicalls'] = implode(' AND ',array_values($where_compare));

                $query = '
                    SELECT
                        '.$children[$child_level].' as item,

                        COUNT(*) as count,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count,
                        
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as count_issuances,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_issuances,
                        ';
                        if ($child_level<4)  $query .= '
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count'].' AND sub.'.$children[$child_level+1].' != \'\''.(($GET['project'])?' AND '.$where_dealership:'').') as children_ad,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count'].' AND sub.'.$children[$child_level+1].' != \'\') as children_ct,
                        
                        ';

                        $query .= '
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_calls'].') as count_calls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_calls'].') as compare_count_calls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_bids'].') as count_bids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_bids'].') as compare_count_bids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_unicalls'].') as count_unicalls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unicalls'].') as compare_count_unicalls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_unibids'].') as count_unibids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unibids'].') as compare_count_unibids

                    FROM yapps_app_analytics_autodealer as main
                    WHERE '.$where_parts['count'].(($GET['project'])?' AND '.$where_dealership:'').'
                    GROUP BY main.'.$children[$child_level].'

                    UNION ALL

                     SELECT
                        '.$children[$child_level].' as item,

                        0 as count,
                        0 as compare_count,
                        
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as count_issuances,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_issuances,
                        ';
                        if ($child_level<4)  $query .= '
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count'].' AND sub.'.$children[$child_level+1].' != \'\''.(($GET['project'])?' AND '.$where_dealership:'').') as children_ad,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count'].' AND sub.'.$children[$child_level+1].' != \'\') as children_ct,
                        
                        ';

                        $query .= '
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_calls'].') as count_calls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_calls'].') as compare_count_calls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_bids'].') as count_bids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_bids'].') as compare_count_bids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_unicalls'].') as count_unicalls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unicalls'].') as compare_count_unicalls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts['count_unibids'].') as count_unibids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unibids'].') as compare_count_unibids

                    FROM yapps_app_analytics_calltouch as main
                    WHERE '.$where_parts['count'].'
                    GROUP BY main.'.$children[$child_level].'

                ';
                if( $GET['debug'] ) {
                    Helper::sp($query, false, '$query');
                }
                $tmp = $this->MySQL->getRow( $query );
                $tmp['children'] = ( (int)$tmp['children_ad'] || (int)$tmp['children_ct'] ) ? 1 : 0;
                $res[] = $tmp;

            }
            foreach ( $ci_items as $item ) {

                $where_parts = $where_parts_compare = []; $where = $ci_where;

                $where['item'] = $where_compare['item'] = $this->MySQL->parse($children[$child_level].' = ?s', $item);

                unset( 
                    $where['contracts'],
                    $where_compare['contracts'],
                    $where['issuances'],
                    $where_compare['issuances'],
                    $where['type'],
                    $where_compare['type'],
                    $where['unique_flag'],
                    $where_compare['unique_flag']
                );
                $w = $this->MySQL->parse(
                    '((contract_date >= ?s AND contract_date < ?s AND contract = ?i) OR (issuance_date >= ?s AND issuance_date < ?s AND issuance = ?i))',
                    $dateFrom, $dateTo, 1, $dateFrom, $dateTo, 1
                );
                $w2 = $where;
                unset(
                    $w2['dateFrom'],
                    $w2['dateTo']
                );

                $where_parts['count'] = $w.' AND '.implode(' AND ',array_values($w2));
                $where_parts_compare['count'] = implode(' AND ',array_values($where_compare));

                $where_contracts = $where; $where_contracts_compare = $where_compare;
                $where_contracts['dateFrom'] = $this->MySQL->parse('contract_date >= ?s', $dateFrom);
                $where_contracts['dateTo'] = $this->MySQL->parse('contract_date < ?s', $dateTo);
                $where_contracts_compare['dateFrom'] = $this->MySQL->parse('contract_date >= ?s', $dateFromCompare);
                $where_contracts_compare['dateTo'] = $this->MySQL->parse('contract_date < ?s', $dateToCompare);
                $where_contracts['contracts'] = $where_contracts_compare['contracts'] = $this->MySQL->parse('contract = ?i', 1);
                $where_parts['count_contracts'] = implode(' AND ',array_values($where_contracts));
                $where_parts_compare['count_contracts'] = implode(' AND ',array_values($where_contracts_compare));

                $where_issuances = $where; $where_issuances_compare = $where_compare;
                $where_issuances['dateFrom'] = $this->MySQL->parse('issuance_date >= ?s', $dateFrom);
                $where_issuances['dateTo'] = $this->MySQL->parse('issuance_date < ?s', $dateTo);
                $where_issuances_compare['dateFrom'] = $this->MySQL->parse('issuance_date >= ?s', $dateFromCompare);
                $where_issuances_compare['dateTo'] = $this->MySQL->parse('issuance_date < ?s', $dateToCompare);
                $where_issuances['issuances'] = $where_issuances_compare['issuances'] = $this->MySQL->parse('issuance = ?i', 1);
                $where_parts['count_issuances'] = implode(' AND ',array_values($where_issuances));
                $where_parts_compare['count_issuances'] = implode(' AND ',array_values($where_issuances_compare));

                unset($where['contracts'], $where['issuances'], $where_compare['contracts'], $where_compare['issuances']);

                $where['type'] = $where_compare['type'] = $this->MySQL->parse('type = ?s', 'call');
                $where_parts['count_calls'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_calls'] = implode(' AND ',array_values($where_compare));

                $where['type'] = $where_compare['type'] = $this->MySQL->parse('type = ?s', 'bid');
                $where_parts['count_bids'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_bids'] = implode(' AND ',array_values($where_compare));

                $where['unique_flag'] = $where_compare['unique_flag'] = $this->MySQL->parse('unique_flag = ?i', 1);
                $where_parts['count_unibids'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_unibids'] = implode(' AND ',array_values($where_compare));

                $where['type'] = $where_compare['type'] = $this->MySQL->parse('type = ?s', 'call');
                $where_parts['count_unicalls'] = implode(' AND ',array_values($where));
                $where_parts_compare['count_unicalls'] = implode(' AND ',array_values($where_compare));
                
                
                // Helper::sp($where_parts);
                $query = '
                    SELECT
                        '.$children[$child_level].' as item,

                        0 as count,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count,
                        
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as count_issuances,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_issuances,
                        ';
                        if ($child_level<4)  $query .= '
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count'].' AND sub.'.$children[$child_level+1].' != \'\''.(($GET['project'])?' AND '.$where_dealership:'').') as children_ad,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.implode(' AND ',array_values($where)).' AND sub.'.$children[$child_level+1].' != \'\') as children_ct,
                        
                        ';

                        $query .= '
                        0 as count_calls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_calls'].') as compare_count_calls,
                        0 as count_bids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_bids'].') as compare_count_bids,
                        0 as count_unicalls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unicalls'].') as compare_count_unicalls,
                        0 as count_unibids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unibids'].') as compare_count_unibids

                    FROM yapps_app_analytics_autodealer as main
                    WHERE '.$where_parts['count'].(($GET['project'])?' AND '.$where_dealership:'').'
                    GROUP BY main.'.$children[$child_level].'

                    UNION ALL

                     SELECT
                        '.$children[$child_level].' as item,

                        0 as count,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_contracts'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_contracts,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as count_issuances,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts_compare['count_issuances'].(($GET['project'])?' AND '.$where_dealership:'').') as compare_count_issuances,
                        ';
                        if ($child_level<4)  $query .= '
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_autodealer as sub
                            WHERE '.$where_parts['count'].' AND sub.'.$children[$child_level+1].' != \'\''.(($GET['project'])?' AND '.$where_dealership:'').') as children_ad,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.implode(' AND ',array_values($where)).' AND sub.'.$children[$child_level+1].' != \'\') as children_ct,
                        
                        ';

                        $query .= '
                        0 as count_calls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_calls'].') as compare_count_calls,
                        0 as count_bids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_bids'].') as compare_count_bids,
                        0 as count_unicalls,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unicalls'].') as compare_count_unicalls,
                        0 as count_unibids,
                        (SELECT 
                            COUNT(*) 
                            FROM yapps_app_analytics_calltouch as sub
                            WHERE '.$where_parts_compare['count_unibids'].') as compare_count_unibids

                    FROM yapps_app_analytics_calltouch as main
                    WHERE '.implode(' AND ',array_values($where)).'
                    GROUP BY main.'.$children[$child_level].'

                ';
                if( $GET['debug'] ) {
                    Helper::sp($query, false, '$query');
                }
                $tmp = $this->MySQL->getRow( $query );
                $tmp['children'] = ( (int)$tmp['children_ad'] || (int)$tmp['children_ct'] ) ? 1 : 0;
                $res[] = $tmp;

            }


            return $res;
            
        }


        public function getAPIResult( $GET ) {
            
            $res = $this->makeQuery( (int)$GET['child_level'], $GET );

            if ( $GET['sort'] ) {
                switch ( $GET['sort'] ) {
                    case 'calls':
                        foreach ( $res as $r ) $arr[] = $r['count_calls'];
                        array_multisort($arr, SORT_DESC, SORT_NUMERIC, $res);
                        break;
                    case 'unicalls':
                        foreach ( $res as $r ) $arr[] = $r['count_unicalls'];
                        array_multisort($arr, SORT_DESC, SORT_NUMERIC, $res);
                        break;
                    case 'bids':
                        foreach ( $res as $r ) $arr[] = $r['count_bids'];
                        array_multisort($arr, SORT_DESC, SORT_NUMERIC, $res);
                        break;
                    case 'unibids':
                        foreach ( $res as $r ) $arr[] = $r['count_unibids'];
                        array_multisort($arr, SORT_DESC, SORT_NUMERIC, $res);
                        break;
                    case 'cases':
                        foreach ( $res as $r ) $arr[] = $r['count'];
                        array_multisort($arr, SORT_DESC, SORT_NUMERIC, $res);
                        break;
                    case 'contracts':
                        foreach ( $res as $r ) $arr[] = $r['count_contracts'];
                        array_multisort($arr, SORT_DESC, SORT_NUMERIC, $res);
                        break;
                    case 'issuances':
                        foreach ( $res as $r ) $arr[] = $r['count_issuances'];
                        array_multisort($arr, SORT_DESC, SORT_NUMERIC, $res);
                        break;

                    default: break;
                }
            }

            return $res;
        }
        public function __getAPIResult( $GET ) {
            
            $res = $this->__makeQuery( (int)$GET['child_level'], $GET );

            if ( $GET['sort'] ) {
                switch ( $GET['sort'] ) {
                    case 'calls':
                        foreach ( $res as $r ) $arr[] = $r['count_calls'];
                        array_multisort($arr, SORT_DESC, SORT_NUMERIC, $res);
                        break;
                    case 'unicalls':
                        foreach ( $res as $r ) $arr[] = $r['count_unicalls'];
                        array_multisort($arr, SORT_DESC, SORT_NUMERIC, $res);
                        break;
                    case 'bids':
                        foreach ( $res as $r ) $arr[] = $r['count_bids'];
                        array_multisort($arr, SORT_DESC, SORT_NUMERIC, $res);
                        break;
                    case 'unibids':
                        foreach ( $res as $r ) $arr[] = $r['count_unibids'];
                        array_multisort($arr, SORT_DESC, SORT_NUMERIC, $res);
                        break;
                    case 'cases':
                        foreach ( $res as $r ) $arr[] = $r['count'];
                        array_multisort($arr, SORT_DESC, SORT_NUMERIC, $res);
                        break;
                    case 'contracts':
                        foreach ( $res as $r ) $arr[] = $r['count_contracts'];
                        array_multisort($arr, SORT_DESC, SORT_NUMERIC, $res);
                        break;
                    case 'issuances':
                        foreach ( $res as $r ) $arr[] = $r['count_issuances'];
                        array_multisort($arr, SORT_DESC, SORT_NUMERIC, $res);
                        break;

                    default: break;
                }
            }

            return $res;
        }









        
	}
?>