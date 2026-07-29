<?php
    if ( $currentRoute->action == 'delete' ) $app->Hot->delItem($currentRoute->id);
    if ( $currentRoute->action == 'activate' ) $app->Hot->activateItem($currentRoute->id);
    if ( $currentRoute->action == 'deactivate' ) $app->Hot->deactivateItem($currentRoute->id);
	$site_id = ($_GET['site_id']) ? $_GET['site_id'] : 'All';
	$arRes = $app->Hot->getItems( $authUser, $site_id );
?>

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Hot->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Hot->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
    <?php if ($POSTRes) HTML::Error($POSTRes); ?>
    
    <div class="row">
      <div class="col-md-12">
        
        <div class="box box-primary">
          
          <div class="box-header with-border">
            <h3 class="box-title">Список предложений</h3>
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
                <form method="get">
                 
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
            
            <table id="data-table-items" class="table table-hover table-striped table-condensed dataTable">
              <thead>
                <tr>
                <th style="width: 5%">ID</th>
                  <th style="width: 15%">Модель</th>
                  <th style="width: 15%">Комплектация</th>
                  <th style="width: 10%">Цвет</th>
                  <th style="width: 10%">VIN</th>
                  <th style="width: 15%">ДЦ</th>
                  <th style="width: 15%">Цена, ₽</th>
                  <th style="width: 5%">Фото</th>
                  <th style="width: 10%"></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ( $arRes as $item ) { ?>
                <tr>
                  <td><?=$item['id']?></td>
                  <td><?=$app->Hot->getModel($item['model_id'])['ru_name']?></td>
                  <td><?=$item['complectation']?></td>
                  <td><?=$item['color']?></td>
                  <td><?=$item['vin']?></td>
                  <td><?=$app->getDC($item['dc_id'])['ru_name']?></td>
                  <td>
				    <strike><?=number_format((float)$item['price'], 0, '', ' ')?></strike>&nbsp;
                    <?=number_format((float)$item['spec_price'], 0, '', ' ')?>
                  </td>
                  <td><img src="<?=$item['images'][0]['url']?>" style="width: 30px;" /></td>
                  <td class="text-right">
                    <a href="/hot/items/<?=(($item['slider'])?'deactivate':'activate')?>/<?=$item['id']?>/" role="delete">
                      <span 
                        class="label label-<?=(($item['slider'])?'success':'default')?> hint--top" 
                        aria-label="<?=(($item['slider'])?'Убрать из слайдера':'Добавить в слайдер')?>"
                      >
                        <i class="fa fa-files-o" aria-hidden="true"></i>
                      </span>
                    </a>
                    <?php /*
                    <a href="/hot/items/edit/<?=$item['id']?>/">
                        <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                    </a>
                    */ ?>
                    <a href="/hot/items/delete/<?=$item['id']?>/" role="delete">
                      <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                    </a>
                  </td>
                </tr>
                <?php } // foreach items ?>
              </tbody>
            </table>
          </div>
            
        </div>
          
      </div>
    </div>
  
  </section>
  <!-- /.content -->