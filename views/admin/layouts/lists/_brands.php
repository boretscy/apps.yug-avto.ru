<?php if ( $currentRoute->action == 'delete' ) $app->delBrand( $currentRoute->id ); ?>
<?php $arRes = $app->YApps_GetBrands(); ?>
<!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Настройки <small>Бренды</small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
      
      <div class="row">
        
        <div class="col-md-12">
          
          
          
          <div class="box box-primary">
            
            <div class="box-header with-border">
              
              <h3 class="box-title">Бренды (<?=count($arRes)?>)</h3>
                
              <!-- /.box-tools -->
            </div>
            
            <div class="box-body">
              
              <div class="callout callout-default">
                <p>Список актуальных брендов парсится автоматически с сайта холдинга раз в сутки</p>
              </div>
              
              <?php // Helper::sp( $globalSites ); ?>
              
              <table id="data-table-brands" class="table table-hover table-striped table-condensed dataTable">
                <thead>
                  <tr>
                    <th style="width: 5%">ID</th>
                    <th style="width: 20%">Название</th>
                    <th style="width: 20%">Название на русском</th>
                    <th style="width: 10%">Логотип</th>
                    <th style="width: 20%">Привязка к сайтам</th>
                    <th style="width: 15%">&nbsp;</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach( $arRes as $arItem ) { ?>
                  <tr>
                    <td><?=$arItem['id']?></td>
                    <td><?=$arItem['en_name']?></td>
                    <td><?=$arItem['ru_name']?></td>
                    <td><img src="<?=$arItem['logo']?>" style="width: 60px;" /></td>
                    <td>
                      <?php foreach ( $arItem['site_ids'] as $s ) echo $globalSites[$s]['ru_name'].', ';?>
                    </td>
                    <td class="text-right">
                      <a href="/admin/brands/edit/<?=$arItem['id']?>/">
                        <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                      </a>
                      <a href="#<?php /* /admin/brands/delete/<?=$arItem['id']?>/ */ ?>" role="delete">
                        <span class="label label-default hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                      </a>
                    </td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
              
            </div>
            <!-- /.box-body -->
          </div>
        
        </div>
      </div>
      
    </section>