<?php

	class Widgets extends App {
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Init ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, PHPMailer &$mailer ) {
			
			$this->MySQL		= &$mysql;
			$this->Conf		= (object)$arConf['modules'][get_class($this)];
			$this->Mailer	= &$mailer;
			$this->Yandex	= (object)$arConf['App']['Yandex'];
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
        // Private Area ///////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		private function sendForm( $arIns ) {
			
			$this->Mailer->CharSet = 'utf-8';
			$this->Mailer->setFrom('widgets@apps.yug-avto.ru', 'Оповещения Юг-Авто Apps. Виджеты.');
			$this->Mailer->ClearAddresses();
			
			$site = $this->YApps_GetSiteByID($arIns['site_id']);
			
			$arRec = array_merge( $arIns['widget']['recipients'], $arIns['settings']['recipients'] );
			if ( !$arRec ) $arRec = [$this->Conf->Defaults->Recipients];
			
			if ($arIns['widget']['id'] == 289) {
				$this->Mailer->addAddress('anton.boreckiy@yug-avto.ru', '');
			} else {
				foreach ($arRec as $email) $this->Mailer->addAddress($email, '');
			}
			
			
			$this->Mailer->Subject = 'Сайт: '.$site['ru_name'].'. '.$arIns['event_name'];

			$message = '<h3>Сайт: '.$site['ru_name'].'. '.$arIns['event_name'].'</h3>';
			if ($arIns['name']) $message .= 'Имя: '.$arIns['name'].'<br />';
			$message .= 'Телефон: '.(($arIns['phone'])?Helper::formatPhoneOut($arIns['phone']):'').'<br />';
			if ($arIns['datetime']) $message .= 'Дата и время звонка: '.$arIns['datetime'].'<br />';
			if ($arIns['text']) $message .= 'Время звонка: '.$arIns['text'].'<br />';
			
			
			if ( $arIns['quiz'] ) {
				
				$message .= '<br /><br />';
				
				foreach( $arIns['quiz'] as $slide ) {
					
					$message .= '<strong>'.$slide['slide_name'].'</strong><br />-----------------------------------------------<br />';
					foreach ( $slide['items'] as $item ) {
						
						if ( $item['item_name'] ) $message .= $item['item_name'].'<br />';
						$message .= $item['item_value'].'<br />';
					}
					$message .= '<br />';
				}
			}
			
			
			$message .= '<br /><br />';
			$message .= 'Страница-источник: <a href="'.$arIns['source_url'].'" target="_blank">'.(($arIns['source_title'])?:$arIns['source_url']).'</a>';

			if ($arIns['widget']['id'] == 289) {

				$message .= '<br /><br />';
				$message .= print_r($_SERVER, true);

				$message .= '<br /><br />';
				$message .= print_r($_REQUEST, true);
			}

			$this->Mailer->msgHTML($message);
			
			return $this->Mailer->Send();
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Types //////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getTypes() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_types');
		}

		public function getTypesIDs() {
			
			return $this->MySQL->getCol('SELECT id FROM yapps_app_widgets_types');
		}
		
		public function getTypeById( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_types WHERE id = ?i', (int)$id);
		}
		
		public function getTypeByKey( $key ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_types WHERE keyword = ?s', (string)$key);
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Recipients /////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getRecipients( $site_id, $widget_id = false ) {
			
			$res = $this->MySQL->getCol('SELECT recipient FROM yapps_app_widgets_recipients WHERE site_id = ?i AND widget_id = ?i', (int)$site_id, 0);
			if ( $widget_id ) $res = array_merge($res, $this->MySQL->getCol('SELECT recipient FROM yapps_app_widgets_recipients WHERE widget_id = ?i', (int)$widget_id));
			
			return $res;
		}
		
		public function getSetsRecipients( $site_id ) {
			
			return $this->MySQL->getCol('SELECT recipient FROM yapps_app_widgets_recipients WHERE site_id = ?i AND widget_id = ?i', (int)$site_id, 0);
		}
		
		public function getWidgetRecipients( $id ) {
			
			return $this->MySQL->getCol('SELECT recipient FROM yapps_app_widgets_recipients WHERE widget_id = ?i', (int)$id);
		}
		
		public function setWidgetRecipients( $recipients, $site_id, $widget_id ) {
			
			$this->MySQL->query('DELETE FROM yapps_app_widgets_recipients WHERE site_id = ?i AND widget_id = ?i', $site_id, $widget_id);
			foreach ( Helper::findEmails($recipients) as $email ) $this->MySQL->query('INSERT INTO yapps_app_widgets_recipients SET ?u', ['site_id'=>$site_id, 'widget_id'=>$widget_id, 'recipient'=>$email]);
		}
		
		public function setSetsRecipients( $recipients, $site_id ) {
			
			$this->MySQL->query('DELETE FROM yapps_app_widgets_recipients WHERE site_id = ?i', $site_id);
			foreach ( Helper::findEmails($recipients) as $email ) $this->MySQL->query('INSERT INTO yapps_app_widgets_recipients SET ?u', ['site_id'=>$site_id, 'recipient'=>$email]);
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Goals //////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function setGoal( $arGoal, $site, $widget_id ) {
			
			$goal_id = $this->MySQL->getOne(
				'SELECT goal_id FROM yapps_goals WHERE site_id = ?i AND widget_id = ?i AND goal_name = ?s AND goal_js = ?s', 
				$site['id'], $widget_id, $arGoal['name'], $arGoal['goal']);
			
			if ( !$goal_id ) {
				
				$resGoal = Yandex::setGoal( $this->Yandex, $site['yandex_id'], $arGoal );
				$arInsGoal = [
					'site_id' => $site['id'],
					'app_id' => $this->AppInfo()->id,
					'widget_id' => $widget_id,
					'goal_id' => (string)$resGoal->goal->id,
					'goal_type' => ($resGoal->goal->type)?:'action',
					'goal_name' => $arGoal['name'],
					'goal_js' => $arGoal['goal']
				];
				$this->MySQL->query('INSERT INTO yapps_goals SET ?u', $arInsGoal);
			}
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Urls ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getUrls( $widget_id ) {
			
			return $this->MySQL->getCol('SELECT value FROM yapps_app_widgets_urls WHERE widget_id = ?i', $widget_id);
		}
		
		public function setUrls( $urls, $widget_id ) {
			
			$w = $this->MySQL->getRow('SELECT site_id, type_id FROM yapps_app_widgets WHERE id = ?i', $widget_id);
			
			$this->MySQL->query('DELETE FROM yapps_app_widgets_urls WHERE widget_id = ?i', $widget_id);
			foreach ( $urls as $url ) if ( $url ) $this->MySQL->query('INSERT INTO yapps_app_widgets_urls SET ?u', ['site_id'=>$w['site_id'], 'widget_id'=>$widget_id, 'type_id'=>$w['type_id'], 'value'=>Helper::parseWidgetURL($url)]);
			
			return 1;
		}
		
		public function getExceptUrls( $widget_id ) {
			
			return $this->MySQL->getCol('SELECT value FROM yapps_app_widgets_except_urls WHERE widget_id = ?i', (int)$widget_id);
		}
		
		public function setExceptUrls( $urls, $widget_id ) {
			
			$w = $this->MySQL->getRow('SELECT site_id, type_id FROM yapps_app_widgets WHERE id = ?i', $widget_id);
			
			$this->MySQL->query('DELETE FROM yapps_app_widgets_except_urls WHERE widget_id = ?i', $widget_id);
			foreach ( $urls as $url ) if ( $url ) $this->MySQL->query('INSERT INTO yapps_app_widgets_except_urls SET ?u', ['site_id'=>$w['site_id'], 'widget_id'=>$widget_id, 'type_id'=>$w['type_id'], 'value'=>Helper::parseWidgetURL($url)]);
			
			return 1;
        }
        
        public function selectWidgetIDByUrl( $url, $type_id, $sets, $competitor = false ) {

            $res = false;

            $where = ''; $w = [];
            $w[] = $this->MySQL->parse('site_id = ?i', $sets['site_id']);
            $w[] = $this->MySQL->parse('value = ?s', Helper::parseWidgetURL($url));
            $w[] = $this->MySQL->parse('type_id = ?i', $type_id);
            if ( $competitor ) $w[] =  $this->MySQL->parse('id NOT IN (?a)', $competitor);
            $where = 'WHERE '.implode(' AND ', $w);
            $res = $this->MySQL->getOne('SELECT widget_id FROM yapps_app_widgets_urls ?p ORDER BY id DESC', $where);

            if ( !$res ) {

                $w[1] = $this->MySQL->parse('(?s LIKE CONCAT(value, "%"))', Helper::parseWidgetURL($url));
                $where = 'WHERE '.implode(' AND ', $w);
                $res = $this->MySQL->getOne('SELECT widget_id FROM yapps_app_widgets_urls ?p ORDER BY id DESC', $where);
            }

            if ( 
                !$res ||
                !$this->isActiveWidget($res) ||
                ($this->isUseTimer($res) && !$this->isShowByTimer($res))
                ) {

                    $w[1] = $this->MySQL->parse('value = ?s', $this->Conf->Defaults->Url);
                    $where = 'WHERE '.implode(' AND ', $w);
                    $res = $this->MySQL->getOne('SELECT widget_id FROM yapps_app_widgets_urls ?p ORDER BY id DESC', $where);
            }

            if ( $competitor ) {


            }

            return $res;
        }
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // competitors ////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getCompetitors( $widget_id ) {
			
			return $this->MySQL->getCol('SELECT value FROM yapps_app_widgets_competitor WHERE widget_id = ?i', $widget_id);
		}
		
		public function setCompetitors( $comps, $widget_id ) {
			
			$site_id = $this->MySQL->getOne('SELECT site_id FROM yapps_app_widgets WHERE id = ?i', $widget_id);
			
			$this->MySQL->query('DELETE FROM yapps_app_widgets_competitor WHERE widget_id = ?i', $widget_id);
			foreach ( $comps as $comp ) if ( $comp ) $this->MySQL->query('INSERT INTO yapps_app_widgets_competitor SET ?u', ['site_id'=>$site_id, 'widget_id'=>$widget_id, 'value'=>$comp]);
			
			return 1;
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Positions //////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getPositions() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_positions');
		}
		
		public function getPosition( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_positions WHERE id = ?i', $id);
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // A/B Test ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getWidgetsABTest( $type_id, $user ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets WHERE type_id = ?i AND site_id IN (?a)', (int)$type_id, $this->YApps_GetUserSiteIDs($user));
		}
		
		public function getABTests ( $type_id, $user ) {
			
			$widgets_ids = $this->MySQL->getCol('SELECT id FROM yapps_app_widgets WHERE type_id = ?i AND site_id IN (?a)', (int)$type_id, $this->YApps_GetUserSiteIDs($user));
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_abtest WHERE type_id = ?i AND a_widget_id IN (?a)', (int)$type_id, $widgets_ids);
		}
		
		public function getABTest ( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_abtest WHERE id = ?i', (int)$id);
		}
		
		public function setABTest( $POST ) {
			
			$arIns = $POST;
			unset( $arIns['form'], $arIns['id'], $arIns['active'] );
			$arIns['active'] = ( $POST['active'] == 'on' ) ? 1 : 0;
			
			$arIns['type_id'] = (int)$POST['type_id'];
			$arIns['a_widget_id'] = (int)$POST['a_widget_id'];
			$arIns['b_widget_id'] = (int)$POST['b_widget_id'];
			
			$arIns['last_widget_id'] = $arIns['b_widget_id'];
			
			if ( (int)$POST['id'] != 0 ) {
				
				$this->MySQL->query('UPDATE yapps_app_widgets_abtest SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_widgets_abtest SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
		}
		
		public function delABTest( $id ) {
			
			$this->MySQL->query('DELETE FROM yapps_app_widgets_abtest WHERE id = ?i', (int)$id);			
			return Helper::getRes(0);
		}
		
		private function selectABWidgetId( $ab ) {
			
			$res = ( $ab['last_widget_id'] == $ab['a_widget_id'] ) ? $ab['b_widget_id'] : $ab['a_widget_id'];
			$this->MySQL->query('UPDATE yapps_app_widgets_abtest SET ?u WHERE id = ?i', ['last_widget_id'=>$res], $ab['id']);
			
			if ( $res == $ab['a_widget_id'] ) $this->MySQL->query('UPDATE yapps_app_widgets_abtest SET a_show = a_show + 1 WHERE id = ?i', $ab['id']);
			if ( $res == $ab['b_widget_id'] ) $this->MySQL->query('UPDATE yapps_app_widgets_abtest SET b_show = b_show + 1 WHERE id = ?i', $ab['id']);
			
			return $res;
		}
		
		private function ABTest( $id ) {
			
			return $this->MySQL->getOne('SELECT id FROM yapps_app_widgets_abtest WHERE a_widget_id = ?i OR b_widget_id = ?i', (int)$id, (int)$id);
		}
		
		private function setABRes( $id ) {
			
			$ab = $this->getABTest( $id );
			
			if ( $ab['last_widget_id'] == $ab['a_widget_id'] ) $this->MySQL->query('UPDATE yapps_app_widgets_abtest SET a_send = a_send + 1 WHERE id = ?i', $ab['id']);
			if ( $ab['last_widget_id'] == $ab['b_widget_id'] ) $this->MySQL->query('UPDATE yapps_app_widgets_abtest SET b_send = b_send + 1 WHERE id = ?i', $ab['id']);
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Quiz ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getQZSlideTypes() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_qz_slide_types');
		}
		
		public function getQZSlideType( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_qz_slide_types WHERE id = ?i', $id);
		}
		
		public function getQZSlidesIdByWidget( $id ) {
			
			return $this->MySQL->getCol('SELECT id FROM yapps_app_widgets_qz_slides WHERE widget_id = ?i', $id);
		}
		
		public function getQZSlidesByWidget( $id ) {
			
			foreach ( $this->getQZSlidesIdByWidget($id) as $i ) $res[] = $this->API_getQZSlide($i);
			
			return $res;
		}
		
		public function getQZSlide( $id ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_qz_slides WHERE id = ?i', $id);
			$res['type'] = $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_qz_slide_types WHERE id = ?i', $res['type_id']);
			
			switch ( $res['type_id'] ) {
				
				case 1:
					
					$res['models_id'] = $this->MySQL->getCol('SELECT model_id FROM yapps_app_widgets_qz_slide_models WHERE slide_id = ?i', $res['id']);
					$res['models'] = $this->MySQL->getInd('id', 'SELECT id, en_name FROM yapps_models WHERE id IN (?a)', $res['models_id']);
					break;
				
				case 2: case 3:
					
					$res['answers'] = $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_qz_slide_answers WHERE slide_id = ?i', $res['id']);
					break;
				
				case 4:
					
					$res['inputs'] = $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_qz_slide_inputs WHERE slide_id = ?i', $res['id']);
					break;
				
				default: break;
				
			}
			
			
			return $res;
		}
		
		public function API_getQZSlide( $id ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_qz_slides WHERE id = ?i', $id);
			$res['disclamer'] = $this->MySQL->getOne('SELECT disclamer FROM yapps_app_widgets_qz_slide_types WHERE id = ?i', $res['type_id']);
			
			switch ( $res['type_id'] ) {
				
				case 1:
					
					$res['items'] = $this->MySQL->getInd('id', 
						'SELECT yapps_app_widgets_qz_slide_models.id, yapps_app_widgets_qz_slide_models.model_id, yapps_models.en_name AS value, yapps_models.photo 
						FROM yapps_app_widgets_qz_slide_models JOIN yapps_models 
						ON yapps_models.id = yapps_app_widgets_qz_slide_models.model_id 
						WHERE yapps_app_widgets_qz_slide_models.slide_id = ?i', $res['id']);
					break;
				
				case 2: case 3:
					
					$res['items'] = $this->MySQL->getInd('id', 'SELECT * FROM yapps_app_widgets_qz_slide_answers WHERE slide_id = ?i', $res['id']);
					break;
				
				case 4:
					
					$res['items'] = $this->MySQL->getInd('id', 'SELECT * FROM yapps_app_widgets_qz_slide_inputs WHERE slide_id = ?i', $res['id']);
					break;
				
				default: break;
				
			}
			
			
			return $res;
		}
		
		public function setQZSlide( $POST ) {
			
			unset($POST['form']);
			
			$arIns = [
				'widget_id' => $POST['widget_id'],
				'type_id' => $POST['type_id'],
				'ru_name' => $POST['ru_name'],
				'active' => (($POST['active']=='on')?1:0),
				'required' => (($POST['required']=='on')?1:0),
				'disclamer' => $this->getQZSlideType($POST['type_id'])['disclamer'],
				'sort' => $POST['sort']
			];
			
			if ( (int)$POST['id'] ) {
				
				$last_id = (int)$POST['id'];
				$this->MySQL->query('UPDATE yapps_app_widgets_qz_slides SET ?u WHERE id = ?i', $arIns, $POST['id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_widgets_qz_slides SET ?u', $arIns);
				$last_id = $this->MySQL->insertId();
			}
			
			
			switch ( (int)$POST['type_id'] ) {
				
				case 1:
					
					$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slide_models WHERE slide_id = ?i', $last_id);
					$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slide_answers WHERE slide_id = ?i', $last_id);
					$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slide_inputs WHERE slide_id = ?i', $last_id);
					foreach ( $POST['models_id'] as $i ) $this->MySQL->query('INSERT INTO yapps_app_widgets_qz_slide_models SET ?u', ['slide_id'=>$last_id, 'model_id'=>$i]);
					
					break;
				
				case 2:
					
					$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slide_models WHERE slide_id = ?i', $last_id);
					$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slide_answers WHERE slide_id = ?i', $last_id);
					$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slide_inputs WHERE slide_id = ?i', $last_id);
					foreach ( $POST['answers_value'] as $i ) if ( $i ) $this->MySQL->query('INSERT INTO yapps_app_widgets_qz_slide_answers SET ?u', ['slide_id'=>$last_id, 'value'=>$i]);
					
					break;
					
				case 3:
					
					$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slide_models WHERE slide_id = ?i', $last_id);
					$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slide_answers WHERE slide_id = ?i', $last_id);
					$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slide_inputs WHERE slide_id = ?i', $last_id);
					foreach ( $POST['answers_value'] as $i ) if ( $i ) $this->MySQL->query('INSERT INTO yapps_app_widgets_qz_slide_answers SET ?u', ['slide_id'=>$last_id, 'value'=>$i]);
					
					break;
				
				case 4:
					
					$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slide_models WHERE slide_id = ?i', $last_id);
					$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slide_answers WHERE slide_id = ?i', $last_id);
					$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slide_inputs WHERE slide_id = ?i', $last_id);
					foreach ( $POST['inputs_value'] as $k => $i ) {
						
						if ( $i ) {
							
							$ins = [
								'slide_id' => $last_id,
								'value' => $i,
								'description' => $POST['inputs_description'][$k],
								'required' => ( in_array((string)$k, $POST['inputs_required']) ) ? 1 : 0
							];
							
							$this->MySQL->query('INSERT INTO yapps_app_widgets_qz_slide_inputs SET ?u', $ins);
						}
					}
					
					break;
				
				default: break;
				
			}
			
			
			return Helper::getRes(0);
		}

		public function delQZSlide( $id ) {

			$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slide_models WHERE slide_id = ?i', $id);
			$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slide_answers WHERE slide_id = ?i', $id);
			$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slide_inputs WHERE slide_id = ?i', $id);
			$res = $this->MySQL->getOne('SELECT widget_id FROM yapps_app_widgets_qz_slides WHERE id = ?i', $id);
			$this->MySQL->query('DELETE FROM yapps_app_widgets_qz_slides WHERE id = ?i', $id);

			return $res;
		}
		
		public function getQZSlideItem( $item_id, $slide_id ) {
			
			switch ( $this->MySQL->getOne('SELECT type_id FROM yapps_app_widgets_qz_slides WHERE id - ?i', $slide_id) ) {
				
				case 1:
					
					return $this->MySQL->getRow( 
						'SELECT yapps_app_widgets_qz_slide_models.id, yapps_app_widgets_qz_slide_models.model_id, yapps_models.en_name AS value
						FROM yapps_app_widgets_qz_slide_models JOIN yapps_models 
						ON yapps_models.id = yapps_app_widgets_qz_slide_models.model_id 
						WHERE yapps_app_widgets_qz_slide_models.id = ?i', $item_id);
					break;
					
				case 2: case 3:
					
					return $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_qz_slide_answers WHERE id = ?i', $id);
					break;
					
				case 4:
					
					return $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_qz_slide_inputs WHERE id = ?i', $id);
					break;
					
				default: break;	
			}
			
			return $res;
		}
		
		public function getQZStat( $id ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_qz_stat WHERE stat_id = ?i', $id);
        }



        ///////////////////////////////////////////////////////////////////////////////////////////
        // CI /////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        public function getCITypes() {

            return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_ci_types');
        }

        public function getCIType( $id ) {

            return $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_ci_types WHERE id = ?i', $id);
        }

        public function getCITypeByURL( $sets, $url ) {

            // Костыль, переделать на базу
            $countPath = count( explode( '/', parse_url($url)['path'] ) )-2;

            $res = 1;
            if ( $countPath > $sets['ci_level_list'] && $countPath <= $sets['ci_level_model'] ) $res = 2;
            if ( $countPath > $sets['ci_level_model'] ) $res = 3;

            return $this->getCIType( $res );
        }

		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Widgets ////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getAllWidgets() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets');
		}
		
		public function getWidgetsByType( $type ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets WHERE type_id = ?i AND site_id IN (?a)', (int)$type, $GLOBALS['USER_SITES']['sites_ids']);
		}
		
		public function getAllWidgetsByType( $type ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets WHERE type_id = ?i', (int)$type);
		}
		
		public function getWidgetById( $id ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_widgets WHERE id = ?i', (int)$id);
			$res['recipients'] = $this->getWidgetRecipients( (int)$id );
            $res['lg_url'] = $this->getUrls( (int)$id );
            $res['qz_url'] = $this->getUrls( (int)$id );
            $res['ci_url'] = $this->getUrls( (int)$id );
			$res['lg_except_url'] = $this->getExceptUrls( (int)$id );
			$res['lg_competitor'] = $this->getCompetitors( (int)$id );
			$res['url'] = $this->getUrls( (int)$id );
			$res['except_url'] = $this->getExceptUrls( (int)$id );
			
			if ( $res['type_id'] == 7 ) $res['slides'] = $this->getQZSlidesByWidget($id);
			if ( $res['type_id'] == 10 ) $res ['items'] = $this->getEHItems($id);
			
			return $res;
		}
		
		
		public function copyWidget( $id ) {
			
            $arIns = $this->getWidgetById( $id );
            $name = $arIns['ru_name'];
			unset( $arIns['id'], $arIns['public_key'], $arIns['recipients'], $arIns['lg_url'], $arIns['lg_competitor'] );
			$arIns['active'] = 0;
			$arIns['timestamp'] = $arIns['last_update'] = time();
			$arIns['ru_name'] .= ' (Копия ID: '.$id.')';
			$arIns['public_key'] = md5( $this->Conf->Secret.'_'.$arIns['ru_name'].'_'.time() );
			
			switch ( $arIns['type_id'] ) {
				
				case 1:
					
					$fields = ['lg_use_competitor','lg_head','lg_image','lg_timer_flag','lg_timer','lg_timer_description','lg_time_start','lg_title','lg_text','lg_link','lg_link_text'];
					foreach ( $fields as $f ) unset( $arIns[$f] );
					
					$this->MySQL->query('INSERT INTO yapps_app_widgets SET ?u', $arIns);
					$n_id = $this->MySQL->insertId();
					
					break;
					
				case 2:
					
					$fields = ['cb_title_prologue','cb_title_span_proroque','cb_text','cb_description_now','cb_description_later', 'lg_except_url', 'url', 'except_url'];
					foreach ( $fields as $f ) unset( $arIns[$f] );
					
					if ( $arIns['lg_image'] ) {
						
						mkdir( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'] );
						$file = explode('/', parse_url($arIns['lg_image'])['path'])[4];
						copy( __DIR__.'/../..'.$this->Conf->FileDir.'/'.explode('/', parse_url($arIns['lg_image'])['path'])[3].'/'.$file, __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.$file );
						
						$arIns['lg_image'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.$file;
					}
					
					$this->MySQL->query('INSERT INTO yapps_app_widgets SET ?u', $arIns);
					$n_id = $this->MySQL->insertId();
					
					if ( $rec = $this->getCompetitors( $id ) ) $this->setCompetitors($rec, $n_id);
					if ( $rec = $this->getUrls( $id ) ) $this->setUrls($rec, $n_id);
					if ( $rec = $this->getExceptUrls( $id ) ) $this->setExceptUrls($rec, $n_id);
					
					break;
					
				case 3:
					
					$fields = ['cb_title_prologue','cb_title_span_proroque','cb_text','cb_description_now','cb_description_later','lg_use_competitor','lg_head','lg_image','lg_timer_flag','lg_timer','lg_timer_description','lg_time_start','lg_title','lg_text','lg_link','lg_link_text'];
					foreach ( $fields as $f ) unset( $arIns[$f] );
					
					$this->MySQL->query('INSERT INTO yapps_app_widgets SET ?u', $arIns);
					$n_id = $this->MySQL->insertId();
					
					break;
			}
			
            if ( $rec = $this->getWidgetRecipients( $id ) ) $this->setWidgetRecipients(implode(', ', $rec), $arIns['site_id'], $n_id);
            
            $res = Helper::getRes(0);
            $res->description = 'Виджет <strong>ID: '.$id.'. '.$name.'</strong> успешно скопирован. <a href="/widgets/lg/edit/'.$n_id.'/">Редактировать</a>';

            return $res;
        }
        
		
		public function setWidget( $POST, $FILES = false ) {

			$arIns = $POST;
			unset( $arIns['form'], $arIns['active'], $arIns['recipients'], $arIns['id'], $arIns['public_key'], $arIns['lg_url'], $arIns['lg_except_url'], $arIns['lg_competitor'], $arIns['lg_timer_flag'], $arIns['_wysihtml5_mode'], $arIns['qz_url'], $arIns['qz_except_url'], $arIns['ms_whatsapp'], $arIns['ms_telegram'], $arIns['ms_viber'], $arIns['ms_skype'], $arIns['ci_url'] );
			$arIns['active'] = ( $POST['active'] == 'on' ) ? 1 : 0;
			
			$arIns['type_id'] = (int)$POST['type_id'];
			$arIns['site_id'] = (int)$POST['site_id'];
			$arIns['public_key'] = ( $POST['public_key'] ) ?: md5( $this->Conf->Secret.'_'.$POST['name'].'_'.time() );
			
			$arIns['lg_use_competitor'] = ( $POST['lg_use_competitor'] == 'on' ) ? 1 : 0;
			
			$arIns['lg_timer_flag'] = ( $POST['lg_timer_flag'] == 'on' ) ? 1 : 0;
			$arIns['lg_hide_buttons'] = ( $POST['lg_hide_buttons'] == 'on' ) ? 1 : 0;
            $arIns['lg_hp_use_wname'] = ( $POST['lg_hp_use_wname'] == 'on' ) ? 1 : 0;
			$arIns['lg_timer'] = strtotime($POST['lg_timer']);
			$arIns['lg_time_start'] = strtotime($POST['lg_time_start']);
			
			$arIns['qz_hp_use'] = ( $POST['qz_hp_use'] == 'on' ) ? 1 : 0;
			
			$arIns['last_update'] = time();
			if ( !$POST['id'] ) $arIns['timestamp'] = $arIns['last_update'];
			
			if ( $FILES && $FILES['lg_image']['error'] == 0 && $arIns['public_key'] ) {

				Helper::removeDirectory( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'] );
				mkdir( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'] );
				
				$arIns['lg_image'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.$FILES['lg_image']['name'];
				
				$file = __DIR__.'/../..'.$this->Conf->FileDir.'/'.$arIns['public_key'].'/'.$FILES['lg_image']['name'];
				move_uploaded_file( $FILES['lg_image']['tmp_name'], $file );
			}
			
			if ( $POST['id'] ) {
				
				$this->MySQL->query('UPDATE yapps_app_widgets SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				$lastId = (int)$POST['id'];
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_widgets SET ?u', $arIns);
				$lastId = $this->MySQL->insertId();
			}
			
			$site = $this->YApps_GetSiteByID( $arIns['site_id'] );

			if ( $POST['recipients'] ) $this->setWidgetRecipients( $POST['recipients'], $site['id'], $lastId );
			
			if ( $POST['lg_url'] ) $this->setUrls( $POST['lg_url'], $lastId );
			if ( $POST['lg_except_url'] ) $this->setExceptUrls($POST['lg_except_url'], $lastId);
			if ( $POST['lg_competitor'] ) $this->setCompetitors( $POST['lg_competitor'], $lastId );
			
			if ( $POST['qz_url'] ) $this->setUrls( $POST['qz_url'], $lastId );
            if ( $POST['qz_except_url'] ) $this->setExceptUrls($POST['qz_except_url'], $lastId);
            
            if ( (int)$arIns['type_id'] == 8 ) {

                $arMess = [];
                if ( $POST['ms_whatsapp'] ) $arMess[] = [1, Helper::formatPhoneIn($POST['ms_whatsapp'])];
                if ( $POST['ms_telegram'] ) $arMess[] = [2, $POST['ms_telegram']];
                if ( $POST['ms_viber'] ) $arMess[] = [5, Helper::formatPhoneIn($POST['ms_viber'])];
                if ( $POST['ms_skype'] ) $arMess[] = [4, $POST['ms_skype']];

                $this->setWidgetMessengers( $arMess, $lastId );
            }

			if ( (int)$arIns['type_id'] == 10 && !$this->getEHItems($lastId) ) $this->setDefaultEHItems( $lastId );

			if ( $site['yandex_id'] ) {
				
				switch ( $arIns['type_id'] ) {
					
					// CB, LG, QZ, CI, EH
					case 1:
					case 2:
					case 7:
                    case 9:
					case 10:
						
						$arGoals = [
							[
								'name' => $this->AppInfo()->ru_name.'. ID: '.$lastId.'. '.$this->getTypeById( $arIns['type_id'] )['ru_name'].'. Немедленно.' ,
								'goal' => 'YApps_Goals-Widgets_'.$this->getTypeById( $arIns['type_id'] )['keyword'].'-Send_Now'
							],
							[
								'name' => $this->AppInfo()->ru_name.'. ID: '.$lastId.'. '.$this->getTypeById( $arIns['type_id'] )['ru_name'].'. Отложенно.' ,
								'goal' => 'YApps_Goals-Widgets_'.$this->getTypeById( $arIns['type_id'] )['keyword'].'-Send_Later'
							]
						];
						foreach ( $arGoals as $arGoal ) $this->setGoal( $arGoal, $site, $lastId );
						
						break;
					
					// NV	
					case 3:
						
						$arGoals = [
							[
								'name' => $this->AppInfo()->ru_name.'. ID: '.$lastId.'. '.$this->getTypeById( $arIns['type_id'] )['ru_name'].'. Маршрут.',
								'goal' => $this->Conf->Defaults->NVGoalRoute
							],
							[
								'name' => $this->AppInfo()->ru_name.'. ID: '.$lastId.'. '.$this->getTypeById( $arIns['type_id'] )['ru_name'].'. Звонок.',
								'goal' => $this->Conf->Defaults->NVGoalCall
							],
						];
						foreach ( $arGoals as $arGoal ) $this->setGoal( $arGoal, $site, $lastId );
						
						break;
                        
                    // MS
					case 8:
						
                        foreach ( $this->getMessengersByWidget($lastId) as $mess ) 
                            $this->setGoal( [
                                'name' => $this->AppInfo()->ru_name.'. ID: '.$lastId.'. '.$this->getTypeById( $arIns['type_id'] )['ru_name'].'. '.$mess['ru_name'].'.',
								'goal' => $mess['goal']
                            ], $site, $lastId );
						
						break;
					
					default: break;
				}
			}
			
			$return = Helper::getRes(0);
			$return->redirect = '/widgets/'.mb_strtolower($this->getTypeById( $arIns['type_id'] )['keyword']).'/edit/'.$lastId.'/';

			return $return;
		}
		
		public function delWidget( $id ) {
			
			$widget = $this->getWidgetById( $id );
			
			$this->MySQL->query('DELETE FROM yapps_app_widgets WHERE id = ?i', (int)$id);
			$this->MySQL->query('DELETE FROM yapps_app_widgets_recipients WHERE widget_id = ?i', (int)$id);
			$this->MySQL->query('DELETE FROM yapps_goals WHERE widget_id = ?i', (int)$id);
			$this->MySQL->query('DELETE FROM yapps_app_widgets_abtest WHERE a_widget_id = ?i OR b_widget_id = ?i', (int)$id, (int)$id);
			$this->MySQL->query('DELETE FROM yapps_app_widgets_competitor WHERE widget_id = ?i', (int)$id);
			$this->MySQL->query('DELETE FROM yapps_app_widgets_urls WHERE widget_id = ?i', (int)$id);
			$this->delEHItems($id);
			
			Helper::removeDirectory( __DIR__.'/../..'.$this->Conf->FileDir.'/'.$widget['public_key'] );
			
			return Helper::getRes(0);
		}
		
		private function isUseTimer( $id ) {
			
			return ( $this->MySQL->getOne('SELECT lg_timer_flag FROM yapps_app_widgets WHERE id = ?i', (int)$id) ) ? true: false;
		}
		
		private function isShowByTimer( $id ) {
			
			return ( $this->MySQL->getOne('SELECT lg_timer FROM yapps_app_widgets WHERE id = ?i', (int)$id) > time() );
		}

		private function isShowByTimeStart( $id ) {
			
			return ( $this->MySQL->getOne('SELECT lg_time_start FROM yapps_app_widgets WHERE id = ?i', (int)$id) <= time() );
		}
		
		public function isActiveWidget( $id ) {
			
			return ( $this->MySQL->getOne('SELECT active FROM yapps_app_widgets WHERE id = ?i', (int)$id) ) ? true : false;
		}
		
		public function selectCB( $sets ) {
			
			if ( $this->isShutdownBySite($sets['site_id']) ) return false;
			
			$id = false;
			
			/*
			if ( time() >= strtotime(date('Y-m-d').' '.$sets['cb_time_start']) && time() <= strtotime(date('Y-m-d').' '.$sets['cb_time_end']) ) {
			
				$id = $this->MySQL->getOne('SELECT id FROM yapps_app_widgets WHERE site_id = ?i AND active = ?i AND type_id = ?i ORDER BY id DESC', $sets['site_id'], 1, 1);
				if ( $ab = $this->ABTest( $id ) ) $id = $this->selectABWidgetId( $this->getABTest($ab) );
			}
			*/
			$id = $this->MySQL->getOne('SELECT id FROM yapps_app_widgets WHERE site_id = ?i AND active = ?i AND type_id = ?i ORDER BY id DESC', $sets['site_id'], 1, 1);
			if ( $ab = $this->ABTest( $id ) ) $id = $this->selectABWidgetId( $this->getABTest($ab) );
			
			return $id;
        }
        
        public function selectMS( $sets ) {
			
			if ( $this->isShutdownBySite($sets['site_id']) ) return false;
			
			$id = false;
			$id = $this->MySQL->getOne('SELECT id FROM yapps_app_widgets WHERE site_id = ?i AND active = ?i AND type_id = ?i ORDER BY id DESC', $sets['site_id'], 1, 8);
			if ( $ab = $this->ABTest( $id ) ) $id = $this->selectABWidgetId( $this->getABTest($ab) );
			
			return $id;
		}
		
		public function isTimeCB( $sets ) {
			
			$time = time();
			return ( $time >= strtotime(date('Y-m-d').' '.$sets['cb_time_start']) && $time <= strtotime(date('Y-m-d').' '.$sets['cb_time_end']) ) ? true : false;
		}
		
		public function selectNV( $sets ) {
			
			$id = $this->MySQL->getOne('SELECT id FROM yapps_app_widgets WHERE site_id = ?i AND active = ?i AND type_id = ?i ORDER BY id DESC', $sets['site_id'], 1, 3);
			if ( $ab = $this->ABTest( $id ) ) $id = $this->selectABWidgetId( $this->getABTest($ab) );
			
			return $id;
		}

		public function selectEH( $sets ) {
			
			$id = $this->MySQL->getOne('SELECT id FROM yapps_app_widgets WHERE site_id = ?i AND active = ?i AND type_id = ?i ORDER BY id DESC', $sets['site_id'], 1, 10);
			return $id;
		}
		
		public function selectLG( $sets, $url ) {
			
			if ( $this->isShutdownBySite($sets['site_id']) ) return false;
			
			$competitor = ( Helper::getUtm($url)['utm_competitor'] ) ?: false; // Конкурентный запрос
			$ids_use_competitor = ( $this->MySQL->getCol('SELECT id FROM yapps_app_widgets WHERE site_id = ?i AND lg_use_competitor = ?i AND active = ?i', $sets['site_id'], 1, 1)) ?: false;
			
			$where = ''; $w = [];
			
			$w[] = $this->MySQL->parse('site_id = ?i', $sets['site_id']);
			$w[] = $this->MySQL->parse('value = ?s', Helper::parseWidgetURL($url));
			$w[] = $this->MySQL->parse('type_id = ?i', 2);
			if ( $ids_use_competitor ) $w[] =  $this->MySQL->parse('id NOT IN (?a)', $ids_use_competitor);
			$where = 'WHERE '.implode(' AND ', $w);
			$ids = $this->MySQL->getCol('SELECT widget_id FROM yapps_app_widgets_urls ?p', $where);
			
			$where = ''; $w = [];
			$w[] = $this->MySQL->parse('site_id = ?i', $sets['site_id']);
			$w[] = $this->MySQL->parse('value = ?s', (($ids)?Helper::parseWidgetURL($url):$this->Conf->Defaults->LGUrl));
			$w[] = $this->MySQL->parse('type_id = ?i', 2);
			if ( $ids_use_competitor ) $w[] =  $this->MySQL->parse('widget_id NOT IN (?a)', $ids_use_competitor);
			$where = 'WHERE '.implode(' AND ', $w);
			$res = $id = $this->MySQL->getOne('SELECT widget_id FROM yapps_app_widgets_urls ?p ORDER BY id DESC', $where);
			
			if ( ($this->isUseTimer($res) && !$this->isShowByTimer($res)) && !$this->isActiveWidget( $id ) ) {
				
				$w[1] = $this->MySQL->parse('value = ?s', $this->Conf->Defaults->LGUrl);
				$where = 'WHERE '.implode(' AND ', $w);
                $res = $id = (int)$this->MySQL->getOne('SELECT widget_id FROM yapps_app_widgets_urls ?p ORDER BY id DESC', $where);
                
			}
			
			if ( $competitor ) {
				
				$where = ''; $w = [];
				$w[] = $this->MySQL->parse('site_id = ?i', $sets['site_id']);
				$w[] = $this->MySQL->parse('value = ?s', (($ids)?Helper::parseWidgetURL($url):$this->Conf->Defaults->LGUrl));
				$w[] = $this->MySQL->parse('type_id = ?i', 2);
				if ( $ids_use_competitor ) $w[] =  $this->MySQL->parse('widget_id IN (?a)', $ids_use_competitor);
				$where = 'WHERE '.implode(' AND ', $w);
				$res = ( $this->MySQL->getOne('SELECT widget_id FROM yapps_app_widgets_urls ?p ORDER BY id DESC', $where) ) ?: $id;
			}
			
			if ( $this->isUseTimer($res) ) if ( !$this->isShowByTimer($res) ) return false;
			if ( !$this->isShowByTimeStart($res) ) return false;
			if ( $ab = $this->ABTest( $res ) ) $res = $this->selectABWidgetId( $this->getABTest($ab) );
			
			if ( in_array(Helper::parseWidgetURL($url), $this->getExceptUrls($res)) ) return false;
			
			return $res;
        }
        
        public function selectCI( $sets, $url ) {

            if ( $this->isShutdownBySite($sets['site_id']) || !$this->YApps_GetShowroomByUrl($url) ) return false;

            $res = $this->MySQL->getOne(
                'SELECT id FROM yapps_app_widgets WHERE site_id = ?i
                AND type_id = ?i
                AND active = ?i',
                (int)$sets['site_id'], 9, 1
            );

			if ( $ab = $this->ABTest( $res ) ) $res = $this->selectABWidgetId( $this->getABTest($ab) );
			
			return $res;
		}
		
		public function selectQZ( $sets, $url ) {
			
            if ( $this->isShutdownBySite($sets['site_id']) ) return false;
            
            $res = $this->selectWidgetIDByUrl($url, 7, $sets);
			
			if ( $ab = $this->ABTest( $res ) ) $res = $this->selectABWidgetId( $this->getABTest($ab) );
			if ( in_array(Helper::parseWidgetURL($url), $this->getExceptUrls($res)) ) return false;
			
			return $res;
		}
		
		private function selectCH( $sets ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_chat_settings WHERE site_id = ?i', $sets['site_id']);
			return ( $res && $res['active'] == 1 ) ? true : false ;
		}
		
		private function selectFB( $sets ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_chat_settings WHERE site_id = ?i', $sets['site_id']);
			return ( $res && $res['active'] == 1 ) ? true : false ;
		}
		
		private function selectHT( $sets, $url ) {
			
			if ( $this->MySQL->getOne('SELECT active FROM yapps_app_hot_settings WHERE site_id = ?i', $sets['site_id']) && $sets['hp_use_hot'] ) { 
			
				$brands = $tmp = $this->YApps_GetBrandsByIds( $this->searchBrandsIDsByURL($url) );
				
				$res['count'] = (int)$this->MySQL->getOne('SELECT COUNT(id) FROM yapps_app_hot_items WHERE site_id = ?i', $sets['site_id']);
				$res['link'] = $sets['link_hot'];
				$res['text'] = '<strong>'.$res['count'].'</strong> '.Helper::getWorld($res['count'], 'hot');
				
				if ( count($brands) == 1 ) {
					
					$brand = array_shift( $tmp );
					$res['text'] .= ' на автомобили '.$brand['en_name'];
				}
				
			} else {
				
				$res = ['count'=>0];
			}
			
			return $res;
		}
		
		public function selectAV( $sets, $url ) {
			
            if ( !$sets['hp_use_avail'] ) return ['count'=>0];
            if ( $sets['hp_use_avail'] && !$sets['hp_use_avail_count'] ) return ['count'=>1, 'link'=>$sets['link_avail']];
			
			// Brands
			$brands = $tmp = $this->YApps_GetBrandsByIds( $this->searchBrandsIDsByURL($url) );
			$res['count'] = $this->MySQL->getOne('SELECT SUM(in_stock) FROM yapps_brands WHERE id IN (?a)', array_keys($brands));
			
			$res['link'] = ( $this->YApps_GetShowroomBySite($sets['site_id'])['url'] ) ?: $sets['link_avail'];
			
			if ( count($brands) > 1 ) {
				
				$res['link'] = 'https://yug-avto.ru/new-cars?InStock&';
				foreach ( $brands as $brand ) $res['link'] .= 'Brands%5B%5D='.$brand['url_key'].'&';
				$res['link'] = substr($res['link'], 0, -1);
				
				$res['text'] = 'В наличии <strong>'.$res['count'].'</strong> '.Helper::getWorld($res['count'], 'a');
				
			} else {
				
				$brand = array_shift( $tmp );
				
				$dcs = $this->YApps_GetDCIDsBySite( $sets['site_id'] );
				$res['count'] = $this->MySQL->getOne('SELECT SUM(in_stock) FROM yapps_models_dcs WHERE dc_id IN (?a)', $dcs);
				
				$res['text'] = 'В наличии <strong>'.$res['count'].'</strong> '.Helper::getWorld($res['count'], 'a').' '.$brand['en_name'];
			}
			
			return $res;
		}
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Settings ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getSettings( $sites = false ) {
			
			if ( $sites ) foreach ( $sites as $k => $s ) $sites[$k]['settings'] = $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_settings WHERE site_id = ?i', $s['id']);
			return $sites;
		}
		
		public function getSettingsById( $site_id ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_settings WHERE site_id = ?i', (int)$site_id);
			$res['recipients'] = $this->getSetsRecipients( $res['site_id'] );
			$res['shutdown'] = $this->getSetsShutdowns( $res['site_id'] );
			
			return $res;
		}
		
		public function getSettingsByHost( $host ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_settings WHERE site_id = ?i', $this->YApps_GetSiteByHost($host)->id);
			if ( $res ) $res['recipients'] = $this->getRecipients( $res['site_id'] );
			if ( $res ) $res['position'] = $this->getPosition( $res['hp_lg_plate_position_id'] );
			
			return $res;
		}
		
		public function getSettingsByShowroom( $url ) {
			
			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_settings WHERE site_id = ?i', $this->YApps_GetShowroomByUrl( $url )['site_id']);
			if ( $res ) $res['recipients'] = $this->getRecipients( $res['site_id'] );
			if ( $res ) $res['position'] = $this->getPosition( $res['hp_lg_plate_position_id'] );
			
			return $res;
		}
		
		public function setSettings( $POST) {
			
			$arIns = $POST;
			unset( $arIns['form'], $arIns['active'], $arIns['use_new'], $arIns['recipients'], $arIns['navi_ids'], $arIns['shutdown'] );
            $arIns['term_checked'] = ( $POST['term_checked'] == 'on' ) ? 1 : 0;
            $arIns['active'] = ( $POST['active'] == 'on' ) ? 1 : 0;
            $arIns['use_new'] = ( $POST['use_new'] == 'on' ) ? 1 : 0;
            $arIns['hp_start_open'] = ( $POST['hp_start_open'] == 'on' ) ? 1 : 0;
            $arIns['hp_bind_widgets'] = ( $POST['hp_bind_widgets'] == 'on' ) ? 1 : 0;
			$arIns['hp_use_hot'] = ( $POST['hp_use_hot'] == 'on' ) ? 1 : 0;
			$arIns['hp_use_avail'] = ( $POST['hp_use_avail'] == 'on' ) ? 1 : 0;
			$arIns['hp_use_avail_count'] = ( $POST['hp_use_avail_count'] == 'on' ) ? 1 : 0;
			$arIns['hp_lg_button_use_wname'] = ( $POST['hp_lg_button_use_wname'] == 'on' ) ? 1 : 0;
			$arIns['hp_lg_plate_use_wname'] = ( $POST['hp_lg_plate_use_wname'] == 'on' ) ? 1 : 0;
			$arIns['hp_lg_plate_draggable'] = ( $POST['hp_lg_plate_draggable'] == 'on' ) ? 1 : 0;
            $arIns['hp_eh_use_startstop'] = ( $POST['hp_eh_use_startstop'] == 'on' ) ? 1 : 0;
			
			if ( $this->MySQL->getOne('SELECT id FROM yapps_app_widgets_settings WHERE site_id = ?i', (int)$POST['site_id']) ) {
				
				$this->MySQL->query('UPDATE yapps_app_widgets_settings SET ?u WHERE site_id = ?i', $arIns, (int)$POST['site_id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_widgets_settings SET ?u', $arIns);
			}
			
			if ( $POST['recipients'] ) $this->setSetsRecipients( $POST['recipients'], $arIns['site_id'] );
			if ( $POST['navi_ids'] ) $this->setSetsNavigators( $POST['navi_ids'], $arIns['site_id'] );
			$this->setSetsShutdowns( $POST['shutdown'], $arIns['site_id'] );
			
			$site = $this->YApps_GetSiteByID( $arIns['site_id'] );
			$arGoal = [
				'name' => $this->AppInfo()->ru_name.'. Звонок с помощника.',
				'goal' => 'YApps_Goals-Helper-Call'
			];
			
			$this->setGoal( $arGoal, $site, 0 );
			
			return Helper::getRes(0);
		}
		
		public function delSettings( $id ) {
			
			$res = ($this->MySQL->query('DELETE FROM yapps_app_widgets_settings WHERE site_id = ?i', (int)$id)) ? 0 : 41;
			$res = ($this->MySQL->query('DELETE FROM yapps_app_widgets_recipients WHERE site_id = ?i', (int)$id)) ? 0 : 41;
			return Helper::getRes($res);
		}
		
		public function activateSets($id, $action = true) {
			
			$arIns['active'] = ( $action ) ? 1 : 0;
			return ( $id == 'all' ) ? $this->MySQL->query('UPDATE yapps_app_widgets_settings SET ?u', $arIns) : $this->MySQL->query('UPDATE yapps_app_widgets_settings SET ?u WHERE site_id = ?i', $arIns, (int)$id);
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Shutdowns //////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function setSetsShutdowns( $shuts, $site_id ) {
			
			$this->MySQL->query('DELETE FROM yapps_app_widgets_shutdowns WHERE site_id = ?i', $site_id);
			$arIns['site_id'] = $site_id;
			foreach ( $shuts as $sh ) {
				
				$shArr = explode(' - ', $sh);	
				$arIns['start'] = strtotime($shArr[0]);
				$arIns['end'] = strtotime($shArr[1]);
				
				$this->MySQL->query('INSERT INTO yapps_app_widgets_shutdowns SET ?u', $arIns);
			}
		}
		
		public function getShutdowns( $id ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_shutdowns WHERE site_id = ?i', (int)$id);
		}
		
		public function getSetsShutdowns( $id ) {
			
			$shuts = $this->getShutdowns($id);
			foreach( $shuts as $sh ) $res[] = date('Y-m-d H:i', $sh['start']).' - '.date('Y-m-d H:i', $sh['end']);
			
			return $res;
		}
		
		public function isShutdownBySite( $site_id ) {
			
			return $this->MySQL->getOne('SELECT id FROM yapps_app_widgets_shutdowns WHERE site_id = ?i AND start <= ?i AND end >= ?i', (int)$site_id, time(), time());	
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Navi ///////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getNavigators() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_navigators');
		}
		
		public function getNavigator( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_navigators WHERE id = ?i', (int)$id);
		}
		
		public function getNavigatorIDsBySite( $id ) {
			
			return $this->MySQL->getCol('SELECT navigator_id FROM yapps_app_widgets_sets_navis WHERE site_id = ?i', (int)$id);
		}
		
		public function getNavigatorsBySite( $id ) {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_navigators WHERE id IN (?a) ORDER BY sort ASC', $this->getNavigatorIDsBySite((int)$id));
		}
		
		public function setNavigator( $POST, $FILES ) {
			
			$arIns = $POST;
			unset( $arIns['form'] );
			
			if ( $FILES && $FILES['image']['error'] == 0 ) {
				
				$arIns['image'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$this->Conf->FileDir.'/Navigators/'.$FILES['image']['name'];
				$file = __DIR__.'/../..'.$this->Conf->FileDir.'/Navigators/'.$FILES['image']['name'];
				
				move_uploaded_file( $FILES['image']['tmp_name'], $file );
			}
			
			if ( $cur_navi = $this->getNavigator($POST['id']) ) {
				
				if ( $arIns['image'] ) unlink( $_SERVER['DOCUMENT_ROOT'].$cur_navi['image'] );
				$this->MySQL->query('UPDATE yapps_app_widgets_navigators SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_widgets_navigators SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
		}
		
		public function delNavigator( $id ) {
			
			$cur_navi = $this->getNavigator($POST['id']);
			unlink( $_SERVER['DOCUMENT_ROOT'].$cur_navi['image'] );
			
			return ( $this->MySQL->query('DELETE FROM yapps_app_widgets_navigators WHERE id = ?i', (int)$id) ) ? Helper::getRes(0) : false;
		}
		
		public function setSetsNavigators( $navis, $site_id ) {
			
			$this->MySQL->query('DELETE FROM yapps_app_widgets_sets_navis WHERE site_id = ?i', $site_id);
			foreach ( $navis as $nv ) $this->MySQL->query('INSERT INTO yapps_app_widgets_sets_navis SET ?u', ['site_id'=>$site_id, 'navigator_id'=>(int)$nv]);
		}
        
        
        ///////////////////////////////////////////////////////////////////////////////////////////
        // Messengers /////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getMessengers() {
			
			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_messengers');
		}
		
		public function getMessenger( $id ) {
			
			return $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_messengers WHERE id = ?i', (int)$id);
		}
		
		public function getMessengersIDsByWidget( $id ) {
			
			return $this->MySQL->getCol('SELECT messenger_id FROM yapps_app_widgets_ms_messengers WHERE widget_id = ?i', (int)$id);
		}
		
		public function getMessengersByWidget( $id ) {
			
            $res = $this->MySQL->getInd('id', 'SELECT * FROM yapps_app_widgets_messengers WHERE id IN (?a) ORDER BY sort ASC', $this->getMessengersIDsByWidget((int)$id));
            foreach ($res as $k => $r) $res[$k]['value'] = $this->getValueByWidgetAndType( $r['id'], $id );

            return $res;
        }
        
        public function getValueByWidgetAndType( $messenger_id, $widget_id ) {
			
			return $this->MySQL->getOne('SELECT value FROM yapps_app_widgets_ms_messengers WHERE messenger_id = ?i AND widget_id = ?i', $messenger_id, $widget_id);
        }
		
		public function setMessenger( $POST, $FILES ) {
			
			$arIns = $POST;
			unset( $arIns['form'] );
			
			if ( $FILES && $FILES['image']['error'] == 0 ) {
				
				$arIns['image'] = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$this->Conf->FileDir.'/Messengers/'.$FILES['image']['name'];
				$file = __DIR__.'/../..'.$this->Conf->FileDir.'/Messengers/'.$FILES['image']['name'];
				
				move_uploaded_file( $FILES['image']['tmp_name'], $file );
			}
			
			if ( $cur = $this->getMessenger($POST['id']) ) {
				
				if ( $arIns['image'] ) unlink( $_SERVER['DOCUMENT_ROOT'].$cur['image'] );
				$this->MySQL->query('UPDATE yapps_app_widgets_messengers SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);
				
			} else {
				
				$this->MySQL->query('INSERT INTO yapps_app_widgets_messengers SET ?u', $arIns);
			}
			
			return Helper::getRes(0);
		}
		
		public function delMessenger( $id ) {
			
			$cur = $this->getMessenger($POST['id']);
			unlink( $_SERVER['DOCUMENT_ROOT'].$cur['image'] );
			
			return ( $this->MySQL->query('DELETE FROM yapps_app_widgets_messengers WHERE id = ?i', (int)$id) ) ? Helper::getRes(0) : false;
		}
		
		public function setWidgetMessengers( $arr, $widget_id ) {

			$this->MySQL->query('DELETE FROM yapps_app_widgets_ms_messengers WHERE widget_id = ?i', $widget_id);
			foreach ( $arr as $i ) $this->MySQL->query('INSERT INTO yapps_app_widgets_ms_messengers SET ?u', ['widget_id'=>$widget_id, 'messenger_id'=>(int)$i[0], 'value'=>$i[1]]);

            return true;
        }
        

        ///////////////////////////////////////////////////////////////////////////////////////////
        // Script /////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

        private function getHPHtml() {
            
            return file_get_contents(__DIR__.'/html/'.get_class($this).'/HP.php');
        }
		
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // Script /////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getScript( $user, $URL ) {
            
            // if ($_GET['s']) Helper::sp( $_GET );

			$host = parse_url( $URL )['host'];
			$land = $this->YApps_GetLandSettings( $this->YApps_GetLandIdByUrl($URL) );
			if ( $land ) $host = $this->YApps_GetSiteByID( $land['site_id'] )['url'];
			$settings = $this->getSettingsByHost( $host );
			
			if ( !$settings ) $settings = $this->getSettingsByShowroom( $URL );

			if ( $settings['use_new'] ) return '';
			
			if ( $settings['active'] &&
				( !$land ||
					( $land && 
						( $land['use_lg'] || $land['use_cb'] || $land['use_nv'] || $land['use_ch'] || $land['use_av'] || $land['use_ht'] || $land['use_qz'] || $land['use_eh'] )
					)
				)
			) {

				if ( $settings['version'] == 'Vuejs' ) return $this->getVueScript().PHP_EOL;
				
				$CB = $this->selectCB( $settings );
				$widget['CB'] = ( $CB ) ? $this->getWidgetById( $CB ) : false;
				if ( $widget['CB'] ) $widget['CB']['work_flag'] = $this->isTimeCB( $settings );
				
				$NV = $this->selectNV( $settings );
                $widget['NV'] = ( $NV ) ? $this->getWidgetById( $NV ) : false;
                
                $MS = $this->selectMS( $settings );
				$widget['MS'] = ( $MS ) ? $this->getWidgetById( $MS ) : false;
				
				$LG = $this->selectLG( $settings, $URL );
				$widget['LG'] = ( $LG ) ? $this->getWidgetById( $LG ) : false;
                if ( $widget['LG']['lg_timer_flag'] ) if ( $widget['LG']['lg_timer'] <= time() ) $widget['LG'] = false;
                if ( $widget['LG'] && !$this->isActiveWidget($widget['LG']['id']) ) $widget['LG'] = false;
				
				$CH = $this->selectCH( $settings );
				$FB = $this->selectFB( $settings );
				
				$HT = $this->selectHT( $settings, $URL );
				$AV = $this->selectAV( $settings, $URL );
				
				$QZ = $this->selectQZ( $settings, $URL );
				$widget['QZ'] = ( $QZ ) ? $this->getWidgetById( $QZ ) : false;
                
                $CI = $this->selectCI( $settings, $URL );
				$widget['CI'] = ( $CI ) ? $this->getWidgetById( $CI ) : false;
				
				// Helper HTML
				$i = 0;
				$html = $this->getHPHtml();
				
				$repl = '';
				if  ( $widget['LG'] &&
					( !$land ||
						( $land && $land['use_lg'] )
					)
				 ) {
					
					$repl = file_get_contents(__DIR__.'/html/'.get_class($this).'/HP_Items/LG_Button.php');
					$repl = str_replace('%%WIDGET.HP.ITEM_LG.PLATE_TEXT%%', (($settings['hp_lg_plate_use_wname']||$widget['LG']['lg_hp_use_wname'])?$widget['LG']['lg_title']:$settings['hp_lg_plate']), $repl);
					$repl = str_replace('%%WIDGET.HP.ITEM_LG.DRAGGABLE%%', (($settings['hp_lg_plate_draggable'])?'YApps_Helper--LeadgenButton-Draggable':''), $repl);
				}
				$html = str_replace('%%WIDGET.HP.ITEM_LG_BUTTON%%', $repl, $html);
				
				$repl = '';
				if ( $CB &&
					( !$land ||
						( $land && $land['use_cb'] )
					)
				) {
					
					$icon = '<svg xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps-Widgets_CallButton"></use></svg>';
					
					$repl = file_get_contents(__DIR__.'/html/'.get_class($this).'/HP_Items/CB.php');
					$repl = str_replace(
						'%%WIDGET.HP.ITEM_CB.BUTTON_TEXT%%', 
						(($widget['CB']['work_flag'])?'Перезвоним за '.$settings['cb_timer_await'].' секунд!':$settings['hp_cb_button']), 
						$repl);
					$i++;
				}
				$html = str_replace('%%WIDGET.HP.ITEM_CB%%', $repl, $html);
				
				$repl = ''; $icon = '';
				if ( $NV &&
					( !$land ||
						( $land &&  $land['use_nv'] )
					)
				) {
					
					$icon = '<svg xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps-Widgets_NavigatorButton"></use></svg>';
					
					$repl = file_get_contents(__DIR__.'/html/'.get_class($this).'/HP_Items/NV.php');
					$repl = str_replace('%%WIDGET.HP.ITEM_NV.BUTTON_TEXT%%', $settings['hp_nv_button'], $repl);
					$i++;
				}
				$html = str_replace('%%WIDGET.HP.ITEM_NV%%', $repl, $html); $html = str_replace('%%WIDGET.HP.ICON_NV%%', $icon, $html);
                
                
                $repl = '';
				if ( $MS &&
					( !$land ||
						( $land &&  $land['use_ms'] )
					)
				) {
					
					$repl = file_get_contents(__DIR__.'/html/'.get_class($this).'/HP_Items/MS.php');
					$repl = str_replace('%%WIDGET.HP.ITEM_MS.BUTTON_TEXT%%', $widget['MS']['ms_title'], $repl);
					$i++;
				}
				$html = str_replace('%%WIDGET.HP.ITEM_MS%%', $repl, $html); $html = str_replace('%%WIDGET.HP.ICON_MS%%', $icon, $html);


				$repl = ''; $icon = '';
				if ( $CH &&
					( !$land ||
						( $land &&  $land['use_ch'] )
					)
				) {
					
					$icon = '<svg xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps-Widgets_ChatButton"></use></svg>';
					
					$repl = file_get_contents(__DIR__.'/html/'.get_class($this).'/HP_Items/CH.php');
					$repl = str_replace('%%WIDGET.HP.ITEM_CH.BUTTON_TEXT%%', $settings['hp_ch_button'], $repl);
					$i++;
				}
				$html = str_replace('%%WIDGET.HP.ITEM_CH%%', $repl, $html); $html = str_replace('%%WIDGET.HP.ICON_CH%%', $icon, $html);
				
				$repl = ''; $icon = '';
				if ( $QZ &&
					( !$land ||
						( $land &&  $land['use_qz'] )
					)
				) {
					
					$icon = '<svg xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps-Widgets_QuizButton"></use></svg>';
					
					$repl = file_get_contents(__DIR__.'/html/'.get_class($this).'/HP_Items/QZ.php');
					$repl = str_replace('%%WIDGET.HP.ITEM_QZ.BUTTON_TEXT%%', $widget['QZ']['qz_hp_button'], $repl);
					$i++;
				}
				$html = str_replace('%%WIDGET.HP.ITEM_QZ%%', $repl, $html); $html = str_replace('%%WIDGET.HP.ICON_CH%%', $icon, $html);
				
				$repl = ''; $icon = '';
				if ( $FB &&
					( !$land ||
						( $land &&  $land['use_ch'] )
					)
				) {
					
					$icon = '<svg xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps-Widgets_FeedbackButton"></use></svg>';
					
					$repl = file_get_contents(__DIR__.'/html/'.get_class($this).'/HP_Items/FB.php');
					$repl = str_replace('%%WIDGET.HP.ITEM_FB.BUTTON_TEXT%%', $settings['hp_fb_button'], $repl);
					$i++;
				}
				$html = str_replace('%%WIDGET.HP.ITEM_FB%%', $repl, $html); $html = str_replace('%%WIDGET.HP.ICON_FB%%', $icon, $html);
				
				$repl = ''; $icon = '';
				if ( $AV['count'] &&
					( !$land ||
						( $land && $land['use_av'] )
					)
				) {
					
					$icon = '<svg xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps-Widgets_AvailcarsButton"></use></svg>';
					
					$repl = file_get_contents(__DIR__.'/html/'.get_class($this).'/HP_Items/AV.php');
					$av_text = ($settings['hp_use_avail_count']) ? $AV['text'] : 'Автомобили в наличии';
					$repl = str_replace('%%WIDGET.HP.ITEM_AV.BUTTON_TEXT%%', $av_text, $repl);
					$av_count = ($settings['hp_use_avail_count']) ? '<span class="YApps_Helper--Item_Content">'.$AV['count'].'</span>' : '';
					$repl = str_replace('%%WIDGET.HP.ITEM_AV.BUTTON_COUNT%%', $av_count, $repl);
					$repl = str_replace('%%WIDGET.HP.ITEM_AV.APP_LINK%%', $AV['link'], $repl);
					$i++;
				}
				$html = str_replace('%%WIDGET.HP.ITEM_AV%%', $repl, $html); $html = str_replace('%%WIDGET.HP.ICON_AV%%', $icon, $html);
				
				$repl = ''; $icon = '';
				if ( $HT['count'] &&
					( !$land ||
						( $land &&  $land['use_ht'] )
					)
				) {
					
					$icon = '<svg xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps-Widgets_HotButton"></use></svg>';
					
					$repl = file_get_contents(__DIR__.'/html/'.get_class($this).'/HP_Items/HT.php');
					$repl = str_replace('%%WIDGET.HP.ITEM_HT.BUTTON_TEXT%%', $HT['text'], $repl);
					$repl = str_replace('%%WIDGET.HP.ITEM_HT.BUTTON_COUNT%%', $HT['count'], $repl);
					$repl = str_replace('%%WIDGET.HP.ITEM_HT.APP_LINK%%', $HT['link'], $repl);
					$i++;
				}
				$html = str_replace('%%WIDGET.HP.ITEM_HT%%', $repl, $html); $html = str_replace('%%WIDGET.HP.ICON_HT%%', $icon, $html);
				
				$repl = ''; $icon = '';
				if ( $widget['LG'] &&
					( !$land ||
						( $land &&  $land['use_lg'] )
					)
				) {
					
					$icon = '<svg xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps-Widgets_LeadgenButton"></use></svg>';
					
					$repl = file_get_contents(__DIR__.'/html/'.get_class($this).'/HP_Items/LG.php');
					$repl = str_replace('%%WIDGET.HP.ITEM_LG.BUTTON_TEXT%%', (($settings['hp_lg_button_use_wname']||$widget['LG']['lg_hp_use_wname'])?$widget['LG']['lg_title']:$settings['hp_lg_button']), $repl);
					$i++;
				}
				$html = str_replace('%%WIDGET.HP.ITEM_LG%%', $repl, $html); $html = str_replace('%%WIDGET.HP.ICON_LG%%', $icon, $html);
				
				$html = str_replace('%%WIDGET.HP.ITEM_CB.BUTTON_OUT_TEXT%%', $settings['hp_cb_out_button'], $html);
				$i++;
				
				// Widgets HTML
				
				if ( $widget['CB'] &&
					( !$land ||
						( $land &&  $land['use_cb'] )
					)
				) {
					
					$w_html = file_get_contents(__DIR__.'/html/'.get_class($this).'/CB.php');
					$w_html = str_replace('%%WIDGET.CB.TITLE_PROLOGUE%%', $widget['CB']['cb_title_prologue'], $w_html);
					$w_html = str_replace('%%WIDGET.CB.TIMER_AWAIT%%', $settings['cb_timer_await'], $w_html);
					$w_html = str_replace('%%WIDGET.CB.TEXT%%', $widget['CB']['cb_text'], $w_html);
					$w_html = str_replace('%%WIDGET.CB.BUTTON_NOW%%', $settings['cb_form_button_now'], $w_html);
					$w_html = str_replace('%%WIDGET.FORM_SUCCESS%%', $settings['form_success'], $w_html);
					$w_html = str_replace('%%WIDGET.FORM_ERROR%%', $settings['form_error'], $w_html);
					$w_html = str_replace('%%WIDGET.CB.DESCRIPTION_NOW%%', $widget['CB']['cb_description_now'], $w_html);
					$d_style = ( $widget['CB']['work_flag'] ) ? '' : 'display:none;';
					$w_html = str_replace('%%WIDGET.CB.WORKFLAG%%', $d_style, $w_html);
					
					$html .= $w_html.PHP_EOL;
                }
                
                if ( $widget['MS'] &&
					( !$land ||
						( $land &&  $land['use_ms'] )
					)
				) {
					
					$w_html = file_get_contents(__DIR__.'/html/'.get_class($this).'/MS.php');
					$w_html = str_replace('%%WIDGET.MS.TITLE%%', $widget['MS']['ms_title'], $w_html);
                    $w_html = str_replace('%%WIDGET.MS.TEXT%%', $widget['MS']['ms_text'], $w_html);
                    
                    $mss = '';
                    foreach ( $this->getMessengersByWidget($widget['MS']['id']) as $messenger ) {
                        
                        $m = file_get_contents(__DIR__.'/html/'.get_class($this).'/MS_Items/MS.php');

                        $messenger['url_scheme'] = str_replace('%%WIDGET.MS.VALUE%%', $messenger['value'], $messenger['url_scheme']);
                        $m = str_replace('%%WIDGET.MS.ITEM.URL-SCHEME%%', $messenger['url_scheme'], $m);
                        $m = str_replace('%%WIDGET.MS.ITEM.GOAL%%', $messenger['goal'], $m);
                        $m = str_replace('%%WIDGET.MS.ITEM.ICON%%', $messenger['image'], $m);
                        $m = str_replace('%%WIDGET.MS.ITEM.NAME%%', $messenger['ru_name'], $m);

                        $mss .= $m;
                    } 
                    $w_html = str_replace('%%WIDGET.MS.MESSs%%', $mss, $w_html);

					$html .= $w_html.PHP_EOL;
				}
				
				if ( $FB &&
					( !$land ||
						( $land &&  $land['use_ch'] )
					)
				) {
					
					$w_html = file_get_contents(__DIR__.'/html/'.get_class($this).'/FB.php');
					$w_html = str_replace('%%WIDGET.HP.ITEM_FB.BUTTON_TEXT%%', $settings['hp_fb_button'], $w_html);
					
					$html .= $w_html.PHP_EOL;
				}
				
				if ( $CH &&
					( !$land ||
						( $land &&  $land['use_ch'] )
					)
				) { $html .= file_get_contents(__DIR__.'/html/'.get_class($this).'/CH.php').PHP_EOL; }
				
				if ( $widget['NV'] &&
					( !$land ||
						( $land &&  $land['use_nv'] )
					)
				) {
					
					$w_html = file_get_contents(__DIR__.'/html/'.get_class($this).'/NV.php');
					$w_html = str_replace('%%WIDGET.HP.ITEM_NV.BUTTON_TEXT%%', $settings['hp_nv_button'], $w_html);
					
					$dcs = '';
					$dcc = $this->YApps_GetDCsBySite($settings['site_id']);
					foreach ( $dcc as $dc ) {
						
						$dc['ru_name'] = str_replace([' PKW', ' NFZ'], ['', ''], $dc['ru_name']);
						
						$d = file_get_contents(__DIR__.'/html/'.get_class($this).'/NV_Items/DC.php');
						$d = str_replace('%%WIDGET.NV.ITEM_DC.LAT%%', $dc['coords_lat'], $d);
						$d = str_replace('%%WIDGET.NV.ITEM_DC.LON%%', $dc['coords_lon'], $d);
						$d = str_replace('%%WIDGET.NV.ITEM_DC.WIDTH%%', 100/count($dcc)-3, $d);
						$d = str_replace('%%WIDGET.NV.ITEM_DC.NAME%%', $dc['ru_name'], $d);
						$d = str_replace('%%WIDGET.NV.ITEM_DC.ADDRESS%%', $dc['address'], $d);
						$d = str_replace('%%WIDGET.NV.ITEM_DC.PHONE%%', Helper::formatPhoneIn($dc['phone']), $d);
						
						$dcs .= $d.PHP_EOL;
					}
					$w_html = str_replace('%%WIDGET.NV.DCs%%', $dcs, $w_html);
					
					if ( $navis = $this->getNavigatorsBySite($settings['site_id']) ) {
						
						$sns = file_get_contents(__DIR__.'/html/'.get_class($this).'/SN.php');
						$sns = str_replace('%%WIDGET.SN.TEXT%%', $settings['sn_text'], $sns);
						
						$sn = '';
						foreach ( $navis as $navi ) {
							
							$n = file_get_contents(__DIR__.'/html/'.get_class($this).'/SN_Items/NAVI.php');
							$n = str_replace('%%WIDGET.SN.ITEM.URL-SCHEME%%', $navi['url_scheme'], $n);
							$n = str_replace('%%WIDGET.SN.ITEM.ICON%%', $navi['image'], $n);
							$n = str_replace('%%WIDGET.SN.ITEM.NAME%%', $navi['ru_name'], $n);
							
							$sn .= $n.PHP_EOL;
						}
						
						$sns = str_replace('%%WIDGET.SN.NAVIs%%', $sn, $sns);
						
						$w_html .= $sns.PHP_EOL;
					}
					
					
					$html .= $w_html.PHP_EOL;
				}
				
				if ( $widget['LG'] &&
					( !$land ||
						( $land &&  $land['use_lg'] )
					)
				) {
					
					$w_html = file_get_contents(__DIR__.'/html/'.get_class($this).'/LG.php');
					$w_html = str_replace('%%WIDGET.LG.HEAD%%', $widget['LG']['lg_head'], $w_html);
					$w_html = str_replace('%%WIDGET.LG.IMAGE%%', $widget['LG']['lg_image'], $w_html);
					
					$repl = '';
					if ( $HT['count'] && !$widget['LG']['lg_hide_buttons']) {
						
						$repl = file_get_contents(__DIR__.'/html/'.get_class($this).'/LG_Items/HT_Button.php');
						$repl = str_replace('%%WIDGET.HP.ITEM_HT.APP_LINK%%', $HT['link'], $repl);
						$repl = str_replace('%%WIDGET.LG.ITEMS_HT.BUTTON%%', '<strong>'.$HT['count'].'</strong> '.Helper::getWorld($HT['count'], 'offer'), $repl);
					}
					$w_html = str_replace('%%WIDGET.LG.USE_HT%%', $repl, $w_html);
					
					$repl = '';
					if ( $AV['count'] && !$widget['LG']['lg_hide_buttons'] ) {
						
						$repl = file_get_contents(__DIR__.'/html/'.get_class($this).'/LG_Items/AV_Button.php');
						$repl = str_replace('%%WIDGET.HP.ITEM_AV.APP_LINK%%', $AV['link'], $repl);
						$avn = ($settings['hp_use_avail_count']) ? '<strong>'.$AV['count'].'</strong> в наличии' : 'Автомобили в наличии';
						
						$repl = str_replace('%%WIDGET.LG.ITEMS_AV.BUTTON%%', $avn, $repl);
					}
					$w_html = str_replace('%%WIDGET.LG.USE_AV%%', $repl, $w_html);
					
					$timer = '';
					if ( $widget['LG']['lg_timer_flag'] && $widget['LG']['lg_timer'] - time() > 0 ) {
						
						$time = Helper::Timeout($widget['LG']['lg_timer']-time());
						$timer = file_get_contents(__DIR__.'/html/'.get_class($this).'/LG_Items/Timer.php');
						$timer = str_replace('%%WIDGET.LG.TIMER_DAYS%%', $time['Days'], $timer);
						$timer = str_replace('%%WIDGET.LG.TIMER_HOURS%%', $time['Hours'], $timer);
						$timer = str_replace('%%WIDGET.LG.TIMER_MINUTS%%', $time['Minuts'], $timer);
						$timer = str_replace('%%WIDGET.LG.TIMER_SECONDS%%', $time['Seconds'], $timer);
						$timer = str_replace('%%WIDGET.LG.TIMER_DESCRIPTION%%', $widget['LG']['lg_timer_description'], $timer);
					}
					$w_html = str_replace('%%WIDGET.LG.ITEMS_TIMER%%', $timer, $w_html);
					
					$w_html = str_replace('%%WIDGET.LG.TIME_START%%', date('d.m.Y', $widget['LG']['lg_time_start']), $w_html);
					$w_html = str_replace('%%WIDGET.LG.TITLE%%', $widget['LG']['lg_title'], $w_html);
					$w_html = str_replace('%%WIDGET.LG.TIMER_END%%', date('d.m.Y', $widget['LG']['lg_timer']+24*3600), $w_html);
					$w_html = str_replace('%%WIDGET.LG.TEXT%%', $widget['LG']['lg_text'], $w_html);
					
					$link = '';
					if ( $widget['LG']['lg_link'] ) {
						
						$link = file_get_contents(__DIR__.'/html/'.get_class($this).'/LG_Items/Link.php');
						$link = str_replace('%%WIDGET.LG.LINK_URL%%', $widget['LG']['lg_link'], $link);
						$link = str_replace('%%WIDGET.LG.LINK_TEXT%%', $widget['LG']['lg_link_text'], $link);
					}
					$w_html = str_replace('%%WIDGET.LG.LINK%%', $link, $w_html);
					
					$only = '';
					if ( $widget['LG']['lg_timer_flag'] ) {
						
						$only = file_get_contents(__DIR__.'/html/'.get_class($this).'/LG_Items/Only.php');
						$only = str_replace('%%WIDGET.LG.TIMER_END%%', date('d.m.Y', $widget['LG']['lg_timer']+24*3600), $only);
					}
					$w_html = str_replace('%%WIDGET.LG.ITEMS_ONLY%%', $only, $w_html);
					
					$w_html = str_replace('%%WIDGET.LG.BUTTON%%', $settings['lg_form_button'], $w_html);
					$w_html = str_replace('%%WIDGET.FORM_SUCCESS%%', $settings['form_success'], $w_html);
					$w_html = str_replace('%%WIDGET.FORM_ERROR%%', $settings['form_error'], $w_html);
					
					$html .= $w_html.PHP_EOL;
				}
				
				if ( $widget['QZ'] &&
					( !$land ||
						( $land &&  $land['use_qz'] )
					)
				) {
					
					$w_html = file_get_contents(__DIR__.'/html/'.get_class($this).'/QZ.php');
					$w_html = str_replace('%%WIDGET.QZ.LAST_TITLE%%', $widget['QZ']['qz_last_title'], $w_html);
					$w_html = str_replace('%%WIDGET.QZ.LAST_BIGTEXT%%', $widget['QZ']['qz_last_bigtext'], $w_html);
					$w_html = str_replace('%%WIDGET.QZ.LAST_TEXT%%', $widget['QZ']['qz_last_text'], $w_html);
					$w_html = str_replace('%%WIDGET.QZ.LAST_STEP%%', count($widget['QZ']['slides'])+1, $w_html);
					$w_html = str_replace('%%WIDGET.QZ.BUTTON_TEXT%%', $widget['QZ']['qz_form_button'], $w_html);
					
					$repl = '';
					for ( $i=1; $i<=count($widget['QZ']['slides'])+1; $i++ ) { 
						
						$r = file_get_contents(__DIR__.'/html/'.get_class($this).'/QZ_Items/QZ_Pagination.php');
						$r = str_replace('%%WIDGET.QZ.SLIDE_STEP%%', $i, $r);
						$repl .= $r;
					}
					$w_html = str_replace('%%WIDGET.QZ.PAGINATION%%', $repl, $w_html);
					
					$repl = '';
					foreach ( $widget['QZ']['slides'] as $k => $slide ) {
						
						$r = file_get_contents(__DIR__.'/html/'.get_class($this).'/QZ_Items/QZ_Slide.php');
						$r = str_replace('%%WIDGET.QZ.SLIDE_STEP%%', $k+1, $r);
						$r = str_replace('%%WIDGET.QZ.SLIDE_ID%%', $slide['id'], $r);
						$r = str_replace('%%WIDGET.QZ.SLIDE_TYPE_ID%%', $slide['type_id'], $r);
						$r = str_replace('%%WIDGET.QZ.SLIDE_TYPE_REQUIRED%%', (((int)$slide['required'])?'required':''), $r);
						$r = str_replace('%%WIDGET.QZ.SLIDE_TITLE%%', $slide['ru_name'], $r);
						$r = str_replace('%%WIDGET.QZ.SLIDE_DISCLAMER%%', $slide['disclamer'], $r);
						
						$rn = '';
						foreach ( $slide['items'] as $item ) {
							
							$q = file_get_contents(__DIR__.'/html/'.get_class($this).'/QZ_Items/QZ_Type_'.$slide['type_id'].'.php');
							$q = str_replace('%%WIDGET.QZ.SLIDE_ID%%', $slide['id'], $q);
							$q = str_replace('%%WIDGET.QZ.SLIDE.ITEM_ID%%', $item['id'], $q);
							$q = str_replace('%%WIDGET.QZ.SLIDE.ITEM_IMAGE%%', $item['photo'], $q);
							$q = str_replace('%%WIDGET.QZ.SLIDE.ITEM_NAME%%', $item['value'], $q);
							$q = str_replace('%%WIDGET.QZ.SLIDE.ITEM_DESCRIPTION%%', $item['description'], $q);
							$q = str_replace('%%WIDGET.QZ.SLIDE.ITEM_REQUIRED%%', (((int)$item['required'])?'required':''), $q);
							$q = str_replace('%%WIDGET.QZ.SLIDE.ITEM_REQUIRED_STAR%%', (((int)$item['required'])?'*':''), $q);
							
							$rn .= $q;
						}
						
						$r = str_replace('%%WIDGET.QZ.SLIDE_ITEMS%%', $rn, $r);
						
						$repl .= $r;
					}
					$w_html = str_replace('%%WIDGET.QZ.SLIDES%%', $repl, $w_html);
					
					
					$w_html = str_replace('%%WIDGET.QZ.BUTTON%%', $settings['qz_form_button'], $w_html);
					$w_html = str_replace('%%WIDGET.FORM_SUCCESS%%', $settings['form_success'], $w_html);
					$w_html = str_replace('%%WIDGET.FORM_ERROR%%', $settings['form_error'], $w_html);
					
					$html .= $w_html.PHP_EOL;
                }
                
                if ( $widget['CI'] &&
					( !$land ||
						( $land &&  $land['use_ci'] )
					)
				) {
					
					$w_html = file_get_contents(__DIR__.'/html/'.get_class($this).'/CI.php');
					$w_html = str_replace('%%WIDGET.CI.BUTTON%%', $settings['ci_form_button'], $w_html);
					$w_html = str_replace('%%WIDGET.FORM_SUCCESS%%', $settings['form_success'], $w_html);
					$w_html = str_replace('%%WIDGET.FORM_ERROR%%', $settings['form_error'], $w_html);
					
					$html .= $w_html.PHP_EOL;
				}




				
				$html .= file_get_contents(__DIR__.'/html/'.get_class($this).'.html');
                
                $t_html = file_get_contents(__DIR__.'/html/HTML_Items/Terms.html');
                $t_html = str_replace('%%WIDGET.TERM_PERSONAL%%', $settings['term_personal'], $t_html);
                $t_html = str_replace('%%WIDGET.TERM_COMMUNICATIONS%%', $settings['term_communications'], $t_html);
                $t_html = str_replace('%%WIDGET.TERM_CHECKED.CLASS%%', (($settings['term_checked'])?'YApps--Form_Personal-Item_Checked':''), $t_html);
                $t_html = str_replace('%%WIDGET.TERM_CHECKED.ICON%%', (($settings['term_checked'])?'Check':'UnCheck'), $t_html);
                $html = str_replace('%%WIDGET.TERMS%%', $t_html, $html);
                
                $html = str_replace('%%WIDGET.TERM_PERSONAL%%', $settings['term_personal'], $html);
				$html = str_replace('%%WIDGET.TERM_COMMUNICATIONS%%', $settings['term_communications'], $html);

				$html = str_replace(' руб ', ' ₽ ', $html);
                $html = str_replace(' руб. ', ' ₽ ', $html);
                
                
                
                
				
				// SCRIPT
				
                $vendor = ( in_array($settings['site_id'], [2,22,23,30]) ) ? file_get_contents(__DIR__.'/js/vendor.js').PHP_EOL : '';
                
				$script = $vendor.file_get_contents(__DIR__.'/js/'.get_class($this).'.js').PHP_EOL;
				$script = str_replace('%%WIDGET.RESULT_TIMEOUT%%', $settings['result_timeout'], $script);
				$script = str_replace('%%WIDGET.INIT_TIMEOUT%%', $settings['init_timeout'], $script);
				$script = str_replace('%%WIDGETS.HTML%%', JSMin::minifyHTML($html), $script);
				$script = str_replace('%%WIDGETS.SVG%%', JSMin::minifyHTML(file_get_contents(__DIR__.'/svg/'.get_class($this).'.php')), $script);
				
				$hp_js = file_get_contents(__DIR__.'/js/'.get_class($this).'/HP.js');
				$hp_js = str_replace('%%WIDGET.HP.SHOW_INTERVAL%%', $settings['hp_show_interval'], $hp_js);
				$hp_js = str_replace('%%WIDGET.HP.CLOSE_TIMEOUT%%', $settings['hp_close_timeout'], $hp_js);
				$hp_js = str_replace('%%WIDGET.HP.ITEMS_COUNT%%', $i+1, $hp_js);
				$hp_js = str_replace('%%WIDGET.HP.CLOSE_TIMEOUT%%', $settings['hp_close_timeout'], $hp_js);
                $hp_js = str_replace('%%WIDGET.HP.ICON_INTERVAL%%', $settings['hp_icons_interval'], $hp_js);
                
                $phone = ( $settings['site_id'] == 7 ) ? '78612127200' : $this->YApps_GetDCsBySite($settings['site_id'])[0]['phone'];

				$hp_js = str_replace('%%WIDGET.HP.PHONE%%', Helper::formatPhoneIn($phone), $hp_js);
				$hp_js = str_replace('%%WIDGET.HP.CT_SESS%%', $this->MySQL->getOne('SELECT calltouch_sess FROM yapps_sites WHERE id = ?i', $settings['site_id']), $hp_js);
				$hp_js = str_replace('%%WIDGET.HP.LG_PLATE_STICK%%', (($settings['position']['stick']=='left'||$settings['position']['stick']=='right')?'y':'x'), $hp_js);
				$hp_js = str_replace('%%WIDGET.HP.LG_PLATE_DRAGGABLE%%', (($settings['hp_lg_plate_draggable'])?'true':'false'), $hp_js);
				$script .= $hp_js.PHP_EOL;
				
				$w_init = '';
				if ( $widget['CB'] &&
					( !$land ||
						( $land &&  $land['use_cb'] )
					)
				) {
					
					$w_js = file_get_contents(__DIR__.'/js/'.get_class($this).'/CB.js');
					$w_js = str_replace('%%WIDGET.CB.IDLE_TIMEOUT%%', $settings['cb_idle_timeout'], $w_js);
					$w_js = str_replace('%%WIDGET.CB.TIMER_AWAIT%%', $settings['cb_timer_await'], $w_js);
					$w_js = str_replace('%%WIDGET.CB.TIMER_TIMEOUT%%', $settings['cb_timer_timeout'], $w_js);
					$w_js = str_replace('%%WIDGET.CB.AWAIT_DAYS%%', $settings['cb_await_days'], $w_js);
					$w_js = str_replace('%%WIDGET.CB.PROLOGUE%%', $widget['CB']['cb_title_prologue'], $w_js);
					$w_js = str_replace('%%WIDGET.CB.TITLE_SPAN_PROROQUE%%', $widget['CB']['cb_title_span_proroque'], $w_js);
					$w_js = str_replace('%%WIDGET.CB.DESCRIPION_NOW%%', $widget['CB']['cb_description_now'], $w_js);
					$w_js = str_replace('%%WIDGET.CB.DESCRIPION_LATER%%', $widget['CB']['cb_description_later'], $w_js);
					$w_js = str_replace('%%WIDGET.CB.ID%%', $widget['CB']['id'], $w_js);
					$w_js = str_replace('%%WIDGET.CB.NAME%%', $widget['CB']['ru_name'], $w_js);
					$w_js = str_replace('%%WIDGET.CB.WORKFLAG%%', (($widget['CB']['work_flag'])?'true':'false'), $w_js);
					
					$script .= $w_js.PHP_EOL;
					
					$w_init = 'YApps.Widgets.CB.Init();';
				}
				$script = str_replace('%%WIDGET.CB.INIT%%', $w_init, $script);
				
				if ( $CH &&
					( !$land ||
						( $land &&  $land['use_ch'] )
					)
				) { $script .= str_replace('%%WIDGET.CH.TIMEOUT%%', $settings['ch_timeout'], file_get_contents(__DIR__.'/js/'.get_class($this).'/CH.js')).PHP_EOL;}
				
				if ( $FB &&
					( !$land ||
						( $land &&  $land['use_ch'] )
					)
                ) { $script .= file_get_contents(__DIR__.'/js/'.get_class($this).'/FB.js').PHP_EOL; }
                
                if ( $MS &&
					( !$land ||
						( $land &&  $land['use_ms'] )
					)
				) { $script .=  str_replace('%%WIDGET.MS.IDLE_TIMEOUT%%', $widget['MS']['ms_idle_timeout'], file_get_contents(__DIR__.'/js/'.get_class($this).'/MS.js')).PHP_EOL; }
				
				$w_init = '';
				if ( $widget['NV'] &&
					( !$land ||
						( $land &&  $land['use_nv'] )
					)
				) {
					
					$w_js = file_get_contents(__DIR__.'/js/'.get_class($this).'/NV.js');
					$w_js = str_replace('%%WIDGET.COLOR_FILL%%', (($settings['color_fill'])?:$this->Conf->Defaults->ColorFill), $w_js);
					$w_js = str_replace('%%WIDGET.SN.TO_NAV%%', (($navis)?'true':'false'), $w_js);
					
					$w_js .= file_get_contents(__DIR__.'/js/'.get_class($this).'/SN.js');
					
					$script .= $w_js.PHP_EOL;
					
					$w_init = 'YApps.Widgets.NV.Init();';
				}
				$script = str_replace('%%WIDGET.NV.INIT%%', $w_init, $script);
				
				$w_init = '';
				if ( $widget['LG'] &&
					( !$land ||
						( $land &&  $land['use_lg'] )
					)
				) {
					
					$w_js = file_get_contents(__DIR__.'/js/'.get_class($this).'/LG.js');
					$w_js = str_replace('%%WIDGET.LG.TIMER_FLAG%%', (($widget['LG']['lg_timer_flag'])?'true':'false'), $w_js);
					$w_js = str_replace('%%WIDGET.LG.SHOW_TIMEOUT%%', $settings['lg_show_timeout'], $w_js);
					$w_js = str_replace('%%WIDGET.LG.SHOW_SECOND%%', $settings['lg_second_timeout'], $w_js);
					$w_js = str_replace('%%WIDGET.LG.SHOW_COUNT%%', $settings['lg_show_count'], $w_js);
					$w_js = str_replace('%%WIDGET.LG.SHOW_COUNT_2%%', $settings['lg_second_count'], $w_js);
					$w_js = str_replace('%%WIDGET.LG.ID%%', $widget['LG']['id'], $w_js);
					$w_js = str_replace('%%WIDGET.LG.NAME%%', $widget['LG']['ru_name'], $w_js);
					
					$time = Helper::Timeout($widget['LG']['lg_timer']-time());
					$w_js = str_replace('%%WIDGET.LG.TIMER_DAYS%%', $time['Days'], $w_js);
					$w_js = str_replace('%%WIDGET.LG.TIMER_HOURS%%', $time['Hours'], $w_js);
					$w_js = str_replace('%%WIDGET.LG.TIMER_MINUTS%%', $time['Minuts'], $w_js);
					$w_js = str_replace('%%WIDGET.LG.TIMER_SECONDS%%', $time['Seconds'], $w_js);
					
					$script .= $w_js.PHP_EOL;
					
					$w_init = 'YApps.Widgets.LG.Init();';
				}
				$script = str_replace('%%WIDGET.LG.INIT%%', $w_init, $script);
				
				$w_init = '';
				if ( $widget['QZ'] &&
					( !$land ||
						( $land &&  $land['use_qz'] )
					)
				) {
					
					$w_js = file_get_contents(__DIR__.'/js/'.get_class($this).'/QZ.js');
					$w_js = str_replace('%%WIDGET.QZ.SLIDES_COUNT%%', count($widget['QZ']['slides'])+1, $w_js);
					$w_js = str_replace('%%WIDGET.QZ.ID%%', $widget['QZ']['id'], $w_js);
					
					$script .= $w_js.PHP_EOL;
					
					$w_init = 'YApps.Widgets.QZ.Init();';
				}
				$script = str_replace('%%WIDGET.QZ.INIT%%', $w_init, $script);

				$w_init = '';
				if ( $widget['CI'] &&
					( !$land ||
						( $land &&  $land['use_ci'] )
					)
				) {
					
					$w_js = file_get_contents(__DIR__.'/js/'.get_class($this).'/CI.js');
					$w_js = str_replace('%%WIDGET.CI.ID%%', $widget['CI']['id'], $w_js);
					$w_js = str_replace('%%WIDGET.CI.TIMEOUT%%', $settings['ci_timeout_1'], $w_js);
                    $w_js = str_replace(
                        '%%WIDGET.CI.LIST.TEXT%%', 
                        str_replace(
                            '{{RANDOM}}',
                            rand($widget['CI']['ci_list_random_min'], $widget['CI']['ci_list_random_max']),
                            $widget['CI']['ci_list_text']
                        ), 
                        $w_js
                    );
                    $w_js = str_replace(
                        '%%WIDGET.CI.MODEL.TEXT%%', 
                        str_replace(
                            '{{RANDOM}}',
                            rand($widget['CI']['ci_model_random_min'], $widget['CI']['ci_model_random_max']),
                            $widget['CI']['ci_model_text']
                        ), 
                        $w_js
                    );
                    $w_js = str_replace(
                        '%%WIDGET.CI.ITEM.TEXT%%', 
                        str_replace(
                            '{{RANDOM}}',
                            rand($widget['CI']['ci_item_random_min'], $widget['CI']['ci_item_random_max']),
                            $widget['CI']['ci_item_text']
                        ), 
                        $w_js
                    );

                    $w_js = str_replace(
                        '%%WIDGET.CI.LIST.TITLE%%', 
                        str_replace(
                            '{{RANDOM}}',
                            rand($widget['CI']['ci_list_random_min'], $widget['CI']['ci_list_random_max']),
                            $widget['CI']['ci_list_title']
                        ), 
                        $w_js
                    );
                    $w_js = str_replace(
                        '%%WIDGET.CI.MODEL.TITLE%%', 
                        str_replace(
                            '{{RANDOM}}',
                            rand($widget['CI']['ci_model_random_min'], $widget['CI']['ci_model_random_max']),
                            $widget['CI']['ci_model_title']
                        ), 
                        $w_js
                    );
                    $w_js = str_replace(
                        '%%WIDGET.CI.ITEM.TITLE%%', 
                        str_replace(
                            '{{RANDOM}}',
                            rand($widget['CI']['ci_item_random_min'], $widget['CI']['ci_item_random_max']),
                            $widget['CI']['ci_item_title']
                        ), 
                        $w_js
                    );

                    $w_js = str_replace('%%WIDGET.CI.LIST.LEVEL%%', $settings['ci_level_list'], $w_js);
                    $w_js = str_replace('%%WIDGET.CI.MODEL.LEVEL%%', $settings['ci_level_model'], $w_js);
                    $w_js = str_replace('%%WIDGET.CI.ITEM.LEVEL%%', $settings['ci_level_item'], $w_js);
					
					$script .= $w_js.PHP_EOL;
				}
				
				return $script;
			}
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // CSS ////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////
		
		public function getCSS( $user, $URL ) {

			$host = parse_url( $URL )['host'];
			$land = $this->YApps_GetLandSettings( $this->YApps_GetLandIdByUrl($URL) );
			if ( $land ) $host = $this->YApps_GetSiteByID( $land['site_id'] )['url'];
			$settings = $this->getSettingsByHost( $host );
            if ( !$settings ) $settings = $this->getSettingsByShowroom( $URL );
			if ( $settings['use_new'] ) return '';
            
            // $dev = ( $settings['site_id'] == 27 ) ? 's' : '';

			if ( $settings['active']  ) {
				
				$CB = $this->selectCB( $settings );
				$widget['CB'] = ( $CB ) ? $this->getWidgetById( $CB ) : false;
				if ( $widget['CB'] ) $widget['CB']['work_flag'] = $this->isTimeCB( $settings );
				
				$NV = $this->selectNV( $settings );
				$widget['NV'] = ( $NV ) ? $this->getWidgetById( $NV ) : false;
				
				$LG = $this->selectLG( $settings, $URL );
				$widget['LG'] = ( $LG ) ? $this->getWidgetById( $LG ) : false;
				if ( $widget['LG']['lg_timer_flag'] ) if ( $widget['LG']['lg_timer'] <= time() ) $widget['LG'] = false;
				
				$vendor = '';
				$css = $vendor.file_get_contents(__DIR__.'/css/'.get_class($this).'.css');
                $css .= file_get_contents(__DIR__.'/css/'.get_class($this).'/HP.css');
                if ( $settings['hp_start_open'] ) $css .= file_get_contents(__DIR__.'/css/'.get_class($this).'/HPOpen.css');
                $css .= file_get_contents(__DIR__.'/css/'.get_class($this).'/CB.css');
                $css .= file_get_contents(__DIR__.'/css/'.get_class($this).'/MS.css');
				$css .= file_get_contents(__DIR__.'/css/'.get_class($this).'/CH.css');
				$css .= file_get_contents(__DIR__.'/css/'.get_class($this).'/FB.css');
				$css .= file_get_contents(__DIR__.'/css/'.get_class($this).'/NV.css');
				$css .= file_get_contents(__DIR__.'/css/'.get_class($this).'/SN.css');
				$css .= file_get_contents(__DIR__.'/css/'.get_class($this).'/LG.css');
                $css .= file_get_contents(__DIR__.'/css/'.get_class($this).'/QZ.css');
                $css .= file_get_contents(__DIR__.'/css/'.get_class($this).'/CI.css');

                if ( $settings['hp_bind_widgets'] ) $css .= file_get_contents(__DIR__.'/css/'.get_class($this).'/HPBind.css');
                if ( $settings['hp_bind_widgets'] && $settings['hp_start_open'] ) $css .= file_get_contents(__DIR__.'/css/'.get_class($this).'/HPBindOpen.css');
				
                $css = str_replace( '%%WIDGET.COLOR_BG%%', (($settings['color_bg'])?:$this->Conf->Defaults->ColorBg), $css );
                $css = str_replace( '%%WIDGET.COLOR_DARKBG%%', (($settings['color_darkbg'])?:$this->Conf->Defaults->ColorDarkBg), $css );
				$css = str_replace( '%%WIDGET.COLOR_FILL%%', (($settings['color_fill'])?:$this->Conf->Defaults->ColorFill), $css );
				$css = str_replace( '%%WIDGET.COLOR_TEXT%%', (($settings['color_text'])?:$this->Conf->Defaults->ColorText), $css );
				$css = str_replace( '%%WIDGET.COLOR_BUTTON%%', (($settings['color_button'])?:$this->Conf->Defaults->ColorButton), $css );
				$css = str_replace( '%%WIDGET.COLOR_BUTTON_TEXT%%', (($settings['color_button_text'])?:$this->Conf->Defaults->ColorButtonText), $css );
				$css = str_replace( '%%WIDGET.COLOR_ERROR%%', (($settings['color_error'])?:$this->Conf->Defaults->ColorError), $css );
				$css = str_replace( '%%WIDGET.COLOR_LIGHTGRAY%%', (($settings['color_lightgray'])?:$this->Conf->Defaults->ColorLightgray), $css );
				$css = str_replace( '%%WIDGET.COLOR_DARKGRAY%%', (($settings['color_darkgray'])?:$this->Conf->Defaults->ColorDarkgray), $css );
				$css = str_replace( '%%WIDGET.COLOR_SHADOW%%', (($settings['color_shadow'])?:$this->Conf->Defaults->ColorShadow), $css );
				$css = str_replace( '%%WIDGET.HP.ICON_INTERVAL%%', (($settings['hp_icons_interval'])?:$this->Conf->Defaults->HPIconsInterval), $css );
				
				$css = str_replace( '%%HELPER.LG_BUTTON.POSITION_TOP%%', $settings['position']['position_top'], $css );
				$css = str_replace( '%%HELPER.LG_BUTTON.POSITION_RIGHT%%', $settings['position']['position_right'], $css );
				$css = str_replace( '%%HELPER.LG_BUTTON.POSITION_BOTTOM%%', $settings['position']['position_bottom'], $css );
				$css = str_replace( '%%HELPER.LG_BUTTON.POSITION_LEFT%%', $settings['position']['position_left'], $css );
				$css = str_replace( '%%HELPER.LG_BUTTON.RADIUS_TL%%', $settings['position']['border_radius_tl'], $css );
				$css = str_replace( '%%HELPER.LG_BUTTON.RADIUS_TR%%', $settings['position']['border_radius_tr'], $css );
				$css = str_replace( '%%HELPER.LG_BUTTON.RADIUS_BR%%', $settings['position']['border_radius_br'], $css );
				$css = str_replace( '%%HELPER.LG_BUTTON.RADIUS_BL%%', $settings['position']['border_radius_bl'], $css );
				$css = str_replace( '%%HELPER.LG_BUTTON.TRANSFORM%%', $settings['position']['transform'], $css );
                
				$css .= $settings['css'];
				
                if ( $widget['CB'] ) $css .= $widget['CB']['css'];
                if ( $widget['MS'] ) $css .= $widget['MS']['css'];
				if ( $widget['NV'] ) $css .= $widget['NV']['css'];
                if ( $widget['LG'] ) $css .= $widget['LG']['css'];
				if ( $widget['QZ'] ) $css .= $widget['QZ']['css'];
                
				
				return $css.PHP_EOL;
			}
		}
		
		
		///////////////////////////////////////////////////////////////////////////////////////////
        // API ////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function getEHItemTypes() {

			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_eh_item_types');
		}

		public function getTypeIDByKey( $q ) {

			return $this->MySQL->getOne('SELECT id FROM yapps_app_widgets_eh_item_types WHERE url_key = ?s', $q);
		}

		public function getEHItemActionTypes() {

			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_eh_item_action_types');
		}

		public function getItemActionIDByKey( $q ) {

			return $this->MySQL->getOne('SELECT id FROM yapps_app_widgets_eh_item_action_types WHERE url_key = ?s', $q);
		}

		public function setDefaultEHItems( $id ) {

			$items = $this->Conf->Defaults->Vue->Widgets->EH->Items;
			foreach ( $items as $i => $item ) {

				$iIns = [
					'widget_id' => $id,
					'eh_key' => 'eh'.$id.'_item'.($i+1),
					'type_id' => $this->getTypeIDByKey( $item['Type'] ),
					'text' => $item['Text'],
					'inited_status' => $item['Inited']['Status'],
					'cookie_status' => $item['Cookie']['Status'],
					'blank' => $item['blank']
				];

				if ( $item['Value'] ) $iIns['value'] = $item['Value'];
				if ( $iIns['inited_status'] ) $iIns['inited_delay'] = $item['Inited']['Delay'];
				if ( $iIns['cookie_status'] ) {
					$iIns['cookie_name'] = $item['Cookie']['Name'];
					$iIns['cookie_count'] = $item['Cookie']['Count'];
				}

				$this->MySQL->query('INSERT INTO yapps_app_widgets_eh_items SET ?u', $iIns);
				$lastId = $this->MySQL->insertId();

				foreach ( $item['items'] as $action ) {

					$aIns = [
						'item_id' => $lastId,
						'type_id' => $this->getItemActionIDByKey( $action['action'] ),
						'text' => $action['text'],
						'value' => ( $action['action'] == 'step' ) ? 'eh'.$id.'_item'.$action['value'] : $action['value'],
						'blank' => $action['blank']
					];
					$this->MySQL->query('INSERT INTO yapps_app_widgets_eh_item_actions SET ?u', $aIns);
				}
			}
		}

		public function setEHItems( $POST ) {

			// Helper::sp( $POST );

			$id = $POST['id'];

			$this->delEHItems( $id );

			foreach ( $POST['ITEMS'] as $i => $item ) {
				
				$iIns = [
					'widget_id' => $id,
					'eh_key' => 'eh'.$id.'_item'.($i+1),
					'type_id' => $item['type'],
					'text' => $item['text'],
					'value' => ($item['value']) ?: '',
					'inited_status' => ( $item['inited_status'] == 'on' ) ? 1 : 0,
					'inited_delay' => $item['inited_delay'],
					'cookie_status' => ( $item['cookie_status'] == 'on' ) ? 1 : 0,
					'cookie_name' => ( (int)$item['type'] == 5 ) ? 'YApps_Widgets--EH_Items-CI' : '',
					'cookie_count' => $item['cookie_count'],
					'blank' => ( $item['blank'] == 'on' ) ? 1 : 0,
				];

				switch ( (int)$item['type'] ) {

					case 5: $iIns['cookie_name'] = 'YApps_Widgets--EH_Items-CI';break;
					default: break;
				}

				$this->MySQL->query('INSERT INTO yapps_app_widgets_eh_items SET ?u', $iIns);
				// Helper::sp( $iIns );
				$lastId = $this->MySQL->insertId();

				if ( (int)$this->MySQL->getOne('SELECT use_items FROM yapps_app_widgets_eh_item_types WHERE id = ?i', $item['type']) ) {

					foreach ( $item['actions'] as $action ) {

						if ( $action['type_id'] ) {

							$aIns = [
								'item_id' => $lastId,
								'type_id' => $action['type_id'],
								'text' => $action['text']
							];
							switch ( $action['type_id'] ) {
	
								case 1: $aIns['value'] = $action['widget_type']; break;
								case 2: $aIns['value'] = $action['link']; break;
								case 3: 
									$aIns['value'] = $action['step'];
									$aIns['blank'] = ( $action['blank'] == 'on' ) ? 1 : 0;
									break;
								default: break;
							}
							$this->MySQL->query('INSERT INTO yapps_app_widgets_eh_item_actions SET ?u', $aIns);
							// Helper::sp($aIns);
						}
					}
				}
			}

			return Helper::getRes(0);
		}

		public function delEHItems( $id ) {

			$items = $this->MySQL->getCol('SELECT id FROM yapps_app_widgets_eh_items WHERE widget_id = ?i', $id);
			$this->MySQL->query('DELETE FROM yapps_app_widgets_eh_item_actions WHERE item_id IN (?a)', $items);
			$this->MySQL->query('DELETE FROM yapps_app_widgets_eh_items WHERE widget_id = ?i', $id);

			return true;
		}

		public function delEHItem( $id ) {

			$this->MySQL->query('DELETE FROM yapps_app_widgets_eh_item_actions WHERE item_id = ?i', $id);
			$this->MySQL->query('DELETE FROM yapps_app_widgets_eh_items WHERE id = ?i', $id);

			return true;
		}

		public function delEHItemAction( $id ) {

			$this->MySQL->query('DELETE FROM yapps_app_widgets_eh_item_actions WHERE id = ?i', $id);

			return true;
		}

		public function getEHItems( $id ) {

			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_eh_items WHERE widget_id = ?i', $id);
			foreach ( $res as $ki => $i ) {

				$res[$ki]['type'] = $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_eh_item_types WHERE id = ?i', $i['type_id']);
				$res[$ki]['actions'] = $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_eh_item_actions WHERE item_id = ?i', $i['id']);
				foreach ( $res[$ki]['actions'] as $ka => $a ) $res[$ki]['actions'][$ka]['type'] = $this->MySQL->getRow('SELECT * FROM yapps_app_widgets_eh_item_action_types WHERE id = ?i', $a['type_id']);
			}

			return $res;
		}

		public function getVueData( $user, $URL ) {

			if ( $URL == 'http://localhost:8080/' ) $URL = 'https://to.yug-avto.ru/';
			
			$host = parse_url( $URL)['host'];
			$land = $this->YApps_GetLandSettings( $this->YApps_GetLandIdByUrl($URL) );
			if ( $land ) $host = $this->YApps_GetSiteByID( $land['site_id'] )['url'];
			$settings = $this->getSettingsByHost( $host );
			if ( !$settings ) $settings = $this->getSettingsByShowroom( $URL );
			$site = $this->YApps_GetSiteByID($settings['site_id']);

			if ( $settings['active'] &&
				( !$land ||
					( $land && 
						( $land['use_lg'] || $land['use_cb'] || $land['use_nv'] || $land['use_ch'] || $land['use_av'] || $land['use_ht'] || $land['use_qz'] || $land['use_eh'] )
					)
				)
			) {

				$CB = $this->selectCB( $settings );
				$widget['CB'] = ( $CB ) ? $this->getWidgetById( $CB ) : false;
				if ( $widget['CB'] ) $widget['CB']['work_flag'] = $this->isTimeCB( $settings );
				
				$NV = $this->selectNV( $settings );
                $widget['NV'] = ( $NV ) ? $this->getWidgetById( $NV ) : false;

				$EH = $this->selectEH( $settings );
                $widget['EH'] = ( $EH ) ? $this->getWidgetById( $EH ) : false;
                
                $MS = $this->selectMS( $settings );
				$widget['MS'] = ( $MS ) ? $this->getWidgetById( $MS ) : false;
				
				$LG = $this->selectLG( $settings, $URL );
				$widget['LG'] = ( $LG ) ? $this->getWidgetById( $LG ) : false;
                if ( $widget['LG']['lg_timer_flag'] ) if ( $widget['LG']['lg_timer'] <= time() ) $widget['LG'] = false;
                if ( $widget['LG'] && !$this->isActiveWidget($widget['LG']['id']) ) $widget['LG'] = false;

				$AV = $this->selectAV( $settings, $URL );
                
                $CI = $this->selectCI( $settings, $URL );
				$widget['CI'] = ( $CI ) ? $this->getWidgetById( $CI ) : false;


				$res = [
					'Description' => 'Виджеты YApps разработаны для компании Юг-Авто. Разработчик - Борецкий Антон, boretscy@gmail.com',
					'Development' => ( $settings['site_id'] == 27 ) ? true : false,
					'Colors' => [
						'ColorBg' => ( $settings['color_bg'] ) ?: $this->Conf->Defaults->Vue->Colors->ColorBg,
						'ColorDarkBg' => ( $settings['color_darkbg'] ) ?: $this->Conf->Defaults->Vue->Colors->ColorDarkBg,
						'ColorFill' => ( $settings['color_fill'] ) ?: $this->Conf->Defaults->Vue->Colors->ColorFill,
						'ColorText' => ( $settings['color_text'] ) ?: $this->Conf->Defaults->Vue->Colors->ColorText,
						'ColorButton' => ( $settings['color_button'] ) ?: $this->Conf->Defaults->Vue->Colors->ColorButton,
						'ColorButtonText' => ( $settings['color_button_text'] ) ?: $this->Conf->Defaults->Vue->Colors->ColorButtonText,
						'ColorError' => ( $settings['color_error'] ) ?: $this->Conf->Defaults->Vue->Colors->ColorError,
						'ColorLightgray' => ( $settings['color_lightgray'] ) ?: $this->Conf->Defaults->Vue->Colors->ColorLightgray,
						'ColorMiddlegray' => ( $settings['color_middlegray'] ) ?: $this->Conf->Defaults->Vue->Colors->ColorMiddlegray,
						'ColorDarkgray' => ( $settings['color_darkgray'] ) ?: $this->Conf->Defaults->Vue->Colors->ColorDarkgray,
						'ColorShadow' => ( $settings['color_shadow'] ) ?: $this->Conf->Defaults->Vue->Colors->ColorShadow,
					],
					'Fonts' => $this->Conf->Defaults->Vue->Fonts,
	
					'CloseButtonText' => $this->Conf->Defaults->Vue->CloseButtonText,
					'BackButtonText' => $this->Conf->Defaults->Vue->BackButtonText,
					'ForwardButtonText' => $this->Conf->Defaults->Vue->ForwardButtonText,
	
					'Form' => [
						'Headers' => $this->Conf->Defaults->Vue->Form->Headers,
						
						'Return' => [
							'Success' => [
								'Status' => false,
								'Text' => ( $settings['form_success'] ) ?: $this->Conf->Defaults->Vue->Form->SuccessText,
							],
							'Error' => [
								'Status' => false,
								'Text' => ( $settings['form_error'] ) ?: $this->Conf->Defaults->Vue->Form->ErrorText
							],
							'Status' => false
						],
				
						'Button' => [
							'Text' => $this->Conf->Defaults->Vue->Form->ButtonText,
							'Pressed' => false,
						],
				
						'DelayedCall' => [
							'DelayedText' => ( $settings['form_text_delayed'] ) ?: $this->Conf->Defaults->Vue->Form->DelayedText,
							'NowText' => ( $settings['form_text_now'] ) ?: $this->Conf->Defaults->Vue->Form->NowText,
							'Status' => false,
							'Worktime' => [
								'Start' => (int)explode(':', $settings['cb_time_start'])[0],
								'End' => (int)explode(':', $settings['cb_time_end'])[0],
								'TomorrowText' => 'завтра',
								'TodayText' => 'сегодня',
								'Status' => true,
								'Times' => [],
								'Selected' => [
									'HourIndex' => 0,
									'MinutesIndex' => 0
								]
							],
						],
				
						'Terms' => [
							'Personal' => [
								'Status' => false,
								'Link' => $settings['term_personal'] ?: '#'
							],
							'Communications' => [
								'Status' => false,
								'Link' => $settings['term_communications'] ?: '#'
							]
						],
				
						// Send
						'SendData' => [
							
							'YandexID' => $site['yandex_id'],
							'GoogleID' => $site['google_id'],
							'MatomoID' => $site['piwik_id'],
							'Referrer' => null,
				
							'YandexVisitorID' => null,
							'GoogleVisitorID' => null,
							'MatomoVisitorID' => null,
				
							'SiteID' => $site['id'],
							'SourceLink' => null,
							'SourceTitle' => null,
				
							'Name' => null,
							'Phone' => null,
							'DelayedCall' => null,
				
							'AppName' => null,
							
							'CTSession' => $site['calltouch_sess'],
							'CTSiteID' => $site['calltouch_id'],
						],
						'Goals' => [
							'Yandex' => [
								'Now' => null,
								'Delayed' => null
							],
							'DataLayer' => [
								'event' => 'FormSubmission',
								'eventCategory' => null,
								'eventAction' => 'submit'
							],
							'Name' => null
						]
					],
				];

				// HELPER
				$res['Helper'] = [
					'CTSession' => $settings['calltouch_sess'],
					'ActiveInterval' => ( $settings['hp_active_interval'] ) ?: $this->Conf->Defaults->Vue->Helper->ActiveInterval,
					'IntervalID' => null,
					'Buttons' => [
						'Interval' => 30
					]
				];

				if ( $widget['EH'] &&
						( !$land ||
							( $land && $land['use_eh'] )
						)
					) {
					
					$res['Helper']['Buttons']['Items'][] =  [
						
						'Name' => ( $settings['hp_eh_use_startstop'] ) ? 'Startstop' : 'Question',
						'Content' => [
							'Status' => false,
							'Value' => '!',
							'Delay' => ( $settings['hp_eh_content_delay'] ) ?: $this->Conf->Defaults->Vue->Helper->ContentDelay,
							'HideOnClick' => true
						],
						'DescriptionText' => ( $settings['hp_eh_button'] ) ?: $this->Conf->Defaults->Vue->Helper->EHText,
						'Target' => 'EH',
						'Active' => false,
						'Type' => 'widget',
						'MobileOnly' => false
					];
				}

				if ( $settings['hp_use_avail'] ) {

					$res['Helper']['Buttons']['Items'][] = [

						'Name' => 'Car',
						'DescriptionText' => ( $settings['hp_av_button'] ) ?: $this->Conf->Defaults->Vue->Helper->AVText,
						'Target' => ( $settings['link_avail'] ) ?: '/',
						'Active' => false,
						'Type' => 'link',
						'MobileOnly' => false
					];
				}

				if ( $widget['NV'] &&
						( !$land ||
							( $land && $land['use_nv'] )
						)
					) {
						
					$res['Helper']['Buttons']['Items'][] = [

						'Name' => 'Map',
						'DescriptionText' => ( $settings['hp_nv_button'] ) ?: $this->Conf->Defaults->Vue->Helper->NVText,
						'Target' => 'NV',
						'Active' => false,
						'Type' => 'widget',
						'MobileOnly' => false
					];
				}

				$res['Helper']['Buttons']['Items'][] = [
					'Name' => 'Call',
					'DescriptionText' => ( $settings['hp_cb_out_button'] ) ?: $this->Conf->Defaults->Vue->Helper->CallOutText,
					'Target' => 'tel:'.Helper::formatPhoneIn($this->YApps_GetDCsBySite($site['id'])['phone']),
					'Active' => false,
					'Type' => 'link',
					'MobileOnly' => true
				];


				$res['Widgets']['CoverShow'] = false;

				// CB
				if ( $widget['CB'] &&
						( !$land ||
							( $land &&  $land['use_cb'] )
						)
					) {
					
					$res['Widgets']['Items']['CB'] = [
						'Id' => $widget['CB']['id'],
						'Type' => $this->getTypeById( $widget['CB']['type_id'] ),
						'Status' => false,
						'Title' => $widget['CB']['cb_title_prologue'],
						'Text' =>  $widget['CB']['cb_text'],
						'Time' => $settings['cb_timer_await'],
						'Timer' => [
							'ID' => null,
							'Status' => false,
							'Seconds' => null,
							'DSeconds' => null
						],
						'Timeouts' => false,
						'IDToneCategory' => 'callback'
						
					];
				}

				// CI
				if ( $widget['CI'] &&
						( !$land ||
							( $land &&  $land['use_ci'] )
						)
					) {
					
					$res['Widgets']['Items']['CI'] = [
						'Id' => $widget['CI']['id'],
						'Type' => $this->getTypeById( $widget['CI']['type_id'] ),
						'Status' => false,

						'Content' => [
							'List' => [
								'title' => str_replace(
									'{{RANDOM}}',
									rand($widget['CI']['ci_list_random_min'], $widget['CI']['ci_list_random_max']),
									$widget['CI']['ci_list_title']
								),
								'text' => str_replace(
									'{{RANDOM}}',
									rand($widget['CI']['ci_list_random_min'], $widget['CI']['ci_list_random_max']),
									$widget['CI']['ci_list_text']
								),
								'level' => $settings['ci_level_list']
							],
							'Model' => [
								'title' => str_replace(
									'{{RANDOM}}',
									rand($widget['CI']['ci_model_random_min'], $widget['CI']['ci_model_random_max']),
									$widget['CI']['ci_model_title']
								),
								'text' => str_replace(
									'{{RANDOM}}',
									rand($widget['CI']['ci_model_random_min'], $widget['CI']['ci_lmodel_random_max']),
									$widget['CI']['ci_model_text']
								),
								'level' => $settings['ci_level_model']
							],
							'Item' => [
								'title' => str_replace(
									'{{RANDOM}}',
									rand($widget['CI']['ci_item_random_min'], $widget['CI']['ci_item_random_max']),
									$widget['CI']['ci_item_title']
								),
								'text' => str_replace(
									'{{RANDOM}}',
									rand($widget['CI']['ci_item_random_min'], $widget['CI']['ci_item_random_max']),
									$widget['CI']['ci_item_text']
								),
								'level' => $settings['ci_level_item']
							],
						],
						'PathDetector' => [
							'path' => null,
							'content' => 'List',
							'level' => null
						],

						'Timeouts' => [
							$settings['ci_timeout_1'],
							$settings['ci_timeout_2']*60
						],
						'TimeoutID' => null,
						'Cookies' => [
							'Status' => true,
							'Name' => 'YApps_Widgets--CI',
						],
						'IDToneCategory' => 'special'
						
					];
				}

				// LG
				if ( $widget['LG'] &&
						( !$land ||
							( $land &&  $land['use_lg'] )
						)
					) {
					
					$res['Widgets']['Items']['LG'] = [
						'Id' => $widget['LG']['id'],
						'Type' => $this->getTypeById( $widget['LG']['type_id'] ),
						'Status' => false,
						'Image' => [
							'Url' => $widget['LG']['lg_image'],
							'Position' => 'right'
						],
						'Scheme' => [
							'Image' => [
								'Width' => 600,
								'Height' => 600
							],
							'Content' => [
								'Width' => 400,
								'Height' => 600
							]
						],
						'Title' => $widget['LG']['lg_title'],
						'Text' => $widget['LG']['lg_text'],
						'Timer' => [
							'ID' => null,
							'Status' => (boolean)$widget['LG']['lg_timer_flag'],
							'Text' => $widget['LG']['lg_timer_description'],
							// 'Time' => [
							// 	'd' => 16,
							// 	'h' => 5,
							// 	'm' => 38,
							// 	's' => 12
							// ],
							'Time' => null,
							'Separator' => ':'
						],
						'Timeouts' => [
							$settings['lg_show_timeout'],
							$settings['lg_second_timeout']*60
						],
						'TimeoutID' => null,
						'Cookies' => [
							'Status' => true,
							'Name' => 'YApps_Widgets--LG',
						]
					];
					if ( $widget['LG']['lg_timer_flag'] ) $res['Widgets']['Items']['LG']['Timer']['Time'] = Helper::shortTimeout( $widget['LG']['lg_timer'] );
				}

				// MS
				if ( $widget['MS'] &&
						( !$land ||
							( $land &&  $land['use_ms'] )
						)
					) {
					
					$res['Widgets']['Items']['MS'] = [
						'Id' => $widget['MS']['id'],
						'Type' => $this->getTypeById( $widget['MS']['type_id'] ),
						'Status' => false,
						'Title' => $widget['MS']['ms_title'],
						'Text' => $widget['MS']['ms_text'],
						'Timeouts' => false
					];

					foreach ( $this->getMessengersByWidget($widget['MS']['id']) as $item ) {

						$res['Widgets']['Items']['MS']['Items'][] = [
							
							'YandexGoal' => $item['goal'],
							'Url' => str_replace('%%WIDGET.MS.VALUE%%', $item['value'], $item['url_scheme']),
							'Name' => $item['ru_name']
						];
					}
				}

				//NV
				if ( $widget['NV'] &&
						( !$land ||
							( $land &&  $land['use_nv'] )
						)
					) {
					
					$res['Widgets']['Items']['NV'] = [
						'Id' => $widget['NV']['id'],
						'Type' => $this->getTypeById( $widget['NV']['type_id'] ),
						'Status' => false,
						'Title' => $widget['NV']['nv_title'] ?:  $this->Conf->Defaults->NVTitle,
						'Settings' => [
							'apiKey' => '34ddb940-0941-4b80-ab80-b0aa351b6560',
							'lang' => 'ru_RU',
							'coordorder' => 'latlong',
							'version' => '2.1'
						],
						'Route' => null,
						'Timeouts' => false,
					];

					foreach ( $this->YApps_GetDCsBySite($settings['site_id']) as $item ) {

						$res['Widgets']['Items']['NV']['Items'][] = [
							'Title' => $item['ru_name'],
							'Coords' => [$item['coords_lat'], $item['coords_lon']],
							'Address' => $item['address'],
							'Working' => $item['feeds_working'],
							'Routed' => false,
							'Phone' => Helper::formatPhoneIn($item['phone'])
						];
					}

					if ( $navis = $this->getNavigatorsBySite($settings['site_id']) ) {
						
						$res['Widgets']['Items']['NV']['SecondView'] = [
							'Status' => false,
							'Title' => $widget['NV']['nv_second_title'] ?:  $this->Conf->Defaults->NVSecondTitle,
							'Text' => $widget['NV']['nv_second_text'] ?:  $this->Conf->Defaults->NVSecondText,
							'Items' => []
						];

						foreach ( $navis as $item ) $res['Widgets']['Items']['NV']['SecondView']['Used'][] = $item['name'];
					}
				}

				//EH
				if ( $widget['EH'] &&
						( !$land ||
							( $land && $land['use_eh'] )
						)
					) {

					$res['Widgets']['Items']['EH'] = [
						'Id' => $widget['EH']['id'],
						'Type' => $this->getTypeById( $widget['EH']['type_id'] ),
						'Status' => false,
						'Title' => ( $widget['EH']['eh_title'] ) ?: $this->Conf->Defaults->Vue->Helper->EHTitle,
						'Social' => [
							'Text' => ( $widget['EH']['eh_social_text'] ) ?: $this->Conf->Defaults->EHSocialText,
							'Items' => [
								'youtube' => ( $widget['EH']['eh_youtube'] ) ?: $this->Conf->Defaults->Vue->Helper->EHSocialItems->Youtube,
								'instagram' => ( $widget['EH']['eh_instagram'] ) ?: $this->Conf->Defaults->Vue->Helper->EHSocialItems->Instagram,
								'facebook' => ( $widget['EH']['eh_facebook'] ) ?: $this->Conf->Defaults->Vue->Helper->EHSocialItems->Facebook,
								'vkontakte' => ( $widget['EH']['eh_vkontakte'] ) ?: $this->Conf->Defaults->Vue->Helper->EHSocialItems->Vkontakte,
							]
						],
						'BackStatus' => false,
						'Timeouts' => false
					];

					foreach ( $widget['EH']['items'] as $item ) {

						if ( $item['type']['url_key'] == 'involv' ) $item['text'] = str_replace('{{RANDOM}}', rand($widget['CI']['ci_item_random_min'], $widget['CI']['ci_list_random_max']), $item['text']); 

						$eh_item = [
							'ID' => $item['id'],
							'Status' => false,
							'Type' => $item['type']['url_key'],
							'Indx' => $item['eh_key'],
							'Text' => $item['text'],
							'Value' => $item['value'],
							'Inited' => [
								'Status' => (bool)$item['inited_status'],
								'Delay' => $item['inited_delay']
							],
							'Cookie' => [
								'Status' => (bool)$item['cookie_status'],
								'Name' => $item['cookie_name'],
								'Count' => (int)$item['cookie_count']
							],
							'blank' => (bool)$item['blank']
						];
						if ( $item['actions'] ) {

							foreach ( $item['actions'] as $action ) {

								if (
									$this->isShutdownBySite( $settings['site_id'] ) &&
									(
										$action['type']['url_key'] == 'step' ||
										(
											$action['type']['url_key'] == 'widget' && $action['value'] == 'LG'
										)
										||
										(
											$action['type']['url_key'] == 'widget' && $action['value'] == 'CB'
										)
										||
										(
											$action['type']['url_key'] == 'widget' && $action['value'] == 'MS'
										)
										||
										(
											$action['type']['url_key'] == 'widget' && $action['value'] == 'CI'
										)
									)
								) { } else {

									$eh_item['items'][] = [
										'type' => 'button',
										'text' => $action['text'],
										'action' => $action['type']['url_key'],
										'value' => $action['value'],
										'blank' => (bool)$action['blank']
									];
								}
								
							}
						}

						$res['Widgets']['Items']['EH']['Items'][] = $eh_item;
					}
				}
	
			}

			return $res;
		}

		public function getVueScript( $user, $URL ) {

			$script = '';

			$host = parse_url( $URL )['host'];
			$land = $this->YApps_GetLandSettings( $this->YApps_GetLandIdByUrl($URL) );
			if ( $land ) $host = $this->YApps_GetSiteByID( $land['site_id'] )['url'];
			$settings = $this->getSettingsByHost( $host );

			// isShutdownBySite

			// Helper::sp($settings);
			
			if ( !$settings ) $settings = $this->getSettingsByShowroom( $URL );
			
			if ( $settings['active'] &&
				( !$land ||
					( $land && 
						( $land['use_lg'] || $land['use_cb'] || $land['use_nv'] || $land['use_ch'] || $land['use_av'] || $land['use_ht'] || $land['use_qz'] || $land['use_eh'] )
					)
				)
			) {

				foreach ( glob($_SERVER['DOCUMENT_ROOT'].$this->Conf->FrontendDir.'/dist/js/*.js') as $file ) {
					
					$script .= file_get_contents($file).PHP_EOL;
					$arF = explode('/', $file);
					$script .= '//@ sourceMappingURL='.$this->Conf->FrontendDir.'/dist/js/'.$arF[count($arF)-1].'.map';
				}
			}

			return $script;
		}

		public function setOldEvent( $POST, $url, $ip ) {
            
            $site = (array)$this->YApps_GetSiteByHost( parse_url($url)['host'] );

			if ( $site ) {

                $ids = [
                    'piwik_visitorId' => explode('.', $POST['PiwikVisitorID'])[0],
                    'yandex_visitorId' => $POST['YandexVisitorID'],
                    'google_visitorId' => explode('.', $POST['GoogleVisitorID'])[2].'.'.explode('.', $POST['GoogleVisitorID'])[3]
                ];

                $st_data = [
                    'widget_id' => 0,
					'type_id' => 0,
					'user_id' => 0,
                    'site_id' => $site['id'],
					'phone' => Helper::formatPhoneIn( $POST['Phone'] ),
                    'source_title' => ( $POST['SourceTitle'] ) ?: '',
                    'source_url' => $POST['SourceLink'],
                    'event_name' => 'Виджет '.$POST['EventCategory'].': Посетитель оставил контакные данные',
                    'referrer' => $url,
					'visitorIP' => $ip,
                    'timestamp' => time(),
                ];
				if ( $POST['Name'] ) $st_data['name'] = $POST['Name'];

                $st_data = array_merge( $st_data, $ids );
                if ( $utms = Helper::getUtm( $url ) ) $st_data = array_merge( $st_data, $utms );
                
                $lastId = $this->addStat( $st_data );

                $cl_data = [
                    'phone' => Helper::formatPhoneIn( $POST['Phone'] ),
                    'url' => $url,
                    'event' => 'Виджет '.$POST['EventCategory'].': Посетитель оставил контакные данные',
                    'stat_id' => $lastId,
                    'app_id' => $this->AppInfo()->id,
                    'site_id' => $site['id'],
                    'referrer' => $url,
                    'user_agent' => $POST['user_agent']
                ];

                if ( $POST['Name'] ) $cl_data['name'] = $POST['Name'];
                
                $cl_data = array_merge( $cl_data, $utms );

                $geo = Helper::getGeo( $ip );

                $this->YApps_PushClient( $cl_data, $ids, $geo );

			} // if
		}
        
        
		private function addStat( $data ) {

            $this->MySQL->query('INSERT INTO yapps_app_widgets_stat SET ?u', $data);
            return $this->MySQL->insertId();
        }
		
		public function pushStat( $POST, $user, $ip ) {
			
			if ( $POST['new'] ) return Helper::getRes(0);

			$host = parse_url( $POST['SourceLink'] )['host'];
			
			$land = $this->YApps_GetLandSettings( $this->YApps_GetLandIdByUrl($POST['SourceLink']) );
			if ( $land ) $host = $this->YApps_GetSiteByID( $land['site_id'] )['url'];
			
			
			$settings = $this->getSettingsByHost( $host );
			if ( !$settings ) $settings = $this->getSettingsByShowroom( $POST['SourceLink'] );
			
			if ( $settings['active'] && Helper::isNotFakePhone($POST['Phone']) ) {
				
				$widget = $this->getWidgetById( $POST['Id'] );
				if ( $ABTest = $this->ABTest($widget['id']) ) $this->setABRes($ABTest);
				
				$ids = [
                    'piwik_visitorId' => explode('.', $POST['PiwikVisitorID'])[0],
                    'yandex_visitorId' => $POST['YandexVisitorID'],
                    'google_visitorId' => explode('.', $POST['GoogleVisitorID'])[2].'.'.explode('.', $POST['GoogleVisitorID'])[3]
                ];
				
				$st_data = [
					'widget_id' => $widget['id'],
					'type_id' => $widget['type_id'],
                    'user_id' => $user->id,
                    'site_id' => (int)$POST['SiteID'],
					'phone' => Helper::formatPhoneIn( $POST['Phone'] ),
                    'source_title' => $POST['SourceTitle'],
                    'source_url' => $POST['SourceLink'],
                    'event_name' => 'ID: '.$widget['id'].' | '.$this->getTypeById($widget['type_id'])['ru_name'].' | '.$POST['EventAction'],
                    'timestamp' => time(),
                    'referrer' => $POST['Referrer'],
                    'visitorIP' => $ip,
                ];
				if ( $POST['Name'] ) $st_data['name'] = $POST['Name'];
				
				$st_data = array_merge( $st_data, $ids );
                if ( $utms = Helper::getUtm( $POST['SourceLink'] ) ) $st_data = array_merge( $st_data, $utms );
				
				$lastId = $this->addStat( $st_data );
				
				if ( $widget['type_id'] == 7 ) {
					
					$qz = [];
					
					foreach( $widget['slides'] as $slide ) {
						
						$qz_stat = [];
						$qz_stat['stat_id'] = $lastId;
						
						if ( $slide['type_id'] == 4 ) {
							
							$q = [
								'slide_id' => $slide['id'],
								'slide_name' => $slide['ru_name']
							];
							
							foreach( $slide['items'] as $item ) {
								
								if ( $POST['Slide_'.$slide['id'].'__Item_'.$item['id']] ) {
									
									$qz_stat['slide_id'] = $slide['id'];
									$qz_stat['slide_name'] = $slide['ru_name'];
									$qz_stat['item_id'] = $item['id'];
									$qz_stat['item_name'] = $item['value'];
									$qz_stat['item_value'] = $POST['Slide_'.$slide['id'].'__Item_'.$item['id']];
									
									$q['items'][] = $qz_stat;
									
									$this->MySQL->query('INSERT INTO yapps_app_widgets_qz_stat SET ?u', $qz_stat);
								}
							}
							
						} else {
							
							if ( $POST['Slide_'.$slide['id']] ) {
								
								
								
								$q = [
									'slide_id' => $slide['id'],
									'slide_name' => $slide['ru_name']
								];
								
								if ( $slide['type_id'] == 3 ) {
									
									$qz_stat['slide_id'] = $slide['id'];
									$qz_stat['slide_name'] = $slide['ru_name'];
									
									foreach( json_decode(str_replace('&quot;', '"', $POST['Slide_'.$slide['id']]), true) as $item ) {
										
										$qz_stat['item_id'] = (int)explode('_', $item)[1];
										$qz_stat['item_value'] = $slide['items'][$qz_stat['item_id']]['value'];
										
										$q['items'][] = $qz_stat;
										
										$this->MySQL->query('INSERT INTO yapps_app_widgets_qz_stat SET ?u', $qz_stat);
									}
									
								} else {
									
									$qz_stat['slide_id'] = $slide['id'];
									$qz_stat['slide_name'] = $slide['ru_name'];
									$qz_stat['item_id'] = (int)explode('_', $POST['Slide_'.$slide['id']])[1];
									$qz_stat['item_value'] = $slide['items'][$qz_stat['item_id']]['value'];
									
									$this->MySQL->query('INSERT INTO yapps_app_widgets_qz_stat SET ?u', $qz_stat);
									
									$q['items'][] = $qz_stat;
								}
							}
						}
						
						$qz[] = $q;
					}
				}
				
				
				$cl_data = [
                    'phone' => Helper::formatPhoneIn( $POST['Phone'] ),
                    'url' => $POST['SourceLink'],
                    'event' => 'Виджет '.$POST['EventCategory'].': Посетитель оставил контакные данные',
                    'stat_id' => $lastId,
                    'app_id' => $this->AppInfo()->id,
                    'site_id' => (int)$POST['SiteID'],
                    'referrer' => $POST['Referrer']
                ];

                if ( $POST['Name'] ) $cl_data['name'] = $POST['Name'];
                
                $cl_data = array_merge( $cl_data, $utms );

                $geo = Helper::getGeo( $ip );

                $this->YApps_PushClient( $cl_data, $ids, $geo );
				
				$send_data = [
					'widget' => $widget,
					'settings' => $settings,
					'site_id' => (int)$POST['SiteID'],
                    'source_title' => $POST['SourceTitle'],
                    'source_url' => $POST['SourceLink'],
					'event_name' => 'ID: '.$widget['id'].' | '.$this->getTypeById($widget['type_id'])['ru_name'].' | '.$POST['EventAction'],
					'phone' => Helper::formatPhoneIn( $POST['Phone'] ),
					'text' => $POST['DelayedCall']
				];
				if ( $POST['Name'] ) $send_data['name'] = $POST['Name'];
				if ( $POST['DateTime'] ) $send_data['datetime'] = $POST['DateTime'];
				if ( $POST['EventType'] == 'QZ' ) $send_data['quiz'] = $qz;

				$this->sendForm( $send_data );
			}
			
			return Helper::getRes(0);
		}
		
		public function getStats( $user, $date1, $date2 ) {

			$sites = $this->YApps_GetUserSiteIDs($user);

			return $this->MySQL->getAll('SELECT * FROM yapps_app_widgets_stat WHERE site_id IN (?a) AND timestamp >= ?i AND timestamp < ?i ORDER BY id DESC', $sites, strtotime($date1), strtotime($date2));
		}
		
		public function getUserWidgets( $user ) {
			
			$sites = $this->YApps_GetUserSiteIDs($user);
			return $this->MySQL->getAll('SELECT id, ru_name FROM yapps_app_widgets WHERE site_id IN (?a)', $sites);
		}
		
        
	}