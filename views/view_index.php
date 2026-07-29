<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
    <section class="content">

        <?php $arNews = $app->News->getUserAppNews( 3 ) ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info box-solid">
                    <div class="box-header with-border"><h3 class="box-title">Свежие новости и релизы</h3></div>
                    <div class="box-body">
                        <?php foreach ( $arNews as $k => $item ) { ?>
                        <div class="box">
                            <div class="box-header">
                                <h3 class="box-title"><?=$app->Apps->getAppById($item['app_id'])['ru_name']?></h3>
                            </div>
                            <div class="box-body">
                                <p><small><?=date('d.m.Y H:i', $item['timestamp'])?></small></p>
                                <p><strong><?=$item['title']?></strong></p>
                                <?=$item['text']?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">Подключение приложений на сайт</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <strong>Новые виджеты</strong>
                        <pre>
&lt;script&gt;
    document.addEventListener("DOMContentLoaded", () => {
        var yappstoken= '<?=$authUser->public_key?>';
        let yappwidgets = document.createElement('yappwidgets');
            yappwidgets.id = 'YAppWidgets';
        document.body.appendChild(yappwidgets);
        let yappsscript = document.createElement('script');
            yappsscript.type = 'text/javascript';
            yappsscript.charset = 'utf-8';
            yappsscript.src = 'https://apps.yug-avto.ru/API/get/vue-script/?token='+yappstoken;
        document.body.appendChild(yappsscript);
    });
&lt;/script&gt;
                        </pre>

                        <strong>Старые виджеты</strong> + остальные приложения
                        <pre>
&lt;script&gt;
    document.addEventListener("DOMContentLoaded", () => {
        var t = '<?=$authUser->public_key?>', r = location.href, 
            s = document.createElement('script');
        s.type = 'text/javascript';
        s.charset = 'utf-8';
        s.src = 'https://apps.yug-avto.ru/API/get/script/'+'?token='+t+'&r='+r;
        document.body.append(s);
    });
&lt;/script&gt;
                        </pre>
                        или
                        <pre>
&lt;script src="https://apps.yug-avto.ru/API/get/script/?token=<?=$authUser->public_key?>" charset="utf-8"&gt;&lt;/script&gt;
                        </pre>
                    </div><!-- /.box-body -->
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-body box-profile">
                        <img class="profile-user-img img-responsive img-circle" src="<?=(($authUser->avatar)?$authUser->avatar:'/assets/img/avatar5.png')?>" alt="User profile picture">
                        <h3 class="profile-username text-center"><?=$authUser->name?></h3>
                        <?php $arRes = $app->Apps->getAll( $authUser ); ?>
                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item text-center">
                            <?php foreach ( $arRes as $item ) { ?>
                                <?php if ( $item['settings']['view_in_menu'] ) { ?>
                                <a class="btn btn-app" href="/<?=$item['settings']['url_key']?>/">
                                <?php $appNews = $app->News->getCountNewApp( $item['settings']['id'] ); ?>
                                <?php if ( $appNews > 0 ) { ?>
                                <span class="badge bg-aqua"><?=$appNews?></span>
                                <?php } ?>
                                <i class="fa fa-<?=$item['settings']['fa_icon']?>"></i>  <?=$item['settings']['ru_name']?>
                                </a>
                                <?php } ?>
                            <?php } ?>
                            </li>
                        </ul>
                        <h3>Пользователь</h3>
                        <ul class="list-group list-group-unbordered">
                                <li class="list-group-item"><b>Компания</b> <span class="pull-right"><?=$authUser->company?></span></li>
                                <li class="list-group-item"><b>Email</b> <span class="pull-right"><?=$authUser->email?></span></li>
                                <li class="list-group-item"><b>Телефон</b> <span class="pull-right"><?=$authUser->phone?></span></li>
                                <li class="list-group-item">
                                <div class="box box-default box-solid collapsed-box">
                                    <div class="box-header with-border">
                                    <h3 class="box-title">Сайты</h3>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                    <!-- /.box-tools -->
                                    </div>
                                    <!-- /.box-header -->
                                    <div class="box-body" style="">
                                    <?=$app->getUserSites($authUser)['sites_string']?>
                                    </div>
                                    <!-- /.box-body -->
                                </div>
                                </li>
                                <li class="list-group-item">
                                <div class="box box-default box-solid collapsed-box">
                                    <div class="box-header with-border">
                                    <h3 class="box-title">Приложения</h3>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                    <!-- /.box-tools -->
                                    </div>
                                    <!-- /.box-header -->
                                    <div class="box-body" style="">
                                    <?=$app->Apps->getString($authUser)['apps_string']?>
                                    </div>
                                    <!-- /.box-body -->
                                </div>
                                </li>
                                <li class="list-group-item">
                                <div class="box box-default box-solid collapsed-box">
                                    <div class="box-header with-border">
                                    <h3 class="box-title">Ключ API</h3>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                    <!-- /.box-tools -->
                                    </div>
                                    <!-- /.box-header -->
                                    <div class="box-body" style="">
                                    <?=$authUser->public_key?>
                                    </div>
                                    <!-- /.box-body -->
                                </div>
                                </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Настройки пользователя</h3>
                    </div>
                    <div class="box-body">
                        <?php include __DIR__.'/user/layouts/_user_form.php'; ?>
                    </div>
                    
                </div>
            </div>
            
        </div>
        
    </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->
