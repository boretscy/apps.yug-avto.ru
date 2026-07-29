'use strict';

%%SITE.JQUERY%%


var YApps = {};
YApps.Cookie = {};
YApps.Description = 'Виджеты YApps разработаны для компании Юг-Авто. Разработчик - Борецкий Антон, boretscy@gmail.com';
YApps.SendData = {};
YApps.SendData.SiteID = %%SITE.ID%%;
YApps.SendData.PiwikID = '%%SITE.PIWIKID%%';
YApps.SendData.YandexID = '%%SITE.YANDEXID%%';
YApps.SendData.YandexCounter = 'yaCounter'+'%%SITE.YANDEXID%%';
YApps.SendData.GoogleID = '%%SITE.GOOGLEID%%';
YApps.SendData.SourceLink = location.href;
YApps.SendData.SourceTitle = document.getElementsByTagName("title")[0].innerText;
YApps.SendData.Referrer = document.referrer;

YApps.jQload = true;
if ( typeof window.jQuery != 'undefined' ) YApps.jQload = false;
if ( YApps.jQload ) {
	
	var script = document.createElement('script');
	script.type = 'text/javascript';
    script.src = 'https://apps.yug-avto.ru/pub/libs/jquery/3.4.1/jquery.min.js';
	script.onload = function() {  YApps.jQ = $.noConflict(true);  };
	document.getElementsByTagName('head')[0].appendChild(script);
}

%%SITE.START_SCRIPT%%


///////////////////////////////////////////////////////////////////////////////////////////
// Cookie! Om-nom-nom! ////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////

YApps.Cookie.Get = function(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : false;
}
YApps.Cookie.GetMatomoID = () => {

    var cookies = {};
    for (let cookie of document.cookie.split('; ')) {
        let [name, value] = cookie.split("=");

        if (~name.indexOf("_pk_id.1.")) return decodeURIComponent(value);
    }

    return false;
}
YApps.Cookie.Set = function(name, value, options) {
    options = options || {};

    var expires = options.expires;

    if (typeof expires == "number" && expires) {
        var d = new Date();
        d.setTime(d.getTime() + expires * 1000);
        expires = options.expires = d;
    }
    if (expires && expires.toUTCString) {
        options.expires = expires.toUTCString();
    }

    value = encodeURIComponent(value);

    var updatedCookie = name + "=" + value;

    for (var propName in options) {
        updatedCookie += "; " + propName;
        var propValue = options[propName];
        if (propValue !== true) {
            updatedCookie += "=" + propValue;
        }
    }

    document.cookie = updatedCookie;
}
YApps.Cookie.Del = function(name) {
    YApps.Cookie.Get(name, "", {
        expires: -1
    })
}



