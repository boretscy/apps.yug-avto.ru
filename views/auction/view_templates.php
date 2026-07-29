<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  
  <section class="content-header">
    <h1><?=$app->Auction->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
  
    <div class="row">
      <div class="col-md-12">
        
        <?php if ( $app->Auction->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
		
		<?php if ( !$app->User->isAdministrator( $authUser->ssid ) && !in_array($authUser->id, $app->Auction->getAdmins()) ) { HTML::Denied(); } else {
			
			include __DIR__.Route::getSubRoute($currentRoute).'.php';
			
		} ?>
      </div>
    </div>
  
  </section>
  
</div>
<!-- /.content-wrapper -->