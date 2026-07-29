<?php if ( $currentRoute->action == 'delete' ) $app->Calc->delModel( $currentRoute->id ); ?>
<?php $arUserSites = $app->getUserSites( $authUser ) ?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?=$app->Calc->AppInfo()->ru_name?> <small>Сайты и модели</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <?php foreach ( $arUserSites['sites'] as $site ) { ?>
  <div class="row">
    <div class="col-md-12">
      <div class="box box-success">
            
        <div class="box-header with-border">
          <h3 class="box-title"><?=$site['ru_name']?> </h3>
        </div>
        
        <div class="box-body">
          <?php if ( $arModels = $app->Calc->getModelsBySite( $site['id'] ) ) { ?>
          
          <a style="width: 300px;" href="/calc/models/new//?site_id=<?=$site['id']?>" class="btn btn-info btn-flat hint--top" aria-label="Добавить модель">Добавить модель</a>
          
          <?php foreach ( $arModels['models'] as $model ) { ?>
          <div class="box box-solid">
            <div class="box-header with-border">
              <h2 class="box-title">ID: <?=$model['id']?>. Модель: <strong><?=$model['ru_name']?></strong> <a href="/calc/models/edit/<?=$model['id']?>/" class="btn btn-default btn-flat hint--top" aria-label="Редактировать модель"><i class="fa fa-edit" aria-hidden="true"></i> Редактировать</a></h2>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
              
              <div class="col-md-12">
                <h3>Модификации <a href="/calc/mods/new//?model=<?=$model['id']?>" class="btn btn-info btn-flat hint--top" aria-label="Добавить Модификацию"><i class="fa fa-plus" aria-hidden="true"></i></a></h3>
                <table class="table table-hover table-striped table-condensed">
                  <thead>
                    <tr>
                      <th style="width: 5%">ID</th>
                      <th style="width: 85%">Наименование</th>
                      <th style="width: 10%"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ( $model['mods'] as $item ) { ?>
                    <tr>
                      <td><?=$item['id']?></td>
                      <td><?=$item['ru_name']?></td>
                      <td class="text-right">
                        <a href="/calc/mods/new//?model=<?=$model['id']?>">
                          <span class="label label-info hint--top" aria-label="Добавить Модификацию"><i class="fa fa-plus" aria-hidden="true"></i></span>
                        </a>
                        <a href="/calc/mods/edit/<?=$item['id']?>/?model=<?=$model['id']?>">
                          <span class="label label-success hint--top" aria-label="Редактировать Модификацию"><i class="fa fa-edit" aria-hidden="true"></i></span>
                        </a>
                      </td>
                    </tr>
                    <?php } // foreach ?>
                  </tbody>
                </table>
              </div>
              
            </div>
            <!-- /.box-body -->
          </div>
          <?php } ?>
          
          <?php } // if ?>
          
        </div>
        
      </div>
    </div>
  </div>
  <?php } ?>
  
</section>
<!-- /.content -->