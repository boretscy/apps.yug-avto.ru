<?php
	if ( $currentRoute->action == 'delete' ) $app->Hot->delItem($currentRoute->id);
	$site_id = ($_GET['site_id']) ? $_GET['site_id'] : 'All';
	$arRes = $app->Hot->getItems( $authUser, $site_id );
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Hot->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Hot->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
    <?php if ($POSTRes) HTML::Error($POSTRes); ?>
    
    <div class="row">
      <div class="col-md-12">
        
        <div class="box box-default box-solid collapsed-box">
          <div class="box-header with-border">
            <h3 class="box-title">Подготовка к использованию.</h3>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
              </button>
            </div>
          </div><!-- /.box-header -->
          <div class="box-body">
          	<h4><strong>Настроить <a href="/hot/settings/">сайт</a></strong></h4>
            <ol>
              <li>Цветовая гамма</li>
              <li><strong>Ссылка на основную страницу сервиса на сайте</strong> - Заполняется если на сайте существует выделенный раздел для горячих предложений (как на Хендэ)</li>
              <li>Баннеры</li>
              <li>Дополнительные стили - если нужны, лучше обратитесь к вебмастеру</li>
              <li>Получатели - список email'ов получателей формы брони</li>
              <li>Активность - нужно включить, чтобы сервис начал работу на сайте</li>
            </ol>
            <p>При сохранении настроек сайта создается цель в метрике.</p>
            <h4><strong>Создать <a href="/hot/models/">модели</a></strong></h4>
            Вот этот блок в списке:<br />
            <img width="768" src="/upload/Hot/example-models.jpg" />
            <ol>
              <li>Привязка к сайту</li>
              <li>Название</li>
              <li>Изображение модели</li>
            </ol>
            <h4><strong>Импортировать горячие предложения</strong></h4>
            Вот этот блок:<br />
            <img width="768" src="/upload/Hot/example-offers.jpg" />
            <ol>
              <li>Подготовить архив в соответствии с <a href="/hot/import/">инструкцией</a></li>
              <li>Импортировать его</li>
            </ol>
          </div><!-- /.box-body -->
        </div>

        <div class="box box-info box-solid collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title">Установка модуля на сайт</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                    </button>
                </div>
            </div><!-- /.box-header -->
            <div class="box-body">
            <p>Тег для подключения модуля:
            <pre><strong>&lt;yappshot&gt;&lt;/yappshot&gt;</strong> или &lt;div <strong>id="YApps_Hot"</strong>&gt;&lt;/div&gt;</pre>
            Этот тег нужно вставить в нужное место в html-код сайта.</p>
            <p>Отображаемые модели или дц можно комбинировать спомощью атрибутов тега, например:</p>
            <pre>&lt;yappshot <strong>data-dc="25"</strong>&gt;&lt;/yappshot&gt;</pre>
            <pre>&lt;yappshot <strong>data-dc="24,25"</strong>&gt;&lt;/yappshot&gt;</pre>
            <pre>&lt;yappshot <strong>data-model="9"</strong>&gt;&lt;/yappshot&gt;</pre>
            <pre>&lt;yappshot <strong>data-model="9,11,15"</strong>&gt;&lt;/yappshot&gt;</pre>
            <pre>&lt;yappshot <strong>data-dc="25" data-model="9,11,15"</strong>&gt;&lt;/yappshot&gt;</pre>
            <p>Так же возможно использование вместо тега div, тегов <strong>span, p, input(hidden)</strong> и т.п.</p>
            
            <p>Есть возможность автоматически скрывать ненужные автомобили или показать карточку автомобиля по прямой ссылке (комбинировать нельзя):</p>
            <pre>https://site/hot/<strong>#/yappshot-model/Polo</strong></pre>
            <pre>https://site/hot/<strong>#/yappshot-dc/volkswagen-yug-avto-krasnodar_pkw</strong></pre>
            <pre>https://site/hot/<strong>#/yappshot-item/3306</strong></pre>

            <p>Есть возможность изменять Заголовок модуля не только в настройках, но и при подключении на сайт:</p>
            <pre>&lt;yappshot <strong>data-title="Другой тайтл"</strong>&gt;&lt;/yappshot&gt;</pre>
            
            <p>Есть возможность показать слайдер супергорячих преложения отдельно от основного приложения</p>
            <pre>&lt;yappshot <strong>data-block="slider"</strong>&gt;&lt;/yappshot&gt;</pre>
            </div><!-- /.box-body -->
        </div>

        <div class="box box-warning box-solid collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title">Инструкция по формированию архива для загрузки</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                    </button>
                </div>
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

        <div class="box box-danger box-solid collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title">ВНИМАНИЕ! Названия моделей в файле .csv должны быть ТОЛЬКО из списков ниже</h3>
                <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                </button>
                </div>
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
                        <li><?=str_replace('Š', 'S', $b['en_name'])?> <?=$i['en_name']?></li>
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