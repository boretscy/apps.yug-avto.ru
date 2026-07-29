<?php 
  $arRes = $app->Expertbot->getUnknowns( $_GET );
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
                ],
                'clear' => '/expertbot/unknowns/'
            ];
            
          ?>
          
          <?php HTML::statAppFilter( $arFilter ); ?>

      <div class="box box-primary">

        <div class="box-body">
          
          <?php if ($POSTRes) HTML::Error($POSTRes); ?>

          <table class="table table-striped table-bordered table-sm" id="data-table-expertbot-unknowns">
            <thead>
              <tr>
                <th style="width: 10%">Дата</th>
                <th style="width: 20%">ФИО</th>
                <th style="width: 70%">Текст</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ( $arRes as $item ) { ?>
              <tr>
                <td><?= date('Y-m-d H:i:s', $item['timestamp']);?></td>
                <td><?= $item['user'];?></td>
                <td><?= $item['text'];?></td>
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
  