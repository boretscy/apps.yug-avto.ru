<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Cis->AppInfo()->ru_name?> <small><?=HTML::getPageTitle( $currentRoute )?></small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Cis->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    
    <?php
        $getLatestLog = function($type) {
            $baseDir = YAPPS_DOCUMENT_ROOT . '/core/YApps/Logs/Cis';
            for ($i = 0; $i < 7; $i++) {
                $datePath = date('Y/m/d', time() - $i * 86400);
                $filePath = $baseDir . '/' . $datePath . '/' . $type . '.txt';
                if (file_exists($filePath)) {
                    return [
                        'url' => '/core/YApps/Logs/Cis/' . $datePath . '/' . $type . '.txt',
                        'time' => filemtime($filePath),
                        'content' => file_get_contents($filePath)
                    ];
                }
            }
            return null;
        };

        $newLog = $getLatestLog('new');
        $usedLog = $getLatestLog('used');
        
        $prodTable = $app->Cis->getTable()->prod;

        // Блок новых автомобилей
        $newStatus = new stdClass();
        if ($newLog) {
            $updatedTime = date('d.m.Y H:i:s', $newLog['time']);
            $newStatus->status = (time() - $newLog['time'] <= 3 * 3600) ? 'success' : 'error';
            $newStatus->description = '<strong>Новые автомобили</strong><br />' .
                'Обновлено: ' . $updatedTime . '<br />' .
                'Таблица: ' . $prodTable . '<br />' .
                'Количество: ' . $app->Cis->apiDBGetCount('new') . '<hr />' .
                '<a download href="' . $newLog['url'] . '">Скачать лог (new.txt)</a>&nbsp;&nbsp;&nbsp;' .
                '<a href="#" role="viewLog" data-target="#log_new">Показать лог</a>' .
                '<br /><br /><pre id="log_new" style="display: none; max-height: 400px; overflow-y: auto;">' . htmlspecialchars($newLog['content']) . '</pre>';
        } else {
            $newStatus->status = 'error';
            $newStatus->description = '<strong>Новые автомобили</strong><br />Лог синхронизации не найден за последние 7 дней.';
        }
        HTML::Error($newStatus);

        // Блок автомобилей с пробегом
        $usedStatus = new stdClass();
        if ($usedLog) {
            $updatedTime = date('d.m.Y H:i:s', $usedLog['time']);
            $usedStatus->status = (time() - $usedLog['time'] <= 3 * 3600) ? 'success' : 'error';
            $usedStatus->description = '<strong>Автомобили с пробегом</strong><br />' .
                'Обновлено: ' . $updatedTime . '<br />' .
                'Таблица: ' . $prodTable . '<br />' .
                'Количество: ' . $app->Cis->apiDBGetCount('used') . '<hr />' .
                '<a download href="' . $usedLog['url'] . '">Скачать лог (used.txt)</a>&nbsp;&nbsp;&nbsp;' .
                '<a href="#" role="viewLog" data-target="#log_used">Показать лог</a>' .
                '<br /><br /><pre id="log_used" style="display: none; max-height: 400px; overflow-y: auto;">' . htmlspecialchars($usedLog['content']) . '</pre>';
        } else {
            $usedStatus->status = 'error';
            $usedStatus->description = '<strong>Автомобили с пробегом</strong><br />Лог синхронизации не найден за последние 7 дней.';
        }
        HTML::Error($usedStatus);
    ?>
    <script>
      $(document).on('click', '[role="viewLog"]', function() {
          var target = $(this).attr('data-target');
          $(target).toggle();
          return false;
      });
    </script>
    
    <?php if ($POSTRes) HTML::Error($POSTRes); ?>
    
    <div class="row">
      <div class="col-md-12">
        
          <?php include __DIR__.'/layouts/lists/_vehicles.php';?>
          
      </div>
    </div>
  
  </section>
  <!-- /.content -->
  
</div>