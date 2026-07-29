$(function () {
  $("[data-mask]").inputmask();
});
//iCheck for checkbox and radio inputs
$('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
  checkboxClass: 'icheckbox_minimal-blue',
  radioClass: 'iradio_minimal-blue'
});
//Red color scheme for iCheck
$('input[type="checkbox"].minimal-red, input[type="radio"].minimal-red').iCheck({
  checkboxClass: 'icheckbox_minimal-red',
  radioClass: 'iradio_minimal-red'
});
//Flat red color scheme for iCheck
$('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
  checkboxClass: 'icheckbox_flat-green',
  radioClass: 'iradio_flat-green'
});
//Date picker
$('[role="timepicker"]').timepicker({
	showMeridian: false,
	defaultTime: '09:00'
});
$('#datepicker').datepicker({
  autoclose: true,
  format: 'yyyy-mm-dd',
  language: 'ru',
  orientation: 'bottom'
});

$('a[role="viewPiwik"]').click( function() {
		  
	var iframe = '';
	iframe += 'https://analytics.yug-avto.ru/index.php?date=';
	iframe += $(this).attr('data-date');
	iframe += '&module=Widgetize&action=iframe&visitorId=';
	iframe += $(this).attr('data-id')+'&idSite=1';
	iframe += '&period=month&moduleToWidgetize=Live&actionToWidgetize=getVisitorProfilePopup&token_auth=9b4e015178573140e83d2fe7eb174195';
	$('iframe#viewPiwik')[0].src= iframe;
	$('[data-remodal-id="viewPiwik"]').remodal().open();
	
	console.log( iframe );
	
	return false;
});

$('a[role="delete"]').click( function() { if(!confirm('Уверены?')) return false; });

$('a[role="add_input"]').click( function() {
	
	$(this).parent().parent().parent().find('input').last().clone().appendTo( $(this).parent().parent().parent() );
	return false;
});

$(document).on('change', 'select[role="showto"]', function() {
	
	var showname = $(this).find('option:selected').data('showname');
	$('[hidable]').hide();
	$('[hidable][data-showname="'+showname+'"]').show();
});





$('a[role="add_datetimerange"]').click( function() {
	
	var i = Number( $(this).data('index') )+1;
	$('[role="datetimerange"][data-index="'+i+'"]').show().find('input').attr('disabled', false);
	return false;
});

$('a[role="remove_datetimerange"]').click( function() {
	
	var i = Number( $(this).data('index') );
	$('[role="datetimerange"][data-index="'+i+'"]').hide().find('input').attr('disabled', true);
	return false;
});


$('a[role="add_linegrouptext"]').click( function() {
	
	var i = Number( $(this).data('index') )+1;
	var target = $(this).data('target');
	$('[role="linegrouptext"][data-target="'+target+'"][data-index="'+i+'"]').show().find('input').attr('disabled', false);
	return false;
});

$('a[role="remove_linegrouptext"]').click( function() {
	
	var target = $(this).data('target')
	var i = Number( $(this).data('index') );
	$('[role="linegrouptext"][data-target="'+target+'"][data-index="'+i+'"]').hide().find('input').attr('disabled', true);
	return false;
});



$(document).on('change', '[role="dinamic-parent"]', function() {

	let DName = $(this).attr('dinamic-name');
	let DVal = $(this).val();

	console.log( DName, DVal );
	console.log( $('[role="dinamic-child"][dinamic-name="'+DName+'"]') );

	$('[role="dinamic-child"][dinamic-name="'+DName+'"]').hide();
	$('[role="dinamic-child"][dinamic-name="'+DName+'"]').each(function(i, e) {
		console.log(JSON.parse($(e).attr('dinamic-if')), JSON.parse($(e).attr('dinamic-if')).indexOf(Number(DVal)) );
		if ( JSON.parse($(e).attr('dinamic-if')).indexOf(Number(DVal)) != -1 ) $(e).show();
	});
	// $('[role="dinamic-child"][dinamic-name="'+DName+'"][dinamic-value="'+DVal+'"]').show();
});


//kdvjakvjbqavjbadkvjba