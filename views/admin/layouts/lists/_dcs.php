<?php if ( $currentRoute->action == 'delete' ) $app->delDC( $currentRoute->id ); ?>
<?php if ( $_GET['update_stock_dc'] ) $GETRes = $app->YApps_RefreshDCAV($_GET['update_stock_dc']); ?>
<?php $arRes = $app->getDCs(); ?>
<!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Настройки <small>Дилерские центры</small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
      
      <div class="row">
        
        <div class="col-md-12">
          
          <div class="box box-primary">
            
            <div class="box-header with-border">
              
              <h3 class="box-title">Дилерские центры (<?=count($arRes)?>)</h3>
                
              <!-- /.box-tools -->
            </div>
            
			<div class="box-body">
		      <div class="col-xs-12">
			    <a href="/admin/dcs/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
			  </div>
			</div>
            
            <div class="box-body">
              
              <?php if ( $GETRes ) HTML::Error( $GETRes ); ?>
              
              <table id="data-table-dcs" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 15%">Сайт</th>
                    <th style="width: 15%">Название</th>
                    <th style="width: 15%">Телефон</th>
                    <th style="width: 15%">Координаты</th>
                    <th style="width: 15%">Алиас</th>
                    <th style="width: 5%">Сорт</th>
                    <th style="width: 10%">&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach( $arRes as $item ) { ?>
                  <tr>
                    <td><a href="/admin/dcs/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                    <td><a href="/admin/dcs/edit/<?=$item['id']?>/"><?=$app->getSite($item['site_id'])['ru_name']?></a></td>
                    <td><a href="/admin/dcs/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                    <td><?=Helper::formatPhoneOut($item['phone'])?></td>
                    <td><?=$item['coords_lat']?>, <?=$item['coords_lon']?></td>
                    <td><?=$item['url_key']?></td>
                    <td><?=$item['sort']?></td>
                    <td class="text-right">
                      <a href="/admin/dcs/?update_stock_dc=<?=((in_array($item['id'], [9,10,11,12,13,14]))?str_replace(['_pkw', '_nfz'], ['',''], $item['url_key']):$item['url_key'])?>">
                        <span class="label label-default hint--top" aria-label="Обновить кол-во АВН"><i class="fa fa-refresh" aria-hidden="true"></i></span>
                      </a>
                      &nbsp;&nbsp;
                      <a href="/admin/dcs/edit/<?=$item['id']?>/">
                        <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                      </a>
                      <a href="/admin/dcs/delete/<?=$item['id']?>/" role="delete">
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