  
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">

    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><?=$app->Calc->AppInfo()->ru_name?> <small>Работы ТО</small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
      
      <div class="row">
        
        <div class="col-md-12">
          
          <div class="box box-primary">
            
            <div class="box-header with-border">
              
              <h3 class="box-title">Работы ТО</h3>
                
              <!-- /.box-tools -->
            </div>
             
            <div class="box-body">
              
              <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
    
			  <?php include __DIR__.Route::getSubRoute($currentRoute).'.php'; ?>
          
            </div>
            <!-- /.box-body -->
          </div>
          
        </div>
        
      </div>
    
    </section>
    <!-- /.content -->
        
  </div>
  <!-- /.content-wrapper -->