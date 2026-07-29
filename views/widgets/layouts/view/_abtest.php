<?php $arRes = $app->Widgets->getABTest( $currentRoute->id ); ?>

<section class="content-header">
  <h1><?=$app->Widgets->AppInfo()->ru_name?> <small>A/B Тестирование</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    <div class="col-md-12">
    
      <div class="box box-primary">
        
        <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-eye-slash"></i> <?=$arRes['ru_name']?></h3></div>
        
        <div class="box-body">
          
          <h3>Виджет А: <?=$app->Widgets->getWidgetById($arRes['a_widget_id'])['ru_name']?></h3>
          <p>Показов: <?=$arRes['a_show']?>. Отправок: <?=$arRes['a_send']?>. <strong>Конверсия: <?=number_format($arRes['a_send']/$arRes['a_show']*100, 2, '.', '')?>%</strong>.</p>
          <div class="progress">
            <div class="progress-bar progress-bar-aqua" role="progressbar" aria-valuenow="<?=$arRes['a_send']/$arRes['a_show']*100?>" aria-valuemin="0" aria-valuemax="<?=$arRes['a_show']?>" style="width: <?=$arRes['a_send']/$arRes['a_show']*100?>%">
              <span class="sr-only"><?=$arRes['a_send']/$arRes['a_show']*100?>%</span>
            </div>
          </div>
          
          <h3>Виджет B: <?=$app->Widgets->getWidgetById($arRes['b_widget_id'])['ru_name']?></h3>
          <p>Показов: <?=$arRes['b_show']?>. Отправок: <?=$arRes['b_send']?>. <strong>Конверсия: <?=number_format($arRes['b_send']/$arRes['b_show']*100, 2, '.', '')?>%</strong>.</p>
          <div class="progress">
            <div class="progress-bar progress-bar-aqua" role="progressbar" aria-valuenow="<?=$arRes['b_send']/$arRes['b_show']*100?>" aria-valuemin="0" aria-valuemax="<?=$arRes['b_show']?>" style="width: <?=$arRes['b_send']/$arRes['b_show']*100?>%">
              <span class="sr-only"><?=$arRes['b_send']/$arRes['b_show']*100?>%</span>
            </div>
          </div>
        
        </div>
        
      </div>
      
    </div>
  </div>
  
</section>