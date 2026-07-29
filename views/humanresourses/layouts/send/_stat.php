<?php $arRes = $app->HumanResourses->getStat($currentRoute->id); ?>
<?php if ( $currentRoute->id ) $POSTRes = $app->HumanResourses->sendMessage( $currentRoute->id ); ?>

<section class="content-header">
  <h1><?=$app->HumanResourses->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <?php if ( $app->HumanResourses->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
  <?php if ($POSTRes) HTML::Error($POSTRes); ?>
 
  <div class="row">
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border"><h3 class="box-title">Просмотр</h3></div>
        <div class="box-body"><?=$arRes['html']?></div>
      
      </div>
    </div>
  </div>
  
</section>