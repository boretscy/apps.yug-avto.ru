<?php 
  $arRes = $app->Expertbot->getItems( $_GET );
  $date_from = ($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', time()-3*24*3600);
  $date_to = ($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d', time());
  ?>

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Expertbot->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Expertbot->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
    <?php if ($POSTRes) HTML::Error($POSTRes); ?>
    
    <div class="row">
      <div class="col-md-12">

        <?php
          
            $arFilter = [
                
                'title' => 'Фильтр: c <strong>'.$date_from.'</strong> по <strong>'.$date_to.'</strong>',
                'cols' => [
                    
                    [
                        'name' => 'Период',
                        'fields' => [
                            [
                                'type' => 'date',
                                'name' => 'date_from',
                                'placeholder' => 'С:',
                                'value' => $date_from,
                            ],
                            [
                                'type' => 'date',
                                'name' => 'date_to',
                                'placeholder' => 'По:',
                                'value' => $date_to,
                            ]
                        ]
                    ],
                    [
                        'name' => 'Другое',
                        'fields' => [
                            [
                                'type' => 'select',
                                'multiple' => false,
                                'name' => 'dealership',
                                'placeholder' => 'Дилерский центр',
                                'items' => $app->Expertbot->getDealerships(),
                                'value' => [$_GET['dealership']],
                                'select_field' => 'name',
                                'first_empty' => true,
                            ],
                            [
                                'type' => 'select',
                                'multiple' => false,
                                'name' => 'type',
                                'placeholder' => 'Направление',
                                'items' => $app->Expertbot->getTypes(),
                                'value' => [$_GET['type']],
                                'select_field' => 'name',
                                'first_empty' => true,
                            ],
                            [
                                'type' => 'select',
                                'multiple' => false,
                                'name' => 'departament',
                                'placeholder' => 'Подразделение',
                                'items' => $app->Expertbot->getDepartaments(),
                                'value' => [$_GET['departament']],
                                'select_field' => 'name',
                                'first_empty' => true,
                            ]
                        ]
                    ]
                ],
                'clear' => '/expertbot/items/'
            ];
            
          ?>
          
          <?php HTML::statAppFilter( $arFilter ); ?>

      <div class="box box-primary">

        <div class="box-body">
          
          <?php if ($POSTRes) HTML::Error($POSTRes); ?>

          <table class="table table-striped table-bordered table-sm" id="data-table-expertbot-feedbacks">
            <thead>
              <tr>
                <th style="width: 5%">Дата</th>
                <th style="width: 15%">ФИО</th>
                <th style="width: 10%">Дилерский центр</th>
                <th style="width: 5%">Направление</th>
                <th style="width: 5%">Подразделение</th>
                <th style="width: 5%">Источник</th>
                <th style="width: 5%">Дата отзыва</th>
                <th style="width: 5%">Скриншот</th>
                <th style="width: 5%">Статус</th>
                <th style="width: 15%">Маркетолог</th>
                <th style="width: 15%">Комментарий</th>
                <th style="width: 5%">Дата ответа</th>
                <th style="width: 5%"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes as $item ) { ?>
              <tr>
                <td><a href="/expertbot/items/edit/<?=$item['id']?>/"><?= $item['date'];?></a></td>
                <td><a href="/expertbot/items/edit/<?=$item['id']?>/"><?= $item['user'];?></a></td>
                <td><a href="/expertbot/items/edit/<?=$item['id']?>/"><?= $item['dealership'];?></a></td>
                <td><?= $item['type'];?></td>
                <td><?= $item['departament'];?></td>
                <td><?= $item['source'];?></td>
                <td><?= $item['date_feedback'];?></td>
                <td>
                    <?php if ($item['screenshot']) { ?>
                    <a href="<?= $item['screenshot'];?>" target="_balnk">Скриншот</a></td>
                    <?php } ?>
                <td><?= $item['status'];?></td>
                <td><?= $item['checker_name'];?></td>
                <td><?= $item['checker_comment'];?></td>
                <td><?= (($item['date_response']!='0000-00-00')?$item['date_response']:'');?></td>
                <td class="text-right">
                  <a href="/expertbot/items/edit/<?=$item['id']?>/">
                    <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
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
  