<?php $token = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/core/yandex_token.txt'); ?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Настройки <small>Токен авторизации Яндекс.Метрики</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    
    <div class="col-md-12">
      
      <div class="box box-primary">
        
        <div class="box-header with-border">
          
          <h3 class="box-title">Токен авторизации Яндекс.Метрики</h3>
            
          <!-- /.box-tools -->
        </div>
        
        <div class="box-body">
			
		  <div class="col-xs-12">
            <a href="https://oauth.yandex.ru/authorize?response_type=code&client_id=<?=$arConf['App']['Yandex']['AppID']?>" class="btn btn-info btn-flat"><i class="fa fa-refresh" aria-hidden="true"></i> Обновить вручную</a>
          </div>
		
		</div>
		<div class="box-body">
		  
		  <div class="callout callout-default">
          	  <p>Токен автоматически обновляется 1 раз в 7 дней</p>
          </div>
		
        </div>
        
        <div class="box-body">
          
          <?php Helper::sp( $token ); ?>
          
        </div>
        <!-- /.box-body -->
      </div>
    
    </div>
  </div>
  
</section>