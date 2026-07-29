<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    
  <section class="content-header">
    <h1><?=$app->Auction->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Auction->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
    <?php if ( !$app->User->isAdministrator( $authUser->ssid ) && !in_array($authUser->id, $app->Auction->getAdmins()) ) { HTML::Denied(); } else { ?>
    
		<?php if ($POSTRes) HTML::Error($POSTRes); ?>
		<?php include __DIR__.'/layouts/lists/_categories.php'; ?>
        <?php include __DIR__.'/layouts/lists/_admins.php'; ?>
        <?php include __DIR__.'/layouts/lists/_templates.php'; ?>
  	
    <?php } // / Denied ?>
  </section>
  
</div>
<!-- /.content-wrapper -->