<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    
  <section class="content-header">
    <h1><?=$app->Foots->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Foots->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
    <?php if ( !$app->User->isAdministrator( $authUser->ssid ) ) { HTML::Denied(); } else { ?>
    
		<?php if ($POSTRes) HTML::Error($POSTRes); ?>
        <?php include __DIR__.'/layouts/lists/_users.php'; ?>
        <?php include __DIR__.'/layouts/lists/_hostess.php'; ?>
        <?php include __DIR__.'/layouts/lists/_targets.php'; ?>
  	
    <?php } // / Denied ?>
  </section>
  
</div>
<!-- /.content-wrapper -->