YApps.CheckEmail = function(email) {

    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|("\.+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

YApps.Formatter = function(q) {

    var Price = new Intl.NumberFormat('ru', { currency: 'RUR' });
    return Price.format(q);
}
YApps.FormatPhone = function(q) {

    q = q.replace(/[^\d;]/g, '');
    return '+' + q[0] + ' (' + q[1] + q[2] + q[3] + ') ' + q[4] + q[5] + q[6] + '-' + q[7] + q[8] + '-' + q[9] + q[10];
}
YApps.FormatPhoneIn = function(q) {
	
    return String(q).replace(/[^\d;]/g, '');
}

YApps.AppPushStat = function(SendData) {

    if ( !SendData.SiteID ) SendData.SiteID = YApps.SendData.SiteID;
	if ( !SendData.PiwikID ) SendData.PiwikID = YApps.SendData.PiwikID;
	if ( !SendData.YandexID ) SendData.YandexID = YApps.SendData.YandexID;
	if ( !SendData.GoogleID ) SendData.GoogleID = YApps.SendData.GoogleID;
    SendData.PiwikVisitorID = YApps.Cookie.GetMatomoID();
    SendData.YandexVisitorID = YApps.Cookie.Get('_ym_uid');
    SendData.GoogleVisitorID = YApps.Cookie.Get('_ga');
    if ( !SendData.SourceLink ) SendData.SourceLink = location.href;
    if ( !SendData.SourceTitle ) SendData.SourceTitle = document.getElementsByTagName("title")[0].innerText;
    if ( !SendData.EventAction ) SendData.EventAction = document.getElementsByTagName("title")[0].innerText;
    if ( !SendData.Referrer ) SendData.Referrer = document.referrer;
    console.log(SendData);
	
	var res;
	
    $.ajax({
        type: 'POST',
        url: 'https://apps.yug-avto.ru/API/stat/?token=%%USER.TOKEN%%',
        data: SendData,
		success: function(data) { res= JSON.parse( data ) },
		error: function() { console.log( 'error' ); res = {status: 'error', description: 'Ошибка на сервере'} }
    });
	
	return res;
}

YApps.AppPushStatN = function( SendData ) {
	
	return new Promise((resolve, reject) => {
		
		if ( !SendData.SiteID ) SendData.SiteID = YApps.SendData.SiteID;
		if ( !SendData.PiwikID ) SendData.PiwikID = YApps.SendData.PiwikID;
		if ( !SendData.YandexID ) SendData.YandexID = YApps.SendData.YandexID;
		if ( !SendData.GoogleID ) SendData.GoogleID = YApps.SendData.GoogleID;
		if ( !SendData.PiwikVisitorID ) SendData.PiwikVisitorID = YApps.SendData.PiwikVisitorID;
		if ( !SendData.YandexVisitorID ) SendData.YandexVisitorID = YApps.SendData.YandexVisitorID;
		if ( !SendData.GoogleVisitorID ) SendData.GoogleVisitorID = YApps.SendData.GoogleVisitorID;
		if ( !SendData.SourceLink ) SendData.SourceLink = YApps.SendData.SourceLink;
		if ( !SendData.SourceTitle ) SendData.SourceTitle = YApps.SendData.SourceTitle;
		if ( !SendData.EventAction ) SendData.EventAction = document.getElementsByTagName("title")[0].innerText;
		if ( !SendData.Referrer ) SendData.Referrer = YApps.SendData.Referrer;
		console.log(SendData);
		
		$.ajax({
            type: 'POST',
            crossDomain: true,
			url: 'https://apps.yug-avto.ru/API/stat/?token=%%USER.TOKEN%%',
			data: SendData,
			success: function(data) { ( JSON.parse( data ).status == 'success' ) ? resolve( JSON.parse( data ) ) : reject( JSON.parse( data ) ) },
			error: function() { reject( {status: 'error', description: 'Ошибка на сервере'} ) }
        })
        .fail( function(xhr, textStatus, errorThrown) {
            console.log(xhr);
            console.log(textStatus);
        });
	});
}
YApps.AppPushGoal = function( GoalData ) {
	
	console.log( GoalData );
	
	// Metrics
	if ( typeof window.dataLayer != 'undefined' ) dataLayer.push({'event': 'FormSubmission', 'eventCategory': GoalData.IDToneCategory, 'eventAction': 'submit', 'eventLabel': GoalData.IDToneLabel || false});
	// if ( typeof window.Matomo != 'undefined' ) _paq.push(["trackEvent", GoalData.Category, GoalData.Action, GoalData.Name]);
	if ( typeof window[YApps.SendData.YandexCounter] != 'undefined' && typeof GoalData.Yandex != 'undefined' ) window[YApps.SendData.YandexCounter].reachGoal(GoalData.Yandex);
	
	// console.log( dataLayer );
	
	// CallTouch
	if ( GoalData.CallTouch.Flag ) { 
	
		var CallTouchURL = 'https://api.calltouch.ru/calls-service/RestAPI/requests/'+'%%SITE.CT_ID%%'+'/register/';
		CallTouchURL += '?subject='+encodeURIComponent(GoalData.Name)+' '+encodeURIComponent(GoalData.Action);
		CallTouchURL += '&sessionId='+window.call_value_%%SITE.CT_SESS%%;
		CallTouchURL += '&phoneNumber='+GoalData.CallTouch.Phone.replace(/[^\d;]/g, '');
        if ( typeof GoalData.CallTouch.Name != 'undefined' ) CallTouchURL += '&fio='+encodeURIComponent(GoalData.CallTouch.Name);
        if ( typeof GoalData.CallTouch.Email != 'undefined' ) CallTouchURL += '&email='+GoalData.CallTouch.Email;
        CallTouchURL += '&requestUrl='+location.href;
		
		var request = new XMLHttpRequest();
		request.open('GET', CallTouchURL, true);
		request.send();
	}
}
YApps.SVG = () => { $('body').prepend('<div id="YApps_SVG">%%YAPPS.SVG%%</div>') }

YApps.LoadScripts = function( data ) {
	
	var Scripts = {}, Css = {};
	
	Scripts.Inputmask = {
		o: 'Inputmask',
		s: 'https://apps.yug-avto.ru/pub/libs/jquery.inputmask/3.3.4/jquery.inputmask.bundle.min.js'
	}
	Scripts.Flatplickr = {
		o: 'flatpickr',
		s: [
			'https://apps.yug-avto.ru/pub/libs/flatpickr/4.5.7/flatpickr.min.js',
			'https://apps.yug-avto.ru/pub/libs/flatpickr/4.5.7/l10n/ru.js'
		]
	}
	Scripts.Ymaps = {
		o: 'ymaps',
		s: 'https://api-maps.yandex.ru/2.1/?apikey=34ddb940-0941-4b80-ab80-b0aa351b6560&lang=ru_RU'
	}
	
	Scripts.MobileDetect = {
		o: 'MobileDetect',
		s: 'https://apps.yug-avto.ru/pub/libs/mobile-detect/1.4.3/mobile-detect.min.js'
	}
	
	Scripts.Draggabilly = {
		o: 'Draggabilly',
		s: 'https://apps.yug-avto.ru/pub/libs/draggabilly/2.2.0/draggabilly.pkgd.min.js'
    }
    
    Scripts.Swiper = {
		o: 'Swiper',
		s: 'https://apps.yug-avto.ru/pub/libs/swiper/5.4.0/swiper.min.js'
	}
	
    Css.Flatplickr = 'https://apps.yug-avto.ru/pub/libs/flatpickr/4.5.7/flatpickr.min.css';
    Css.Swiper = 'https://apps.yug-avto.ru/pub/libs/swiper/5.4.0/swiper.min.css';
	
	// ПЕРЕПИСАТЬ!!!!!!!
	for ( var s in data ) {
		
		// Css
		if ( Css[s] ) {
			
			if ( typeof window[Scripts[s].o] == 'undefined' ) {
				
				if ( Array.isArray( Css[s] ) ) {
		
					Css[s].forEach( function(item, i, arr) { $('head').append('<link href="'+item+'" rel="stylesheet">') });
					
				} else if ( typeof Css[s] == 'string' ) {
					
					$('head').append('<link href="'+Css[s]+'" rel="stylesheet">');
				}
			}
		}
		
		// Scripts
		if ( Scripts[s] ) {
			
			if ( typeof window[Scripts[s].o] == 'undefined' ) {
				
				switch ( Scripts[s].o ) {
					
					case 'Inputmask':
						
						var script = document.createElement('script');
						script.type = 'text/javascript';
						script.charset = 'utf-8';
						script.src = Scripts.Inputmask.s;
						script.onload = function() { if ( data[s] ) $( data[s] ).inputmask({ 'mask': '+7 (999) 999-99-99', showMaskOnHover: false }) };
						document.getElementsByTagName('head')[0].appendChild(script);
						
						break;
						
					case 'flatpickr':
						
						var script1 = document.createElement('script');
						script1.type = 'text/javascript';
						script1.charset = 'utf-8';
						script1.src = Scripts.Flatplickr.s[0];
						script1.onload = function() {
							
							var script2 = document.createElement('script');
							script2.type = 'text/javascript';
							script2.charset = 'utf-8';
							script2.src = Scripts.Flatplickr.s[1];
							script2.onload = function() {
								
								if ( data[s] ) {
									var calendar = new flatpickr(data[s], {
										locale: "ru",
										altInput: true,
										enableTime: true,
										time_24hr: true
									}); 
								 }
							};
							document.getElementsByTagName('head')[0].appendChild(script2);
						};
						document.getElementsByTagName('head')[0].appendChild(script1);
						
						break;
					
					case 'ymaps':
						
						var script = document.createElement('script');
                        script.type = 'text/javascript';
                        script.async = 'true';
						script.src = Scripts.Ymaps.s;
						script.onload = function() {};
						document.getElementsByTagName('head')[0].appendChild(script);
						
						break;
					
					case 'MobileDetect':
						
						var script = document.createElement('script');
						script.type = 'text/javascript';
						script.src = Scripts.MobileDetect.s;
						script.onload = function() { if ( data[s] ) YApps.MobileDetect = new MobileDetect(window.navigator.userAgent) };
						document.getElementsByTagName('head')[0].appendChild(script);
						
                        break;
                    
                    case 'Swiper':
						
						var script = document.createElement('script');
						script.type = 'text/javascript';
						script.src = Scripts.Swiper.s;
						script.onload = function() { if ( data[s] ) YApps.Swiper = Swiper; };
						document.getElementsByTagName('head')[0].appendChild(script);
						
						break;
				}
				
			} else {
				
				switch ( Scripts[s].o ) {
					
					case 'Inputmask':
						
						if ( data[s] ) $( data[s] ).inputmask({ 'mask': '+7 (999) 999-99-99', showMaskOnHover: false });
						
					case 'flatpickr':
						
						if ( data[s] ) {
							var calendar = new flatpickr(data[s], {
								locale: "ru",
								altInput: true,
								enableTime: true,
								time_24hr: true
							}); 
						}
						break;
					
					case 'MobileDetect':
						
						if ( data[s] ) YApps.MobileDetect = new MobileDetect(window.navigator.userAgent);
						break;
				}
			}
		}
	}
}
YApps.ToggleTermCheck = function( el, action ) {
	
	var cl = 'YApps--Form_Personal-Item_Checked';
	var icon = ( action ) ? 'Check' : 'UnCheck';
	var status = ( action ) ? 'Y' : 'N';
	
	( action ) ? $(el).addClass(cl) : $(el).removeClass(cl);
	$(el).find('svg use').attr('xlink:href', '#YApps-'+icon);
	$(el).parent().parent().siblings('input[name="'+$(el).data('action')+'"]').val(status);
	if ( action ) $(el).removeClass('YApps--Form_Personal-Item_Error');
}

YApps.Init = function() {
	
	// YApps.LoadScripts( {MobileDetect: true} );
	$('head').append('<link href="https://apps.yug-avto.ru/API/get/css/?token=%%USER.TOKEN%%&r='+location.href+'" rel="stylesheet">');
    YApps.SVG();
    

    $(document).on('click', '.YApps--Form_Personal-Item', function() {
        
        var action = !$(this).hasClass('YApps--Form_Personal-Item_Checked');
        YApps.ToggleTermCheck( $(this), action );
    });
}


%%SITE.END_SCRIPT%%


// setTimeout( function() { YApps.Init();
YApps.Init();


