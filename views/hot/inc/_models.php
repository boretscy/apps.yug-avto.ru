<script>
	$("#data-table-models").DataTable({
		"order": [[ 1, "asc" ]],
        "pageLength": 50
	});
</script>
<script>
	$(document).on('change', 'select[multisteps="Y"][step="1"]', function() {
		
		var k, i = Number($(this).val());
		var html = '<option disabled="" selected="">Выбрать..</option>';
		
		for ( k in Brands[i].models) html += '<option value="'+Brands[i].models[k].id+'">'+Brands[i].models[k].ru_name+'</option>';
		
		$('select[multisteps="Y"][step="2"][name="'+$(this).attr('target')+'"]').html( html );
	});
</script>