<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Cis->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Cis->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
    <?php if ($POSTRes) HTML::Error($POSTRes); ?>
    
    <div class="row">
      <div class="col-md-12">
          <?php include __DIR__.'/layouts/lists/_bodies.php';?>
          <?php include __DIR__.'/layouts/lists/_colors.php';?>
          <?php include __DIR__.'/layouts/lists/_drives.php';?>
          <?php include __DIR__.'/layouts/lists/_engines.php';?>
          <?php include __DIR__.'/layouts/lists/_transmissions.php';?>
          <?php include __DIR__.'/layouts/lists/_dealerships.php';?>
      </div>
    </div>
  
  </section>
  <!-- /.content -->
  
</div>