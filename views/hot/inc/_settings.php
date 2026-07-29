<link rel="stylesheet" href="/plugins/colorpicker/bootstrap-colorpicker.min.css">
<script src="/plugins/colorpicker/bootstrap-colorpicker.min.js"></script>
<script>
	$('.my-colorpicker_color_dark').colorpicker();
	$('.my-colorpicker_color_gray').colorpicker();
	$('.my-colorpicker_color_lightgray').colorpicker();
	$('.my-colorpicker_color_light').colorpicker();
	$('.my-colorpicker_color_error').colorpicker();
	
	$("#data-table-sets").DataTable({
		"order": [[ 0, "asc" ]],
        "pageLength": 30
	});
</script>