<?php if ( $currentRoute->action == 'delete' ) $app->Foots->delManager( $currentRoute->id ); ?>
<?php $arRes = $app->Foots->getManagersByUser( $authUser ); ?>
<!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Настройки <small>Менеджеры</small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
      
      <div class="row">
        
        <div class="col-md-12">
          
          <div class="box box-primary">
            
            <div class="box-header with-border">
              
              <h3 class="box-title">Менеджеры (<?=count($arRes)?>)</h3>
                
              <!-- /.box-tools -->
            </div>
            
			<div class="box-body">
		      <div class="col-xs-12">
			    <a href="/foots/managers/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить менеджера</a>
			  </div>
			</div>
            
            <div class="box-body">
              
              <table id="data-table-managers" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 10%">ID</th>
                    <th style="width: 40%">ФИО</th>
                    <th style="width: 40%">ДЦ</th>
                    <th style="width: 10%"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach( $arRes as $item ) { ?>
                  <tr>
                    <td><?=$item['id']?></td>
                    <td><?=$item['ru_name']?></td>
                    <td><?=implode(', ', $app->Foots->getManagerDCNames($item['id']))?></td>
                    <td class="text-right">
                      <a href="/foots/managers/edit/<?=$item['id']?>/">
                        <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                      </a>
                      <a href="/foots/managers/delete/<?=$item['id']?>/" role="delete">
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