<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?=$app->Hot->AppInfo()->ru_name?> <small>Импорт CSV</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <?php if ( $app->Hot->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
  
  <div class="row">

	<div class="col-md-12">
	  
      <div class="box box-warning box-solid">
        <div class="box-header with-border">
          <h3 class="box-title">Инструкция по формированию архива для загрузки</h3>
        </div><!-- /.box-header -->
        <div class="box-body">
          <p>В файле нужно предоставлять <strong>ВСЕ имеющиеся автомобили для ДЦ</strong>, т к те автомобили, которых нет в файле будут <strong>УДАЛЕНЫ</strong> с сайта</p>
          <p>Наименование фото: <strong>VIN-N.jpg</strong>, где VIN - VIN автомобиля (XT0000000000), N - порядковый номер фото, т е должно получиться <strong>XT0000000000-1.jpg</strong>, <strong>XT0000000000-2.jpg</strong> и тд<br />
          Размер фото: <strong>880*660px</strong>. Фото большего размера использовать <strong>НЕ ЖЕЛАТЕЛЬНО</strong> - архив будет импортироваться долго, даже возможно выпадение в 500 ошибку!</p>
          <p><strong>Внимание!</strong> Наименования моделей должны строго соответствовать спискам ниже, иначе они не распознаются программой.<br />
          Начало списка дополнительного оборудования нужно помечать не пустой ячейкой, а ячейкой со значением <strong>DOP</strong> (см <a href="/upload/Hot/example.csv">пример файла</a>);</p>
          <h4>Сохранение файла csv</h4>
          <ol>
            <li>Выделить в подготовленном exel-файле содержательную область ячеек</li>
            <li>Скопировать в буфер обмена (правый клик -> копировать или ctrl+c)</li>
            <li>Создать новое окно exel (файл -> создать -> пустая книга)</li>
            <li>Вставить содержимое буфера обмена (правый клик на ячейке А1 -> вставить скопированые ячейки -> диапазон со сдвигом вниз или ctrl+v)</li>
            <li>Файл -> Сохранить как -> Имя файла: <strong>hot</strong>, Тип файла: <strong>CSV (разделители запятые)</strong> (*.csv)</li>
          </ol>
          <p>Подготовленные фото и файл hot.csv нужно скопировать в одну папку (вложенных папок не должно быть), далее зайти в эту папку, выделить все файлы и сжать в <strong>архив .zip</strong><br />
          <strong>Внимание!</strong> Архив должен быть именно <strong>ZIP</strong>, а не 7z</p>
        </div><!-- /.box-body -->
      </div>
      
	  <div class="box box-primary">
		<div class="box-header with-border">
		  <h3 class="box-title">Импорт CSV</h3>
		</div>
	    
	    <div class="box-body">
	    
	      <?php if ($POSTRes) HTML::Error($POSTRes); ?>
			
		  <form role="form" method="post" enctype="multipart/form-data">

			<input type="hidden" name="form" value="formHotImport" />
			<?php if ( $currentRoute->id ) { ?>
			<input type="hidden" name="id" value="<?=$currentRoute->id?>" />
			<?php } ?>

			<?php

				$formSet = [
					'fields' => [
						[
							'type' => 'select',
							'name' => 'dc_id',
							'placeholder' => 'Привязка к Дилерскому центру',
							'items' => $app->getUserDCs( $authUser ),
							'description' => 'Обратите внимание в какой ДЦ загружаете автомобили!'
						],
						[
							'type' => 'checkbox',
							'name' => 'delete_current',
							'placeholder' => 'Удалить сушествующие предложения',
							'value' => 1,
							'items' => [
								[
									'text' => 'Удалить сушествующие предложения',
									'value' => 1
								],
							],
							'class' => ''
						],
						[
							'type' => 'file',
							'name' => 'import',
							'placeholder' => 'Файл .zip',
							'description' => 'Образец файла <a href="/upload/Hot/example.zip">example.zip</a>'
						],
					],
					'submit' => [
						'class' => 'primary',
						'text' => 'Загрузить'
					]
				];
			?>

			<?php HTML::Form( $formSet ); ?>
			
			<div class="formCover"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div>
			
		  </form>
		  
		</div>
	  </div>
		
      <div class="box box-danger box-solid">
        <div class="box-header with-border">
          <h3 class="box-title">ВНИМАНИЕ! Названия моделей в файле .csv должны быть ТОЛЬКО из списков ниже</h3>
        </div><!-- /.box-header -->
        <div class="box-body">
            <?php foreach ( $app->YApps_GetUserModels($GLOBALS['USER_SITES']['sites_ids']) as $b ) { ?>
              <div class="box box-default">
                <div class="box-header with-border">
                  <h3 class="box-title"><?=str_replace('Š', 'S', $b['en_name'])?></h3>
                </div>
                <div class="box-body">
                  <div class="btn-group" style="width: 100%; margin-bottom: 10px;">
                    <ul>
                    <?php foreach ( $b['items'] as $i ) { ?>
                    <li><?=$i['en_name']?></li>
                    <?php } ?>
                    </ul>
                  </div>
                </div>
              </div>
			<?php } ?>
        </div><!-- /.box-body -->
      </div>
      
	</div>
  </div>

</section>
<!-- /.content -->

</div>