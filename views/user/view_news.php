<?php $arRes = $app->News->getUserAppNews( 20 ) ?>
 <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
  
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><?=$app->User->AppInfo()->ru_name?> <small>Новости и релизы</small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
      
      <div class="row">
        
        <div class="col-md-12">
          
          <?php foreach ( $arRes as $k => $item ) { ?>
          
          <div class="box box-<?=(($k==0)?'info':'default')?> box-solid">
            <div class="box-header with-border">
              <h3 class="box-title"><?=$app->Apps->getAppById($item['app_id'])['ru_name']?></h3>
            </div><!-- /.box-header -->
            <div class="box-body">
              <p><small><?=date('d.m.Y H:i', $item['timestamp'])?></small></p>
              <p><strong><?=$item['title']?></strong></p>
              <?=$item['text']?>
            </div><!-- /.box-body -->
          </div>
          
          <?php } ?>
          
        </div>
        
      </div>
    
    </section>
    <!-- /.content -->
    
  </div>