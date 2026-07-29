<?php
	$date1 = ($_GET['date1']) ? $_GET['date1'] : date('Y-m-d', time()-24*3600);
	$date2 = ($_GET['date2']) ? $_GET['date2'] : date('Y-m-d', time());
	$arRes = $app->Attack->getStats( $authUser, $date1, $date2 );
	$page = ($_GET['page']) ? $_GET['page'] : 1;
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Attack->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ($POSTRes) HTML::Error($POSTRes); ?>
    
    <div class="row">
      <div class="col-md-12">
      
         <div class="box box-primary">
        
        	<div class="box-header with-border"><h3 class="box-title">Хакерская активность</h3></div>
            
            <div class="box-body">
              
              <?php HTML::statFilter( $date1, $date2 ); ?>
            	
              <table id="data-table-stats" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 3%">ID</th>
                    <th style="width: 10%">Сайт</th>
                    <th style="width: 10%">Активность</th>
                    <th style="width: 10%">IP Злоумышленника</th>
                    <th style="width: 10%">Дата</th>
                    <th style="width: 10%">Источник</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ( $arRes as $item ) { ?>
                  <tr>
                    <td><?=$item['id']?></td>
                    <td><?=$userSites['sites'][array_search($item['site_id'], $userSites['sites_ids'])]['ru_name']?></td>
                    <td><?=$app->Attack->getTypeAttack($item['type_id'])['ru_name']?></td>
                    <td><?=$item['hacker_ip']?></td>
                    <td><?=date('Y-m-d H:i', $item['timestamp'])?></td>
                    <td><?=$item['source_url']?></td>
                  </tr>
                  <? } ?>
                </tbody>
              </table>
            </div>
            
            <?php $p =  intdiv(count($arRes), 1000) + 1; ?>
          
            <div class="box-body">
              <div class="btn-group">
                <?php for ( $i = 1; $i <= $p; $i++ ) { ?>
                <a href="/calc/stat//?page=<?=$i?>" class="btn btn-<?=(((int)$page == $i)?'info':'default')?> btn-sm"><?=$i?></a>
                <? } ?>
              </div>
            </div>
            
          </div>
        
      </div>
    </div>
  
  </section>
  <!-- /.content -->
  
</div>