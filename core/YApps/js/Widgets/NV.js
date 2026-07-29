YApps.Widgets.NV = {};
YApps.Widgets.NV.MapIconColor = '%%WIDGET.COLOR_FILL%%';
YApps.Widgets.NV.ToNav = %%WIDGET.SN.TO_NAV%%;
YApps.Widgets.NV.Phone = '%%WIDGET.NV.PHONE%%';

YApps.Widgets.NV.Goal = {};
YApps.Widgets.NV.Goal.Yandex = {};
YApps.Widgets.NV.Goal.Action = {};
YApps.Widgets.NV.Goal.Name = {};
YApps.Widgets.NV.Goal.Category = 'Виджеты';
YApps.Widgets.NV.Goal.Yandex.Route = 'YApps_Goals-Widgets_NV-Route';
YApps.Widgets.NV.Goal.Yandex.Call = 'YApps_Goals-Widgets_NV-Call';
YApps.Widgets.NV.Goal.Action.Route = 'Маршрут в ДЦ';
YApps.Widgets.NV.Goal.Action.Call = 'Звонок в ДЦ';
YApps.Widgets.NV.Goal.Name.Route = 'Построение маршрута в ';
YApps.Widgets.NV.Goal.Name.Call = 'Звонок в ';

YApps.Widgets.NV.Reset = function() {
	
	var i;
	YApps.Widgets.NV.Items = [];
	$('ul.YApps_Widget--Form_Title-DCs li').each( function(i,e) {
	   
	   i = {};
	   i.coords = [ $(e).data('lat'), $(e).data('lon') ];
	   i.name = $(e).find('span.YApps_Widget--Form_Title-DCs_Name').text();
	   i.address = $(e).find('span.YApps_Widget--Form_Title-DCs_Address').text();
	   
	   YApps.Widgets.NV.Items.push( i );
	});
	
	$('ul.YApps_Widget--Form_Title-DCs li').removeClass('YApps_Widget--Form_Title-DCs_Active');
	$('ul.YApps_Widget--Form_Title-DCs li').eq(0).removeClass('YApps_Widget--Form_Title-DCs_Active').addClass('YApps_Widget--Form_Title-DCs_Active');
	
	if ( typeof YApps.Widgets.NV.Map != 'undefined') {
		
		YApps.Widgets.NV.RouteTo( YApps.Widgets.NV.Items[0].coords );
		
	} else {
		
		YApps.Widgets.NV.MapInit();
	}
};
YApps.Widgets.NV.MapInit = function() {
	
    YApps.Widgets.NV.Map = new ymaps.Map('YApps_Widget--Navigator_Map', {
        center: YApps.Widgets.NV.Items[0].coords,
        zoom: 16,
        controls: ['largeMapDefaultSet']
    }, {
        buttonMaxWidth: 300
    });
	
	YApps.Widgets.NV.Items.forEach( function(item,i,arr) {
		
		YApps.Widgets.NV.Map.geoObjects
			.add(new ymaps.Placemark(item.coords, {
				balloonContent: item.address,
				iconCaption: item.name
			}, {
				preset: 'islands#dotIcon',
				iconColor: YApps.Widgets.NV.MapIconColor
			})
		);
	});
	
	YApps.Widgets.NV.RouteTo( YApps.Widgets.NV.Items[0].coords );
}
YApps.Widgets.NV.RouteTo = function( coords ) {
	
	YApps.Widgets.NV.Map.geoObjects.remove( YApps.Widgets.NV.Route );
	
	var userPosition;
	ymaps.geolocation.get({provider: 'browser', autoReverseGeocode: true})
		.then(function (result) {
        	userPosition = result.geoObjects.get(0).geometry.getCoordinates();

			return userPosition;
		})
		.then(function(userPosition) {
			YApps.Widgets.NV.Route = new ymaps.multiRouter.MultiRoute(
				{
					referencePoints: [userPosition, coords],
					params: {results: 2}
				},
				{
					boundsAutoApply: true
				}
			);

			YApps.Widgets.NV.Map.geoObjects.add( YApps.Widgets.NV.Route );
		});
}
YApps.Widgets.NV.OpenNav = function( coords ) {
	
	
}
YApps.Widgets.NV.Init = function() { 
	
	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.src = 'https://api-maps.yandex.ru/2.1/?apikey=34ddb940-0941-4b80-ab80-b0aa351b6560&lang=ru_RU';
	script.onload = function( /* YApps.Widgets.NV.Reset() */ ) {};
	document.getElementsByTagName('head')[0].appendChild(script);
	
	if ( YApps.MobileDetect.mobile() || window.innerHeight <= 768 ) $('.YApps_Widget--Navigator_Content #YApps_Widget--Navigator_Map').css('height', 'calc(100% - '+(150+85*$('.YApps_Widget--Form_Title-DCs li').length)+'px)');
}

$(document).on('click', 'li[role="YApps_Widget--Navigator_PanTo"]', function() {
	
	$('ul.YApps_Widget--Form_Title-DCs li').removeClass('YApps_Widget--Form_Title-DCs_Active');
	$(this).addClass('YApps_Widget--Form_Title-DCs_Active');
	YApps.Widgets.NV.RouteTo([$(this).data('lat'), $(this).data('lon')]);
});
$(document).on('click', '.YApps_Widget--Form_Title-DCs_ToNav', function() {
	
	YApps.Widgets.SN.Set( $(this).parent().data('name'), $(this).parent().data('lat'), $(this).parent().data('lon') );
	if ( YApps.Widgets.NV.ToNav ) {
		
		YApps.Widgets.ShowStatus = true;
		YApps.Helper.StartWidget( 'YApps_Widget--SelectNavi', 'SN' );
	}
});
$(document).on('click', 'a[role="YApps_Widget--SN_ToNav"]', function() {
	
	YApps.AppPushGoal({
		Category: YApps.Widgets.NV.Goal.Category,
		Action: YApps.Widgets.NV.Goal.Action.Route,
		Name: $('.YApps_Widget--SelectNavi_Container .YApps_Widget--Form_Title').text(),
		Yandex: YApps.Widgets.NV.Goal.Yandex.Route,
		CallTouch: {
			Flag: false
		}
	});
});
$(document).on('click', '.YApps_Widget--Form_Title-DCs_Phone', function() {
	
	YApps.AppPushGoal({
		Category: YApps.Widgets.NV.Goal.Category,
		Action: YApps.Widgets.NV.Goal.Action.Call,
		Name: YApps.Widgets.NV.Goal.Name.Call+$(this).data('name'),
		Yandex: YApps.Widgets.NV.Goal.Yandex.Call,
		CallTouch: {
			Flag: false
		}
	});
});