<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    
  <section class="content-header">
    <h1><?=$app->Sale->AppInfo()->ru_name?> <small>UTM-метки</small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Sale->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    <?php $arRes = $app->YApps_GetBrandsByIds( $app->YApps_GetBrandsIDsByUser($authUser) ); ?>

    <div class="row">
        <div class="col-md-12">
            <?php foreach( $arRes as $item ) { ?>
                <div class="box box-primary">
                    <div class="box-body">
                        
                        <h3><?= $item['ru_name'];?></h3>

                        <h5><strong>Email</strong></h5>
                        <p>
                            https://sale.yug-avto.ru/?utm_source=sendpulse&utm_medium=email&utm_campaign=<?= date('mY');?>sale&utm_content=<?= $item['url_key']?>#<?= $item['url_key']?>
                            <br />
                            <?= file_get_contents(
                                'https://is.gd/create.php?format=simple&url='.
                                urlencode('https://sale.yug-avto.ru/?utm_source=sendpulse&utm_medium=email&utm_campaign='.date('mY').'sale&utm_content='.$item['url_key'].'#'.$item['url_key'])
                            );?>
                        </p>
                        <hr />

                        <h5><strong>SMS</strong></h5>
                        <p>
                            https://sale.yug-avto.ru/?utm_source=crm&utm_medium=sms&utm_campaign=<?= date('mY');?>sale&utm_content=<?= $item['url_key']?>#<?= $item['url_key']?>
                            <br />
                            <?= file_get_contents(
                                'https://is.gd/create.php?format=simple&url='.
                                urlencode('https://sale.yug-avto.ru/?utm_source=crm&utm_medium=sms&utm_campaign='.date('mY').'sale&utm_content='.$item['url_key'].'#'.$item['url_key'])
                                );?>
                        </p>
                        <hr />

                        <h5><strong>Google</strong></h5>
                        <p>
                            https://sale.yug-avto.ru/?utm_source=google_context&utm_medium=cpc&utm_campaign=<?= date('mY');?>sale&utm_content=<?= $item['url_key']?>#<?= $item['url_key']?>
                            <br />
                            <?= file_get_contents(
                                'https://is.gd/create.php?format=simple&url='.
                                urlencode('https://sale.yug-avto.ru/?utm_source=google_context&utm_medium=cpc&utm_campaign='.date('mY').'sale&utm_content='.$item['url_key'].'#'.$item['url_key'])
                            );?>
                        </p>
                        <hr />

                        <h5><strong>Yandex</strong></h5>
                        <p>
                            https://sale.yug-avto.ru/?utm_source=yandex_context&utm_medium=cpc&utm_campaign=<?= date('mY');?>sale&utm_content=<?= $item['url_key']?>#<?= $item['url_key']?>
                            <br />
                            <?= file_get_contents(
                                'https://is.gd/create.php?format=simple&url='.
                                urlencode('https://sale.yug-avto.ru/?utm_source=yandex_context&utm_medium=cpc&utm_campaign='.date('mY').'sale&utm_content='.$item['url_key'].'#'.$item['url_key'])
                            );?>
                        </p>
                        <hr />

                    </div>
                </div>
                <?php } // foreach User Brands ?>
        </div>
    </div>

  </section>
  
</div>
<!-- /.content-wrapper -->