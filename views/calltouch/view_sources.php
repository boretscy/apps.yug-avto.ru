<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?=$app->Calltouch->AppInfo()->ru_name?> <small>Правила соответствия</small></h1>
    </section>
  
    <!-- Main content -->
    <section class="content">
        
        <?php if ( $app->Calltouch->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
        
        <?php if ($POSTRes) HTML::Error($POSTRes); ?>
        
        <div class="row">
            <div class="col-md-12">
                
                
                
            </div>
        </div>
    
    </section>
    <!-- /.content -->
  
</div>