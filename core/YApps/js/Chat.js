(function(d, w, m) {
    window.supportAPIMethod = m;
    var s = d.createElement('script');
    s.type ='text/javascript'; s.id = 'supportScript'; s.charset = 'utf-8';
    s.async = true;
    var id = '{{API.TOKEN}}';
    s.src = 'https://lcab.talk-me.ru/support/support.js?h='+id;
    var sc = d.getElementsByTagName('script')[0];
    w[m] = w[m] || function() { (w[m].q = w[m].q || []).push(arguments); };
    if (sc) sc.parentNode.insertBefore(s, sc); 
    else d.documentElement.firstChild.appendChild(s);
})(document, window, 'TalkMe');

if ( typeof YApps.SendData == 'undefined' ) YApps.SendData = {};

TalkMe("setCallback", 'openSupport', function(data) {

    if (typeof Matomo != 'undefined') _paq.push(['trackEvent', 'Чат', 'Посетитель открыл чат', 'OK']);
    if (typeof ga != 'undefined') ga('send', 'event', 'Чат', 'Посетитель открыл чат');

});

TalkMe("setCallback", 'clientSendMessage', function(data) {

    if (typeof Matomo != 'undefined') _paq.push(['trackEvent', 'Чат', 'Посетитель отправил сообщение', 'OK']);
    if (typeof ga != 'undefined') ga('send', 'event', 'Чат', 'Посетитель отправил сообщение');

    TalkMe("setClientInfo", {
        custom: {
            ct_sess: window.call_value_{{SITE.CT_SESS}},
            matomo_id: YApps.Cookie.GetMatomoID(),
            site_id: {{SITE.ID}},
			google_id: YApps.Cookie.Get('_ga'),
			yandex_id: YApps.Cookie.Get('_ym_uid')
        }
    });

});

TalkMe("setCallback", 'contactsUpdated', function(data) {

    if (typeof Matomo != 'undefined') _paq.push(['trackEvent', 'Чат', 'Посетитель оставил контакные данные', 'OK']);
    if (typeof ga != 'undefined') ga('send', 'event', 'Чат', 'Посетитель оставил контакные данные');

    let CallTouchURL = 'https://api-node{{SITE.CT_NODE}}.calltouch.ru/calls-service/RestAPI/requests/{{SITE.CT_ID}}/register/';
    CallTouchURL += '?subject=Чат: Посетитель оставил контакные данные';
    CallTouchURL += '&sessionId=' + window.call_value_{{SITE.CT_SESS}};
    CallTouchURL += '&fio=' + data.name;
    CallTouchURL += '&email=' + data.email;
    CallTouchURL += '&phoneNumber=' + data.phone.replace(/[^\d;]/g, '');

    $.get(CallTouchURL);
	
	YApps.SendData.EventCategory = 'Онлайн-консультант';
	YApps.SendData.AppName = 'Chat';
    YApps.AppPushStat(YApps.SendData);
});