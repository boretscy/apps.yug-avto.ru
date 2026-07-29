<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <!-- Content Header (Page header) -->
    <section class="content-header">
            <h1><?=$app->Analytics->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
    </section>
    
    <!-- Main content -->
    <section class="content">
        
        <?php if ( $app->Analytics->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
        
        <?php if ($POSTRes) HTML::Error($POSTRes); ?>
        
        <div class="row">
            <div class="col-md-12">

                <?php

                    Helper::sp($app->Analytics->getState());
                    $state = $app->Analytics->getState();
                    $state['status'] = ( $state['state']['attention'] ) ? 'error' : 'success';
                    $state['description'] = 'Обновлено';
                    HTML::Error((object)$state);


                    
                    // $table->status = ( time() - strtotime($table->updated) <= 3600+600 ) ? 'success' : 'error';
                    // $table->description = 'Обновлено '.$table->updated.'<br />Таблица: '.$table->prod;
                    // HTML::Error($table);
                ?>

                    <?php
                    Helper::sp( 'Дата последней записи в yapps_app_analytics_autodealer: '.$app->MySQL->getRow('SELECT * FROM yapps_app_analytics_autodealer ORDER BY timestamp DESC LIMIT 1')['timestamp'] );
                    Helper::sp( 'Дата последней записи в yapps_app_analytics_calltouch: '.$app->MySQL->getRow('SELECT * FROM yapps_app_analytics_calltouch ORDER BY timestamp DESC LIMIT 1')['timestamp'] );
                    Helper::sp('Следующая дата: '.date('d.m.Y', json_decode(file_get_contents(__DIR__.'/../../_cron/Analytics/data/date.json'), true)['timestamp']));
                    ?>
                    <hr />
                    <?php

                    $tt = '31.12.2023';
                    Helper::sp($tt.': '.strtotime($tt));

                    // Helper::sp($events = $app->Analytics->getAStages(3846203));
                    // Helper::sp(
                    //     $app->Analytics->getAStages(
                    //         [
                    //             'caseTypes' => 4,
                    //             'types' => 12,
                    //             'states' => 3,
                    //             'createdSince' => '20.12.2024',
                    //             'createdTill' => '20.12.2024',
                    //         ]
                    //     )
                    // );

                    // Helper::sp($state);





                    // $a = $app->Analytics->getARetailCases(['updatedSince' => '20.11.2024','updatedTill' => '20.11.2024']);
                    // Helper::sp( count($a) );
                    // foreach ( $a as $r ) {
                    //     if ( (int)$r['ext_id'] == 3837801 ) {
                    //         Helper::sp($r);
                    //         exit;
                    //     }
                    // }

                    
                ?>
                <script>
                    let rl = <?= json_encode($a).PHP_EOL.';';?>
                </script>

            </div>
        </div>
    
    </section>
    <!-- /.content -->
  
</div>