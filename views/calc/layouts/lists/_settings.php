<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?=$app->Calc->AppInfo()->ru_name?> <small>Настройки приложения</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <?php if ($POSTRes) HTML::Error($POSTRes); ?>
  
  <div class="row">
    
    <div class="col-md-12">
      
      <div class="callout callout-warning">
        <h4>Настройки приложения не определены</h4>
      
        <p>This is a green callout.</p>
      </div>
      
    </div>
    
  </div>

</section>
<!-- /.content -->



