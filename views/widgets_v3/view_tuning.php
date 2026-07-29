<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    
  <section class="content-header">
    <h1><?=$app->Widgets->AppInfo()->ru_name?> <small>Установки</small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ($POSTRes) HTML::Error($POSTRes); ?>
    
    <?php if ( $app->Widgets->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
	<?php include __DIR__.'/layouts/lists/_sites.php'; ?>
  
  </section>
  
</div>
<!-- /.content-wrapper -->