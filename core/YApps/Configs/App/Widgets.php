<?php
	
	return [
        'Version' => '2.3.5',

		'PageCount' => 1000,
		'FileDir' => '/upload/Widgets',
		'Secret' => md5('YApps_Widgets'),
		
		'ExportDir' => '/upload/Export/Widgets',
		'FrontendDir' => '/vue-apps/vue-yapps',
		
		'Delimiter' => '%%',
		
		'Defaults' => (object)[
			
			// Image Sizes
			'LGImageWidth' => 380,
			'LGImageHeight' => 350,
			
			// Colors
            'ColorBg' => '#ffffff',
            'ColorDarkBg' => '#f5f6f7',
			'ColorFill' => '#003375',
			'ColorText' => '#303030',
			'ColorButton' => '#fdba4d',
			'ColorButtonText' => '#303030',
			'ColorError' => '#f04124',
			'ColorLightgray' => '#f5f6f7',
			'ColorMiddlegray' => '#a9a9a9',
			'ColorDarkgray' => '#515c69',
			'ColorShadow' => '#007bff',
			
			// HP
			'HPShowTimeout' => 1, // min
			'HPCloseTimeout' => 10, // sec
			'HPIconsInterval' => 3, // sec
			'HPLGButtonText' => 'Индивидуальные условия специально для Вас!',
			'HPLGButtonUseWName' => 1,
			'HPLGPlateText' => 'Индивидуальные условия специально для Вас!',
			'HPLGPlateUseWName' => 1,
			'HPLGPlateDraggable' => 0,
			'HPLGPlatePositionID' => 7,
			'HPCHButton' => 'Задайте ваш вопрос в чате',
			'HPNVButton' => 'Проложите маршрут',
			'HPFBButton' => 'Отзывы наших клиентов',
            'HPQZButton' => 'Подберем для Вас лучший автомобиль',
            'HPSCButton' => 'Напишите нам',
			'HPCBButton' => 'Заказать обратный звонок',
			'HPCBOutButton' => 'Позвонить',
			'HPGoal' => 'YApps_Goals-Helper-Call',
			
			// CB
			'CBIdleTimeout' => 15, // min
			'CBTimerAwait' => 60, // sec
			'CBTimerTimeout' => 15, // sec
			'CBAwaitDays' => 2, // days
			'CBFormButtomNow' => 'Жду звонка',
			'CBFormButtomLater' => 'Жду звонка',
			'CBTitlePrologue' => 'Перезвоним вам',
			'CBTitleSpanProroque' => 'в указанное Вами время',
			'CBDescriptionNow' => 'Не сейчас? Выберите удобное время для звонка.',
			'CBDescriptionLater' => 'Готовы поговорить прямо сейчас?',
			'CBText' => 'Наш специалист ответит на все Ваши вопросы.<br />Это просто и бесплатно!',
			'CBGoalNow' => 'YApps_Goals-Widgets_CB-Send_Now',
			'CBGoalLater' => 'YApps_Goals-Widgets_CB-Send_Later',
			'CBTimeStart' => '08:00',
			'CBTimeEnd' => '20:00',
			
			// LG
			'LGShowTimeout' => 15, // sec
			'LGShowSecond' => 15, // min
			'LGShowCount' => 1, // раз
			'LGShowCount2' => 1, // раз
			'LGFormButton' => 'Жду звонка',
			'LGHead' => 'Специальное предложение',
			'LGImage' => 'https://apps.yug-avto.ru/upload/Widgets/YApps_Widgets_LG_default.jpg',
			'LGTimerDescription' => 'Осталось до конца акции',
			'LGLinkText' => 'Подробнее',
			'LGUrl' => '/',
			'LGHPUseWName' => 1,
			
			// NV
			'NVGoalRoute' => 'YApps_Goals-Widgets_NV-Route',
			'NVGoalCall' => 'YApps_Goals-Widgets_NV-Call',
			'NVSelectNaviText' => 'Выберите приложение навигации',
			'NVTitle' => 'Проложить маршрут в дилерский центр',
			'NVSecondTitle' => 'Проложить маршрут в ',
			'NVSecondText' => 'Выберите приложение навигации',
			
			// CH
			'CHTimeout' => 25, // sec
			
			//QZ
			'QZFormButton' => 'Подберите мне автомобиль',
			'QZLastTitle' => 'Отлично! Последний шаг!',
			'QZLastBigText' => 'Наш специалист подберет автомобиль',
			'QZLastText' => 'по указанным критериям и свяжется с вами',
			'QZUrl' => '/',

			// MS
			'MSTitle' => 'Видеозвонок',
            'MSText' => 'Наши менеджеры готовы помочь с заполнением формы!',
            'MSDefaultMessage' => '',
            'MSIdleTimeout' => 10, // sec

            //CI
            'CIUrl' => '/',
            'CIRandomMin' => 3,
            'CIRandomMax' => 90,
            'CILevelList' => 2,
            'CILevelModel' => 3,
            'CILevelItem' => 4,
            'CITitle' => 'Просмотров за последний час - {{RANDOM}}',
            'CIText' => 'Успейте приобрести этот автомобиль на самых выгодных условиях!',
            'CIFTimeout' => 25, // sec
            'CISTimeout' => 5, // min
            'CIFormButton' => 'Жду звонка',

			//EH
			'EHTitle' => 'Персональный помощник',
			'EHSocialText' => 'Подпишитесь на нас в соцсетях:',
			'EHSocialItems' => (object)[
				'Youtube' => 'https://www.youtube.com/user/webyugavto',
				// 'Instagram' => 'https://www.instagram.com/yugavto/',
				// 'Facebook' => 'https://www.facebook.com/yugavto',
				'Vkontakte' => 'https://vk.com/yugavto'
			],
			
			// All
			'ResultTimeout' => 15, // sec
			'InitTimeout' => 1500,
			'FormSuccess' => 'Спасибо за Вашу заявку!<br />Уже набираем Ваш номер.',
			'FormError' => 'Ой, что-то пошло не так!<br />Повторите попытку позднее.',
            'Recipients' => 'callcenter@adv.yug-avto.ru',
            'TermChecked' => 1,
            'Url' => '/',

			'Vue' => (object)[
				
				'Fonts' => '"PT Sans", sans-serif',

				'CloseButtonText' => 'Закрыть',
				'BackButtonText' => 'Назад',
				'ForwardButtonText' => 'Вперед',

				'Colors' => (object)[
					'ColorBg' => '#ffffff',
					'ColorDarkBg' => '#f5f6f7',
					'ColorFill' => '#003375',
					'ColorText' => '#303030',
					'ColorButton' => '#fdba4d',
					'ColorButtonText' => '#303030',
					'ColorError' => '#f04124',
					'ColorLightgray' => '#f5f6f7',
					'ColorMiddlegray' => '#a9a9a9',
					'ColorDarkgray' => '#515c69',
					'ColorShadow' => '#007bff',
				],

				'Form' => (object)[
					'Headers' => [
						'Content-Type' => 'application/x-www-form-urlencoded',
						'Accept' => 'application/json'
					],
					'SuccessText' => 'Спасибо за Вашу заявку!<br />Уже набираем Ваш номер.',
					'ErrorText' => 'Ой, что-то пошло не так!<br />Повторите попытку позднее.',
					'ButtonText' => 'Отправить',
					'DelayedText' => 'В указанное время',
					'NowText' => 'Сейчас',
				],
				
				'Helper' => (object)[
					'CallOutText' => 'Позвонить',
					'NVText' => 'Проложить маршрут',
					'AVText' => 'Автомобили в наличии',
					'EHText' => 'Персональный помощник',
					'EHContentDelay' => 5,
					'ActiveInterval' => 30,
				],

				'Widgets' => (object)[

					'EH' => (object)[

						'Items' => [
							[
								'Status' => false,
								'Type' => 'text',
								'Text' => 'Здравствуйте!<br />Я ваш персональный помощник.',
								'Value' => null,
								'blank' => false,
								'Inited' => [
									'Status' => true,
									'Delay' => 0.2
								],
								'Cookie' => [ 'Status' => false ]
							],
							[
								'Status' => false,
								'Type' => 'buttons',
								'Text' => 'Помогу быстро найти интересующую Вас информацию. Выбирайте из списка &darr;',
								'Value' => null,
								'blank' => false,
								'Inited' => [
									'Status' => true,
									'Delay' => 0.5
								],
								'items' => [
									[
										'type' => 'button',
										'text' => 'Автомобили в наличии',
										'action' => 'link',
										'value' => 'https://yug-avto.ru/',
										'blank' => false
									],
									[
										'type' => 'button',
										'text' => 'Специальное предложение',
										'action' => 'widget',
										'value' => 'LG',
										'blank' => false
									],
									[
										'type' => 'button',
										'text' => 'Заказать звонок',
										'action' => 'step',
										'value' => 3,
										'blank' => true
									],
									[
										'type' => 'button',
										'text' => 'Построить маршрут в ДЦ',
										'action' => 'widget',
										'value' => 'NV',
										'blank' => false
									],
									[
										'type' => 'button',
										'text' => 'Написать нам в мессенджер',
										'action' => 'step',
										'value' => 4,
										'blank' => true,
									],
								],
								'Cookie' => [ 'Status' => false ]
							],
							[
								'Status' => false,
								'Text' => 'Есть вопросы? Перезвоним Вам за 60 секунд:',
								'Type' => 'form',
								'Value' => 'BaseFormPhone',
								'blank' => true,
								'Cookie' => [ 'Status' => false ],
								'Inited' => [ 'Status' => false ],
							],
							[
								'Status' => false,
								'Type' => 'messengers',
								'Text' => 'Напишите нам в мессенджер',
								'Value' => null,
								'blank' => true,
								'Cookie' => [ 'Status' => false ],
								'Inited' => [ 'Status' => false ],
							],
							[
								'Status' => false,
								'Type' => 'involv',
								'Text' => 'Кстати!<br />Просмотров за последний час - {{RANDOM}}<br />Успейте приобрести этот автомобиль на самых выгодных условиях!',
								'Value' => null,
								'blank' => false,
								'Inited' => [
									'Status' => true,
									'Delay' => 10
								],
								'items' => [
									[
										'type' => 'button',
										'text' => 'Посмотите выгодное предложение',
										'action' => 'widget',
										'value' => 'LG',
										'blank' => false
									],
									[
										'type' => 'button',
										'text' => 'Или давайте мы Вам позвоним!',
										'action' => 'step',
										'value' => 3,
										'blank' => true,
									],
								],
								'Cookie' => [
									'Status' => true,
									'Name' => 'YApps_Widgets--EH_Items-CI',
									'Count' => 2
								]
							],
						],

					]
				]
			],
		],
	];
