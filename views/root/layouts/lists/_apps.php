<?php if ( $currentRoute->action == 'delete' ) $app->delSite( $currentRoute->id ); ?>
<?php $arRes = $app->Apps->getApps(); ?>
<!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Настройки <small>Приложения</small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
      
      <div class="row">
        
        <div class="col-md-12">
          
          <div class="box box-primary">
            
            <div class="box-header with-border">
              
              <h3 class="box-title">Приложения (<?=count($arRes)?>)</h3>
                
              <!-- /.box-tools -->
            </div>
            
			<div class="box-body">
		      <div class="col-xs-12">
			    <a href="/root/apps/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
			  </div>
			</div>
            
            <div class="box-body">
              
              <table id="data-table-apps" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 15%">Название</th>
                    <th style="width: 15%">Class</th>
                    <th style="width: 15%">URL</th>
                    <th style="width: 15%">Иконка</th>
                    <th style="width: 10%">Меню</th>
                    <th style="width: 10%">Активность</th>
                    <th style="width: 10%">Sort</th>
                    <th style="width: 5%">&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach( $arRes as $arItem ) { ?>
                  <tr>
                    <td><?=$arItem['id']?></td>
                    <td><a href="/root/apps/edit/<?=$arItem['id']?>/"><?=$arItem['ru_name']?></a></td>
                    <td>
                      <?php if ( file_exists($_SERVER['DOCUMENT_ROOT'].'/core/YApps/'.$arItem['class'].'.php') ) { ?>
                      <span class="label label-default hint--top" aria-label="Файл скрипта"><i class="fa fa-check" aria-hidden="true"></i></span>&nbsp;
                      <?php } else { ?>
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                      <?php } ?>
					  <?=$arItem['class']?>
                    </td>
                    <td><?=$arItem['url_key']?></td>
                    <td><i class="fa fa-<?=$arItem['fa_icon']?>"></i></td>
                    <td><span class="label label-<?=(($arItem['view_in_menu']==1)?'success':'warning')?> hint--top" aria-label="<?=(($arItem['view_in_menu']==1)?'А':'Неа')?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span></td>
                    <td><span class="label label-<?=(($arItem['activity']==1)?'success':'warning')?> hint--top" aria-label="<?=(($arItem['activity']==1)?'А':'Неа')?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span></td>
                    <td><?=$arItem['sort']?></td>
                    <td class="text-right">
                      <a href="/root/apps/edit/<?=$arItem['id']?>/">
                        <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                      </a>
                      <a href="/root/apps/delete/<?=$arItem['id']?>/" role="delete">
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