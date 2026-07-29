<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
  
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><?=$app->Parts->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
      
      <?php if ($POSTRes) HTML::Error($POSTRes); ?>
      
      <div class="row">
        
        <div class="col-md-12">
        
          <div class="callout callout-warning">
            <h4>Настройки для приложения "<?=$app->Parts->AppInfo()->ru_name?>" не определены</h4>
            <p>...</p>
          </div>
          
          <?php	Helper::sp($currentRoute); ?>
          
        </div>
        
      </div>
    
    </section>
    <!-- /.content -->
    
  </div>