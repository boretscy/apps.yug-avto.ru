<?php if ( $currentRoute->action == 'delete' ) $app->Goals->del( $currentRoute->id ); ?>
<?php $arRes = $app->Goals->getByApp( $authUser, $app->Hot->AppInfo()->id ); ?>
<!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Настройки <small>Цели</small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
      
      <div class="row">
        
        <div class="col-md-12">
          
          <div class="box box-primary">
            
            <div class="box-header with-border">
              
              <h3 class="box-title">Цели (<?=count($arRes)?>)</h3>
                
              <!-- /.box-tools -->
            </div>
            
			<div class="box-body">
		      <div class="col-xs-12">
			    <a href="/hot/goals/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить цель</a>
			  </div>
			</div>
            
            <div class="box-body">
              
              <table id="data-table-goals" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 20%">Сайт</th>
                    <th style="width: 30%">Название</th>
                    <th style="width: 25%">Ссылка</th>
                    <th style="width: 20%">&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach( $arRes as $arItem ) { ?>
                  <?php $site = $app->getSite($arItem['site_id']); ?>
                  <tr>
                    <td><?=$arItem['id']?></td>
                    <td><?=$site['ru_name']?></td>
                    <td><?=$arItem['goal_name']?></td>
                    <td><a href="http://<?=$site['url']?><?=$arItem['goal_url']?>" target="_blank"><?=$site['url']?><?=$arItem['goal_url']?></a></td>
                    <td class="text-right">
                      <a href="/hot/goals/edit/<?=$arItem['id']?>/">
                        <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                      </a>
                      <a href="/hot/goals/delete/<?=$arItem['id']?>/" role="delete">
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