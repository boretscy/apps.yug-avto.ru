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
        
        <div class="box box-info box-solid">
          <div class="box-header with-border">
            <h3 class="box-title">Установка калькулятора на сайт</h3>
          </div><!-- /.box-header -->
          <div class="box-body">
            <p>Тег для подключения калькулятора:
            <pre><strong>&lt;yappscalc&gt;&lt;/yappscalc&gt;</strong> или &lt;div <strong>id="YApps_Calc"</strong>&gt;&lt;/div&gt;</pre>
            Этот тег нужно вставить в нужное место в html-код сайта.</p>
            <p>Если нужно стартовать калькулятор с какой-либо определенной модели (например для использования в акции), то в тег необходимо добавить атрибут <strong>data-model</strong> со значением, равным <strong>ID</strong> необходимой модели (ID модели можно посмотреть на странице <a href="/calc/models/">Модели</a>):
            <pre>&lt;div id="YApps_Calc" <strong>data-model="2"</strong>&gt;&lt;/div&gt;</pre></p>
          </div><!-- /.box-body -->
        </div>
        
      </div>
    </div>
  
  </section>
  <!-- /.content -->
  
</div>