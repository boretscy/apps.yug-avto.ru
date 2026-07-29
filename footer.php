<?php if ($app->User->checkAUth()) { ?>

  <footer class="main-footer">
    <div class="pull-right hidden-xs">
      <b>Версия</b> 1.8
    </div>
    <strong>&copy; <?=date('Y', time())?> <a href="https://apps.yug-avto.ru">YugAvto Apps</a>.</strong> Все права защищены.
  </footer>

<?php include $_SERVER['DOCUMENT_ROOT'].'/assets/img/svg.php'; ?>

<script src="/plugins/slimScroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="/plugins/fastclick/fastclick.js"></script>
<!-- iCheck 1.0.1 -->
<script src="/plugins/iCheck/icheck.min.js"></script>
<!-- bootstrap datepicker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.1/locales/bootstrap-datepicker.ru.min.js"></script>
<!-- bootstrap time picker -->
<script src="/plugins/timepicker/bootstrap-timepicker.min.js"></script>

<!-- DataTables -->
<script src="/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/plugins/datatables/dataTables.bootstrap.min.js"></script>

<!-- AdminLTE App -->
<script src="/assets/js/app.min.js"></script>

<!-- InputMask -->
<script src="/plugins/input-mask/jquery.inputmask.js"></script>
<script src="/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="/plugins/input-mask/jquery.inputmask.extensions.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/remodal/1.1.1/remodal.min.js"></script>

<div class="remodal" data-remodal-id="viewPiwik">
  <button data-remodal-action="close" class="remodal-close"></button>
  <iframe src="" frameborder="0" marginheight="0" marginwidth="0" width="100%" height="100%" id="viewPiwik"></iframe>
</div>

<script src="/assets/js/yapps.js?<?=md5_file(__DIR__.'/assets/js/yapps.js')?>"></script>
<?php if (file_exists( __DIR__.'/views/'.$currentRoute->section.'/inc_'.$currentRoute->view.'.php' )) include __DIR__.'/views/'.$currentRoute->section.'/inc_'.$currentRoute->view.'.php'; ?>
<?php if ( file_exists( __DIR__.'/views/'.$currentRoute->section.'/inc/_'.$currentRoute->view.'.php' ) ) include __DIR__.'/views/'.$currentRoute->section.'/inc/_'.$currentRoute->view.'.php';?>


<?php } else { ?>

<script src="/plugins/iCheck/icheck.min.js"></script>
<script>
  //iCheck for checkbox and radio inputs
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-red',
      radioClass: 'iradio_square-red',
      increaseArea: '20%' // optional
    });
  });
</script>

<?php } ?>

</body>
</html>