YApps.Parts = {};

YApps.Parts.StartRender = () => {
	
	var htmlPartsRender = '{{PARTS.STARTHTML}}';
	
	$('div#YApps_Parts').after( htmlPartsRender );
}

YApps.Parts.SearchSuccessRender = function( JData ) {
	
	//console.log( JData );
	
	$('div.YApps_Parts--price-container').html('').hide();
	
	if ( YApps.MobileDetect.mobile() ) {
		
		var htmlSearchRender = '<div class="YApps_Parts--price-container_Item">';
		htmlSearchRender += '<div class="YApps_Parts--col YApps_Parts--col60 Yapps_Parts--Search-title">Наименование</div>';
		htmlSearchRender += '<div class="YApps_Parts--col YApps_Parts--col40 Yapps_Parts--Search-title">Цена<sup>*</sup></div>';
		htmlSearchRender += '</div>';
		
		JData.Items.forEach( function(item, i, arr) {
			htmlSearchRender += '<div class="YApps_Parts--price-container_Item">';
			htmlSearchRender += '<div class="YApps_Parts--col YApps_Parts--col60 Yapps_Parts--Search-item">'+item.ru_name+'</div>';
			htmlSearchRender += '<div class="YApps_Parts--col YApps_Parts--col40 Yapps_Parts--Search-item">';
			htmlSearchRender += YApps.Formatter(Number(item.price))+' ₽ ';
			htmlSearchRender += '</div>';
			htmlSearchRender += '</div>';
		});
		
	} else {
	
		var htmlSearchRender = '<div class="YApps_Parts--price-container_Item">';
		htmlSearchRender += '<div class="YApps_Parts--col YApps_Parts--col20 Yapps_Parts--Search-title">Артикул</div>';
		htmlSearchRender += '<div class="YApps_Parts--col YApps_Parts--col60 Yapps_Parts--Search-title">Наименование</div>';
		htmlSearchRender += '<div class="YApps_Parts--col YApps_Parts--col20 Yapps_Parts--Search-title">Цена<sup>*</sup> <small>/ На складе</small></div>';
		htmlSearchRender += '</div>';
		
		JData.Items.forEach( function(item, i, arr) {
			htmlSearchRender += '<div class="YApps_Parts--price-container_Item">';
			htmlSearchRender += '<div class="YApps_Parts--col YApps_Parts--col20 Yapps_Parts--Search-item">'+item.sku+'</div>';
			htmlSearchRender += '<div class="YApps_Parts--col YApps_Parts--col60 Yapps_Parts--Search-item">'+item.ru_name+'</div>';
			htmlSearchRender += '<div class="YApps_Parts--col YApps_Parts--col20 Yapps_Parts--Search-item">';
			htmlSearchRender += YApps.Formatter(Number(item.price))+' ₽ ';
			htmlSearchRender += '<small>/ '+item.stock+' шт</small></div>';
			htmlSearchRender += '</div>';
		});
	}
	
	$('div.YApps_Parts--price-container').html( htmlSearchRender );
	
	$('div.YApps_Parts--price_title').fadeIn(500);
	$('div.YApps_Parts--disclamer').fadeIn(500);
	$('div.YApps_Parts--price-container').slideDown(500);
}
YApps.Parts.SearchErrorRender = function( JData ) {
	
	//console.log( JData );
	
	$('div.YApps_Parts--price-container').html('').hide();
	
	var htmlSearchRender = '<div class="YApps_Parts--price-container_Item">';
	htmlSearchRender += '<div class="YApps_Parts--price-container_Item--Error">Ничего найти не удалось.</div>';
	htmlSearchRender += '</div>';
	
	$('div.YApps_Parts--price-container').html( htmlSearchRender );
	
	$('div.YApps_Parts--price_title').fadeIn(500);
	$('div.YApps_Parts--price-container').slideDown(500);
	$('div.YApps_Parts--disclamer').fadeOut(500);
}

