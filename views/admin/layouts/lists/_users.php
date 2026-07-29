<?php if ( $currentRoute->action == 'delete' ) $app->User->delete( $currentRoute->id ); ?>
<?php $arRes = $app->User->getAll( 'all' ); ?>
<!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Настройки <small>Пользователи и права</small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
      
      <div class="row">
        
        <div class="col-md-12">
          
          <div class="box box-primary">
            
            <div class="box-header with-border">
              
              <h3 class="box-title">Пользователи <span class="badge bg-green"><?=count($arRes)?></span></h3>
                
              <!-- /.box-tools -->
            </div>
            
            <div class="box-body">
		      <div class="col-xs-12">
			    <a href="/admin/users/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
			  </div>
			</div>
            
            <div class="box-body">
              
              <table id="data-table-users" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 2%">ID</th>
                    <th style="width: 16%">Имя</th>
                    <th style="width: 12%">E-mail</th>
                    <th style="width: 10%">Телефон</th>
                    <th style="width: 5%">Права</th>
                    <th style="width: 24%">Сайты</th>
                    <th style="width: 24%">Приложения</th>
                    <th style="width: 7%">&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach( $arRes as $arItem ) { ?>
                  
                  <?php $arItem->sites = (object)$app->getUserSites( $arItem ); ?>
                  <?php $arItem->apps = (object)$app->Apps->getString( $arItem ); ?>
                  
                  <?php // Helper::sp($arItem); ?>
                  
                  <tr>
                    <td><a href="/admin/users/edit/<?=$arItem->id?>/"><?=$arItem->id?></a></td>
                    <td><a href="/admin/users/edit/<?=$arItem->id?>/"><?=$arItem->name?></a></td>
                    <td><?=$arItem->email?></td>
                    <td><?=$arItem->phone?></td>
                    <td><?=$arItem->role->ru_name?></td>
                    <td><?=$arItem->sites->sites_string?></td>
                    <td><?=$arItem->apps->apps_string?></td>
                    <td class="text-right">
                      <span class="label label-<?=(($arItem->active==1)?'success':'warning')?> hint--top" aria-label="<?=(($arItem->active==1)?'А':'Неа')?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;
                      
                      <a href="/admin/users/edit/<?=$arItem->id?>/">
                        <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                      </a>
                      <a href="/admin/users/delete/<?=$arItem->id?>/" role="delete">
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