<?php

	class HTML extends App {
		
/////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////// PRIVATE AREA //////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		private static function formRenderField( $q ) {
			
			switch ( $q['type'] ) {
						
				case 'text':
					self::formTextField( $q );
					break;
					
				case 'phone':
					self::formPhoneField( $q );
					break;
				
				case 'delimiter':
					self::formDelimiter( $q );
					break;
					
				case 'password':
					self::formPasswordField( $q );
					break;
				
				case 'checkbox':
					self::formCkeckboxField( $q );
					break;
					
				case 'select':
					self::formSelectField( $q );
					break;
					
				case 'avatar':
					self::formAvatarField( $q );
					break;
					
				case 'textarea':
					self::formTextareaField( $q );
					break;
					
				case 'hidden':
					self::formHiddenField( $q );
					break;
					
				case 'color':
					self::formColorField( $q );
					break;
					
				case 'date':
					self::formDateTimeField( $q );
					break;
				
				case 'time':
					self::formTimeField( $q );
					break;
					
				case 'file':
					self::formFileField( $q );
					break;
					
				case 'image':
					self::formImageField( $q );
					break;
					
				case 'number':
					self::formNumberField( $q );
					break;
					
				case 'calendar':
					self::formCalendar( $q );
					break;
					
				case 'datetimerange':
					self::formDatetimeRangeField( $q );
					break;
					
				case 'linegrouptext':
					self::formLineGroupText( $q );
					break;

                case 'group':
					self::formGroupField( $q );
					break;

                case 'delete':
					self::formDelete( $q );
					break;

                default: break;
					
			} // switch
		}
		
		
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////// PUBLIC AREA //////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////
		
		static function Error($q) {

            if ( !!$q->redirect ) { header('Location: '.$q->redirect); exit; }

			?>
            <div class="alert alert-<?=(($q->status=='success')?'success':'danger')?> alert-<?=(($q->status=='success')?'check':'dismissible')?>">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
              <h4><i class="icon fa fa-warning"></i> <?=(($q->status=='success')?'Ok':'Ошибка')?></h4>
              <p><?=$q->description?></p>
            </div>
            <?php
		}
		
		static function Denied() {
			
			?>
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
              <h4><i class="icon fa fa-warning"></i> Ошибка</h4>
              <p>Доступ запрещен</p>
            </div>
            <?php
		}
		
		static function DeniedFull() {
			
			?>
            <!-- Main content -->
  			<section class="content">
              <?php self::Denied(); ?>
            </section>
            <?php
		}
		
		static function leftMenu( $app, $route ) {
            
            if ( $app['settings']['view_in_menu'] ) {

			?>
			<li class="treeview <?=(($route->section==$app['settings']['url_key'])?'active menu-open':'')?>">
              <a href="#">
                <i class="fa fa-<?=$app['settings']['fa_icon']?>"></i>
                <span><?=$app['settings']['ru_name']?></span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                
                <?php if ( !$app['settings']['hide_home'] ) { ?>
                <li class="<?=(($route->section==$app['settings']['url_key']&&$route->view=='')?'active':'')?>">
                  <a href="/<?=$app['settings']['url_key']?>/"><i class="fa fa-home"></i> Домашняя страница</a>
                </li>
                <?php } // if view home page ?>
                
				<?php foreach ($app['menu'] as $item) { ?>
                <li class="<?=(($route->section==$app['settings']['url_key']&&$route->view==$item['url_key'])?'active':'')?>">
                  <a href="/<?=$app['settings']['url_key']?>/<?=$item['url_key']?>/"><i class="fa fa-<?=$item['icon']?>"></i> <?=$item['name']?></a>
                </li>
                <? } ?>
                
                <?php foreach ($app['add_menu'] as $item) { ?>
                    <?php if ( !$app['settings']['hide_'.$item['url_key']] ) { ?>
                    <li class="<?=(($route->section==$app['settings']['url_key']&&$route->view==$item['url_key'])?'active':'')?>">
                        <a href="/<?=$app['settings']['url_key']?>/<?=$item['url_key']?>/"><i class="fa fa-<?=$item['icon']?>"></i> <?=$item['name']?></a>
                    </li>
                    <?php } // if !hide ?>
                <? } ?>
                
              </ul>
            </li>
			<?php
            }
		}
		
		
		
		/// Form area
		
		static function formTextField( $q ) {
			
			?>
            <div 
                class="form-group <?=(($q['group_class'])?$q['group_class']:'')?>"
                <?php if ( !!$q['dinamic'] && !$q['dinamic']['parent'] ) { ?>
                role="dinamic-child" dinamic-name="<?= $q['dinamic']['name'];?>" dinamic-value="<?= $q['dinamic']['value'];?>" dinamic-if="<?= json_encode($q['dinamic']['show_if'])?>" 
                style="display: <?= ((in_array($q['dinamic']['value'],$q['dinamic']['show_if']))?'block':'none');?>"
                <?php } ?>
                >
              <?php if ( !$q['hidelabel'] ) { ?>
              <label style="width: 100%;">
			  	<?=$q['placeholder']?>
                <?php if ( $q['multiple'] ) { ?>
                <span class="pull-right"><a href="#" role="add_input">Добавить поле ввода</a></span>
                <?php } // if ?>
              </label>
              <?php } // if ?>
              <?php if ($q['addon']) { ?>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-<?=$q['addon']?>"></i>
                </div>
              <? } // if ?>
              <?php if ( is_array($q['value']) && !empty($q['value']) ) { ?>
				<?php foreach ($q['value'] as $v) { ?>
                <input 
                  type="<?=$q['type']?>" 
                  <?php if ( $q['id'] ) { ?>
                  id="<?=$q['id']?>" 
                  <?php } ?>
                  class="form-control <?=$q['class']?>" 
                  name="<?=$q['name']?>[]" 
                  placeholder="<?=$q['placeholder']?>" 
                  value='<?=(($v)?:'')?>' 
                  <?php if ( $q['name'] == 'phone' ) { ?>
                  data-inputmask='"mask": "+7 (999) 999-99-99"' data-mask 
                  <?php } ?>
                  <?php if ( $q['disabled'] ) { ?>
                  disabled 
                  <?php } ?>
                  <?php if ( $q['hide'] ) { ?>
                  style="display: none;" 
                  <?php } ?>
                  <?php if ($q['params']) {
                      foreach ( $q['params'] as $kP => $vP ) { ?>
                          <?=$kP?>="<?=$vP?>" 
                      <?php }
                  } ?>
                />
                <?php } // foreach ?>
              <?php } else { ?>
              <input 
                type="<?=$q['type']?>" 
                <?php if ( $q['id'] ) { ?>
                id="<?=$q['id']?>" 
                <?php } ?>
                class="form-control <?=$q['class']?>" 
                name="<?=$q['name']?><?=(($q['multiple'])?'[]':'')?>" 
                placeholder="<?=$q['placeholder']?>" 
                value='<?=(($q['value'])?:'')?>' 
                <?php if ( $q['name'] == 'phone' ) { ?>
                data-inputmask='"mask": "+7 (999) 999-99-99"' data-mask 
                <?php } ?>
                <?php if ( $q['disabled'] ) { ?>
                disabled 
                <?php } ?>
                <?php if ( $q['hide'] ) { ?>
                style="display: none;" 
                <?php } ?>
                <?php if ($q['params']) {
					foreach ( $q['params'] as $kP => $vP ) { ?>
						<?=$kP?>="<?=$vP?>" 
					<?php }
				} // ?>
              />
              <?php } // if multiple ?>
              <?php if ($q['addon']) { ?>
              </div>
              <?php } ?>
              <?php if ( $q['description'] ) { ?>
			  <p class="help-block"><?=$q['description']?></p>
			  <?php } ?>
            </div>
            <?php
		}
		
		static function formPhoneField( $q ) {
			
			?>
            <div class="form-group <?=(($q['group_class'])?$q['group_class']:'')?>">
              <?php if ( !$q['hidelabel'] ) { ?>
              <label style="width: 100%;">
			  	<?=$q['placeholder']?>
                <?php if ( $q['multiple'] ) { ?>
                <span class="pull-right"><a href="#" role="add_input">Добавить поле ввода</a></span>
                <?php } // if ?>
              </label>
              <?php } // if ?>
              <?php if ($q['addon']) { ?>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-<?=$q['addon']?>"></i>
                </div>
              <? } // if ?>
              <?php if ( is_array($q['value']) && !empty($q['value']) ) { ?>
				<?php foreach ($q['value'] as $v) { ?>
                <input 
                  type="text" 
                  <?php if ( $q['id'] ) { ?>
                  id="<?=$q['id']?>" 
                  <?php } ?>
                  class="form-control <?=$q['class']?>" 
                  name="<?=$q['name']?>[]" 
                  placeholder="<?=$q['placeholder']?>" 
                  value="<?=(($v)?:'')?>" 
                  data-inputmask='"mask": "+7 (999) 999-99-99"' data-mask 
                  <?php if ( $q['disabled'] ) { ?>
                  disabled 
                  <?php } ?>
                  <?php if ( $q['hide'] ) { ?>
                  style="display: none;" 
                  <?php } ?>
                  <?php if ($q['params']) {
                      foreach ( $q['params'] as $kP => $vP ) { ?>
                          <?=$kP?>="<?=$vP?>" 
                      <?php }
                  } ?>
                />
                <?php } // foreach ?>
              <?php } else { ?>
              <input 
                type="text" 
                <?php if ( $q['id'] ) { ?>
                id="<?=$q['id']?>" 
                <?php } ?>
                class="form-control <?=$q['class']?>" 
                name="<?=$q['name']?><?=(($q['multiple'])?'[]':'')?>" 
                placeholder="<?=$q['placeholder']?>" 
                value="<?=(($q['value'])?:'')?>" 
                data-inputmask='"mask": "+7 (999) 999-99-99"' data-mask 
                <?php if ( $q['disabled'] ) { ?>
                disabled 
                <?php } ?>
                <?php if ( $q['hide'] ) { ?>
                style="display: none;" 
                <?php } ?>
                <?php if ($q['params']) {
					foreach ( $q['params'] as $kP => $vP ) { ?>
						<?=$kP?>="<?=$vP?>" 
					<?php }
				} // ?>
              />
              <?php } // if multiple ?>
              <?php if ($q['addon']) { ?>
              </div>
              <?php } ?>
              <?php if ( $q['description'] ) { ?>
			  <p class="help-block"><?=$q['description']?></p>
			  <?php } ?>
            </div>
            <?php
		}
		
		static function formNumberField( $q ) {
			
			?>
            <div 
                class="form-group 
                <?=(($q['group_class'])?$q['group_class']:'')?>"
                <?php if ( !!$q['dinamic'] && !$q['dinamic']['parent'] ) { ?>
                role="dinamic-child" dinamic-name="<?= $q['dinamic']['name'];?>" dinamic-value="<?= $q['dinamic']['value'];?>" dinamic-if="<?= json_encode($q['dinamic']['show_if'])?>" 
                style="display: <?= ((in_array($q['dinamic']['value'],$q['dinamic']['show_if']))?'block':'none');?>"
                <?php } ?>
                >
              <?php if ( !$q['hidelabel'] ) { ?>
              <label><?=$q['placeholder']?></label>
              <?php } ?>
              <?php if ($q['addon']) { ?>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-<?=$q['addon']?>"></i>
                </div>
              <? } ?>
              <input 
                type="<?=$q['type']?>" 
                <?php if ( $q['id'] ) { ?>
                id="<?=$q['id']?>" 
                <?php } ?>
                class="form-control <?=$q['class']?>" 
                name="<?=$q['name']?>" 
                placeholder="<?=$q['placeholder']?>" 
                value="<?=$q['value']?>" 
                <?php if ( $q['step'] ) { ?>
                step="<?=$q['step']?>" 
                <?php } ?>
                <?php if ( $q['disabled'] ) { ?>
                disabled 
                <?php } ?>
                <?php if ( $q['hide'] ) { ?>
                style="display: none;" 
                <?php } ?>
                <?php if ($q['params']) {
					foreach ( $q['params'] as $kP => $vP ) { ?>
						<?=$kP?>="<?=$vP?>" 
					<?php }
				} ?>
              />
              <?php if ($q['addon']) { ?>
              </div>
              <?php } ?>
              <?php if ( $q['description'] ) { ?>
			  <p class="help-block"><?=$q['description']?></p>
			  <?php } ?>
            </div>
            <?php
		}
		
		static function formColorField( $q ) {
			
			?>
            <div class="form-group <?=(($q['group_class'])?$q['group_class']:'')?>">
              <?php if ( !$q['hidelabel'] ) { ?>
              <label><?=$q['placeholder']?></label>
              <?php } ?>
              <div class="input-group my-colorpicker_<?=$q['name']?>">
                <input 
                  type="text" 
                  <?php if ( $q['id'] ) { ?>
                  id="<?=$q['id']?>" 
                  <?php } ?>
                  class="form-control <?=$q['class']?>" 
                  name="<?=$q['name']?>" 
                  value="<?=$q['value']?>" 
                  <?php if ( $q['disabled'] ) { ?>
                  disabled 
                  <?php } ?>
                  <?php if ( $q['hide'] ) { ?>
                  style="display: none;" 
                  <?php } ?>
                  <?php if ($q['params']) {
                      foreach ( $q['params'] as $kP => $vP ) { ?>
                          <?=$kP?>="<?=$vP?>" 
                      <?php }
                  } ?>
                />
                <div class="input-group-addon"><i></i></div>
              </div>
              <?php if ($q['addon']) { ?>
              </div>
              <?php } ?>
              <?php if ( $q['description'] ) { ?>
			  <p class="help-block"><?=$q['description']?></p>
			  <?php } ?>
            </div>
            <?php
		}
		
		static function formDateTimeField( $q ) {
			
			?>
            <div class="form-group <?=(($q['group_class'])?$q['group_class']:'')?>">
              <?php if ( !$q['hidelabel'] ) { ?>
              <label><?=$q['placeholder']?></label>
              <?php } ?>
              <div class="input-group date">
                <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
                <input  
                  type="text"
                  id="datepicker_<?=$q['name']?>" 
                  class="form-control pull-right <?=$q['class']?>" 
                  name="<?=$q['name']?>" 
                  value="<?=$q['value']?>" 
                  <?php if ( $q['disabled'] ) { ?>
                  disabled 
                  <?php } ?>
                  <?php if ( $q['hide'] ) { ?>
                  style="display: none;" 
                  <?php } ?>
                  <?php if ($q['params']) {
                      foreach ( $q['params'] as $kP => $vP ) { ?>
                          <?=$kP?>="<?=$vP?>" 
                      <?php }
                  } ?>
                />
              </div>
              <?php if ( $q['description'] ) { ?>
			  <p class="help-block"><?=$q['description']?></p>
			  <?php } ?>
            </div>
            <?php
		}
		
		static function formTimeField( $q ) {
			
			?>
            <div class="form-group <?=(($q['group_class'])?$q['group_class']:'')?>">
              <?php if ( !$q['hidelabel'] ) { ?>
              <label><?=$q['placeholder']?></label>
              <?php } ?>
              <div class="input-group date">
                <div class="input-group-addon"><i class="fa fa-clock-o"></i></div>
                <input 
                  type="text"
                  id="timepicker_<?=$q['name']?>" 
                  class="form-control pull-right timepicker_<?=$q['name']?> <?=$q['class']?>" 
                  name="<?=$q['name']?>" 
                  value="<?=$q['value']?>" 
                  <?php if ( $q['disabled'] ) { ?>
                  disabled 
                  <?php } ?>
                  <?php if ( $q['hide'] ) { ?>
                  style="display: none;" 
                  <?php } ?>
                  <?php if ($q['params']) {
                      foreach ( $q['params'] as $kP => $vP ) { ?>
                          <?=$kP?>="<?=$vP?>" 
                      <?php }
                  } ?>
                />
              </div>
              <?php if ( $q['description'] ) { ?>
			  <p class="help-block"><?=$q['description']?></p>
			  <?php } ?>
            </div>
            <?php
		}
		
		static function formDatetimeRangeField( $q ) {
			
			for ($i=1;$i<=$q['count'];$i++) {
				?>
				<div class="form-group <?=(($q['group_class'])?$q['group_class']:'')?>" <?=(($q['value'][$i-1])?'':'style="display: none;"')?> data-index="<?=$i?>" role="datetimerange">
                  <?php if ( !$q['hidelabel'] ) { ?>
                  <label style="width: 100%;">
					<?=$q['placeholder']?>
                    <?php if ( $q['multiple'] ) { ?>
                    <span class="pull-right">
                      <?php if ( $i<$q['count'] ) { ?>
                      <a href="#" role="add_datetimerange" data-index="<?=$i?>">Добавить интервал</a>
                       / 
                      <?php } ?>
                      <a href="#" role="remove_datetimerange" data-index="<?=$i?>">Убрать интервал</a>
                    </span>
                    <?php } // if ?>
                  </label>
                  <?php } ?>
                  <div class="input-group">
                    <div class="input-group-addon"><i class="fa fa-clock-o"></i></div>
                    <input 
                      type="text" 
                      id="datetimerange_<?=preg_replace( "/[^a-zA-ZА-Яа-я0-9\s]/", '', $q['name'])?><?=$i?>" 
                 	  class="form-control pull-right <?=$q['class']?>" 
                      name="<?=$q['name']?>[]" 
                  	  value="<?=$q['value'][$i-1]?>" 
                      <?=(($q['value'][$i-1])?'':'disabled')?> 
                      <?php if ( $q['hide'] ) { ?>
                      style="display: none;" 
                      <?php } ?>
                      <?php if ($q['params']) {
                          foreach ( $q['params'] as $kP => $vP ) { ?>
                              <?=$kP?>="<?=$vP?>" 
                          <?php }
                      } ?>
                    >
                  </div>
                  <?php if ( $q['description'] ) { ?>
                  <p class="help-block"><?=$q['description']?></p>
                  <?php } ?>
                </div>
                <?php
			}
		}
		
		static function formLineGroupText( $q ) {
			
			?>
            <div <?=(($q['hideable'])?'hidable data-showname="'.$q['hidename'].'"':'')?> <?=(($q['hide'])?'style="display:none;"':'')?>>
            <?php
			for ($i=1;$i<=$q['count'];$i++) {
				?>
				<div class="row <?=(($q['group_class'])?$q['group_class']:'')?>" <?=(($q['value'][$i-1])?'':'style="display: none;"')?>  data-index="<?=$i?>" role="linegrouptext" data-target="<?=$q['group_name']?>">
				  <div class="col-md-12">
                    <?php if ( $q['multiple'] ) { ?>
                    <span class="pull-right">
                      <?php if ( $i<$q['count'] ) { ?>
                      <a href="#" role="add_linegrouptext"  data-target="<?=$q['group_name']?>" data-index="<?=$i?>">Добавить ещё</a>
                       / 
                      <?php } ?>
                      <a href="#" role="remove_linegrouptext"  data-target="<?=$q['group_name']?>" data-index="<?=$i?>">Убрать</a>
                    </span>
                    <?php } // if ?>
                  </div>
				  <?php foreach ( $q['fields'] as $f ) { ?>
                  <div class="col-md-<?=(($f['type']=='text')?5:2)?>">
                    <div class="form-group">
                      <?php if ($f['type']=='text') { ?>
                      <label style="width: 100%;">
                        <?=$f['placeholder']?>
                      </label>
                      <input 
                          type="text" 
                          <?php if ( $q['id'] ) { ?>
                          id="<?=$q['id']?>" 
                          <?php } ?>
                          class="form-control <?=$q['class']?>" 
                          name="<?=$f['name']?>[]" 
                          placeholder="<?=$f['placeholder']?>" 
                          value="<?=(($q['value'][$i-1][explode('_',$f['name'])[1]])?:'')?>" 
                          <?php if ( $q['name'] == 'phone' ) { ?>
                          data-inputmask='"mask": "+7 (999) 999-99-99"' data-mask 
                          <?php } ?>
                          <?php if ( $q['disabled'] ) { ?>
                          disabled 
                          <?php } ?>
                          <?php if ($q['params']) {
                              foreach ( $q['params'] as $kP => $vP ) { ?>
                                  <?=$kP?>="<?=$vP?>" 
                              <?php }
                          } // if?>
                        />
                      <?php } // if text ?>
                      <?php if ($f['type']=='checkbox') { ?>
                      <label style="display:block;">&nbsp;</label>
                      <label>
                        <input 
                          type="<?=$f['type']?>" 
                          class="<?=$f['class']?>" 
                          name="<?=$f['name']?>[]" 
                          value="<?=$i-1?>"
                          <?=(((int)$q['value'][$i-1][explode('_',$f['name'])[1]]==1)?'checked':'')?>
                          <?php if ( $q['disabled'] ) { ?>
                          disabled 
                          <?php } ?>
                          <?php if ($f['params']) {
                                foreach ( $f['params'] as $kP => $vP ) { ?>
                                    <?=$kP?>="<?=$vP?>" 
                                <?php }
                            } ?>
                        /> <?=$f['placeholder']?>
                      </label>
                      <?php } // if checkbox ?>
                    </div>
                  </div>
                  <?php } ?>
				</div>
				<?php
			}
			?>
            </div>
            <?php
		}
		
		static function formFileField( $q ) {
			
			?>
            <div class="form-group <?=(($q['group_class'])?$q['group_class']:'')?>">
              <?php if ( !$q['hidelabel'] ) { ?>
              <label><?=$q['placeholder']?></label>
              <?php } ?>
              <?php if ($q['addon']) { ?>
              <div class="input-group">
                <div class="input-group-addon">
                  <i class="fa fa-<?=$q['addon']?>"></i>
                </div>
              <? } ?>
              <input 
                type="<?=$q['type']?>" 
                <?php if ( $q['id'] ) { ?>
                id="<?=$q['id']?>" 
                <?php } ?>
                class="form-control <?=$q['class']?>" 
                name="<?=$q['name']?>" 
                placeholder="<?=$q['placeholder']?>" 
                value="<?=$q['value']?>" 
                <?php if ( $q['disabled'] ) { ?>
                disabled 
                <?php } ?>
                <?php if ( $q['multiple'] ) { ?>
                multiple 
                <?php } ?>
                <?php if ( $q['hide'] ) { ?>
                style="display: none;" 
                <?php } ?>
                <?php if ($q['params']) {
					foreach ( $q['params'] as $kP => $vP ) { ?>
						<?=$kP?>="<?=$vP?>" 
					<?php }
				} ?>
              />
              <?php if ($q['addon']) { ?>
              </div>
              <?php } ?>
              <?php if ( $q['description'] ) { ?>
			  <p class="help-block"><?=$q['description']?></p>
			  <?php } ?>
            </div>
            <?php
		}
		
		static function formImageField( $q ) {
			
			?>
            <div class="form-group <?=(($q['group_class'])?$q['group_class']:'')?>">
              <?php if ( !$q['hidelabel'] ) { ?>
              <label><?=$q['placeholder']?></label>
              <?php } ?>
              <?php if ($q['addon']) { ?>
              <div class="input-group">
                <div class="input-group-addon">
                  <i class="fa fa-<?=$q['addon']?>"></i>
                </div>
              <? } ?>
              <input 
                type="file" 
                <?php if ( $q['id'] ) { ?>
                id="<?=$q['id']?>" 
                <?php } ?>
                class="form-control <?=$q['class']?>" 
                name="<?=$q['name']?>" 
                placeholder="<?=$q['placeholder']?>" 
                value="" 
                <?php if ( $q['disabled'] ) { ?>
                disabled 
                <?php } ?>
                <?php if ( $q['multiple'] ) { ?>
                multiple 
                <?php } ?>
                <?php if ( $q['hide'] ) { ?>
                style="display: none;" 
                <?php } ?>
                <?php if ($q['params']) {
					foreach ( $q['params'] as $kP => $vP ) { ?>
						<?=$kP?>="<?=$vP?>" 
					<?php }
				} ?>
              />
              <?php if ($q['addon']) { ?>
              </div>
              <?php } ?>
              <?php if ( $q['description'] ) { ?>
			  <p class="help-block"><?=$q['description']?></p>
			  <?php } ?>
              <?php if ( $q['value'] ) { ?>
              <p class="help-block">
                <?php if ( is_array($q['value']) ) { ?>
                  <?php foreach( $q['value'] as $v ) { ?><img src="<?=$v?>" style="width: 10%;" /><?php } ?>
                <?php } else { ?>
                <img src="<?=$q['value']?>" style="width: 10%;" />
                <?php } ?>
              </p>
              <?php } ?>
            </div>
            <?php
		}
		
		static function formPasswordField( $q ) {
			
			?>
            <div class="form-group <?=(($q['group_class'])?$q['group_class']:'')?>" hidden="<?=(($q['hide'])?'Y':'')?>" style="<?=(($q['hide'])?'display:none;':'')?>">
              <?php if ( !$q['hidelabel'] ) { ?>
              <label><?=$q['placeholder']?></label>
              <?php } ?>
              <input 
                type="<?=$q['type']?>" 
                class="form-control <?=$q['class']?>" 
                name="<?=$q['name']?>" 
                placeholder="<?=$q['placeholder']?>" 
                <?php if ( $q['disabled'] ) { ?>
                disabled 
                <?php } ?>
                <?php if ($q['params']) {
					foreach ( $q['params'] as $kP => $vP ) { ?>
						<?=$kP?>="<?=$vP?>" 
					<?php }
				} ?>
              />
            </div>
            <?php
		}
		
		static function formCkeckboxField( $q ) {
			
			?>
            <div 
                class="checkbox"
                <?php if ( !!$q['dinamic'] && !$q['dinamic']['parent'] ) { ?>
                role="dinamic-child" dinamic-name="<?= $q['dinamic']['name'];?>" dinamic-value="<?= $q['dinamic']['value'];?>" dinamic-if="<?= json_encode($q['dinamic']['show_if'])?>" 
                style="display: <?= ((in_array($q['dinamic']['value'],$q['dinamic']['show_if']))?'block':'none');?>"
                <?php } ?>
                >
			<?php foreach ( $q['items'] as $i ) { ?>
              <label>
                <input 
                  type="<?=$q['type']?>" 
                  class="<?=$q['class']?>" 
                  name="<?=$q['name']?>" 
                  <?=(($i['value']==1)?'checked':'')?>
                  <?php if ( $q['disabled'] ) { ?>
                  disabled 
                  <?php } ?>
				  <?php if ($q['params']) {
						foreach ( $q['params'] as $kP => $vP ) { ?>
							<?=$kP?>="<?=$vP?>" 
						<?php }
					} ?>
                /> <?=$i['text']?>
              </label>
              <?php if ( $q['description'] ) { ?>
			  <p class="help-block"><?=$q['description']?></p>
			  <?php } ?>
            <?php } ?>
            </div>
            <?php
		}
		
		static function formSelectField( $q ) {
			
			$sf = ( $q['select_field'] ) ?: 'ru_name';
			$si = ( $q['select_id'] ) ?: 'id';
			?>
            <div 
            class="form-group <?=(($q['group_class'])?$q['group_class']:'')?>" 
            <?=(($q['hideable'])?'hidable data-showname="'.$q['hidename'].'"':'')?> 
            <?=(($q['hide'])?'style="display:none;"':'')?>
            <?php if ( !!$q['dinamic'] && !$q['dinamic']['parent'] ) { ?>
            role="dinamic-child" dinamic-name="<?= $q['dinamic']['name'];?>" dinamic-value="<?= $q['dinamic']['value'];?>" dinamic-if="<?= json_encode($q['dinamic']['show_if'])?>" 
            style="display: <?= ((in_array($q['dinamic']['value'],$q['dinamic']['show_if']))?'block':'none');?>"
            <?php } ?>
            >
              <?php if ( !$q['hidelabel'] ) { ?>
              <label><?=$q['placeholder']?></label>
              <?php } ?>
              <?php if ($q['addon']) { ?>
              <div class="input-group date">
                <div class="input-group-addon">
                  <i class="fa fa-<?=$q['addon']?>"></i>
                </div>
              <? } ?>
              <select 
                    <?=(($q['multiple'])?'multiple size="'.(($q['rows'])?$q['rows']:'20').'"':'')?> 
                    class="form-control <?=$q['class']?>" 
                    name="<?=$q['name']?>" 
                    <?php if ($q['showto']) { ?>role="showto" <?php } ?>
                    <?=(($q['disabled'])?'disabled':'')?>
                    <?php if ($q['params']) {
						foreach ( $q['params'] as $kP => $vP ) { ?>
							<?=$kP?>="<?=$vP?>" 
						<?php }
					} ?>
                    <?php if ( $q['dinamic']['parent'] ) { ?>
                    role="dinamic-parent" dinamic-name="<?= $q['dinamic']['name'];?>"      
                    <?php } ?>
                >
                <?php if ( $q['first_empty'] ) { ?>
                <option disabled selected>Выбрать..</option>
                <?php } ?>
                <?php if ( $q['first_empty_not_disabled'] ) { ?>
                <option value="" selected>---</option>
                <?php } ?>
                <?php foreach ( $q['items'] as $i ) { ?>
                <option 
                    value="<?=$i[$si]?>" 
                    <?=((in_array($i[$si], $q['value']))?'selected':'')?> 
                    <?=(($q['showto'])?'data-showname="'.$i['showname'].'"':'')?>
                    <?= (($q['data'])?'data-'.$q['data'].'="'.$i[$q['data']].'"':'');?>
                    ><?=$i[$sf]?></option>
                <?php } ?>
              </select>
              <?php if ($q['addon']) { ?>
              </div>
              <? } ?>
              <?php if ( $q['description'] ) { ?>
			  <p class="help-block"><?=$q['description']?></p>
			  <?php } ?>
            </div>
            <?php
		}
		
		static function formAvatarField( $q ) {
			
			?>
            <div class="form-group <?=(($q['group_class'])?$q['group_class']:'')?>">
              <?php if ( !$q['hidelabel'] ) { ?>
              <label><?=$q['placeholder']?></label>
              <?php } ?>
              <img class="profile-user-img img-responsive img-circle" style="margin: 15px 0;" src="<?=$q['value']?>" />
              <input type="file" name="<?=$q['name']?>">
            </div>
            <?php
		}
		
		static function formTextareaField( $q ) {
			
			?>
            <div 
            class="form-group <?=(($q['group_class'])?$q['group_class']:'')?>"
            <?php if ( !!$q['dinamic'] && !$q['dinamic']['parent'] ) { ?>
            role="dinamic-child" dinamic-name="<?= $q['dinamic']['name'];?>" dinamic-value="<?= $q['dinamic']['value'];?>" dinamic-if="<?= json_encode($q['dinamic']['show_if'])?>" 
            style="display: <?= ((in_array($q['dinamic']['value'],$q['dinamic']['show_if']))?'block':'none');?>"
            <?php } ?>
            >
                <?php if ( !$q['hidelabel'] ) { ?>
                <label><?=$q['placeholder']?></label>
                <?php } ?>
                <textarea 
                  class="form-control <?=$q['class']?>" 
                  name="<?=$q['name']?>" 
                  rows="<?=$q['rows']?>" 
                  <?php if ($q['cols']) { ?>cols="<?=$q['cols']?>" <?php } ?>
				  <?=(($q['ckeditor'])?'id="ckeditor"':'')?> 
                  placeholder="<?=$q['placeholder']?>" 
				  <?=(($q['disabled'])?'disabled':'')?>
				  <?php if ($q['params']) {
                      foreach ( $q['params'] as $kP => $vP ) { ?>
                          <?=$kP?>="<?=$vP?>" 
                      <?php }
                  } ?>
                  ><?=$q['value']?></textarea>
                <?php if ( $q['description'] ) { ?>
                <p class="help-block"><?=$q['description']?></p>
                <?php } ?>
            </div>
            <?php
		}
		
		static function formHiddenField( $q ) {
			
			?>
            <input type="<?=$q['type']?>" name="<?=$q['name']?>" value="<?=$q['value']?>" 
            <?php if ($q['params']) {
					foreach ( $q['params'] as $kP => $vP ) { ?>
						<?=$kP?>="<?=$vP?>" 
					<?php }
				} ?>
            />
            <?php
		}
		
		static function formDelimiter( $q ) {
			?>
            <div 
                class="form-group" 
                <?=(($q['hideable'])?'hidable data-showname="'.$q['hidename'].'"':'')?>
                <?=(($q['hide'])?'style="display:none;"':'')?>
                <?php if ( !!$q['dinamic'] && !$q['dinamic']['parent'] ) { ?>
                role="dinamic-child" dinamic-name="<?= $q['dinamic']['name'];?>" dinamic-value="<?= $q['dinamic']['value'];?>" dinamic-if="<?= json_encode($q['dinamic']['show_if'])?>" 
                style="display: <?= ((in_array($q['dinamic']['value'],$q['dinamic']['show_if']))?'block':'none');?>"
                <?php } ?>
                >
                <?php if ( $q['value'] ) { ?>
                <div class="callout callout-<?=(($q['class'])?:'default')?>">
                    <p>
                    <?=$q['value']?>
                    <?php if ( $q['button'] ) { ?>
                    <span class="pull-right"><a href="#" role="<?=$q['button']['role']?>"  data-target="<?=$q['button']['target']?>" data-index="0"><?=$q['button']['text']?></a></span>
                    <?php } ?>
                    </p>
                </div>
                <?php } else { ?>
                <hr />
                <?php } // if ?>
            </div>
            <?php
		}

        static function formDelete( $q ) {
            ?>
            <div class="form-group <?=(($q['group_class'])?$q['group_class']:'')?>">
                <a href="?action=<?= $q['get_action'];?>&value=<?= $q['value'];?>" class="btn btn-danger btn-flat"><?= $q['text'];?></a>
                <?php if ( $q['description'] ) { ?>
                <p class="help-block"><?=$q['description']?></p>
                <?php } ?>
            </div>
            <?php
        }
		
		static function formCalendar( $q ) {
			
			$cal = Helper::getCalendar();
			$week = Helper::getWeek();
			
			switch ( $q['mode'] ) {
				
				default:
					
					for ( $i=0; $i<7; $i++ ) {
						?>
                        <div class="text-center" style="width: 14.05%; display: inline-block;">
                          <span class="label label-<?=(($i<5)?'primary':'warning')?> text-center" style="display: block;"><?=$week[$i]?></span>
                        </div>
                        <?php
					}
					?>
                    <div class="w-100"></div>
					<?php
					
					foreach ( $cal as $i => $w ) {
						
						foreach ( $w as $k => $d ) {
							
							?>
                            <div style="width: 14.05%; padding: 0 10px; display: inline-block; <?=(($k==0)?'':'border-left: gainsboro 1px solid;')?>">
							  <?php if ( (int)$d != 0 ) {
                              	
								?>
                                <h5 class="text-center"><strong><?=date('d.m.Y', strtotime(date('Y-m-'.$d)))?></strong></h5>
                                <input type="hidden" name="date[]" value="<?=date('Y-m-d', strtotime(date('Y-m-'.$d)))?>">
                                <?php
							  	
								foreach ( $q['fields'] as $f ) {
									
									$tmp = [$f['value'][date('Y-m-d', strtotime(date('Y-m-'.$d)))]['schedule_id']];
									$f['value'] = $tmp;
									self::formRenderField( $f );
								}
							  
                              } ?>
                            </div>
                            <?php
						}
						?>
                        <?php if ( $i < count($cal)-1 ) { ?>
                        <div class="w-100"><hr /></div>
                        <?php } ?>
                        <?php
					}
					
					for ( $i=0; $i<7; $i++ ) {
						?>
                        <div class="text-center" style="width: 14.05%; display: inline-block;">
                          <span class="label label-<?=(($i<5)?'primary':'warning')?> text-center" style="display: block;"><?=$week[$i]?></span>
                        </div>
                        <?php
					}
					?>
                    <div class="w-100"><br /><br /></div>
					<?php
					
					break;	
			}
		}

        static function formGroupField( $q ) {

            self::formDelimiter(
                [
                    'type' => 'delimiter',
                    'value' => $q['title'],
                    'dinamic' => $q['dinamic']
                ]
            ); ?>
            <?php // Helper::sp( $q ); ?>
            <?php 
            for ( $i=0; $i<$q['count']; $i++ ) {
                ?>
                <div 
                    class="box <?= ((!!$q['value'][$q['has_values']][$i])?'box-success':'collapsed-box');?>"
                    <?php if ( !!$q['dinamic'] && !$q['dinamic']['parent'] ) { ?>
                	role="dinamic-child" dinamic-name="<?= $q['dinamic']['name'];?>" dinamic-value="<?= $q['dinamic']['value'];?>" dinamic-if="<?= json_encode($q['dinamic']['show_if'])?>" 
                	style="display: <?= ((in_array($q['dinamic']['value'],$q['dinamic']['show_if']))?'block':'none');?>"
                	<?php } ?>
                    >
                    <div class="box-header with-border">
                        <h4><?= ((!!$q['value'][$q['has_values']][$i])?$q['value']['actions'][$i]['type']['ru_name']:$q['title_one'].' №'.($i+1));?></h4>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-<?= ((!!$q['value'][$q['has_values']][$i])?'minus':'plus');?>"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php 
                            foreach( $q['fields'] as $k => $f ) {

                                $f['name'] = $q['name'].'['.$i.']['.$f['name'].']';
                              
                                $f['value'] = $q['value'][$f['values']][$i][$f['names']];

                                if ( !!$f['dinamic'] ) $f['dinamic']['name'] .= $i;
                                if ( !!$f['dinamic'] && !$f['dinamic']['parent'] && $f['dinamic']['values_name'] ) $f['dinamic']['value'] = (int)$q['value'][$f['values']][$i][$f['dinamic']['values_name']];
									
                                if ( $f['type'] == 'select' ) {
                                    $f['value'] = [$q['value'][$f['values']][$i][$f['names']]];
                                }
                                if ( $f['type'] == 'checkbox' ) {
                                    $f['items'][0]['value'] = $q['value'][$f['values']][$i][$f['items'][0]['names']];
                                    $f['items'][0]['name'] = $f['name'];
                                }

								// Helper::sp( $f );
                                
                                self::formRenderField( $f );
                               
                            }
                        ?>
                    </div>
                </div>
                <?php
            }
        }
		
		static function Form( $arrQ ) {
			
			foreach( $arrQ['fields'] as $f ) {
				
				if ( $f['group'] ) {
					
					switch ( $f['type'] ) {
						
						case 'line':
						
							foreach ( $f['fields'] as $k => $q ) {
								
								?>
                                <div class="col-xs-<?=$f['cols'][$k]?> no-p">
                                  <?php
                                  self::formRenderField( $q );
                                  ?>
                                </div>
                                <?php
								
							} // foreach
							
							?>
                            <div class="clear"></div>
                            <?php
						
							break;
						
					} // switch
					
				} else {
				
					self::formRenderField( $f );
					
				} // if
				
				if ( $q['description'] ) { ?>
                <p class="help-block"><?=$q['description']?></p>
				<?php }
			} // foreach
			
			?>
            <?php if ( $arrQ['submit'] ) { ?>
            <button type="submit" class="btn btn-<?=$arrQ['submit']['class']?>"><?=$arrQ['submit']['text']?></button>
            <?php }
			
			if ( $arrQ['clear'] ) { ?>
            <a href="<?=$arrQ['clear']['link']?>" class="btn btn-<?=$arrQ['clear']['class']?>"><?=$arrQ['clear']['text']?></a>
            <?php }
			
			if ( $arrQ['export'] ) { ?>
            <a href="<?=$arrQ['export']['link']?>" class="btn btn-<?=$arrQ['export']['class']?>"><?=$arrQ['export']['text']?></a>
            <?php }
			
			if ( $arrQ['script'] ) { ?>
			<script><?=JSMin::minify( $arrQ['script'] )?></script>
            <?php }
		} // function
		
		static function FullForm( $arrQ, $id = false, $POSTRes = false ) {
			
			?>
            <form role="form" method="post" enctype="multipart/form-data">
            <input type="hidden" name="form" value="<?=$arrQ['name']?>" />
            <?php if ( $id ) { ?>
            <input type="hidden" name="id" value="<?=$id?>" />
            <?php }
			
			if ( $arrQ['description'] ) { ?>
            <div class="box box-default box-solid collapsed-box">
              <div class="box-header with-border">
                <h3 class="box-title">Описание</h3>
                <div class="box-tools pull-right">
                  <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                  </button>
                </div>
              </div><!-- /.box-header -->
              <div class="box-body" style="display: none;">
              <?=$arrQ['description']?>
              </div><!-- /.box-body -->
            </div>
            <?php }
			
			foreach( $arrQ['fields'] as $f ) {
				
				if ( $f['group'] ) {
					
					switch ( $f['type'] ) {
						
						case 'line':
						
							foreach ( $f['fields'] as $k => $q ) {
								
								?>
                                <div class="col-xs-<?=$f['cols'][$k]?> no-p">
                                  <?php
                                  self::formRenderField( $q );
                                  ?>
                                </div>
                                <?php
								
							} // foreach
							
							?>
                            <div class="clear"></div>
                            <?php
						
							break;
						
					} // switch
					
				} else {
				
					self::formRenderField( $f );
					
				} // if
				
				if ( $q['description'] ) { ?>
                <p class="help-block"><?=$q['description']?></p>
				<?php }
			} // foreach
			
			?>
            <?php if ( $arrQ['submit'] ) { ?>
            <button type="submit" class="btn btn-<?=$arrQ['submit']['class']?>"><?=$arrQ['submit']['text']?></button>
            <?php }
			
			if ( $POSTRes ) { ?>
            <a href="" class="btn btn-info">Добавить еще</a>
            <?php }
			
			if ( $arrQ['script'] ) { ?>
			<script><?=JSMin::minify( $arrQ['script'] )?></script>
            <?php }
			?>
            </form>
            <?php
        } // function
        
        static function TabsForm( $arrQ, $id = false ) {

            ?>
            <form class="nav-tabs-custom" role="form" method="post" enctype="multipart/form-data">
                <input type="hidden" name="form" value="<?=$arrQ['name']?>" />
                <?php if ( $id ) { ?>
                <input type="hidden" name="id" value="<?=$id?>" />
                <?php } ?>
                <ul class="nav nav-tabs">
                    <?php foreach ( $arrQ['tabs'] as $k => $tab ) { ?>
                        <li class="<?= (($k==0)?'active':'');?>"><a href="#<?= $arrQ['name'];?>_tab_<?= $k;?>" data-toggle="tab"><?= $tab['title'];?></a></li>
                    <?php } ?>
                    <li class="pull-left header"><?= $arrQ['title'];?></li>
                </ul>
                
                <?php if ( $arrQ['description'] ) { ?>
                <div class="box-body">
                    <?=$arrQ['description']?>
                </div>
                <?php } ?>

                <div class="tab-content">
                    <?php foreach ( $arrQ['tabs'] as $k => $tab ) { ?>
                    
                    <div class="tab-pane <?= (($k==0)?'active':'');?>" id="<?= $arrQ['name'];?>_tab_<?= $k;?>">
			
                        <?php foreach( $tab['fields'] as $f ) {
                            
                            if ( $f['group'] ) {
                                
                                switch ( $f['type'] ) {
                                    
                                    case 'line':
                                    
                                        foreach ( $f['fields'] as $k => $q ) {
                                            
                                            ?>
                                            <div class="col-xs-<?=$f['cols'][$k]?> no-p">
                                            <?php
                                            self::formRenderField( $q );
                                            ?>
                                            </div>
                                            <?php
                                            
                                        } // foreach
                                        
                                        ?>
                                        <div class="clear"></div>
                                        <?php
                                    
                                        break;
                                    
                                } // switch
                                
                            } else {
                            
                                self::formRenderField( $f );
                                
                            } // if
                            
                            if ( $q['description'] ) { ?>
                            <p class="help-block"><?=$q['description']?></p>
                            <?php }
                        } // foreach
                        
                        ?>
                    </div>
                    <?php } ?>

                    <?php if ( $arrQ['submit'] ) { ?>
                    <div class="box-body">
                        <button type="submit" class="btn btn-<?=$arrQ['submit']['class']?>"><?=$arrQ['submit']['text']?></button>
                    </div>
                    <?php }
                    if ( $arrQ['script'] ) { ?>
                    <script><?=JSMin::minify( $arrQ['script'] )?></script>
                    <?php } ?>
                </div>
            </form>
            <?php

        }
		
		static function getPageTitle( $route, $title = '' ) {
			
			switch ( $route->view ) {
				
				case '':
					$res = 'Домашняя страница';
					break;
				
				case 'settings':
					$res = 'Настройки';
					break;
					
				case 'stat':
					$res = 'Статистика';
					break;
					
				case 'export':
					$res = 'Экспорт';
					break;
					
				default:
					$res = $title;
					break;
			}
			
			return $res;
		}
		
		static function formatYears( $value ) {
			
			if ( in_array((int)$value, [1, 21, 31, 41, 51, 61, 71, 81, 91])) { $res = 'год'; }
			elseif ( in_array((int)$value, [2, 3, 4, 22, 23, 24, 32, 33, 34, 42, 43, 44, 52, 53, 54, 62, 63, 64, 72, 73, 74, 82, 83, 84, 92, 93, 94])) { $res = 'года'; }
			else { $res = 'лет'; }
			
			return $res;
		}
		
		static function statFilter( $date1, $date2, $sites = false ) {
			
			?>
              
              <div class="box box-default collapsed-box box-solid">
                <div class="box-header with-border">
                  <h3 class="box-title">Фильтр: <strong><?=$date1?></strong> - <strong><?=$date2?></strong></h3>
    
                  <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                    </button>
                  </div>
                  <!-- /.box-tools -->
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                  <form method="get">
                   
					  <?php
          
                            $formSet = [
                                'fields' => [
                                    [
                                        'group' => true,
										'cols' => [3,3],
										'type' => 'line',
										'fields' => [
											[
												'type' => 'date',
												'name' => 'date1',
												'placeholder' => 'С:',
												'value' => $date1,
												'class' => ''
											],
											[
												'type' => 'date',
												'name' => 'date2',
												'placeholder' => 'По:',
												'value' => $date2,
												'class' => ''
											]
										],
										
                                    ],
                                    
                                ],
                                'submit' => [
                                    'class' => 'primary',
                                    'text' => 'Применить'
                                ]
                            ];
                            
                            if ( $sites ) {

                                $formSet['fields'][] = [
                                    'type' => 'select',
                                    'name' => 'site_id',
                                    'placeholder' => 'Привязка к сайту',
                                    'items' => $sites
                                ];
                            }
                        ?>
                        
                        <?php self::Form( $formSet ); ?>
                  
                  </form>
                </div>
                <!-- /.box-body -->
              </div>
            <?php
        }
		
		static function statExtendFilter( $arf ) {
			
			?>
              <div class="box box-info box-solid">
                <div class="box-header with-border">
                  <h3 class="box-title"><?=(($arf['title'])?:'Фильтр')?></h3>
    
                  <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
                    </button>
                  </div>
                  <!-- /.box-tools -->
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                  <br />
                  <form method="get">
                  	
                    <div class="col-xs-3">
						  <?php
                                $formSet = [
                                    'fields' => [
										[
											'type' => 'delimiter',
											'value' => 'Период',
										],
										[
											'type' => 'date',
											'name' => 'date1',
											'placeholder' => 'С:',
											'value' => $arf['date1'],
											'class' => ''
										],
										[
											'type' => 'date',
											'name' => 'date2',
											'placeholder' => 'По:',
											'value' => $arf['date2'],
											'class' => ''
										]
                                    ]
                                ];
                            ?>
                            <?php self::Form( $formSet ); ?>
                        </div>
                        
                        <div class="col-xs-3">
                            <?php
                                $formSet = [
                                    'fields' => [
										[
											'type' => 'delimiter',
											'value' => 'Первое обращение',
										],
										[
											'type' => 'select',
											'name' => 'init_site_id',
											'placeholder' => 'Cайт',
											'items' => $arf['sites'],
											'first_empty' => true
										],
										[
											'type' => 'select',
											'name' => 'init_app_id',
											'placeholder' => 'Приложение',
											'items' => $arf['apps'],
											'first_empty' => true
										],
                                    ]
                                ];
                            ?>
                            <?php self::Form( $formSet ); ?>
                        </div>
                        
                        <div class="col-xs-3">
                            <?php
                                $formSet = [
                                    'fields' => [
										[
											'type' => 'delimiter',
											'value' => 'Последняя активность: <strong>сайты</strong>',
										],
										[
											'type' => 'select',
											'multiple' => true,
											'name' => 'last_site_ids[]',
											'placeholder' => 'Cайты',
											'items' => $arf['sites'],
											'rows' => 5
										],
                                    ]
                                ];
                            ?>
                            <?php self::Form( $formSet ); ?>
                        </div>
                        
                        <div class="col-xs-3">
                            <?php
                                $formSet = [
                                    'fields' => [
										[
											'type' => 'delimiter',
											'value' => 'Последняя активность: <strong>приложения</strong>',
										],
										[
											'type' => 'select',
											'multiple' => true,
											'name' => 'last_app_ids[]',
											'placeholder' => 'Приложения',
											'value' => [$arf['last_app_ids']],
											'items' => $arf['apps'],
											'rows' => 5
										],
                                    ]
                                ];
                            ?>
                            <?php self::Form( $formSet ); ?>
                        </div>
                        <div class="col-xs-12">
                        	<?php
                                $formSet = [
                                    'submit' => [
										  'class' => 'primary',
										  'text' => 'Применить'
									  ]
                                ];
                            ?>
                            <?php self::Form( $formSet ); ?>
                        </div>
                        
                    </form>
                </div>
                <!-- /.box-body -->
              </div>
            <?
		}
		
		
		static function statAppFilter( $arF ) {
			
			?>
              <div class="box box-success">
                <div class="box-header with-border">
                  <h3 class="box-title"><?=(($arF['title'])?:'Фильтр')?></h3>
    
                  <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                  </div>
                  <!-- /.box-tools -->
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                  <br />
                  <form method="get">
                  	
                    <?php foreach( $arF['cols'] as $arCol ) { ?>
                    
                        <div class="col-xs-<?=12/count($arF['cols'])?>">
                            
                            <?php self::Form( $arCol ); ?>
                        
                        </div>
                    
                    <?php } // foreach cols ?>
                        
                    <div class="col-xs-12">
                        <?php
                            $formSet = [
                                'submit' => [
									'class' => 'primary',
									'text' => 'Применить'
								],
								'clear' => [
									'class' => 'default',
									'link' => $arF['clear'],
									'text' => 'Сбросить'
								]
                            ];
							
							if ( $arF['export'] ) $formSet['export'] = ['class' => 'success', 'link' => $arF['export'], 'text' => 'Экспорт'];
                        ?>
                        <?php self::Form( $formSet ); ?>
                    </div>
                        
                  </form>
                </div>
                <!-- /.box-body -->
              </div>
            <?
		}
		
        
        static function renderPagination( $page, $count ) {

            $pages =  intdiv($count, 1000) + 1;
            ?>
            <div class="box-body">
			  <div class="btn-group">
				<?php for ( $i = 1; $i <= $pages; $i++ ) { ?>
				  <a href="/clients/?page=<?=$i?>" class="btn btn-<?=(((int)$page == $i)?'info':'default')?> btn-sm"><?=$i?></a>
				<? } // for ?>
			  </div>
			</div>
            <?php
        }
		
		
	}
?>