<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  
  <section class="content-header">
    <h1><?=$app->Foots->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
  
    <div class="row">
      <div class="col-md-12">
        <?php include __DIR__.Route::getSubRoute($currentRoute).'.php'; ?>
      </div>
    </div>
  
  </section>
  
</div>
<!-- /.content-wrapper -->