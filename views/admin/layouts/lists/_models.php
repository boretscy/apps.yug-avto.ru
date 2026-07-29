<?php if ( $currentRoute->action == 'delete' ) $app->delBrand( $currentRoute->id ); ?>
<?php $arRes = $app->YApps_GetModels(); ?>
<!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Настройки <small>Модели</small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
      
      <div class="row">
        
        <div class="col-md-12">
          
          <div class="box box-primary">
            
            <div class="box-header with-border">
              
              <h3 class="box-title">Модели (<?=count($arRes)?>)</h3>
                
              <!-- /.box-tools -->
            </div>
            
            <div class="box-body"><a href="/admin/models/?action=refresh" class="btn btn-info btn-flat"><i class="fa fa-refresh" aria-hidden="true"></i> Обновить вручную</a></div>
            
            <div class="box-body">
              
              <div class="callout callout-default">
                <p>Список актуальных моделей парсится автоматически с сайта холдинга раз в сутки</p>
              </div>
              
              
              
              <table id="data-table-models" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 10%">Название</th>
                    <th style="width: 10%">На русском</th>
                    <th style="width: 10%">Бренд</th>
                    <th style="width: 5%">Изображение</th>
                    <th style="width: 10%">Кол-во в наличии</th>
                    <th style="width: 40%">Дилерские центры</th>
                    <th style="width: 10%">&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach( $arRes as $arItem ) { ?>
                  <tr>
                    <td><a href="/admin/models/edit/<?=$arItem['id']?>/"><?=$arItem['id']?></a></td>
                    <td><a href="/admin/models/edit/<?=$arItem['id']?>/"><?=$arItem['en_name']?></a></td>
                    <td><a href="/admin/models/edit/<?=$arItem['id']?>/"><?=$arItem['ru_name']?></a></td>
                    <td><?=$app->YApps_GetBrand($arItem['brand_id'])['en_name']?></td>
                    <td><img src="<?=$arItem['photo']?>" style="width: 60px;" /></td>
                    <td><?=$arItem['in_stock']?></td>
                    <td><?=implode(', ', $app->YApps_GetDCRuNamesByModel($arItem['id']))?></td>
                    <td class="text-right">
                      <a href="/admin/models/edit/<?=$arItem['id']?>/">
                        <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                      </a>
                      <a href="#<?php /* /admin/brands/delete/<?=$arItem['id']?>/ */ ?>" role="delete">
                        <span class="label label-default hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
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