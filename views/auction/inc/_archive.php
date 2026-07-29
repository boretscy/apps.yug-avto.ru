<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/locale/ru.js"></script>
<script>
  $('#datepicker_datetime_start').datetimepicker({
	  locale: 'ru'
  });
  $('#datepicker_datetime_end').datetimepicker({
	  locale: 'ru'
  });
  $("#data-table-items").DataTable({
		"order": [[ 4, "asc" ]],
        "pageLength": 50
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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/css/select2.min.css" integrity="sha256-FdatTf20PQr/rWg+cAKfl6j4/IY3oohFAJ7gVC3M34E=" crossorigin="anonymous" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/select2.min.js" integrity="sha256-d/edyIFneUo3SvmaFnf96hRcVBcyaOy96iMkPez1kaU=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.10/js/i18n/ru.js" integrity="sha256-UGy3UiUvOdCkVQIln0LDJIwm4dbxPuQuEVu80RSglXY=" crossorigin="anonymous"></script>
<style>
.select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #3c8dbc;
    border-color: #367fa9;
    padding: 1px 10px;
    color: #fff;
}
</style>
<script>
  $('select[select2]').select2();
</script>

<script src="/plugins/ckeditor/ckeditor.js"></script>
<script>
  CKEDITOR.replace('ckeditor');
</script>