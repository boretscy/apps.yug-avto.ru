<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/locale/ru.js"></script>
<!-- CK Editor -->
<script src="/plugins/ckeditor/ckeditor.js"></script>
<script>
  
  $("#data-table-news").DataTable({
	  "order": [[ 0, "desc" ]],
        "pageLength": 50
  });
  CKEDITOR.replace('ckeditor');
	
  $('#datepicker_date').datetimepicker({
	  locale: 'ru'
  });
	 
</script>