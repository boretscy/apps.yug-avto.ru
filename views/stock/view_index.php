<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><?=$app->Stock->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
        
        <?php if ( $app->Stock->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
        
        <div class="row">
            <div class="col-md-12">

                <?php include __DIR__.'/layouts/forms/_settings.php'; ?>
                <?php include __DIR__.'/layouts/lists/_settings.php'; ?>
                
            </div>
        </div>
    
    </section>
    <!-- /.content -->
  
</div>