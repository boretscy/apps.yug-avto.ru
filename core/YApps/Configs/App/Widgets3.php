<?php 
	return [
		'secret' => md5('Виджеты 3.0 Юг-Авто'),
		'FileDir' => '/upload/Widgets3',
        'ExportDir' => '/upload/Export/Widgets3',
		'FrontendDir' => '/vue-apps/vue3-widgets-v3',
		'Secret' => md5('YApps_Widgets_3.0'),

		'Defaults' => [
			'Colors' => [
				'IconDark' => '#FFA718',
				'IconLight' => '#FDBA4D',
				'IconHoverDark' => '#FEEEC7',
				'IconHoverLight' => '#FDDE8F',
				'IconHoverShadow' => '#FF7A00',
				'IconButton' => '#FFFFFF',
				'IconHoverButton' => '#FDBA4D',
				'IconHoverButtonShadow' => '#FFCA7A',
				'WidgetBg' => '#FFFFFF',
				'WidgetText' => '#222222',
				'WidgetTerms' => '#5C5D5E',
				'WidgetFieldBorder' => '#7B8284',
				'WidgetFieldBg' => '#F5F5F5',
				'WidgetButton' => '#FDBA4D',
				'WidgetButtonText' => '#222222',
				'WidgetButtonHover' => '#FFB234',
				'WidgetButtonHoverText' => '#222222',
				'WidgetError' => '#F04124',
				'WidgetTimerBg' => '#7B8284',
				'WidgetTimerText' => '#FFFFFF',
			],

			'CB' => [
				'title' => 'Заказать звонок',
				'text' => 'Закажите бесплатный обратный звонок.<br />Наши специалисты свяжутся с Вами через несколько секунд!',
				'button' => 'Отправить',
				'marking' => '',
				'work_start' => '08:00',
				'work_end' => '20:00',
				'image_back' => 'https://apps.yug-avto.ru/upload/Widgets3/cb_back.svg',
				'image_front' => 'https://apps.yug-avto.ru/upload/Widgets3/cb_front.png',
				'url' => '/',
				'timeout' => 15,
			],

			'LG' => [
				'title' => '',
				'subtext' => '',
				'text' => 'Закажите бесплатный обратный звонок.<br />Наши специалисты свяжутся с Вами через несколько секунд!',
				'timer_use' => 0,
				'timer' => 0,
				'button' => 'Отправить',
				'marking' => '',
				'image_back' => 'https://apps.yug-avto.ru/upload/Widgets3/lg_back.svg',
				'image_front' => 'https://apps.yug-avto.ru/upload/Widgets3/lg_front.png',
				'url' => '/',
				'timeout_1' => 30,
				'timeout_2' => 15,
			],

			'NV' => [
				'coords_lat' => '',
				'coords_lon' => ''
			],

			'Form' => [
				'Success' => 'Спасибо за Вашу заявку!<br />Уже набираем Ваш номер.',
				'Error' => 'Ой, что-то пошло не так!<br />Повторите попытку позднее.',
				'Timeout' => 10,
			],
			
			'Buttons' => [
				'CBClue' => 'Заказать звонок',
				'LGClue' => 'Персональное предложение',
				'NVClue' => 'Проложить маршрут',
				'CISClue' => 'Автомобили в наличии',
			],

			'Margins' => [
				'right' => '60px',
				'bottom' => '90px'
			],

			'Fonts' => '"Roboto", Helvetica, sans-serif',
			'Recipients' => 'callcenter@adv.yug-avto.ru'
		],
    ];