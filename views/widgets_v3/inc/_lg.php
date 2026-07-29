<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/locale/ru.js"></script>
<script>
  $('#datepicker_lg_time_start').datepicker({
	  locale: 'ru'
  });
  $('#datepicker_lg_timer').datetimepicker({
	  locale: 'ru'
  });
</script>
<script>
	$("#data-table-lg").DataTable({
		"order": [[ 0, "asc" ]],
        "pageLength": 30
	});
</script>
<link rel="stylesheet" href="/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
<script src="/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-wysiwyg/0.3.3/locales/bootstrap-wysihtml5.ru-RU.min.js" integrity="sha256-YiHbB4mSu9GG8yITMUJIH+kb5lYsC9KLhiGpG7hrl2c=" crossorigin="anonymous"></script>
<script>
	$('.lg_text').wysihtml5({locale: 'ru-RU'});
</script>