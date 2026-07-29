<?php
	$date1 = ($_GET['date1']) ? $_GET['date1'] : date('Y-m-d', time()-24*3600);
	$date2 = ($_GET['date2']) ? $_GET['date2'] : date('Y-m-d', time());
	$arRes = $app->Calc->getStats( $authUser, $date1, $date2 );
	$page = ($_GET['page']) ? $_GET['page'] : 1;
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Calc->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ($POSTRes) HTML::Error($POSTRes); ?>
    
    <div class="row">
      <div class="col-md-12">
      
         <div class="box box-primary">
        
        	<div class="box-header with-border"><h3 class="box-title">Активность пользователей приложения "<?=$app->Calc->AppInfo()->ru_name?>"</h3></div>
            
            <div class="box-body">
              
              <?php HTML::statFilter( $date1, $date2 ); ?>
            	
              <table id="data-table-stats" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 10%">Сайт</th>
                    <th style="width: 10%">Активность</th>
                    <th style="width: 10%">Имя</th>
                    <th style="width: 10%">Телефон</th>
                    <th style="width: 15%">Email</th>
                    <th style="width: 10%">Дата записи</th>
                    <th style="width: 10%">Автомобиль</th>
                    <th style="width: 10%">Дата</th>
                    <th style="width: 10%"></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ( $arRes as $item ) { ?>
                  <tr>
                    <td><?=$item['id']?></td>
                    <td><?=$userSites['sites'][array_search($item['site_id'], $userSites['sites_ids'])]['ru_name']?></td>
                    <td><?=$item['event_name']?></td>
                    <td><?=$item['name']?></td>
                    <td><?=(($item['phone'])?Helper::formatPhoneOut($item['phone']):'<small>Неизвестно</small>')?></td>
                    <td><?=$item['email']?></td>
                    <td><?=date('Y-m-d H:i', $item['date_timestamp'])?></td>
                    <td>
                      <?=$app->db->getOne('SELECT ru_name FROM yapps_app_calc_models WHERE id = ?i', $item['model_id'])?> 
                      <?=$app->db->getOne('SELECT ru_name FROM yapps_app_calc_mods WHERE id = ?i', $item['mod_id'])?> 
                    </td>
                    <td><?=date('Y-m-d H:i', $item['timestamp'])?></td>
                    <td class="text-right">
                      <a href="/clients/profile/view/<?=$item['piwik_visitorId']?>/">
                        <span class="label label-info hint--top" aria-label="Просмотр"><i class="fa fa-eye" aria-hidden="true"></i></span>
                      </a>
                      <a href="<?=$item['source_url']?>" target="_blank" class="span-label hint--top" aria-label="Страница-источник"><span class="label label-default"><i class="fa fa-link" aria-hidden="true"></i></span></a>
                      <a href="#" role="viewPiwik" data-id="<?=$item['piwik_visitorId']?>" data-site="<?=$item['site_id']?>" data-date="today"><span class="label label-default hint--top" aria-label="О посетителе"><i class="fa fa-external-link" aria-hidden="true"></i></span></a>
                    </td>
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