<?php if ( $currentRoute->id ) { $arRes = $app->User->getById( $currentRoute->id ); } else { $arRes = (object)[]; } ?>
<?php $arRes->sites = (object)$app->getUserSites( $arRes ); ?>
<?php $arRes->apps = (object)$app->Apps->getString( $arRes ); ?>
<?php $arRes->avail_roles = (object)$app->User->getAvailRoles( $authUser ); ?>

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Настройки пользователя <small><?=$arRes->name?></small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  	<div class="row">
    	<div class="col-md-12">
      		
			<div class="box box-info box-solid">
				<div class="box-header with-border">
					<h3 class="box-title">Подключение приложений на сайт</h3>
				</div><!-- /.box-header -->
        		<div class="box-body">
					<strong>Новые виджеты</strong>
                        <pre>
&lt;script&gt;
    document.addEventListener("DOMContentLoaded", () => {
        var yappstoken= '<?=$arRes->public_key?>';
        let yappwidgets = document.createElement('yappwidgets');
            yappwidgets.id = 'YAppWidgets';
        document.body.appendChild(yappwidgets);
        let yappsscript = document.createElement('script');
            yappsscript.type = 'text/javascript';
            yappsscript.charset = 'utf-8';
            yappsscript.src = 'https://apps.yug-avto.ru/API/get/vue-script/?token='+yappstoken;
        document.body.appendChild(yappsscript);
    });
&lt;/script&gt;
                        </pre>

                        <strong>Старые виджеты</strong> + остальные приложения
        			<pre>
&lt;script&gt;
    document.addEventListener("DOMContentLoaded", () => {
        var t = '<?=$arRes->public_key?>', r = location.href, 
            s = document.createElement('script');
        s.type = 'text/javascript';
        s.charset = 'utf-8';
        s.src = 'https://apps.yug-avto.ru/API/get/script/'+'?token='+t+'&r='+r;
        document.body.append(s);
    });
&lt;/script&gt;
        			</pre>
        			или
          			<pre>
&lt;script src="https://apps.yug-avto.ru/API/get/script/?token=<?=$arRes->public_key?>" charset="utf-8"&gt;&lt;/script&gt;
					</pre>
        		</div>
      		</div>

      		<div class="box box-primary">
				<div class="box-header with-border">
					<h3 class="box-title">Пользователь</h3>
				</div>
				<div class="box-body">
					<?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
					<form role="form" method="post">
						
						<input type="hidden" name="form" value="formAdminUser" />
						<input type="hidden" name="from_admin" value="Y" />
						<?php if ( $currentRoute->id ) { ?>
						<input type="hidden" name="id" value="<?= $currentRoute->id?>" />
						<?php } ?>
						
						<?php
							
							$formSet = [
							
								'fields' => [
									[
										'type' => 'text',
										'name' => 'name',
										'placeholder' => 'Имя',
										'value' => $arRes->name,
										'class' => ''
									],
									[
										'type' => 'text',
										'name' => 'email',
										'placeholder' => 'Email',
										'value' => $arRes->email,
										'class' => '',
										'disabled' => true
									],
									[
										'type' => 'text',
										'name' => 'phone',
										'placeholder' => 'Телефон',
										'value' => $arRes->phone,
										'class' => ''
									],
									[
										'type' => 'select',
										'name' => 'role_id',
										'multiple' => false,
										'placeholder' => 'Права',
										'value' => [$arRes->role->id ?? null],
										'items' => $arRes->avail_roles,
										'class' => '',
										'disabled' => ((int)($authUser->role->id ?? 99) > 2) ? true : false
									],
									
									[
										'type' => 'checkbox',
										'name' => 'active',
										'placeholder' => 'Активность',
										'value' => (int)$arRes->active,
										'items' => [
											[
												'text' => 'Активность',
												'value' => (int)$arRes->active
											],
										],
										'class' => ''
									],
									
									[
										'type' => 'delimiter',
									],
									
									[
										'type' => 'select',
										'name' => 'sites[]',
										'multiple' => true,
										'placeholder' => 'Сайты',
										'value' => $arRes->sites->sites_ids ?? [],
										'items' => $app->getSites(),
										'class' => ''
									],
									[
										'type' => 'select',
										'name' => 'apps[]',
										'multiple' => true,
										'placeholder' => 'Приложения',
										'value' => $arRes->apps->apps_ids ?? [],
										'items' => $app->Apps->getApps(),
										'class' => ''
									],
									
									[
										'type' => 'delimiter',
									],
									
									[
										'type' => 'checkbox',
										'name' => 'change_pass',
										'placeholder' => 'Изменить пароль',
										'value' => 0,
										'items' => [
											[
												'text' => 'Изменить пароль',
												'value' => 0
											],
										],
										'class' => ''
									],
									[
										'type' => 'password',
										'name' => 'old_passwd',
										'placeholder' => 'Старый пароль',
										'class' => '',
										'hide' => true
									],
									[
										'type' => 'password',
										'name' => 'passwd',
										'placeholder' => 'Новый пароль',
										'class' => '',
										'hide' => true
									],
									[
										'type' => 'password',
										'name' => 'confim_passwd',
										'placeholder' => 'Подтверждение пароля',
										'class' => '',
										'hide' => true
									],
								],
								'submit' => [
									'class' => 'primary',
									'text' => 'Отправить'
								],
								'script' => '$(document).ready(function(e) {
												$(\'input[name="change_pass"]\').change( function() {
													$(\'div.form-group[hidden="Y"]\').toggle();
												});
											});',
							];
						?>
					
						<?php HTML::Form( $formSet ); ?>
					</form>
				</div>
      		</div>
      
    	</div>
  	</div>
</section>
<!-- /.content -->