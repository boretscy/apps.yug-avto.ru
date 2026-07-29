YApps.Widgets = {};
YApps.Widgets.ResultTimeout = %%WIDGET.RESULT_TIMEOUT%%;
YApps.Widgets.InitTimeout = %%WIDGET.INIT_TIMEOUT%%; // 15
YApps.Widgets.EffectTime = 500; // 500
YApps.Widgets.FormCoverTime = 200;
YApps.Widgets.InitTimeout = 1500;
YApps.Widgets.HTML = '%%WIDGETS.HTML%%';
YApps.Widgets.SVG = '%%WIDGETS.SVG%%';
YApps.Widgets.ShowStatus = true;
YApps.Widgets.ShowTimeout = false;
YApps.Widgets.FormCover = function( appkey, show = true) {
	
	( show ) ? $('[data-appkey="'+appkey+'"] .YApps_Widget--Form_Fields-Cover').fadeIn(YApps.Widgets.FormCoverTime) : $('[data-appkey="'+appkey+'"] .YApps_Widget--Form_Fields-Cover').fadeOut(YApps.Widgets.FormCoverTime);
}
YApps.Widgets.FormResult = function( appkey, func = {show: true}, result ) {
	
	result = result || {status: 'success'};
	
	if ( func.show ) {
		
		$('[data-appkey="'+appkey+'"] .YApps_Widget--Form_Fields').slideUp(YApps.Widgets.EffectTime);
		$('[data-appkey="'+appkey+'"] .YApps_Widget--Form_'+result.status).slideDown(YApps.Widgets.EffectTime);
	}
}
YApps.Widgets.FormReset = function( appkey ) {
	
	$('[data-appkey="'+appkey+'"] .YApps_Widget--Form_Fields').slideDown(YApps.Widgets.EffectTime);
	$('[data-appkey="'+appkey+'"] .YApps_Widget--Form_success').hide();
	$('[data-appkey="'+appkey+'"] .YApps_Widget--Form_error').hide();
}
YApps.Widgets.ClearTimeout = function( q ) {
	
	if ( Array.isArray(q) ) {
		
		q.forEach( function(item, i, arr) {
			
			clearTimeout( item );
		});
		
	} else {
		
		clearTimeout( q );
	}
}
YApps.Widgets.ClearInterval = function( q ) {
	
	if ( Array.isArray(q) ) {
		
		q.forEach( function(item, i, arr) {
			
			clearInterval( item );
		});
		
	} else {
		
		clearInterval( q );
	}
}

YApps.Widgets.Close = function( e ) {

	$('div.YApps--Cover').fadeOut(YApps.Widgets.EffectTime);
	$(e).parent().fadeOut(YApps.Widgets.EffectTime);
	
	YApps.Widgets.ShowStatus = true;
    if ( !!YApps.Widgets.CH ) if ( YApps.Widgets.CH.ShowTimeout ) YApps.Widgets.CH.Show( YApps.Widgets.CH.ShowTimeout );
    if ( !!YApps.Widgets.CI ) if ( YApps.Widgets.CI.ShowTimeout ) YApps.Widgets.CI.Show( YApps.Widgets.CI.ShowTimeout );
	
    if ( location.hash == '#'+YApps.Widgets.Open ) location.hash = '';

    $('div.YApps_Helper--Item_Container').removeClass('YApps_Helper--Item_Active');
    delete YApps.Widgets.Open;
}

YApps.Widgets.Init = function() {
		
	$('div#YApps_SVG').append(YApps.Widgets.SVG);
	$('body').append(YApps.Widgets.HTML);
}

$(document).on('blur', 'input[name="YApps_Widget--Form_Name-Phone"]', function() {
	if ( YApps.FormatPhoneIn($(this).val()).length == 11 ) $(this).removeClass('YApps_Widget--Form_Error');
});
$(document).on('click', 'div.YApps_Widget--Close', function() { YApps.Widgets.Close($(this)) });

YApps.LoadScripts( {Inputmask: false, Flatplickr: false, MobileDetect: true} );
YApps.Widgets.Init();

setTimeout( function() { 
	
	YApps.Helper.Init();
	for (var i in YApps.Widgets) { if ( typeof YApps.Widgets[i].Init == 'function' ) YApps.Widgets[i].Init(); }

}, YApps.Widgets.InitTimeout );