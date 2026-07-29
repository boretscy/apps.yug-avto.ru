<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Auction->AppInfo()->ru_name?> <small>Трейдеры</small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Auction->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
    <?php include __DIR__.Route::getSubRoute($currentRoute).'.php'; ?>
  
  </section>
  <!-- /.content -->
  
</div>