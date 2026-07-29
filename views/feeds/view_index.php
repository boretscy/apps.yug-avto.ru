<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?=$app->Feeds->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
    </section>
  
    <!-- Main content -->
    <section class="content">
        
        <?php if ( $app->Feeds->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
        
        <?php if ($POSTRes) HTML::Error($POSTRes); ?>
        
        <div class="row">
            <div class="col-md-12">
            
                <div class="box box-info box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">Яндекс.Карточки</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <h5><?= $item['ru_name'];?></h5>
                        <div class="input-group">
                            <div class="input-group-addon" style="background: #eee; cursor: pointer;" role="copy">
                                <i class="fa fa-copy"></i>
                            </div>
                            <input type="text" class="form-control" readonly value="https://apps.yug-avto.ru/upload/Feeds/yug-avto_yandex.csv" />
                        </div>
                    </div><!-- /.box-body -->
                    <div class="box-body">
                        <h4>Загрузка файла в интерфейсе</h4>
                        <p>На странице сети перейдите в раздел <strong>Филиалы</strong>. В блоке <strong>Управление филиалами</strong> выберите <strong>Файл</strong>.</p>
                        <p>Укажите ссылку на подготовленный файл, выберите тип <strong>«csv»</strong> и нажмите <strong>Проверить</strong>. Проверка файла может занять несколько часов.</p>
                        <p>Если проверка файла прошла успешно, нажмите кнопку <strong>Опубликовать</strong>. В открывшемся окне проверьте изменения в филиалах. На карте может быть показано до 50 филиалов с изменениями. Нажмите <strong>Все верно, начать загрузку в базу</strong>. Данные из файла пройдут модерацию и будут загружены в базу Справочника. При большом объеме данных загрузка может занять несколько суток.</p>
                    </div>
                </div>

                <?php include __DIR__.'/layouts/lists/_tuning.php'; ?>
            
            </div>
        </div>
    
    </section>
    <!-- /.content -->
  
</div>