<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Widgets->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Widgets->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
    <div class="row">
      <div class="col-md-12">
      
      	<div class="box box-info box-solid collapsed-box">
          <div class="box-header with-border">
            <h3 class="box-title">Логика поведения виджетов и подготовка к использованию.</h3>
            <div class="box-tools pull-right">
              <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
              </button>
            </div>
          </div><!-- /.box-header -->
          <div class="box-body" style="display: none;">
          	<h4><strong>Настроить <a href="/widgets/tuning/">сайт</a></strong></h4>
            <ol>
              <li>Цветовая гамма</li>
              <li>Поведение и стандартные тексты, фразы и пр</li>
              <li>Дополнительные стили - если нужны, лучше обратитесь к вебмастеру</li>
              <li>Получатели - основной список email'ов получателей форм, общий для всех виджетов этого сайта</li>
              <li>Активность - нужно включить, чтобы сервис начал работу на сайте</li>
            </ol>
            <h4><strong>Обратите внимание</strong>: Логика поведения виджетов</h4>
            <p>Все значения таймингов взяты из значений "по-умолчанию" и являются наиболее оптимальными значениями, но могут быть настроены индивидуально</p>
            <a href="https://www.draw.io/?lightbox=1&highlight=0000ff&edit=_blank&layers=1&nav=1&title=%D0%9B%D0%BE%D0%B3%D0%B8%D0%BA%D0%B0%20%D0%BF%D0%BE%D0%B2%D0%B5%D0%B4%D0%B5%D0%BD%D0%B8%D1%8F%20%D0%B2%D0%B8%D0%B4%D0%B6%D0%B5%D1%82%D0%BE%D0%B2.drawio#R7V1bc6s2EP41fkxGF66PtpO0nWlnzvR0enr60iExx6bFJsXkJOmvrwAJhCRi5WIWX14wCIHlXe2nb1creULn66cf8uh%2B9Uu2iNMJQYunCb2aEEIc4rGPsuS5LsHE4yXLPFnwsrbgc%2FJfzAsRL31IFvG2U7HIsrRI7ruFd9lmE98VnbIoz7PHbrVvWdr91vtoGWsFn%2B%2BiVC%2F9kiyKFS%2F1XKe98WOcLFfiq7EX1nfWkajNf8p2FS2yR6mIXk%2FoPM%2Byoj5bP83jtBSfEEz93E3P3aZlebwpbB74c7P6dfbT6vtfvz0Xq9%2FXT%2FHtlz8uAsobVzyLnxwvmAT4ZZYXq2yZbaL0ui2d5dnDZhGXr0Xsqq3zc5bds0LMCv%2BOi%2BKZqzN6KDJWtCrWKb8bPyXFH%2BXjly6%2F%2BirduXrib64unsXFpsifpYfKy6%2Fyvfax6ko89y3bFLwhOGDX9e8tf2SvHHnRNnvI7%2BIXhCc6ZJQv4%2BIlIZNG3cxS4mwdswayB%2FM4jYrke7chEe%2Bxy6Zeq1N2wtX6ChXz936P0gf%2BTRMmmPCmPM6uJ0zsAa7O3eo8FOfseFUdA17C%2BhevzI6kOqLqFupWZuVedZxpfavbcx5XSRF%2Fvo8qIT8yBOn2Et7uOC%2Fip5d1pYuWPxBw2%2BPwE7j15aNkysI8V5IVe2hPughCSHPDkrG1prfL3DrG1tre8OZGLc3NhbQ2qlsb8VLW3Nn2Ptp0VO%2F9%2B1Bi%2F4x17%2BIiSpPlZkKnrEYafytKYyuFhyobudjWRlLe3WT5Okrbp9nZsvy8YPewZKSktkqnOs6qwhtRSDUbd7vmXBt4gw%2BinP8SJpj6x9TfLIpv8z3%2BvAGlSGykyE58TVpIFkW33ZrMgJHR7yIjdZEGjQQZoDHYFzSKBhwvE%2FlAKHQtoZCAMg%2FPH49K0ZtUisenUo9CqtTVhjd00Rl1NPxrR5EaI6diRKkIYl3OUXbGGWR7ye5eV%2BdYvIcdQ1GBSPS0RmhPGsyYkG50mF1l69uH7SAQ61ALjHWGxFg%2FGI9B2mLs6%2BjnBxqkb%2BvdQdqjb3budhmc6ta9hxPOzUY%2FpV1vUfETryULnopXIcnWZ1K1qeRaop7HG9i46VYwNvtKkkYjAWR6hB3nmgQCif%2B5kgctt7N5rb0udOdwcFaIu5BFHN1jNtLCvXnMLqjH%2FEZaGGAqw9YFukTeDuCqrj7FecLEFucfj2bBQTBG%2F8wYP16lngOp0kAbobB7QcZKGSH5YuiOjS%2BGZnbhiPFaEVYZgulKZVvk2T%2FxPEuzEtI22aa0129JmipFPG5zdcdEVaLfrBRkchelU35jnSwWlbGb1NAFgA%2FQBEZKeMSh%2Bjho0gTZlybEuHxyqnDdsWmiZz7l6kUcORp9aKZBCbRCyEkrRDUQcH2QHqh6rU9qHWGHdDaN0ytnD%2FQd%2BEJDlQWBu6CgORJDTNpOPs4pIRxqd3oltKcfDOOVkJ5BQzaVuRYRQ6%2B0qBIULs2IFV5L3ofsjyjJFHKIzpcq7PhWW3%2FHzB6HNnqqhJ1cA8kzDmL7s3nnbPP2iRm2iVAUNBJBe5i7pVnJlmhpVgodUDKtZIMlEtbMpPO5bpsDRiRcZSx2Ddxy2IgE7cHto%2FeDA7IbIgel%2BYKWnRHSBiEdW4SETV5zjNY1VfyqBiGbMKwlmamYSZV%2FhdGkDQC%2FNscKmK0E%2FujYih7fGP8kGdS0CT2M5CnfOzx8PQCdIkidUj19SovjIIn9%2BSpSnqfK%2BF3sjo6Z9qTiHD0zxUShph4Cpqa0Z97yRGYEMLUgKIMqxOvxvPcSbQNmh2oKFcbQ8WsPNOvzI5jEgJn1nu0qIx%2BUHI6I8FvnSZHR69QDnZPwDGvH2gFLXnY57%2BF0b5vM1HD2CKYxy9R%2FL1qX8L653d5X%2FUm9BuSv6tIA7EHTV39EC8GtEcUHQxTbeB4sovgHE6OF0A1sLMAzx1rPaH98aK8m9sKjvWcKRJ1CsELNlsPgrrF3oprASOVA4Ko47bCR53X1QQyBi0H14ZtHyFPRB8aqQqAzezHCmrhH7yR0PATI7WsC2ywpjECJKUZHv0p8HFoG3chBtBNgoyLbXYqmkjMxkzwet%2BvNqHOi4v2NCxJw32WPOyId3H4%2FAfh%2BPwFohu2howyxRBlYjIEdSF4RfjpkFQegIUbRzH2lDCLToqRxcHyz8t%2Fng%2B1m%2FI1XMAjlD49%2BHcQeTTh8L4%2Fnj37Kkk0hR05IeBkEhLokdDD2RQiJdxo%2FCC%2FFHcdHYgMV8f4aT%2Fgrld7RtPEdHca8%2BrMTXnakoLRY8mAOcc%2BlkG8ghYLpxBC1nvFoeXnua9FvmcO5nfrmrLqp%2BAo5LI94jLpDE5GUNEe7rBFp2%2BIiaRWJHLRvUl4A49Rq6orvGEiiKeKA0d7wpydz6OjDo6ESHfU9XReDBn9C0K15Dn0ksM1PDj1IMheap4WGmJM0YrCSB2g5RdnU0ecSmylHeY2dzEH1vQPEyj%2FgzSS16RIffnegnqmrN2w%2B%2BMZQT%2BVpQG39MKbOCR1dwtTdPVwNm%2FUa6huPVb3zVGaPiIVGBp490sV9ZhC2DKKh2LspBPDsUc%2F2PzddVG0cOoG%2Fyr8XAAOaugsNPJ5hdP7vkPeYj20OOEagm1Q0DX2z%2FSBt3mtkthTC25IIdJxt6U22ZJthixHodgZNQ1V%2F9jT%2Fj0AN74XgHiTG%2Bkh%2FtkN7O7SNKmEEulqtaeixxZX6Kvd5%2BBYBkbGFmUYAEmgEf3JxlJGnPXRfaK6pBaLGQDaJuf%2BeSCRKXf1i0sjAO3j3uFgnohCsJgPuUSPssv2H4zrHoP2naHr9Pw%3D%3D" target="_blank"><img style="width: 100%" src="/upload/Widgets/behavior.jpg" /></a>
          </div><!-- /.box-body -->
        </div>
        
        <?php $arRes = $app->Widgets->getWidgetsByType( 1 ); ?>
        <?php $wType = (object)$app->Widgets->getTypeById( 1 ); ?>
        
        <div class="box box-primary">
        
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-<?=$wType->icon?>"></i> <?=$wType->ru_name?></h3></div>
          
          <div class="box-body">
            <div class="col-xs-12">
              <a href="/widgets/cb/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить виджет</a>
            </div>
          </div>
          
          <div class="box-body">
            
            <?php if ($POSTRes) HTML::Error($POSTRes); ?>
            
            <table id="data-table-cb" class="table table-hover table-striped table-condensed dataTable">
              <thead>
                <tr>
                  <th style="width: 5%">ID</th>
                  <th style="width: 20%">Сайт</th>
                  <th style="width: 60%">Название</th>
                  <th style="width: 15%"></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ( $arRes as $item ) { ?>
                <tr>
                  <td><a href="/widgets/cb/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                  <td><?=$app->getSite($item['site_id'])['ru_name']?></td>
                  <td><a href="/widgets/cb/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                  <td style="text-align: right">
                    <span class="label label-<?=($item['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                    <a href="/widgets/stat/?widget_ids[]=<?=$item['id']?>">
                        <span class="label label-default hint--top" aria-label="Статистика"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
                    </a>&nbsp;&nbsp;&nbsp;
                    <a href="/widgets/cb/edit/<?=$item['id']?>/">
                      <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                    </a>
                    <a href="/widgets/cb/delete/<?=$item['id']?>/" role="delete">
                      <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                    </a>
                  </td>
                </tr>
                <?php } // foreach ?>
              </tbody>
            </table>
            
          </div>
        
        </div>
        
        <?php $arRes = $app->Widgets->getWidgetsByType( 2 ); ?>
        <?php $wType = (object)$app->Widgets->getTypeById( 2 ); ?>
        
        <div class="box box-primary">
        
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-<?=$wType->icon?>"></i> <?=$wType->ru_name?></h3></div>
          
          <div class="box-body">
            <div class="col-xs-12">
              <a href="/widgets/lg/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить виджет</a>
            </div>
          </div>
          
          <div class="box-body">
            
            <?php if ($POSTRes) HTML::Error($POSTRes); ?>
            
            <table id="data-table-lg" class="table table-hover table-striped table-condensed dataTable">
              <thead>
                <tr>
                  <th style="width: 5%">ID</th>
                  <th style="width: 15%">Сайт</th>
                  <th style="width: 20%">Название</th>
                  <th style="width: 25%">Страницы</th>
                  <th style="width: 10%">Начало</th>
                  <th style="width: 10%">Таймер</th>
                  <th style="width: 10%">Конкуренты</th>
                  <th style="width: 5%"></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ( $arRes as $item ) { ?>
                <tr>
                  <td><a href="/widgets/lg/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                  <td><?=$app->getSite($item['site_id'])['ru_name']?></td>
                  <td><a href="/widgets/lg/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                  <td><?=implode(", ", $app->Widgets->getUrls($item['id']))?></td>
                  <td><?=date('d.m.Y', $item['lg_time_start'])?></td>
                  <td>
                    <span class="label label-<?=($item['lg_timer_flag'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                    <?=date('d.m.Y H:i', $item['lg_timer'])?>
                  </td>
                  <td><?=implode(", ", $app->Widgets->getCompetitors($item['id']))?></td>
                  <td style="text-align: right">
                    <span class="label label-<?=($item['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                    <a href="/widgets/stat/?widget_ids[]=<?=$item['id']?>">
                        <span class="label label-default hint--top" aria-label="Статистика"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
                    </a>&nbsp;&nbsp;&nbsp;
                    <a href="/widgets/lg/edit/<?=$item['id']?>/">
                      <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                    </a>
                    <a href="/widgets/lg/delete/<?=$item['id']?>/" role="delete">
                      <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                    </a>
                  </td>
                </tr>
                <?php } // foreach ?>
              </tbody>
            </table>
            
          </div>
        
        </div>
        
        <?php $arRes = $app->Widgets->getWidgetsByType( 3 ); ?>
        <?php $wType = (object)$app->Widgets->getTypeById( 3 ); ?>
        
        <div class="box box-primary">
        
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-<?=$wType->icon?>"></i> <?=$wType->ru_name?></h3></div>
          
          <div class="box-body">
            <div class="col-xs-12">
              <a href="/widgets/nv/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить виджет</a>
            </div>
          </div>
          
          <div class="box-body">
            
            <?php if ($POSTRes) HTML::Error($POSTRes); ?>
            
            <table id="data-table-nv" class="table table-hover table-striped table-condensed dataTable">
              <thead>
                <tr>
                  <th style="width: 5%">ID</th>
                  <th style="width: 20%">Сайт</th>
                  <th style="width: 60%">Название</th>
                  <th style="width: 15%"></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ( $arRes as $item ) { ?>
                <tr>
                  <td><a href="/widgets/nv/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                  <td><?=$app->getSite($item['site_id'])['ru_name']?></td>
                  <td><a href="/widgets/nv/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                  <td style="text-align: right">
                    <span class="label label-<?=($item['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                    <a href="/widgets/stat/?widget_ids[]=<?=$item['id']?>">
                        <span class="label label-default hint--top" aria-label="Статистика"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
                    </a>&nbsp;&nbsp;&nbsp;
                    <a href="/widgets/nv/edit/<?=$item['id']?>/">
                      <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                    </a>
                    <a href="/widgets/nv/delete/<?=$item['id']?>/" role="delete">
                      <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                    </a>
                  </td>
                </tr>
                <?php } // foreach ?>
              </tbody>
            </table>
            
          </div>
        
        </div>
        
        <?php $arRes = $app->Widgets->getWidgetsByType( 7 ); ?>
        <?php $wType = (object)$app->Widgets->getTypeById( 7 ); ?>
        
        <div class="box box-primary">
        
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-<?=$wType->icon?>"></i> <?=$wType->ru_name?></h3></div>
          
          <div class="box-body">
            <div class="col-xs-12">
              <a href="/widgets/qz/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить виджет</a>
            </div>
          </div>
          
          <div class="box-body">
            
            <?php if ($POSTRes) HTML::Error($POSTRes); ?>
            
            <table id="data-table-qz" class="table table-hover table-striped table-condensed dataTable">
              <thead>
                <tr>
                  <th style="width: 5%">ID</th>
                  <th style="width: 20%">Сайт</th>
                  <th style="width: 60%">Название</th>
                  <th style="width: 15%"></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ( $arRes as $item ) { ?>
                <tr>
                  <td><a href="/widgets/nv/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                  <td><?=$app->getSite($item['site_id'])['ru_name']?></td>
                  <td><a href="/widgets/nv/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                  <td style="text-align: right">
                    <span class="label label-<?=($item['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                    <a href="/widgets/stat/?widget_ids[]=<?=$item['id']?>">
                        <span class="label label-default hint--top" aria-label="Статистика"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
                    </a>&nbsp;&nbsp;&nbsp;
                    <a href="/widgets/nv/edit/<?=$item['id']?>/">
                      <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                    </a>
                    <a href="/widgets/nv/delete/<?=$item['id']?>/" role="delete">
                      <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                    </a>
                  </td>
                </tr>
                <?php } // foreach ?>
              </tbody>
            </table>
            
          </div>
        
        </div>


        <?php $arRes = $app->Widgets->getWidgetsByType( 8 ); ?>
        <?php $wType = (object)$app->Widgets->getTypeById( 8 ); ?>
        
        <div class="box box-primary">
        
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-<?=$wType->icon?>"></i> <?=$wType->ru_name?></h3></div>
          
          <div class="box-body">
            <div class="col-xs-12">
              <a href="/widgets/qz/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить виджет</a>
            </div>
          </div>
          
          <div class="box-body">
            
            <?php if ($POSTRes) HTML::Error($POSTRes); ?>
            
            <table id="data-table-ms" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                    <tr>
                        <th style="width: 5%">ID</th>
                        <th style="width: 20%">Название</th>
                        <th style="width: 15%">Сайт</th>
                        <?php foreach ( $messengers = $app->Widgets->getMessengers() as $messenger ) { ?>
                        <th style="width: <?php echo 45 / count($messengers);?>%"><?=$messenger['ru_name']?></th>
                        <?php } ?>
                        <th style="width: 15%"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $arRes as $item ) { ?>
                    <?php $mess = $app->Widgets->getMessengersByWidget( $item['id'] ); ?>
                    <tr>
                        <td><a href="/widgets/ms/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                        <td><a href="/widgets/ms/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                        <td><?=$app->getSite($item['site_id'])['ru_name']?></td>
                        <?php foreach ( $messengers as $messenger ) { ?>
                        <td><?=(($messenger['id']==1)?Helper::formatPhoneOut($mess[$messenger['id']]['value']):$mess[$messenger['id']]['value'])?></th>
                        <?php } ?>
                        <td style="text-align: right">
                        <span class="label label-<?=($item['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                        <a href="/widgets/stat/?widget_ids[]=<?=$item['id']?>">
                            <span class="label label-default hint--top" aria-label="Статистика"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
                        </a>&nbsp;&nbsp;&nbsp;
                        <a href="/widgets/ms/edit/<?=$item['id']?>/">
                            <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                        </a>
                        <a href="/widgets/ms/copy/<?=$item['id']?>/">
                            <span class="label label-info hint--top" aria-label="Скопировать"><i class="fa fa-clone" aria-hidden="true"></i></span>
                        </a>
                        <a href="/widgets/ms/delete/<?=$item['id']?>/" role="delete">
                            <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                        </a>
                        </td>
                    </tr>
                    <?php } // foreach ?>
                </tbody>
            </table>
            
          </div>
        
        </div>
        
        <?php $arRes = $app->Widgets->getWidgetsByType( 9 ); ?>
        <?php $wType = (object)$app->Widgets->getTypeById( 9 ); ?>
        
        <div class="box box-primary">
        
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-<?=$wType->icon?>"></i> <?=$wType->ru_name?></h3></div>
          
          <div class="box-body">
            <div class="col-xs-12">
              <a href="/widgets/ci/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить виджет</a>
            </div>
          </div>
          
          <div class="box-body">
            
            <?php if ($POSTRes) HTML::Error($POSTRes); ?>
            
            <table id="data-table-ci" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                    <tr>
                        <th style="width: 5%">ID</th>
                        <th style="width: 25%">Название</th>
                        <th style="width: 25%">Сайт</th>
                        <th style="width: 25%">Границы случайного числа</th>
                        <th style="width: 20%"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $arRes as $item ) { ?>
                    <tr>
                        <td><a href="/widgets/ci/edit/<?=$item['id']?>/"><?= $item['id'];?></a></td>
                        <td><a href="/widgets/ci/edit/<?=$item['id']?>/"><?= $item['ru_name'];?></a></td>
                        <td><?=$app->getSite($item['site_id'])['ru_name']?></td>
                        <td><?= $item['ci_random_min'];?> - <?= $item['ci_random_max'];?></td>
                        <td style="text-align: right">
                            <span class="label label-<?=($item['active'])?'success':'warning'?> hint--top" aria-label="<?=($item['active'])?'А':'Не а'?>ктивен"><i class="fa fa-power-off" aria-hidden="true"></i></span>&nbsp;&nbsp;&nbsp;
                            <a href="/widgets/stat/?widget_ids[]=<?=$item['id']?>">
                                <span class="label label-default hint--top" aria-label="Статистика"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>
                            </a>&nbsp;&nbsp;&nbsp
                            <a href="/widgets/ci/edit/<?=$item['id']?>/">
                                <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                            </a>
                            <a href="/widgets/ci/copy/<?=$item['id']?>/">
                                <span class="label label-info hint--top" aria-label="Скопировать"><i class="fa fa-clone" aria-hidden="true"></i></span>
                            </a>
                            <a href="/widgets/ci/delete/<?=$item['id']?>/" role="delete">
                                <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                            </a>
                        </td>
                    </tr>
                <?php } // foreach ?>
                </tbody>
            </table>
            
          </div>
        
        </div>


      </div>
    </div>
  
  </section>
  <!-- /.content -->
  
</div>