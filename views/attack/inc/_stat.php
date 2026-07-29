<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/locale/ru.js"></script>
<script>
  $("#data-table-stats").DataTable({
	  "order": [[ 0, "asc" ]]
  });
  $('#datepicker_date1').datetimepicker({
	  locale: 'ru',
		format: 'L'
  });
  $('#datepicker_date2').datetimepicker({
	  locale: 'ru',
		format: 'L'
  });
</script>