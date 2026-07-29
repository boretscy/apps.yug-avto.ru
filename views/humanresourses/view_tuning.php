<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    
  <section class="content-header">
    <h1><?=$app->HumanResourses->AppInfo()->ru_name?> <small>Установки</small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->HumanResourses->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
    <?php if ( !$app->User->isAdministrator( $authUser->ssid ) && !in_array($authUser->id, $app->HumanResourses->getAdmins()) ) { HTML::Denied(); } else { ?>
    
        <?php include __DIR__.'/layouts/lists/_managers.php'; ?>
  	
    <?php } // / Denied ?>
  </section>
  
</div>
<!-- /.content-wrapper -->