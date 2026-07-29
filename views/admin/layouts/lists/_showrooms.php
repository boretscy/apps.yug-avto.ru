<?php if ( $currentRoute->action == 'delete' ) $app->delShowroom( $currentRoute->id ); ?>
<?php $arRes = $app->YApps_GetShowrooms(); ?>
<!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Настройки <small>Витрины</small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
      
      <div class="row">
        
        <div class="col-md-12">
          
          
          
          <div class="box box-primary">
            
            <div class="box-header with-border">
              
              <h3 class="box-title">Витрины (<?=count($arRes)?>)</h3>
                
              <!-- /.box-tools -->
            </div>
            
            <div class="box-body">
              <div class="col-xs-12">
                <a href="/admin/showrooms/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
              </div>
            </div>
            
            <div class="box-body">
              
              <?php // Helper::sp( $globalSites ); ?>
              
              <table id="data-table-showrooms" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 10%">ID</th>
                    <th style="width: 20%">Сайт</th>
                    <th style="width: 55%">Ссылка</th>
                    <th style="width: 15%">&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach( $arRes as $item ) { ?>
                  <tr>
                    <td><?=$item['id']?></td>
                    <td><?=$GLOBALS['USER_SITES']['sites'][$item['site_id']]['ru_name']?></td>
                    <td><?=$item['url']?></td>
                    <td class="text-right">
                      <a href="/admin/showrooms/edit/<?=$item['id']?>/">
                        <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                      </a>
                      <a href="#/admin/showrooms/delete/<?=$item['id']?>/" role="delete">
                        <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                      </a>
                    </td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
              
            </div>
            <!-- /.box-body -->
          </div>
        
        </div>
      </div>
      
    </section>