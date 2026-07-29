
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <?php if ( $_GET['action'] == 'refresh' ) include __DIR__.'/refresh_models.php'; ?> 
  <?php include __DIR__.Route::getSubRoute($currentRoute).'.php'; ?>
  
</div>
<!-- /.content-wrapper -->