YApps.Widgets.CH = {};
YApps.Widgets.CH.Timeout = %%WIDGET.CH.TIMEOUT%%; //30
YApps.Widgets.CH.ShowTimeout = false;
YApps.Widgets.CH.TimeoutID = false;
YApps.Widgets.CH.Reset = function() {};

YApps.Widgets.CH.Show = function( timeout ) {
	
	YApps.Widgets.CH.TimeoutID = setTimeout( function() {
	
		if ( !YApps.Cookie.Get('YAppsWidgetsCH_Show') || YApps.Cookie.Get('YAppsWidgetsCH_Show') == 'false' ) {
			
			if ( YApps.Widgets.ShowStatus ) YApps.Cookie.Set('YAppsWidgetsCH_Show', 'true', {path: '/', domain: '.'+location.host});
			if ( !YApps.Widgets.ShowStatus ) YApps.Widgets.CH.ShowTimeout = 5000;
			
			YApps.Helper.StartWidget('YApps_Widget--Chat', 'CH');
		}
		
	}, timeout);
}

YApps.Widgets.CH.Show( YApps.Widgets.CH.Timeout*1000 );