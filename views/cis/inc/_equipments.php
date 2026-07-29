<script>
    let models, brand, type, html;
    $(document).on('change', 'select[name="brand_id"]', function() {
        
        $('select[name="model_id"]').html('');
        type = ( $('select[name="type_id"]').val() ==1) ? 'new' : 'used';
        brand = $('select[name="brand_id"] option[value="'+$('select[name="brand_id"]').val()+'"]').data('code');
        html = '<option disabled selected>Выбрать..</option>';

        $.get( 'https://apps.yug-avto.ru/API/get/cis/models/'+type+'/?brand='+brand+'&token=ef6541490c8bb9d481d37020b6a1953e', function( r ) {
            JSON.parse(r).forEach(e => {
                html += '<option value="'+e.id+'">'+e.name+'</option>';
            });
             $('select[name="model_id"]').html(html);
        });
    });

	$("#data-table-cis-equipments").DataTable({
		"order": [[ 0, "asc" ]],
	    "pageLength": 10
	});
</script>