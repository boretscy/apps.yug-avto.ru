<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Cis->AppInfo()->ru_name?> <small>Учет стоимости доп оборудования в ценах новых атомобилей</small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Cis->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
    <div class="row">
      <div class="col-md-12">
        
          <?php include __DIR__.Route::getSubRoute($currentRoute).'.php'; ?>
          
      </div>
    </div>
  
  </section>
  <!-- /.content -->
  
</div>