$(document).ready( function() { if ( $('div').is('#YApps_Parts') ) { YApps.Parts.StartRender(); } });

$(document).on('click', 'a.YApps_Parts--Form_Submit[role="YApps_Parts--Form_SEARCH"]', function() {
	
	YApps.SendData = {};
	
	YApps.SendData.AppName = 'Parts';
	YApps.SendData.EventName = 'Отправка формы';
	YApps.SendData.EventCategory = 'Поиск запчастей';
	
	YApps.SendData.SiteID = '{{SITE.ID}}';
	YApps.SendData.PiwikID = '{{SITE.PIWIKID}}';
	YApps.SendData.YandexID = '{{SITE.YANDEXID}}';
	YApps.SendData.GoogleID = '{{SITE.GOOGLEID}}';
	YApps.SendData.PiwikVisitorID = YApps.Cookie.Get('_pk_id.1.177e');
	if ( !SendData.PiwikVisitorID ) SendData.PiwikVisitorID = YApps.Cookie.Get('_pk_id.1.e812');
	if ( !SendData.PiwikVisitorID ) SendData.PiwikVisitorID = YApps.Cookie.Get('_pk_id.1.73f4');
	YApps.SendData.YandexVisitorID = YApps.Cookie.Get('_ym_uid');
	YApps.SendData.GoogleVisitorID = YApps.Cookie.Get('_ga');
	YApps.SendData.SourceLink = location.href;
	YApps.SendData.SourceTitle = document.getElementsByTagName("title")[0].innerText;
	YApps.SendData.EventAction = document.getElementsByTagName("title")[0].innerText;
	YApps.SendData.Referrer = document.referrer;
	
	YApps.SendData.Search = $('input[name="YApps_Parts--Form_SEARCH"]').val();
	
	$.ajax({
		type: 'POST',
		url: 'https://apps.yug-avto.ru/API/search/Parts/?token={{USER.TOKEN}}',  
		data: YApps.SendData,
		success: function(data){
			
			YApps.Parts.SearchResult = JSON.parse( data );
			if ( YApps.Parts.SearchResult.Status == 'success' ) YApps.Parts.SearchSuccessRender( YApps.Parts.SearchResult );
			if ( YApps.Parts.SearchResult.Status == 'error' ) YApps.Parts.SearchErrorRender( YApps.Parts.SearchResult );
		},
		error: function() {
			
			$('div.YApps_Parts--price-container').html('').hide();
	
			var htmlSearchRender = '<div class="YApps_Parts--price-container_Item">';
			htmlSearchRender += '<div class="YApps_Parts--price-container_Item--Error">К сожалению что-то пошло не так. Попробуйте повторить запрос позднее.</div>';
			htmlSearchRender += '</div>';
			
			$('div.YApps_Parts--price-container').html( htmlSearchRender );
			
			$('div.YApps_Parts--price_title').fadeIn(500);
			$('div.YApps_Parts--price-container').slideDown(500);
			$('div.YApps_Parts--disclamer').fadeOut(500);
		}
	});
	
	if ( typeof window.ga != 'undefined' ) ga('send', 'event', YApps.SendData.EventCategory, YApps.SendData.EventAction, YApps.SendData.EventName);
	// if ( typeof window.Piwik != 'undefined' ) _paq.push(["trackEvent", YApps.SendData.EventCategory, YApps.SendData.EventAction, YApps.SendData.EventName]);
	if ( typeof window.yaCounter{{SITE.YANDEXID}} != 'undefined' ) yaCounter{{SITE.YANDEXID}}.reachGoal("YApps_Calc--Search");
	
	return false;
});

$(document).on('click', 'div.YApps_Parts--price_title', function() {

	$(this).children('span[role="YApps_Parts--Triangle"]').toggleClass('triangle-up triangle-down');
	$('div.YApps_Parts--price-container').slideToggle(500);
	$(this).children('.YApps_Parts--Icon').toggleClass('active');
});