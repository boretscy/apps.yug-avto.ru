  
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
	  
    <?php include ( $currentRoute->action ) ? __DIR__.Route::getSubRoute($currentRoute).'.php' : __DIR__.'/layouts/lists/_sites.php'; ?>
    
  </div>
  <!-- /.content-wrapper -->