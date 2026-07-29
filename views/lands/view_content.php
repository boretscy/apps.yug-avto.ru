<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    
  <section class="content-header">
    <h1><?=$app->Lands->AppInfo()->ru_name?> <small>Контент</small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Sale->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    <?php include __DIR__.Route::getSubRoute($currentRoute).'.php'; ?>
    
  </section>
  
</div>
<!-- /.content-wrapper -->