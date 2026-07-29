<?php if ( $currentRoute->action == 'delete' ) $app->Widgets->delABTest( $currentRoute->id ); ?>

<section class="content-header">
  <h1><?=$app->Widgets->AppInfo()->ru_name?> <small>A/B Тестирование</small></h1>
</section>

<!-- Main content -->
<section class="content">

<?php if ( $app->Widgets->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
  
  <div class="row">
    <div class="col-md-12">
  	  
      
      
      <?php if ($POSTRes) HTML::Error($POSTRes); ?>
      
      <?php $arRes = $app->Widgets->getABTests( 2, $authUser ); ?>
      <?php $wType = (object)$app->Widgets->getTypeById( 2 ); ?>
        
        <div class="box box-primary">
        
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-<?=$wType->icon?>"></i> <?=$wType->ru_name?></h3></div>
        
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/widgets/abtest/new/?widget=LG" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
          </div>
        </div>
        
        <div class="box-body">
        
          <table id="data-table-lg" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 10%">ID</th>
                <th style="width: 20%">Название</th>
                <th style="width: 40%">Виджеты</th>
                <th style="width: 20%">Сайт</th>
                <th style="width: 10%"></th
              ></tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes as $item ) { ?>
              <tr>
                <td><a href="/widgets/abtest/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                <td><a href="/widgets/abtest/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                <td>
                  <?php $w = $app->Widgets->getWidgetById( $item['a_widget_id'] ); ?>
                  <strong>A:</strong> ID <?=$w['id']?>. <?=$w['ru_name']?> <br />
                  <?php $w = $app->Widgets->getWidgetById( $item['b_widget_id'] ); ?>
                  <strong>B:</strong> ID <?=$w['id']?>. <?=$w['ru_name']?>
                </td>
                <td><?=$app->getSite($w['site_id'])['ru_name']?></td>
                <td style="text-align: right">
                  <a href="/widgets/abtest/view/<?=$item['id']?>/">
                    <span class="label label-info hint--top" aria-label="Посмотреть результаты"><i class="fa fa-eye" aria-hidden="true"></i></span>
                  </a>&nbsp;&nbsp;&nbsp;
                  <span class="label label-<?=($item['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                  <a href="/widgets/abtest/edit/<?=$item['id']?>/">
                    <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                  </a>
                  <a href="/widgets/abtest/delete/<?=$item['id']?>/" role="delete">
                    <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                  </a>
                </td>
              </tr>
              <?php } // foreach ?>
            </tbody>
          </table>
          
        </div>
      </div> <!-- /.box -->
      
      
      <?php $arRes = $app->Widgets->getABTests( 1, $authUser ); ?>
      <?php $wType = (object)$app->Widgets->getTypeById( 1 ); ?>
        
        <div class="box box-primary">
        
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-<?=$wType->icon?>"></i> <?=$wType->ru_name?></h3></div>
        
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/widgets/abtest/new/?widget=CB" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
          </div>
        </div>
        
        <div class="box-body">
        
          <table id="data-table-cb" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 10%">ID</th>
                <th style="width: 20%">Название</th>
                <th style="width: 40%">Виджеты</th>
                <th style="width: 20%">Сайт</th>
                <th style="width: 10%"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes as $item ) { ?>
              <tr>
                <td><a href="/widgets/abtest/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                <td><a href="/widgets/abtest/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                <td>
                  <?php $w = $app->Widgets->getWidgetById( $item['a_widget_id'] ); ?>
                  <strong>A:</strong> ID <?=$w['id']?>. <?=$w['ru_name']?> <br />
                  <?php $w = $app->Widgets->getWidgetById( $item['b_widget_id'] ); ?>
                  <strong>B:</strong> ID <?=$w['id']?>. <?=$w['ru_name']?>
                </td>
                <td><?=$app->getSite($w['site_id'])['ru_name']?></td>
                <td style="text-align: right">
                  <a href="/widgets/abtest/view/<?=$item['id']?>/">
                    <span class="label label-info hint--top" aria-label="Посмотреть результаты"><i class="fa fa-eye" aria-hidden="true"></i></span>
                  </a>&nbsp;&nbsp;&nbsp;
                  <span class="label label-<?=($item['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                  <a href="/widgets/abtest/edit/<?=$item['id']?>/">
                    <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                  </a>
                  <a href="/widgets/abtest/delete/<?=$item['id']?>/" role="delete">
                    <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                  </a>
                </td>
              </tr>
              <?php } // foreach ?>
            </tbody>
          </table>
          
        </div>
      </div> <!-- /.box -->
      
      
      <?php $arRes = $app->Widgets->getABTests( 3, $authUser ); ?>
      <?php $wType = (object)$app->Widgets->getTypeById( 3 ); ?>
        
        <div class="box box-primary">
        
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-<?=$wType->icon?>"></i> <?=$wType->ru_name?></h3></div>
        
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/widgets/abtest/new/?widget=NV" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
          </div>
        </div>
        
        <div class="box-body">
        
          <table id="data-table-nv" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 10%">ID</th>
                <th style="width: 20%">Название</th>
                <th style="width: 40%">Виджеты</th>
                <th style="width: 20%">Сайт</th>
                <th style="width: 10%"></th
              ></tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes as $item ) { ?>
              <tr>
                <td><a href="/widgets/abtest/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                <td><a href="/widgets/abtest/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                <td>
                  <?php $w = $app->Widgets->getWidgetById( $item['a_widget_id'] ); ?>
                  <strong>A:</strong> ID <?=$w['id']?>. <?=$w['ru_name']?> <br />
                  <?php $w = $app->Widgets->getWidgetById( $item['b_widget_id'] ); ?>
                  <strong>B:</strong> ID <?=$w['id']?>. <?=$w['ru_name']?>
                </td>
                <td><?=$app->getSite($w['site_id'])['ru_name']?></td>
                <td style="text-align: right">
                  <a href="/widgets/abtest/view/<?=$item['id']?>/">
                    <span class="label label-info hint--top" aria-label="Посмотреть результаты"><i class="fa fa-eye" aria-hidden="true"></i></span>
                  </a>&nbsp;&nbsp;&nbsp;
                  <span class="label label-<?=($item['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                  <a href="/widgets/abtest/edit/<?=$item['id']?>/">
                    <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                  </a>
                  <a href="/widgets/abtest/delete/<?=$item['id']?>/" role="delete">
                    <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                  </a>
                </td>
              </tr>
              <?php } // foreach ?>
            </tbody>
          </table>
          
        </div>
      </div> <!-- /.box -->
      
      <?php $arRes = $app->Widgets->getABTests( 7, $authUser ); ?>
      <?php $wType = (object)$app->Widgets->getTypeById( 7 ); ?>
        
        <div class="box box-primary">
        
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-<?=$wType->icon?>"></i> <?=$wType->ru_name?></h3></div>
        
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/widgets/abtest/new/?widget=QZ" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
          </div>
        </div>
        
        <div class="box-body">
        
          <table id="data-table-qz" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 10%">ID</th>
                <th style="width: 20%">Название</th>
                <th style="width: 40%">Виджеты</th>
                <th style="width: 20%">Сайт</th>
                <th style="width: 10%"></th
              ></tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes as $item ) { ?>
              <tr>
                <td><a href="/widgets/abtest/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                <td><a href="/widgets/abtest/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                <td>
                  <?php $w = $app->Widgets->getWidgetById( $item['a_widget_id'] ); ?>
                  <strong>A:</strong> ID <?=$w['id']?>. <?=$w['ru_name']?> <br />
                  <?php $w = $app->Widgets->getWidgetById( $item['b_widget_id'] ); ?>
                  <strong>B:</strong> ID <?=$w['id']?>. <?=$w['ru_name']?>
                </td>
                <td><?=$app->getSite($w['site_id'])['ru_name']?></td>
                <td style="text-align: right">
                  <a href="/widgets/abtest/view/<?=$item['id']?>/">
                    <span class="label label-info hint--top" aria-label="Посмотреть результаты"><i class="fa fa-eye" aria-hidden="true"></i></span>
                  </a>&nbsp;&nbsp;&nbsp;
                  <span class="label label-<?=($item['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                  <a href="/widgets/abtest/edit/<?=$item['id']?>/">
                    <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                  </a>
                  <a href="/widgets/abtest/delete/<?=$item['id']?>/" role="delete">
                    <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                  </a>
                </td>
              </tr>
              <?php } // foreach ?>
            </tbody>
          </table>
          
        </div>
      </div> <!-- /.box -->

      <?php $arRes = $app->Widgets->getABTests( 8, $authUser ); ?>
      <?php $wType = (object)$app->Widgets->getTypeById( 8 ); ?>
        
        <div class="box box-primary">
        
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-<?=$wType->icon?>"></i> <?=$wType->ru_name?></h3></div>
        
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/widgets/abtest/new/?widget=QZ" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
          </div>
        </div>
        
        <div class="box-body">
        
          <table id="data-table-qz" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 10%">ID</th>
                <th style="width: 20%">Название</th>
                <th style="width: 40%">Виджеты</th>
                <th style="width: 20%">Сайт</th>
                <th style="width: 10%"></th
              ></tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes as $item ) { ?>
              <tr>
                <td><a href="/widgets/abtest/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                <td><a href="/widgets/abtest/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                <td>
                  <?php $w = $app->Widgets->getWidgetById( $item['a_widget_id'] ); ?>
                  <strong>A:</strong> ID <?=$w['id']?>. <?=$w['ru_name']?> <br />
                  <?php $w = $app->Widgets->getWidgetById( $item['b_widget_id'] ); ?>
                  <strong>B:</strong> ID <?=$w['id']?>. <?=$w['ru_name']?>
                </td>
                <td><?=$app->getSite($w['site_id'])['ru_name']?></td>
                <td style="text-align: right">
                  <a href="/widgets/abtest/view/<?=$item['id']?>/">
                    <span class="label label-info hint--top" aria-label="Посмотреть результаты"><i class="fa fa-eye" aria-hidden="true"></i></span>
                  </a>&nbsp;&nbsp;&nbsp;
                  <span class="label label-<?=($item['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                  <a href="/widgets/abtest/edit/<?=$item['id']?>/">
                    <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                  </a>
                  <a href="/widgets/abtest/delete/<?=$item['id']?>/" role="delete">
                    <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                  </a>
                </td>
              </tr>
              <?php } // foreach ?>
            </tbody>
          </table>
          
        </div>
      </div> <!-- /.box -->
      
    </div>
  </div>

</section>
