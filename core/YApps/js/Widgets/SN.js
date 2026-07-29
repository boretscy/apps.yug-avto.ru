YApps.Widgets.SN = {};
YApps.Widgets.SN.Reset = function() {};

YApps.Widgets.SN.Set = function( dc, lat, lon ) {
	
	$('.YApps_Widget--SelectNavi_Container .YApps_Widget--Form_Title').text( 'Маршрут в '+dc );
	$('ul.YApps_Widget--SelectNavi li').each( function(i, e) {
		
		YApps.Widgets.SN.A = $(e).children('a');
		YApps.Widgets.SN.Link = $(YApps.Widgets.SN.A).data('url').replace('%%WIDGET.NV.LAT%%', lat).replace('%%WIDGET.NV.LON%%', lon);
		
		$(YApps.Widgets.SN.A).attr('href', YApps.Widgets.SN.Link);
	});
}