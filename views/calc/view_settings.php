  
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><?=$app->Calc->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
      
      <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
      
      <div class="row">
        
        <?php // CheckWorks ?>
        <div class="col-md-6">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Работы ТО</h3>
            </div>
            <?php if ( $app->User->isAdminUser($authUser) ) { ?>
            <div class="box-body">
              <div class="col-xs-4">
                <a href="/calc/checkworks/new/" class="btn btn-block btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить Работу</a>
              </div>
            </div>
            <?php } ?>
            
            <div class="box-body">
              <?php include __DIR__.'/layouts/lists/_checkworks.php'; ?>
            </div>
            <!-- /.box-body -->
          </div>
        </div>
        
        <?php // CheckPoints ?>
        <div class="col-md-6">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Периодичность ТО</h3>
            </div>
            <?php if ( $app->User->isAdminUser($authUser) ) { ?>
            <div class="box-body">
              <div class="col-xs-4">
                <a href="/calc/checkpoints/new/" class="btn btn-block btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить Период</a>
              </div>
            </div>
            <?php } ?>
            
            <div class="box-body">
              <?php include __DIR__.'/layouts/lists/_checkpoints.php'; ?>
            </div>
            <!-- /.box-body -->
          </div>
        </div>
        
        <div class="clear"></div>
        
        <?php // Discounts ?>
        <div class="col-md-6">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Скидки</h3>
            </div>
            <?php if ( $app->User->isAdminUser($authUser) ) { ?>
            <div class="box-body">
              <div class="col-xs-6">
                <a href="/calc/discounts/new/" class="btn btn-block btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить Скидку</a>
              </div>
            </div>
            <?php } ?>
            
            <div class="box-body">
              <?php include __DIR__.'/layouts/lists/_discounts.php'; ?>
            </div>
            <!-- /.box-body -->
          </div>
        </div>
        
        <?php // Work Values ?>
        <div class="col-md-6">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Значения</h3>
            </div>
            <?php if ( $app->User->isAdminUser($authUser) ) { ?>
            <div class="box-body">
              <div class="col-xs-6">
                <a href="/calc/workvalues/new/" class="btn btn-block btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить Значение</a>
              </div>
            </div>
            <?php } ?>
            
            <div class="box-body">
              <?php include __DIR__.'/layouts/lists/_workvalues.php'; ?>
            </div>
            <!-- /.box-body -->
          </div>
        </div>
        
      </div>
    
    </section>
    <!-- /.content -->
        
  </div>
  <!-- /.content-wrapper -->
  
