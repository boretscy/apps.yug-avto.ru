<?php if ( $currentRoute->action == 'delete' ) $GETRes = $app->Widgets->delMessenger( $currentRoute->id ); ?>
<?php $arRes = $app->Widgets->getMessengers(); ?>

<div class="row">
  <div class="col-md-12">

    <div class="box box-primary">
      
      <div class="box-header with-border"><h3 class="box-title">Мессенджеры</h3></div>
      
      <div class="box-body">
        <div class="col-xs-12">
          <a href="/widgets/messengers/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
        </div>
      </div>
      
      <div class="box-body">
        <table id="data-table-messengers" class="table table-hover table-striped table-condensed dataTable">
          <thead>
            <tr>
              <th style="width: 20%">Название</th>
              <th style="width: 10%">Иконка</th>
              <th style="width: 50%">URL-схема</th>
              <th style="width: 10%">Sort</th>
              <th style="width: 10%"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $arRes as $item ) { ?>
            <tr>
              <td><?=$item['ru_name']?></td>
              <td><img src="<?=$item['image']?>" style="width: 20px;" /></td>
              <td><?=$item['url_scheme']?></td>
              <td><?=$item['sort']?></td>
              <td class="text-right">
                <a href="/widgets/messengers/edit/<?=$item['id']?>/">
                  <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                </a>
                <a href="/widgets/messengers/delete/<?=$item['id']?>/" role="delete">
                  <span class="label label-danger hint--top" aria-label="Сбросить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                </a>
              </td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    
    </div>
    
  </div>
</div>