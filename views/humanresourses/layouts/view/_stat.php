<?php $arRes = $app->HumanResourses->getStat($currentRoute->id); ?>

<section class="content-header">
  <h1><?=$app->HumanResourses->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <?php if ( $app->HumanResourses->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
  
  <div class="row">
    <div class="col-md-12">
      
      <?php if ($POSTRes) HTML::Error($POSTRes); ?>
      
      <div class="box box-primary">
        
        <div class="box-header with-border"><h3 class="box-title">Просмотр</h3></div>
        
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/humanresourses/stat/edit/<?=$arRes['id']?>/" class="btn btn-default btn-flat"><i class="fa fa-long-arrow-left" aria-hidden="true"></i> вернуться</a>
            <a href="/humanresourses/stat/send/<?=$arRes['id']?>/" class="btn btn-success btn-flat" role="delete"><i class="fa fa-paper-plane-o" aria-hidden="true"></i> Отправить</a>
          </div>
        </div>
        
        <div class="box-body"><?=$arRes['html']?></div>
      
      </div>
    </div>
  </div>
  
</section>