<?php if ( $currentRoute->id ) $arRes = $app->Clients->viewClient($currentRoute->id) ?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?=$app->Clients->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <?php if ( $app->Clients->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
  
  <?php if ($POSTRes) HTML::Error($POSTRes); ?>
  
  <div class="row">
    
    <div class="col-md-12">
      
      <?php /*
      <div class="box box-primary">
         
        <div class="box-header with-border"><h3 class="box-title">Просмотр</h3></div>
        
        <div class="box-body">
          
          <div class="row">
          
          </div>
        
        </div>
            
      </div>
	  */ ?>
      
      <div class="box box-primary">
         
        <div class="box-header with-border"><h3 class="box-title">История активности</h3></div>
        
        <div class="box-body">
          
          <table id="data-table" class="table table-hover table-striped table-condensed dataTable">
            <thead>
              <tr>
                <th style="width: 20%;">Сайт</th>
                <th style="width: 20%;">Приложение</th>
                <th style="width: 40%;">Активность</th>
                <th style="width: 20%;">Дата</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes['stat'] as $item ) { ?>
              <tr>
                <td><?=$GLOBALS['USER_SITES']['sites'][$item['site_id']]['ru_name']?></td>
                <td><?=$app->Apps->getAppByID($item['app_id'])['ru_name']?></td>
                <td><?=$item['event_name']?></td>
                <td><?=date('d.m.Y H:i:s', $item['timestamp'])?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
          
        </div>
            
      </div>
      
      <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title"></h3></div>
        <div class="box-body" style="height: 830px;">
          <iframe src="https://analytics.yug-avto.ru/index.php?date=today&amp;module=Widgetize&amp;action=iframe&amp;visitorId=<?=$arRes['client']['piwik_visitorId']?>&amp;idSite=1&amp;period=month&amp;moduleToWidgetize=Live&amp;actionToWidgetize=getVisitorProfilePopup&amp;token_auth=9b4e015178573140e83d2fe7eb174195" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="770"></iframe>
        </div>
            
      </div>
            
            
     </div>     
            
            
            
            
    <?php // Helper::sp( $arRes['client'] ); ?>
      
    </div>
    
  </div>

</section>
<!-- /.content -->