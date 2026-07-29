<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1><?=$app->Parts->AppInfo()->ru_name?> <small>Импорт CSV</small></h1>
</section>

<!-- Main content -->
<section class="content">

  <div class="row">

	<div class="col-md-12">
	  
	  <div class="box box-primary">
		<div class="box-header with-border">
		  <h3 class="box-title">Импорт CSV</h3>
		</div>
	    
	    <div class="box-body">
	    
	      <?php if ($POSTRes) HTML::Error($POSTRes); ?>
			
		  <form role="form" method="post" enctype="multipart/form-data">

			<input type="hidden" name="form" value="formPartsImport" />
			<?php if ( $currentRoute->id ) { ?>
			<input type="hidden" name="id" value="<?=$currentRoute->id?>" />
			<?php } ?>

			<?php

				$formSet = [
					'fields' => [
						[
							'type' => 'select',
							'name' => 'site_id',
							'placeholder' => 'Привязка к сайту',
							'items' => $userSites['sites'],
						],
						[
							'type' => 'file',
							'name' => 'import',
							'placeholder' => 'Файл .csv',
							'description' => 'Образец файла: <a href="/upload/Parts/example.csv">example.csv</a>'
						],
					],
					'submit' => [
						'class' => 'primary',
						'text' => 'Загрузить'
					]
				];
			?>

			<?php HTML::Form( $formSet ); ?>
			
			<div class="formCover"><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i></div>
			
		  </form>
		  
		</div>
	  </div>
	    
	  

	</div>

  </div>

</section>
<!-- /.content -->

</div>