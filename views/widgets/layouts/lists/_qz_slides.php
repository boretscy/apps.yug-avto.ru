<?php if ( $currentRoute->id ) $res = $app->Widgets->getQZSlidesByWidget( $currentRoute->id ); ?>
<?php if ( $currentRoute->action == 'delete' ) {
    
    $res = $app->Widgets->delQZSlide( $currentRoute->id );
    header('Location: /widgets/qz/edit/'.$res.'/');
}
?>

<div class="row">
  <div class="col-md-12">

    <div class="box box-primary">
      
      <div class="box-header with-border"><h3 class="box-title">Слайды</h3></div>
      
      <div class="box-body">
        <div class="col-xs-12">
          <a href="/widgets/qz_slides/new/<?=$widget_id?>" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить слайд</a>
        </div>
      </div>
      
      <div class="box-body">
        <table id="data-table-qz_slides" class="table table-hover table-striped table-condensed dataTable">
          <thead>
            <tr>
              <th style="width: 10%">ID</th>
              <th style="width: 20%">Тип</th>
              <th style="width: 50%">Вопрос</th>
              <th style="width: 10%">Sort</th>
              <th style="width: 10%"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ( $res as $item ) { ?>
            <tr>
              <td><a href="/widgets/qz_slides/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
              <td><?=$app->Widgets->getQZSlideType($item['type_id'])['ru_name']?></td>
              <td><a href="/widgets/qz_slides/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
              <td><?=$item['sort']?></td>
              <td class="text-right">
                <span class="label label-<?=($item['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                <a href="/widgets/qz_slides/edit/<?=$item['id']?>/">
                  <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                </a>
                <a href="/widgets/qz_slides/delete/<?=$item['id']?>/" role="delete">
                  <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
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