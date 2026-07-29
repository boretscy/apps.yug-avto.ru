<script>
	$("#data-table").DataTable({
		"order": [[ 1, "asc" ]],
        "pageLength": 30
	});
</script>
<script>
    $(document).on('click', '[role="copy"]', function() {

        $('[role="copy"]').css('background', '#eee');
        $(this).css('background', '#00a65a');

        $(this).siblings('input').select();
        document.execCommand("copy");
    });
</script>