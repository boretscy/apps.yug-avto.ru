<?php
	
	if ( $currentRoute->action == 'delete' ) $app->Parts->delItem($currentRoute->id);
	$page = ($_GET['page']) ? $_GET['page'] : 1;
	$site_id = ($_GET['site_id']) ? $_GET['site_id'] : $userSites['sites_ids'][0];
	$arRes = $app->Parts->getParts( $site_id, $page );
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Parts->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <div class="row">
      <div class="col-md-12">
        
        <div class="box box-primary">
        
          <div class="box-header with-border">
            <h3 class="box-title">Список запчастей</h3>
          </div>
          
          <div class="box-body">
            
			<?php if ($POSTRes) HTML::Error($POSTRes); ?>
            
            <div class="box box-default collapsed-box box-solid">
              <div class="box-header with-border">
                <h3 class="box-title">Фильтр. Привязка к сайту: <?=(($_GET['site_id'])?$app->getSite($_GET['site_id'])['ru_name']:'все')?></h3>
                <div class="box-tools pull-right"> <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button></div>
                <!-- /.box-tools -->
              </div>
              <!-- /.box-header -->
              <div class="box-body">
                <form method="get" action="/parts///">
                 
                    <?php
        
                          $formSet = [
                              'fields' => [
                                  [
                                      'type' => 'select',
                                      'name' => 'site_id',
                                      'placeholder' => 'Привязка к сайту',
                                      'items' => $userSites['sites'],
									  'value' => [$_GET['site_id']]
                                  ],
                              ],
                              'submit' => [
                                  'class' => 'primary',
                                  'text' => 'Применить'
                              ]
                          ];
                      ?>
                      
                      <?php HTML::Form( $formSet ); ?>
                
                </form>
              </div>
              <!-- /.box-body -->
            </div>
            
            <table id="data-table-stats" class="table table-hover table-striped table-condensed dataTable">
              <thead>
                <tr>
                  <th style="width: 10%">Артикул</th>
                  <th style="width: 35%">Наименование</th>
                  <th style="width: 8%">На складе</th>
                  <th style="width: 8%">Цена</th>
                  <th style="width: 10%">Производитель</th>
                  <th style="width: 8%">Сайт</th>
                  <th style="width: 8%">Мин. заказ</th>
                  <th style="width: 8%">Актуальность</th>
                  <th style="width: 5%"></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ( $arRes['items'] as $item ) { ?>
                <tr>
                  <td><?=$item['sku']?></td>
                  <td><?=$item['ru_name']?></td>
                  <td><?=$item['stock']?></td>
                  <td><?=number_format($item['price'], 2, '.', ' ')?></td>
                  <td><?=$item['manufacturer']?></td>
                  <td><?=$arRes['site']['ru_name']?></td>
                  <td><?=$item['min_order']?></td>
                  <td><?=date('d/m/Y H:i', $item['timestamp'])?></td>
                  <td class="text-right">
                  	<a href="/parts/item/edit/<?=$item['id']?>/">
                      <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                    </a>
                    <a href="/parts//delete/<?=$item['id']?>/" role="delete">
                      <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                    </a>
                  </td>
                </tr>
                <? } ?>
              </tbody>
            </table>
          </div>
          
          <?php $p =  intdiv($arRes['count'], 1000) + 1; ?>
        
          <div class="box-body">
            <div class="btn-group">
              <?php for ( $i = 1; $i <= $p; $i++ ) { ?>
              <a href="/parts///?page=<?=$i?>" class="btn btn-<?=(((int)$page == $i)?'info':'default')?> btn-sm"><?=$i?></a>
              <? } ?>
            </div>
          </div>
            
        </div>
        
      </div>
    </div>
  
  </section>
  <!-- /.content -->
  
</div>