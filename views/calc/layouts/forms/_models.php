<?php if ( $currentRoute->id ) $arRes = $app->Calc->getModelById( $currentRoute->id ) ?>
<?php $arUserSites = $app->getUserSites( $authUser ) ?>
<section class="content-header">
  <h1><?=$app->Calc->AppInfo()->ru_name?> <small>Сайты и модели</small></h1>
</section>

<!-- Main content -->
<section class="content">
  
  <div class="row">
    <div class="col-md-12">
    	
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs pull-right">
              <?php if ( $arRes ) { ?>
              <li class=""><a href="#tab_2-2" data-toggle="tab" aria-expanded="true">Настройки таблицы</a></li>
              <?php } ?>
              <li class="active"><a href="#tab_1-1" data-toggle="tab" aria-expanded="false">Настройки модели</a></li>
              <li class="pull-left header">
              	<i class="fa fa-th"></i> 
                Модель: <?=(($arRes)?$arRes['ru_name']:'Новая')?> 
                <?php if ($arRes) { ?>
                <div>
                  <small>
                    <?php foreach ($arRes['sites'] as $site) { ?>
                    <button type="button" class="btn btn-default btn-flat"><?=$site['ru_name']?></button>
                    <?php } ?>
                  </small>
                </div>
                <?php } ?>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane active" id="tab_1-1">
                <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
            
				<?php // Helper::sp_h( $arRes ); ?>
    
                <form role="form" method="post">
                            
                    <input type="hidden" name="form" value="formCalcModel" />
                    <?php if ( $currentRoute->id ) { ?>
                    <input type="hidden" name="id" value="<?= $currentRoute->id?>" />
                    <?php } ?>
                    
                    <?php
        
                        foreach ( $app->Calc->getCheckpoints() as $point ) {
                            
                            $p[$point['id']] = [
                                'id' => $point['id'],
                                'ru_name' => $point['milleage'].' км / '.$point['age'].' '.HTML::formatYears($point['age'])
                            ];
                        }
                        
                        $formSet = [
                            'fields' => [
                                [
                                    'type' => 'text',
                                    'name' => 'ru_name',
                                    'placeholder' => 'Наименование',
                                    'value' => $arRes['ru_name'],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'site_id[]',
                                    'multiple' => true,
                                    'placeholder' => 'Привязка к сайту',
                                    'value' => ( $arRes) ? $arRes['sites_ids'] : [$_GET['site_id']],
                                    'rows' => 5,
                                    'items' => $arUserSites['sites'],
                                    'class' => ''
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'checkwork_id[]',
                                    'multiple' => true,
                                    'placeholder' => 'Работы входящие в ТО',
                                    'value' => $arRes['works_ids'],
                                    'rows' => 10,
                                    'items' => $app->Calc->getMainCheckworks(),
                                    'class' => ''
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'checkwork_id_add[]',
                                    'multiple' => true,
                                    'placeholder' => 'Дополнительные работы',
                                    'value' => $arRes['works_ids'],
                                    'rows' => 6,
                                    'items' => $app->Calc->getAdditionalCheckworks(),
                                    'class' => ''
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'checkpoint_id[]',
                                    'multiple' => true,
                                    'placeholder' => 'Периодичность то',
                                    'value' => $arRes['points_ids'],
                                    'rows' => 6,
                                    'items' => $p,
                                    'class' => ''
                                ],
                                [
                                    'type' => 'select',
                                    'name' => 'discount_id[]',
                                    'multiple' => true,
                                    'placeholder' => 'Предоставляемая скидка',
                                    'first_empty' => false,
                                    'value' => $arRes['discounts_ids'],
                                    'rows' => 5,
                                    'items' => $app->Calc->getDiscounts(),
                                    'class' => ''
                                ],
                                [
                                    'type' => 'textarea',
                                    'name' => 'disclamer',
                                    'placeholder' => 'Дисклеймер',
                                    'value' => $arRes['disclamer'],
                                    'rows' => 5,
                                    'class' => ''
                                ],
                                
                            ],
                            'submit' => [
                                'class' => 'primary',
                                'text' => 'Сохранить'
                            ]
                        ];
                    ?>
                    
                    <?php HTML::Form( $formSet ); ?>
                
                </form>
              </div>
              <!-- /.tab-pane -->
              <?php if ( $arRes ) { ?>
              <div class="tab-pane" id="tab_2-2">
                    
                <form role="form" method="post">
                            
                    <input type="hidden" name="form" value="formCalcModelSettings" />
                    <?php if ( $currentRoute->id ) { ?>
                    <input type="hidden" name="id" value="<?= $currentRoute->id?>" />
                    <?php } ?>
                    
                    <h3>Перечень работ по техническому обслуживанию</h3>
                    <table id="data-table-mainworks" class="table table-hover table-striped table-condensed">
                      <thead>
                        <tr>
                          <th style="min-width: 50%"></th>
                          <?php foreach ( $arRes['points_ids'] as $pId ) { ?>
                          <th><?=$p[$pId]['ru_name']?></th>
                          <?php } // foreach ?>
                        </tr>
                      </thead>
                      <tbody>
                      	<?php foreach ( $arRes['mainworks'] as $mw ) { ?>
                        <tr>
                          <td><?=$mw['ru_name']?></td>
                          <?php foreach ( $arRes['points_ids'] as $pId ) { ?>
                          <td>
                          	<?php
							$field = [
								'type' => 'select',
								'name' => 'work_'.$mw['id'].'[]',
								'placeholder' => $p[$pId]['ru_name'],
								'multiple' => false,
								'value' => [$mw['points_values'][$pId]],
								'class' => 'input-sm',
								'group_class' => 'form_field-no_mb',
								'hidelabel' => true,
								'items' => $app->Calc->getWorkvalues(),
								'select_field' => 'value',
								'params' => [
									'data-idWork'=>$mw['id'],
									'data-idPoint'=>$pId
								]
							];
							HTML::formSelectField( $field );
							?>
						  </td>
                          <?php } // foreach ?>
                        </tr>
                        <?php } // foreach <tr> ?>
                      </tbody>
					</table>
                    
					<h3>Стоимость технического обслуживания на модификации <?=$arRes['ru_name']?></h3>
                    <table class="table table-hover table-striped table-condensed">
                      <thead>
                        <tr>
                          <th style="width: 50%"></th>
                          <?php foreach ( $arRes['points_ids'] as $pId ) { ?>
                          <th><?=$p[$pId]['ru_name']?></th>
                          <?php } // foreach ?>
                        </tr>
					  </thead>
                      <tbody>
                      	<?php foreach ( $arRes['mods'] as $mod ) { ?>
                      	<tr>
                      	  <td>
                      	    <?=$arRes['ru_name']?> <?=$mod['ru_name']?>
                      	    <?php foreach ( $arRes['discounts_ids'] as $kD => $disc ) { ?>
                      	    <br /><?php for ($d=1; $d<=$kD+1; $d++) echo '*'; ?> <small><?=$app->Calc->getDiscountById( $disc )['ru_name']?></small>
                      	    <?php } // foreach ?>
                      	  </td>
                      	  <?php foreach ( $arRes['points_ids'] as $pId ) { ?>
                          <td>
                          	<?php
							$field = [
								'type' => 'text',
								'name' => 'mod_'.$mod['id'].'[]',
								'value' => $mod['points_values'][$pId],
								'class' => 'input-sm',
								'group_class' => 'form_field-no_mb',
								'hidelabel' => true,
								'params' => [
									'data-idMod'=>$mod['id'],
									'data-idPoint'=>$pId
								]
							];
							HTML::formTextField( $field );
							
							foreach ( $arRes['discounts_ids'] as $disc ) {
								$field = [
									'type' => 'text',
									'name' => 'mod_'.$mod['id'].'--disc_'.$disc.'[]',
									'value' => ($mod['points_disc_values'][$disc][$pId]!=0) ? $mod['points_disc_values'][$disc][$pId] : '',
									'class' => 'input-sm',
									'group_class' => 'form_field-no_mb',
									'hidelabel' => true,
									'params' => [
										'data-idMod'=>$mod['id'],
										'data-idPoint'=>$pId,
										'data-discount' => $disc
									]
								];
								HTML::formTextField( $field );
							}
							?>
						  </td>
                     	  <?php } // foreach ?>
                      	</tr>
                      	<?php } //foreach ?>
                      </tbody>
					</table>
               
               		
               		<h3>Перечень дополнительных работ по техническому обслуживанию автомобиля</h3>
               		<table class="table table-hover table-striped table-condensed">
                      <thead>
                        <tr>
                          <th style="width: 50%"></th>
                          <th>Стоимость</th>
                        </tr>
					  </thead>
                      <tbody>
                      	<?php foreach ( $arRes['addworks'] as $aw ) { ?>
                      	<tr>
                      	  <td>
                     	    <?=$aw['ru_name']?>
                      	    <?php foreach ( $arRes['discounts_ids'] as $kD => $disc ) { ?>
                      	    <br /><?php for ($d=1; $d<=$kD+1; $d++) echo '*'; ?> <small><?=$app->Calc->getDiscountById( $disc )['ru_name']?></small>
                      	    <?php } // foreach ?>
                      	  </td>
                          <td>
                          	<?php
							$field = [
								'type' => 'text',
								'name' => 'addwork_'.$aw['id'],
								'value' => $aw['price'],
								'class' => 'input-sm',
								'group_class' => 'form_field-no_mb',
								'hidelabel' => true,
								'params' => [
									'data-idMod'=>$mod['id'],
									'data-idPoint'=>$pId
								]
							];
							HTML::formTextField( $field );
							
							foreach ( $arRes['discounts_ids'] as $disc ) {
								$field = [
									'type' => 'text',
									'name' => 'addwork_'.$aw['id'].'--disc_'.$disc,
									'value' => $aw['price_discount'][$disc],
									'class' => 'input-sm',
									'group_class' => 'form_field-no_mb',
									'hidelabel' => true,
									'params' => [
										'data-idAddwork'=>$aw['id'],
										'data-discount' => $disc
									]
								];
								HTML::formTextField( $field );
							}
							?>
						  </td>
                      	</tr>
                      	<?php } //foreach ?>
                      </tbody>
					</table>
               
               		<button type="submit" class="btn btn-primary">Сохранить</button>
                </form>
                
              </div>
              <!-- /.tab-pane -->
              <?php } ?>
              
            </div>
            <!-- /.tab-content -->
          </div>
		  
    </div>
  </div>
  
</section>
<!-- /.content -->