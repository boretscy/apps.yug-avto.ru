<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    
  <section class="content-header">
    <h1><?=$app->Sale->AppInfo()->ru_name?> <small>Автомобили</small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Sale->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
    <?php // if ( !$app->User->isAdministrator( $authUser->ssid ) && !in_array($authUser->id, $app->Auction->getAdmins()) ) { HTML::Denied(); } else { ?>
    
        <?php include __DIR__.Route::getSubRoute($currentRoute).'.php'; ?>
  	
    <?php // } // / Denied ?>
  </section>
  
</div>
<!-- /.content-wrapper -->