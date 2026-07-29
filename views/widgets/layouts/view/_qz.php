<?php $arRes = $app->Widgets->getQZStat( $currentRoute->id ); ?>
<?php $wType = (object)$app->Widgets->getTypeById( 7 ); ?>

<section class="content-header">
  <h1><?=$app->Widgets->AppInfo()->ru_name?> <?=$wType->ru_name?></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    <div class="col-md-12">
        
      <div class="box box-primary">
      
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-<?=$wType->icon?>"></i>  Результат: <?=$currentRoute->id?></h3></div>
      
        <div class="box-body">
        
          <table id="data-table-lg" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 10%">ID</th>
                <th style="width: 45%">Вопрос</th>
                <th style="width: 45%">Ответ</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes as $item ) { ?>
              <tr>
                <td><?=$item['id']?></td>
                <td><?=(($item['item_name'])?:$item['slide_name'])?></td>
                <td><?=$item['item_value']?></td>
              </tr>
              <?php } // foreach ?>
            </tbody>
          </table>
          
        </div>
      </div> <!-- /.box -->
        
    </div>
  </div>
  
</section>