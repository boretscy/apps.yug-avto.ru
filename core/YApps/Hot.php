<?php

	class Hot extends App {

		public function __construct( $arConf, SafeMySQL &$mysql, $mssql = false, PHPMailer &$mailer ) {

			$this->MySQL		= &$mysql;
			$this->Conf		= (object)$arConf['modules'][get_class($this)];
			$this->Mailer	= &$mailer;
			$this->Yandex	= (object)$arConf['App']['Yandex'];
		}

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
			$this->Mailer->setFrom('alert@apps.yug-avto.ru', 'Оповещения Юг-Авто Apps');
			$this->Mailer->ClearAddresses();

			$sets = $this->getSettingsById( $arIns['site_id'] );
			$site = $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE id = ?i', (int)$arIns['site_id']);
			$item = $this->getItem( $arIns['item_id'] );
			$dc = $this->MySQL->getRow('SELECT * FROM yapps_dcs WHERE id = ?i', $item['dc_id']);
			$model = $this->getModel( $item['model_id'] );

			$arRec = array_merge( preg_split('/[\s,;]+/', $dc['recipients']), preg_split('/[\s,;]+/', $sets['recipients']));
			foreach ( array_unique($arRec) as $email ) $this->Mailer->addAddress($email, '');
			
			// $this->Mailer->addAddress('anton.boreckiy@yug-avto.ru', '');

			$this->Mailer->Subject = 'Сайт: '.$site['ru_name'].'. Горячие предложения месяца. Заявка на бронь автомобиля '.$model['ru_name'].' '.$item['complectation'];

			$message = '<h3>Посетитель</h3>';
			$message .= 'Имя: '.$arIns['name'].'<br />';
			$message .= 'Телефон: '.(($arIns['phone'])?Helper::formatPhoneOut($arIns['phone']):'').'<br />';
			$message .= '<br /><br />';
			$message .= '<h3>Автомобиль</h3>';

			$message .= 'Модель: '.$model['ru_name'].'<br />';
			$message .= 'Комплектация: '.$item['complectation'].'<br />';
			$message .= 'Год выпуска: '.$item['year'].'<br />';
			$message .= 'Двигатель '.$item['engine_volume'].' см3, '.$item['engine_power'].' л.с., '.$item['engine_type'].'<br />';
			$message .= 'КПП: '.$item['gearbox'].'<br />';
			$message .= 'Цвет: '.$item['color'].'<br />';
			$message .= 'VIN: '.$item['vin'].'<br />';
			$message .= 'Дилерский центр: '.$dc['ru_name'].'<br /><br />';

			$message .= 'Цена: '.number_format((float)$item['price'], 0, '', ' ').' ₽<br />';
			$message .= 'Цена со скидкой: '.number_format((float)$item['spec_price'], 0, '', ' ').' ₽<br /><br /><br /><br />';

			$message .= 'Страница-источник: <a href="'.$arIns['source_url'].'" target="_blank">'.$arIns['source_title'].'</a>';

			$this->Mailer->msgHTML($message);
			return $this->Mailer->Send();
		}


		///////////////////////////////////////////////////////////////////////////////////////////
        // Models Area ////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function getModels( $user ) {

			$user_sites = $this->MySQL->getCol('SELECT site_id FROM yapps_users_sites WHERE user_id = ?i', (int)$user->id);
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_hot_models WHERE site_id IN (?a) ORDER BY ru_name', $user_sites);

			return $res;
		}

		public function getModel( $id ) {

			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_hot_models WHERE id = ?i', (int)$id);

			return ( $res ) ? $res : false;
		}

		public function setModel( $POST, $FILES = [] ) {

			$arIns = $POST;
			unset( $arIns['id'], $arIns['form'], $arIns['image'] );
			
			$model = $this->YApps_GetModel( $arIns['model_id'] );
			
			$arIns['url_key'] = $model['url_key'];
			$arIns['ru_name'] = $model['ru_name'];
			
			$site_dir = $_SERVER['DOCUMENT_ROOT'].$this->Conf->FileDir.'/'.$POST['site_id'];
			$dir = $_SERVER['DOCUMENT_ROOT'].$this->Conf->FileDir.'/'.$POST['site_id'].'/'.str_replace(' ', '_', trim($model['url_key']));
			
			if ( !file_exists($site_dir) ) mkdir($site_dir);
			if ( !file_exists($dir) ) mkdir($dir);
			
			if ( $FILES && $FILES['image']['error'] == 0 ) {

				$arIns['image_path'] = $this->Conf->FileDir.'/'.$POST['site_id'].'/'.str_replace(' ', '_', trim(mb_strtolower($POST['url_key']))).'/'.$FILES['image']['name'];
				$arIns['image_link'] = 'https://apps.yug-avto.ru'.$this->Conf->FileDir.'/'.$POST['site_id'].'/'.str_replace(' ', '_', trim($POST['url_key'])).'/'.$FILES['image']['name'];
				$file = $_SERVER['DOCUMENT_ROOT'].$arIns['image_path'];
                move_uploaded_file( $FILES['image']['tmp_name'], $file );
			
			} else {
				
				$arIns['image_link'] = $this->YApps_GetModel( $arIns['model_id'] )['photo'];
			}

			if ( $POST['id'] ) {

				if ( $FILES && $FILES['image']['error'] == 0 ) unlink( $_SERVER['DOCUMENT_ROOT'].$this->MySQL->getOne('SELECT image_path FROM yapps_app_hot_models WHERE id = ?i', (int)$POST['id']) );
				$this->MySQL->query('UPDATE yapps_app_hot_models SET ?u WHERE id = ?i', $arIns, (int)$POST['id']);

			} else {

				$this->MySQL->query('INSERT INTO yapps_app_hot_models SET ?u', $arIns);
			}

			return Helper::getRes(0);
		}

		public function delModel( $id ) {

			unlink( $_SERVER['DOCUMENT_ROOT'].$this->getModel( $id )['image'] );
			return $this->MySQL->query('DELETE FROM yapps_app_hot_models WHERE id = ?i', (int)$id);
		}





		///////////////////////////////////////////////////////////////////////////////////////////
        // Import Area ////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function resizeImage( $in, $out ) {

			$im = imagecreatefromjpeg( $in );

			$k1 = $this->Conf->Defaults['ImageWidth']/imagesx($im);
			$k2 = $this->Conf->Defaults['ImageHeight']/imagesy($im);
			$k = ( $k1 > $k2 ) ? $k2 : $k1;

			$w = (int)imagesx($im)*$k;
			$h = (int)imagesy($im)*$k;

			$im1 = imagecreatetruecolor( $w, $h );

			imagecopyresampled($im1,$im,0,0,0,0,$w,$h,imagesx($im),imagesy($im));

			imagejpeg($im1,$out,75);
			imagedestroy($im);
			imagedestroy($im1);

			return true;
		}

		public function importCSV( $POST, $FILES ) {
			
			if (  $FILES && $FILES['import']['error'] == 0 ) {

				$zip = new ZipArchive;

				if ($zip->open($FILES['import']['tmp_name']) === TRUE) { // Если файл загружен и он .zip
					
					$tmp_dir = $_SERVER['DOCUMENT_ROOT'].'/upload/Hot/Zip/'.md5( time().'___'.$POST['dc_id'] );
					mkdir( $tmp_dir );
					
					$zip->extractTo( $tmp_dir );
					$zip->close();
					
					$arIns['dc_id'] = (int)$POST['dc_id'];
					$arIns['site_id'] = (int)$this->MySQL->getOne('SELECT site_id FROM yapps_dcs WHERE id = ?i', (int)$POST['dc_id']);

					if ( file_exists($tmp_dir.'/hot.csv') ) {
                        
                        if ( $_POST['delete_current'] == 'on' ) {
						
                            $delIds = $this->MySQL->getCol('SELECT id FROM yapps_app_hot_items WHERE dc_id = ?i', (int)$POST['dc_id']);
                            foreach ( $delIds as $delID ) $this->delItem( $delID );
                        }

						// CSV Arr
						$handle = fopen($tmp_dir.'/hot.csv', "r");
						while ( ($line = fgetcsv($handle, 0, ";") ) !== FALSE) {
							array_shift( $line );
							$arCSV[] = $line;
						}
						fclose($handle);
						
						$delModels = [];
						for ($i = 0; $i < count($arCSV[0]); $i++) {

							$paramCount = count($arCSV);
							
							if ( !$model_id = (int)$this->YApps_searchModelIdByString( iconv('WINDOWS-1251', 'UTF-8', trim($arCSV[0][$i])) ) ) continue;
                            if ( !$arModel = $this->MySQL->getRow('SELECT * FROM yapps_app_hot_models WHERE model_id = ?i AND site_id = ?i', $model_id, $arIns['site_id']) ) continue;

							// Helper::sp($arModel);
							
							$arIns['year']			= (int)iconv('WINDOWS-1251', 'UTF-8', $arCSV[1][$i]);
							$vol = str_replace(',', '.', iconv('WINDOWS-1251', 'UTF-8', $arCSV[2][$i]));
							$vol = (float)str_replace(' ', '', $vol);
							if ($vol > 100 ) $vol = $vol / 1000;
							$arIns['engine_volume'] = $vol;
							$arIns['engine_power'] 	= (float)iconv('WINDOWS-1251', 'UTF-8', $arCSV[3][$i]);
							$arIns['engine_type'] 	= iconv('WINDOWS-1251', 'UTF-8', $arCSV[4][$i]);
							$arIns['gearbox'] 		= iconv('WINDOWS-1251', 'UTF-8', $arCSV[5][$i]);
							$arIns['color'] 		= iconv('WINDOWS-1251', 'UTF-8', $arCSV[6][$i]);
							$arIns['price'] 		= iconv('WINDOWS-1251', 'UTF-8', (float)preg_replace("/[^0-9]/", '', $arCSV[7][$i]));
							$arIns['spec_price'] 	= iconv('WINDOWS-1251', 'UTF-8', (float)preg_replace("/[^0-9]/", '', $arCSV[8][$i]));
							$arIns['complectation'] = iconv('WINDOWS-1251', 'UTF-8', $arCSV[9][$i]);
							$arIns['vin'] 			= iconv('WINDOWS-1251', 'UTF-8', $arCSV[10][$i]);

							
							$delModels[] = $arIns['model_id'] = (int)$arModel['id'];

							$arIns['additional_list'] = '<ul>';
							$arIns['complect_list'] = '<ul>';
							$toContent = 'complect_list';

							for ($p = 11; $p < $paramCount; $p++) {

								if ( iconv('WINDOWS-1251', 'UTF-8', $arCSV[$p][$i]) == 'DOP' ) {

									$toContent = 'additional_list';

								} else {

									if (iconv('WINDOWS-1251', 'UTF-8', $arCSV[$p][$i])) $arIns[$toContent] .= '<li>'.iconv('WINDOWS-1251', 'UTF-8', $arCSV[$p][$i]).'</li>';
								}
							}

							$arIns['additional_list'] .= '</ul>';
							$arIns['complect_list'] .= '</ul>';

							$arFiles = glob( $tmp_dir.'/'.$arIns['vin'].'*' );
							
							if ( !empty($arFiles) ) {
								
								$arInsF = [];
	
								// Make dir for Items
								if ( !file_exists( $_SERVER['DOCUMENT_ROOT'].$this->Conf->FileDir.'/'.$arModel['site_id'].'/'.$arModel['url_key'].'/Items' ) )
									mkdir( $_SERVER['DOCUMENT_ROOT'].$this->Conf->FileDir.'/'.$arModel['site_id'].'/'.$arModel['url_key'].'/Items' );
									
								if ( !file_exists( $_SERVER['DOCUMENT_ROOT'].$this->Conf->FileDir.'/'.$arModel['site_id'].'/'.$arModel['url_key'].'/Items/'.$arIns['vin'] ) )
									mkdir( $_SERVER['DOCUMENT_ROOT'].$this->Conf->FileDir.'/'.$arModel['site_id'].'/'.$arModel['url_key'].'/Items/'.$arIns['vin'] );
	
								foreach ($arFiles as $file) {
	
									$filename = explode('/', $file);
									$outFile = $this->Conf->FileDir.'/'.$arModel['site_id'].'/'.$arModel['url_key'].'/Items/'.$arIns['vin'].'/'.$filename[count($filename)-1];
									if ( file_exists( $_SERVER['DOCUMENT_ROOT'].$outFile ) ) unlink( $_SERVER['DOCUMENT_ROOT'].$outFile );
	
									if ( Helper::checkImageSize($file, $this->Conf->Defaults['ImageWidth'], $this->Conf->Defaults['ImageHeight']) ) {
	
										copy( $file, $_SERVER['DOCUMENT_ROOT'].$outFile );
	
									} else {
	
										$this->resizeImage( $file, $_SERVER['DOCUMENT_ROOT'].$outFile);
									}
	
									$arInsF[] = $outFile;
								}
                                
                                // проверка на vin
                                if ( $cur_item = $this->MySQL->getRow('SELECT * FROM yapps_app_hot_items WHERE vin = ?s', $arIns['vin']) ) {
                                    
                                    $this->MySQL->query('UPDATE yapps_app_hot_items SET ?u WHERE id = ?i', $arIns, $cur_item['id']);
                                    $ItemsIDs[] = $ItemId = $cur_item['id'];

                                } else {
                                    
                                    $this->MySQL->query('INSERT INTO yapps_app_hot_items SET ?u', $arIns);
                                    $ItemsIDs[] = $ItemId = $this->MySQL->insertId();
                                }
                                
                                // Для импорта без проверки на vin
                                // $this->MySQL->query('INSERT INTO yapps_app_hot_items SET ?u', $arIns);
                                // $ItemsIDs[] = $ItemId = $this->MySQL->insertId();
	
								foreach ( $arInsF as $f ) $this->MySQL->query('INSERT INTO yapps_app_hot_items_images SET ?u', ['item_id'=>(int)$ItemId, 'url'=>$f]);
								
							} // if
						} // for
/*
						$delItemsIDs = $this->MySQL->getCol(
							'SELECT id FROM yapps_app_hot_items WHERE id NOT IN (?a) AND model_id = ?i AND dc_id = ?i',
							$ItemsIDs, $arModel['id'], (int)$POST['dc_id']);

						foreach ( $delItemsIDs as $delID ) $this->delItem( $delID );
*/
						$res = Helper::getRes(0);

					} else {

						Helper::rmDir( $tmp_dir );
						$res = Helper::getRes(51);
						$res->description .= ' ------ csv Error';
					}

				} else {

					Helper::rmDir( $tmp_dir );
					$res = Helper::getRes(51);
					$res->description .= ' ------ Zip Error';
				}

			} else {

                Helper::rmDir( $tmp_dir );
                $res = Helper::getRes(51);
				$res->description .= ' ------ File Error';
			}

			if ( $tmp_dir ) Helper::rmDir( $tmp_dir );
			return $res;
		}



		///////////////////////////////////////////////////////////////////////////////////////////
        // Items Area /////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function getItems( $user, $site_id ) {

			$user_sites = $this->MySQL->getCol('SELECT site_id FROM yapps_users_sites WHERE user_id = ?i', (int)$user->id);

			$query = 'SELECT * FROM yapps_app_hot_items WHERE site_id ';
			$query .= ( $site_id == 'All' ) ? 'IN (?a)' : '= ?i';

			$res = $this->MySQL->getAll($query, ($site_id=='All')?$user_sites:(int)$site_id);

			foreach ( $res as $k => $i ) {

				$res[$k]['images'][0] = $this->MySQL->getRow('SELECT * FROM yapps_app_hot_items_images WHERE item_id = ?i', $i['id']);
				$res[$k]['settings'] = $this->MySQL->getRow('SELECT * FROM yapps_app_hot_settings WHERE site_id = ?i', $res['site_id']);
			}

			return $res;
		}

		public function getItem( $id ) {

			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_hot_items WHERE id = ?i', (int)$id);
			$res['settings'] = $this->MySQL->getRow('SELECT * FROM yapps_app_hot_settings WHERE site_id = ?i', $res['site_id']);

			return $res;
		}

		public function getItemByVIN( $vin ) {

			$res = $this->MySQL->getRow('SELECT * FROM yapps_app_hot_items WHERE vin = ?s', $vin);
			$res['images'] = $this->MySQL->getAll('SELECT * FROM yapps_app_hot_items_images WHERE item_id = ?i', $res['id']);
			$res['settings'] = $this->MySQL->getRow('SELECT * FROM yapps_app_hot_settings WHERE site_id = ?i', $res['site_id']);

			return $res;
		}

		public function getItemsBySite( $id ) {

			$res = $this->MySQL->getInd('id', 'SELECT * FROM yapps_app_hot_items WHERE site_id = ?i ORDER BY spec_price ASC', (int)$id);
			foreach ( $res as $k => $i ) { $res[$k]['images'] = $this->MySQL->getAll('SELECT * FROM yapps_app_hot_items_images WHERE item_id = ?i', $i['id']); }

			return $res;
		}
		
		public function getSliderBySite( $id ) {

			$res = $this->MySQL->getCol('SELECT id FROM yapps_app_hot_items WHERE site_id = ?i AND slider = ?i', (int)$id, 1);

			return $res;
		}

		public function delItem( $id ) {

			$item = $this->MySQL->getRow('SELECT * FROM yapps_app_hot_items WHERE id = ?i', (int)$id);
			$model = $this->MySQL->getRow('SELECT * FROM yapps_app_hot_models WHERE id = ?i', $item['model_id']);

			Helper::rmDir( $_SERVER['DOCUMENT_ROOT'].$this->Conf->FileDir.'/'.$model['site_id'].'/'.$model['url_key'].'/Items/'.$item['vin'] );

			$this->MySQL->query('DELETE FROM yapps_app_hot_items WHERE id = ?i', (int)$id);
			$this->MySQL->query('DELETE FROM yapps_app_hot_items_images WHERE item_id = ?i', (int)$id);

			return true;
        }
        
        public function activateItem( $id ) {

            $this->MySQL->query('UPDATE yapps_app_hot_items SET ?u WHERE id = ?i', ['slider'=>1], $id);
            return Helper::getRes(51);
        }

        public function deactivateItem( $id ) {

            $this->MySQL->query('UPDATE yapps_app_hot_items SET ?u WHERE id = ?i', ['slider'=>0], $id);
            return Helper::getRes(51);
        }



		///////////////////////////////////////////////////////////////////////////////////////////
        // Settings Area //////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function getSettings( $sites = false ) {

			if ( !$sites ) {

				return false;

			} else {

				foreach ( $sites as $k => $s ) $sites[$k]['settings'] = $this->MySQL->getRow('SELECT * FROM yapps_app_hot_settings WHERE site_id = ?i', $s['id']);
				return $sites;
			}
		}

		public function getSettingsByHost( $host ) {

			$site = $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE url = ?s', $host);
			$res = $this->getSettingsById( (int)$site['id'] );
			$res['calltouch_sess'] = $site['calltouch_sess'];
			$res['brand_name'] = $site['brand_name'];


			return ( $res['active'] ) ? $res : false;
		}

		public function getSettingsById( $site_id ) {

			return $this->MySQL->getRow('SELECT * FROM yapps_app_hot_settings WHERE site_id = ?i', (int)$site_id);
		}

		public function setSettings( $POST, $FILES = false ) {

			$arIns = $POST;
			unset( $arIns['id'], $arIns['form'], $arIns['active'] );
            $arIns['active'] = ( $POST['active'] == 'on' ) ? 1 : 0;
            $arIns['term_checked'] = ( $POST['term_checked'] == 'on' ) ? 1 : 0;
            $arIns['use_slider'] = ( $POST['use_slider'] == 'on' ) ? 1 : 0;

			$site_dir = $_SERVER['DOCUMENT_ROOT'].$this->Conf->FileDir.'/'.$POST['id'];
			$banner_dir = $_SERVER['DOCUMENT_ROOT'].$this->Conf->FileDir.'/'.$POST['id'].'/banners';

			if ( !file_exists( $site_dir ) ) mkdir( $site_dir );
			if ( !file_exists( $banner_dir ) ) mkdir( $banner_dir );

			if ( $FILES ) {

				foreach ( $FILES as $key => $banner ) {

					if ( $banner['error'] == 0 ) {

						move_uploaded_file( $banner['tmp_name'], $banner_dir.'/'.$banner['name'] );
						$arIns[$key] = $this->Conf->FileDir.'/'.$POST['id'].'/banners/'.$banner['name'];
					}
				}
			}
			
			if ( $this->MySQL->getOne('SELECT id FROM yapps_app_hot_settings WHERE site_id = ?i', (int)$POST['id']) ) {

				$this->MySQL->query('UPDATE yapps_app_hot_settings SET ?u WHERE site_id = ?i', $arIns, (int)$POST['id']);
				$res = Helper::getRes(0);

			} else {

				$arIns['site_id'] = (int)$POST['id'];

				$this->MySQL->query('INSERT INTO yapps_app_hot_settings SET ?u', $arIns);
				$res = Helper::getRes(0);
			}
			
			if ( $counter = $this->MySQL->getOne('SELECT yandex_id FROM yapps_sites WHERE id = ?i', (int)$POST['id']) ) {
			
				$arGoals = [
					[
						'name' => $this->AppInfo()->ru_name.'. Отправка формы.',
						'goal' => 'YApps_Goals-Hot_Send'
					],
					[
						'name' => $this->AppInfo()->ru_name.'. Маршрут в ДЦ.',
						'goal' => 'YApps_Goals-Hot_Route'
					]
				];
				
				foreach ( $arGoals as $arGoal ) {
					
					$goal_id = $this->MySQL->getOne(
						'SELECT goal_id FROM yapps_goals WHERE site_id = ?i AND goal_name = ?s AND goal_js = ?s', 
                        (int)$POST['id'], $arGoal['name'], $arGoal['goal']
                    );
					
					if ( !$goal_id ) {
						
						$resGoal = Yandex::setGoal( $this->Yandex, $counter, $arGoal );
						$arInsGoal = [
							'site_id' => (int)$POST['id'],
							'app_id' => $this->AppInfo()->id,
							'goal_id' => (string)$resGoal->goal->id,
							'goal_type' => $resGoal->goal->type,
							'goal_name' => $arGoal['name'],
							'goal_js' => $arGoal['goal']
						];
						$this->MySQL->query('INSERT INTO yapps_goals SET ?u', $arInsGoal);
					}
				}
			}
			
			return $res;
		}



		///////////////////////////////////////////////////////////////////////////////////////////
        // API Area ///////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function getScript( $user, $URL ) {

			$host = parse_url( $URL )['host'];
			$land = $this->YApps_GetLandSettings( $this->YApps_GetLandIdByUrl($URL) );
			if ( $land ) $host = $this->YApps_GetSiteByID( $land['site_id'] )['url'];
			$settings = $this->getSettingsByHost( $host );

			if ( $settings['active'] ) {
				
				$site = $this->MySQL->getRow('SELECT * FROM yapps_sites WHERE url = ?s', $host);
                $res = $this->getItemsBySite( $settings['site_id'] );
                $slider = $this->getSliderBySite( $settings['site_id'] );
				
				$dcs = $this->MySQL->getInd('id', 'SELECT id, url_key, address, coords_lat, coords_lon, link, phone, ru_name FROM yapps_dcs WHERE site_id = ?i ORDER BY ru_name', $site['id']);
				foreach ( $dcs as $k => $v ) {
					
					$dcs[$k]['count'] = $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_app_hot_items WHERE dc_id = ?i', $v['id']);
					if ( $dcs[$k]['count'] == 0 ) {
						unset( $dcs[$k] );
					} else {
						$dcs[$k]['items'] = $this->MySQL->getCol( 'SELECT id FROM yapps_app_hot_items WHERE dc_id = ?i', $v['id']);
					}
				}
				
				$models = $this->MySQL->getInd('id', 
					'SELECT 
						yapps_app_hot_models.id, 
						yapps_app_hot_models.model_id, 
						yapps_app_hot_models.brand_id, 
						yapps_app_hot_models.image_link, 
						yapps_models.en_name as ru_name, 
						yapps_brands.en_name as brand, 
						yapps_brands.id as brand_id 
					FROM yapps_app_hot_models JOIN yapps_models
					ON  yapps_app_hot_models.model_id = yapps_models.id 
					JOIN yapps_brands ON yapps_models.brand_id = yapps_brands.id
					WHERE yapps_app_hot_models.site_id = ?i 
					ORDER BY yapps_app_hot_models.ru_name', $site['id']);
					
				$brands_count = count($this->YApps_GetBrandsIDsBySiteIDs([$site['id']]));
				foreach ( $models as $k => $m ) {

					$models[$k]['count'] = $this->MySQL->getOne('SELECT COUNT(*) FROM yapps_app_hot_items WHERE model_id = ?i', $m['id']);
					if ( $models[$k]['brand_id'] == 3 ) $models[$k]['ru_name'] = $models[$k]['brand'];
					if ( $models[$k]['count'] == 0 ) {
						
						unset( $models[$k] );
					
					} else {
						
						$models[$k]['items'] = $this->MySQL->getCol('SELECT id FROM yapps_app_hot_items WHERE model_id = ?i', $m['id']);
						if ( $brands_count > 1 && $models[$k]['brand_id'] != 3 ) $models[$k]['ru_name'] = $models[$k]['brand'].' '.$models[$k]['ru_name'];
					}
				}
				
				$html = file_get_contents(__DIR__.'/html/'.get_class($this).'.html');
				$html = str_replace( '%%HOT.TITLE%%', $settings['title'], $html);
				$html = str_replace( '%%HOT.TEXT%%', $settings['text'], $html);
				$html = str_replace( '%%HOT.THANKS%%', $settings['thanks'], $html);
                $html = str_replace( '%%HOT.ERROR%%', $settings['error'], $html);

                $html = str_replace( '%%HOT.SLIDER%%', (($settings['use_slider']&&!!$slider)?file_get_contents(__DIR__.'/html/'.get_class($this).'/Slider.html'):''), $html);

                $html = str_replace('%%HOT.TERM_PERSONAL%%', $settings['term_personal'], $html);
                $html = str_replace('%%HOT.TERM_COMMUNICATIONS%%', $settings['term_communications'], $html);
                $html = str_replace('%%HOT.TERM_CHECKED.CLASS%%', (($settings['term_checked'])?'YApps--Form_Personal-Item_Checked':''), $html);
                $html = str_replace('%%HOT.TERM_CHECKED.ICON%%', (($settings['term_checked'])?'Check':'UnCheck'), $html);
                
                // Helper::sp($settings);

                $script = file_get_contents(__DIR__.'/js/'.get_class($this).(((int)$settings['site_id']==27)?'N':'').'.js');
                if ( file_exists(__DIR__.'/../../upload/Hot/Scripts/'.$settings['site_id'].'.js') ) $script .= file_get_contents(__DIR__.'/../../upload/Hot/Scripts/'.$settings['site_id'].'.js');
                $script = str_replace( '%%JSON.RESULT%%', json_encode($res), $script);
                $script = str_replace( '%%JSON.SLIDER%%', (($settings['use_slider']&&!!$slider)?json_encode($slider):'false'), $script);
                $script = str_replace( '%%JSON.SLIDES%%', $settings['slider_count'], $script );
				$script = str_replace( '%%JSON.DCS%%', json_encode($dcs), $script );
                $script = str_replace( '%%JSON.MODELS%%', json_encode($models), $script );
                
                unset( $settings['id'], $settings['css'], $settings['recipients'], $settings['site_id'] );
				$script = str_replace( '%%JSON.SETTINGS%%', json_encode($settings), $script );
				$script = str_replace( '%%SITE.YANDEXID%%', $site['yandex_id'], $script );
				$script = str_replace( '%%SITE.PIWIKID%%', $site['piwik_id'], $script );
				$script = str_replace( '%%SITE.GOOGLEID%%', $site['google_id'], $script );
				$script = str_replace( '%%SITE.YANDEXID%%', $site['yandex_id'], $script );
				$script = str_replace( '%%SITE.CALLTOUCHNODE%%', $site['calltouch_node'], $script );
				$script = str_replace( '%%SITE.CALLTOUCHID%%', $site['calltouch_id'], $script );
				$script = str_replace( '%%SITE.CALLTOUCHSESS%%', $site['calltouch_sess'], $script );
				$script = str_replace( '%%HOT.STARTHTML%%', JSMin::minifyHTML( $html ), $script );
				$script = str_replace( '%%HOT.SVG%%', JSMin::minifyHTML(file_get_contents(__DIR__.'/svg/'.get_class($this).'.php')), $script);

				return $script.PHP_EOL;
			}
		}

		public function getSVG() {

			return file_get_contents(__DIR__.'/svg/'.get_class($this).'.php');
        }
        
        public function te() {

            return file_get_contents(__DIR__.'/../../upload/Hot/Scripts/21.js');
        }


		public function getCSS( $user, $URL ) {

			$host = parse_url( $URL )['host'];
			$land = $this->YApps_GetLandSettings( $this->YApps_GetLandIdByUrl($URL) );
			if ( $land ) $host = $this->YApps_GetSiteByID( $land['site_id'] )['url'];
			$settings = $this->getSettingsByHost( $host );;

			if ( $settings['active']  ) {

				$css = file_get_contents(__DIR__.'/css/'.get_class($this).'.css');

				$css = str_replace( '%%COLOR.DARK%%', (($settings['color_dark'])?:$this->Conf->Defaults['ColorDark']), $css );
				$css = str_replace( '%%COLOR.GRAY%%', (($settings['color_gray'])?:$this->Conf->Defaults['ColorGray']), $css );
				$css = str_replace( '%%COLOR.LIGHTGRAY%%', (($settings['color_lightgray'])?:$this->Conf->Defaults['ColorLightgray']), $css );
				$css = str_replace( '%%COLOR.LIGHT%%', (($settings['color_light'])?:$this->Conf->Defaults['ColorLight']), $css );
				$css = str_replace( '%%COLOR.ERROR%%', (($settings['color_error'])?:$this->Conf->Defaults['ColorError']), $css );

				$css .= $settings['css'];

				return $css.PHP_EOL;
			}
		}



		///////////////////////////////////////////////////////////////////////////////////////////
        // Stats Area /////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////

		public function pushStat( $POST, $user, $ip ) {
            
			$res = Helper::getRes(102);
			
            if ( Helper::isNotFakePhone($POST['Phone']) ) {

                $ids = [
                    'piwik_visitorId' => explode('.', $POST['PiwikVisitorID'])[0],
                    'yandex_visitorId' => $POST['YandexVisitorID'],
                    'google_visitorId' => explode('.', $POST['GoogleVisitorID'])[2].'.'.explode('.', $POST['GoogleVisitorID'])[3]
                ];
                
                $utms = Helper::getUtm( $POST['SourceLink'] );
                
                $st_data = [
                    'user_id' => $user->id,
                    'site_id' => (int)$POST['SiteID'],
                    'source_title' => $POST['SourceTitle'],
                    'source_url' => $POST['SourceLink'],
                    'event_name' => $POST['EventName'],
                    'timestamp' => time(),
                    'name' => $POST['Name'],
                    'phone' => Helper::formatPhoneIn( $POST['Phone'] ),
                    'item_id' => (int)$POST['ItemID'],
                    'referrer' => $POST['Referrer'],
                    'visitorIP' => $ip,
                ];

                if ( $utms ) $st_data = array_merge( $st_data, $utms );

                $st_data = array_merge( $st_data, $ids );
                $this->MySQL->query('INSERT INTO yapps_app_hot_stat SET ?u', $st_data);
                $lastId = $this->MySQL->insertId();
				
				if ( $this->sendForm($st_data) ) $res = Helper::getRes(0);

                $cl_data = [
                    'name' => $POST['Name'],
                    'phone' => Helper::formatPhoneIn( $POST['Phone'] ),
                    'url' => $POST['SourceLink'],
                    'event' => $POST['EventName'],
                    'stat_id' => $lastId,
                    'app_id' => $this->AppInfo()->id,
                    'site_id' => (int)$POST['SiteID'],
                    'referrer' => $POST['Referrer']
                ];
                
                if ( $utms ) $cl_data = array_merge( $cl_data, $utms );

                $geo = Helper::getGeo( $ip );

                //$this->YApps_PushClient( $cl_data, $ids, $geo );

            } // if Not Fake Phone
			
			return Helper::getRes(0);
		}





		public function getStats( $user, $date1, $date2 ) {

			$sites = $this->MySQL->getCol('SELECT site_id FROM yapps_users_sites WHERE user_id = ?i', (int)$user->id);
			$res = $this->MySQL->getAll('SELECT * FROM yapps_app_hot_stat WHERE site_id IN (?a) AND timestamp >= ?i AND timestamp < ?i', $sites, strtotime($date1), strtotime($date2));

			foreach ( $res as $k => $s ) {

				$t = $this->MySQL->getRow(
					'SELECT dc_id, model_id, vin, year, engine_volume, engine_power, engine_type, gearbox, color, price, spec_price, complectation
					FROM yapps_app_hot_items WHERE id = ?i', $s['item_id']);

				$res[$k]['item'] = $t;

				$res[$k]['model'] = $this->MySQL->getOne('SELECT ru_name FROM yapps_app_hot_models WHERE id = ?i', $res[$k]['item']['model_id']);
				$res[$k]['dc'] = $this->MySQL->getOne('SELECT ru_name FROM yapps_dcs WHERE id = ?i', $res[$k]['item']['dc_id']);
			}

			return $res;
		}


		// End Class
	}
