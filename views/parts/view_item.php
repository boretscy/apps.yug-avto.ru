<?php if ( $currentRoute->id ) $arRes = $app->Parts->getItem($currentRoute->id) ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Parts->AppInfo()->ru_name?> <small>Редактировать / добавить позицию</small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <div class="row">
      <div class="col-md-12">
      
        <div class="box box-primary">
        
          <div class="box-header with-border">
            <h3 class="box-title"><?=(($arRes)?$arRes['ru_name']:'Новая позиция')?></h3>
          </div>
          
          <div class="box-body">
          
			<?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
            
            <form role="form" method="post">
              
              <input type="hidden" name="form" value="formPartsItem" />
              <?php if ( $currentRoute->id ) { ?>
              <input type="hidden" name="id" value="<?= $currentRoute->id?>" />
              <?php } ?>
              
              <?php
				
					$formSet = [
						'fields' => [
							[
								'type' => 'select',
								'name' => 'site_id',
								'placeholder' => 'Привязка к сайту',
								'items' => $userSites['sites'],
								'value' => [$arRes['id']]
							],
							[
								'type' => 'text',
								'name' => 'sku',
								'placeholder' => 'Артикул',
								'value' => $arRes['sku'],
								'class' => ''
							],
							[
								'type' => 'text',
								'name' => 'ru_name',
								'placeholder' => 'Наименование',
								'value' => $arRes['ru_name'],
								'class' => ''
							],
							[
								'type' => 'text',
								'name' => 'stock',
								'placeholder' => 'На складе',
								'value' => $arRes['stock'],
								'class' => ''
							],
							[
								'type' => 'text',
								'name' => 'price',
								'placeholder' => 'Цена',
								'value' => $arRes['price'],
								'class' => ''
							],
							[
								'type' => 'text',
								'name' => 'manufacturer',
								'placeholder' => 'Производитель',
								'value' => $arRes['manufacturer'],
								'class' => ''
							],
						],
						'submit' => [
							'class' => 'primary',
							'text' => 'Отправить'
						]
					];
				?>
				
				<?php HTML::Form( $formSet ); ?>
              
              
            </form>
            
          </div>
          
        </div>
      
      </div>
    </div>
  
  </section>
</div>