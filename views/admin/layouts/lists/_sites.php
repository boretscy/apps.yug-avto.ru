<?php if ( $currentRoute->action == 'delete' ) $app->delSite( $currentRoute->id ); ?>
<?php $arRes = $app->getSites(); ?>
<!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Настройки <small>Сайты</small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
      
      <div class="row">
        
        <div class="col-md-12">
          
          <div class="box box-primary">
            
            <div class="box-header with-border">
              
              <h3 class="box-title">Сайты (<?=count($arRes)?>)</h3>
                
              <!-- /.box-tools -->
            </div>
            
			<div class="box-body">
		      <div class="col-xs-12">
			    <a href="/admin/sites/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
			  </div>
			</div>
            
            <div class="box-body">
              
              <table id="data-table-sites" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 15%">URL</th>
                    <th style="width: 15%">Название</th>
                    <th style="width: 15%">Бренд</th>
                    <th style="width: 5%">Matomo</th>
                    <th style="width: 15%">Yandex</th>
                    <th style="width: 15%">Google</th>
                    <th style="width: 5%">&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach( $arRes as $arItem ) { ?>
                  <tr>
                    <td><a href="/admin/sites/edit/<?=$arItem['id']?>/"><?=$arItem['id']?></a></td>
                    <td><a href="/admin/sites/edit/<?=$arItem['id']?>/"><?=$arItem['url']?></a></td>
                    <td><a href="/admin/sites/edit/<?=$arItem['id']?>/"><?=$arItem['ru_name']?></a></td>
                    <td><?=$arItem['brand_name']?></td>
                    <td><?=$arItem['piwik_id']?></td>
                    <td><?=$arItem['yandex_id']?></td>
                    <td><?=$arItem['google_id']?></td>
                    <td class="text-right">
                      <a href="/admin/sites/edit/<?=$arItem['id']?>/">
                        <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                      </a>
                      <a href="/admin/sites/delete/<?=$arItem['id']?>/" role="delete">
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