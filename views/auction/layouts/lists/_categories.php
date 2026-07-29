<?php if ( $currentRoute->action == 'delete' ) $app->Auction->delCategory( $currentRoute->id ); ?>
<?php $arRes = $app->Auction->getCategories(); ?>

<div class="box box-primary">
  
  <div class="box-header with-border"><h3 class="box-title">Ценовые категории автомобилей</h3></div>
  
  <div class="box-body">
    <div class="col-xs-12">
      <a href="/auction/categories/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить ценовую категорию</a>
    </div>
  </div>
  
  <div class="box-body">
    <table id="data-table-categories" class="table table-hover table-striped table-condensed dataTable">
      <thead>
        <tr>
          <th style="width: 10%">ID</th>
          <th style="width: 20%">Наименование</th>
          <th style="width: 20%">Числовые значения</th>
          <th style="width: 15%">Минимальный шаг торгов</th>
          <th style="width: 20%">Предлагаемые ставки</th>
          <th style="width: 15%"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ( $arRes as $item ) { ?>
        <tr>
          <td><a href="/auction/categories/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
          <td><a href="/auction/categories/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
          <td><?=number_format($item['min'], 0, '', ' ')?> - <?=number_format($item['max'], 0, '', ' ')?> ₽</td>
          <td><?=number_format($item['cost_step'], 0, '', ' ')?> ₽</td>
          <td><?=implode(', ', json_decode($item['default_costs']))?></td>
          <td class="text-right">
            <a href="/auction/categories/edit/<?=$item['id']?>/">
              <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
            </a>
            <a href="/auction/categories/delete/<?=$item['id']?>/" role="delete">
              <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
            </a>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>

</div>