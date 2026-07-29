<?php if ( $POSTRes->id ) Route::redirect( '/humanresourses/stat/view/'.$POSTRes->id ); ?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->HumanResourses->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
	<?php if ( $app->HumanResourses->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
    <div class="row">
      <div class="col-md-12">
      
        <div class="box box-primary">
          
          <div class="box-header with-border">
            
            <h3 class="box-title">Отправить письмо</h3>
              
            <!-- /.box-tools -->
          </div>
          
          <div class="box-body">
          
			<?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
            
            <form role="form" method="post">
              
              <input type="hidden" name="form" value="formHumanResoursesMessage" />
              <input type="hidden" name="user_id" value="<?=$authUser->id?>" />
              
              <?php
				
				  $formSet = [
					  'fields' => [
						  [
							  'type' => 'delimiter',
							  'value' => 'Соискатель',
						  ],
						  [
							  'type' => 'text',
							  'name' => 'name',
							  'placeholder' => 'ФИО',
							  'value' => $arRes['name'],
							  'description' => '<strong>Внимание</strong>: Фамилия Имя Отчество. Именно в такой последовательности. Можно без отчества, но <strong>ИМЯ</strong> должно быть <strong>ВТОРЫМ</strong>!'
						  ],
						  [
							  'type' => 'text',
							  'name' => 'email',
							  'value' => $arRes['email'],
							  'placeholder' => 'Email'
						  ],
						  [
							  'type' => 'text',
							  'name' => 'phone',
							  'value' => $arRes['phone'],
							  'placeholder' => 'Телефон'
						  ],
						  [
							  'type' => 'text',
							  'name' => 'position',
							  'value' => $arRes['position'],
							  'placeholder' => 'Должность'
						  ],
						  [
							  'type' => 'select',
							  'name' => 'gender_id',
							  'multiple' => false,
							  'placeholder' => 'Пол',
							  'value' => [$arRes['gender_id']],
							  'items' => $app->HumanResourses->getGenders()
						  ],
						  
						  
						  [
							  'type' => 'delimiter',
							  'value' => 'Работа',
						  ],
						  [
							  'type' => 'date',
							  'name' => 'start_date',
							  'value' => date('d.m.Y H:i', (($arRes['start_timestamp'])?:strtotime(date('Y-m-d'))+3600*33)),
							  'placeholder' => 'Начало работы'
						  ],
						  [
							  'type' => 'select',
							  'name' => 'dc_id',
							  'multiple' => false,
							  'placeholder' => 'Дилерский центр',
							  'value' => [$arRes['dc_id']],
							  'items' => $app->HumanResourses->getDCs()
						  ],
						  [
							  'type' => 'checkbox',
							  'name' => 'salary_from',
							  'placeholder' => 'Зарплата "От"',
							  'value' => (int)$arRes['salary_from'],
							  'items' => [
								  [
									  'text' => 'Зарплата "От"',
									  'value' => (int)$arRes['salary_from']
								  ],
							  ],
						  ],
						  [
							  'type' => 'number',
							  'name' => 'salary',
							  'value' => $arRes['salary'],
							  'placeholder' => 'Зарплата'
						  ],
						  [
							  'type' => 'text',
							  'name' => 'salary_add',
							  'value' => $arRes['salary_add'],
							  'placeholder' => 'Дополнение к зарплате'
						  ],
						  [
							  'type' => 'select',
							  'name' => 'dress_id',
							  'multiple' => false,
							  'placeholder' => 'Дресс-код',
							  'value' => [$arRes['dress_id']],
							  'items' => $app->HumanResourses->getDresses()
						  ],
						  [
							  'type' => 'text',
							  'name' => 'work_mode',
							  'value' => $arRes['work_mode'],
							  'placeholder' => 'График работы'
						  ],
						  [
							  'type' => 'select',
							  'name' => 'work_graph[]',
							  'multiple' => true,
							  'rows' => 6,
							  'placeholder' => 'Режим работы',
							  'value' => [$app->HumanResourses->getScheduleByStat($arRes['id'])],
							  'items' => $app->HumanResourses->getSchedules(),
							  'description' => 'Зажав клавишу shift или ctrl можно выбрать несколько значений'
						  ],
						  [
							  'type' => 'text',
							  'name' => 'work_add',
							  'value' => $arRes['work_add'],
							  'placeholder' => 'Дополнение к графику (напр: в соответствии с текущим графиком отдела)'
						  ],
						  [
							  'type' => 'select',
							  'name' => 'probation_id',
							  'multiple' => false,
							  'placeholder' => 'Испытательный срок',
							  'value' => [$arRes['probation_id']],
							  'items' => $app->HumanResourses->getProbations()
						  ],
						  
						  
						  [
							  'type' => 'delimiter',
							  'value' => 'Руководитель',
						  ],
						  [
							  'type' => 'text',
							  'name' => 'boss_name',
							  'value' => $arRes['boss_name'],
							  'placeholder' => 'Имя '
						  ],
						  [
							  'type' => 'text',
							  'name' => 'boss_position',
							  'value' => $arRes['boss_position'],
							  'placeholder' => 'Должность '
						  ],
						  
						  [
							  'type' => 'delimiter',
							  'value' => 'Прочее',
						  ],
						  [
							  'type' => 'select',
							  'name' => 'manager_id',
							  'multiple' => false,
							  'placeholder' => 'Специалист отдела кадров',
							  'value' => [$arRes['manager_id']],
							  'items' => $app->HumanResourses->getManagers(),
							  'first_empty' => true,
						  ],
						  [
							  'type' => 'select',
							  'name' => 'manager_desc',
							  'select_field' => 'id',
							  'placeholder' => 'Описание',
							  'value' => [$arRes['manager_desc']],
							  'items' => [
							  		['id'=>'свяжется с Вами для приглашения на оформление на работу в течение первого рабочего дня'],
									['id'=>'ждет Вас для оформления документов о приеме на работу в 10:00 в дилерском центре «Эксперт» в п. Яблоновский'],
									['id'=>'ждет Вас для оформления документов о приеме на работу в 10:00 в дилерском центре "Hyundai" в с. Владимировка'],
							  ],
						  ]
						  
					  ],
					  'submit' => [
						  'class' => 'primary',
						  'text' => 'Предпросмотр'
					  ]
				  ];
			  ?>
              
              <?php HTML::Form( $formSet ); ?>
            
            </form>
          
          </div>
          
        </div>
          
      </div>
    </div>
  
  </section>
  <!-- /.content -->
  
</div>