<?php if ( $currentRoute->action == 'delete' ) $app->Hot->delModel( $currentRoute->id ); ?>
<?php $arRes = $app->Hot->getModels( $authUser ); ?>
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
            
			<div class="box-body">
		      <div class="col-xs-12">
			    <a href="/hot/models/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить модель</a>
			  </div>
			</div>
            
            <div class="box-body">
              
              <table id="data-table-models" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 20%">Сайт</th>
                    <th style="width: 25%">Название</th>
                    <th style="width: 15%">Картинка</th>
                    <th style="width: 10%">Сорт</th>
                    <th style="width: 25%">&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach( $arRes as $arItem ) { ?>
                  <tr>
                    <td><a href="/hot/models/edit/<?=$arItem['id']?>/"><?=$arItem['id']?></a></td>
                    <td><?=$app->getSite($arItem['site_id'])['ru_name']?></td>
                    <td><a href="/hot/models/edit/<?=$arItem['id']?>/"><?=$arItem['ru_name']?></a></td>
                    <td><img src="<?=$arItem['image_link']?>" style="width: 50px;" /></td>
                    <td><?=$arItem['sort']?></td>
                    <td class="text-right">
                      <a href="/hot/models/edit/<?=$arItem['id']?>/">
                        <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                      </a>
                      <a href="/hot/models/delete/<?=$arItem['id']?>/" role="delete">
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