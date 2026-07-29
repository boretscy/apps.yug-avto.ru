YApps.Helper = {};
YApps.Helper.EffectTime = 500;
YApps.Helper.ShowInterval = %%WIDGET.HP.SHOW_INTERVAL%%;
YApps.Helper.CloseTimeout = %%WIDGET.HP.CLOSE_TIMEOUT%%;
YApps.Helper.ItemEffect = 300;
YApps.Helper.CountItems = %%WIDGET.HP.ITEMS_COUNT%%;
YApps.Helper.ItemsStatus = false;
YApps.Helper.Phone = '%%WIDGET.HP.PHONE%%';
YApps.Helper.CTSess = '%%WIDGET.HP.CT_SESS%%';
YApps.Helper.LGPlateStick = '%%WIDGET.HP.LG_PLATE_STICK%%';
YApps.Helper.LGPlateDraggable = %%WIDGET.HP.LG_PLATE_DRAGGABLE%%;

YApps.Helper.Icons = ['#YApps-Widgets_MainButton'];
YApps.Helper.CurrentIcon = 0;
YApps.Helper.ShowIconsID = false;
YApps.Helper.IconsInterval = %%WIDGET.HP.ICON_INTERVAL%%;

YApps.Helper.Goal = {};
YApps.Helper.Goal.Yandex = 'YApps_Goals-Helper-Call';
YApps.Helper.Goal.Action = 'Запуск звонилки';
YApps.Helper.Goal.Name = 'Запуск звонилки';
YApps.Helper.Goal.Category = 'Помощник';

YApps.Helper.Items = {};

YApps.Helper.StartWidget = function( action = false, appkey ) {

    YApps.Widgets.Open = appkey;

	if ( YApps.Widgets.ShowStatus && $('div').is('[data-appkey="'+appkey+'"]') ) {
		
		if ( action ) {
			
			$('div.'+action+'_Container').siblings('div[role="YApps_Widget"]').fadeOut(YApps.Helper.EffectTime);
            $('div.'+action+'_Container').fadeIn(YApps.Helper.EffectTime);

            $('div.YApps_Helper--Item_Container').removeClass('YApps_Helper--Item_Active');
            $('div.YApps_Helper--Item[data-appkey="'+appkey+'"]').parent().addClass('YApps_Helper--Item_Active');

            location.hash = '';
		}
		if ( !action ) {
			
			$('div[data-appkey="'+appkey+'"]').siblings('div[role="YApps_Widget"]').fadeOut(YApps.Helper.EffectTime);
            $('div[data-appkey="'+appkey+'"]').fadeIn(YApps.Helper.EffectTime);

            $('div.YApps_Helper--Item_Container').removeClass('YApps_Helper--Item_Active');
            $('div.YApps_Helper--Item[data-appkey="'+appkey+'"]').parent().addClass('YApps_Helper--Item_Active');
        }
        
        if ( appkey != 'CH' && appkey != 'CI' ) $('div.YApps--Cover').fadeIn(YApps.Helper.EffectTime);

		YApps.Widgets[appkey].Reset();
		
		YApps.Widgets.ShowStatus = false;
        if ( !!YApps.Widgets.CH ) YApps.Widgets.ClearTimeout( YApps.Widgets.CH.TimeoutID );
        if ( !!YApps.Widgets.CI ) YApps.Widgets.ClearTimeout( YApps.Widgets.CI.TimeoutID );
		if ( !!YApps.Widgets.CH ) YApps.Widgets.CH.ShowTimeout = 10000;
	}
}

YApps.Helper.Toggle = function() {
	
	YApps.Helper.ItemsStatus = ( YApps.Helper.ItemsStatus ) ? false : true;
	YApps.Helper.ActivePhone = ( window['calltouch_phone_'+YApps.Helper.CTSess] ) ? YApps.FormatPhone( window['calltouch_phone_'+YApps.Helper.CTSess] ) : ( window['calltouch_phone'] ) ? YApps.FormatPhone( window['calltouch_phone'] ) : YApps.FormatPhone( YApps.Helper.Phone );
	// Костыль для распродаж и холдинга




	$('.YApps_Helper--Item[data-role="YApps_Helper--Call"]').data('action', 'tel:'+YApps.Helper.ActivePhone);
	
	$('.YApps_Helper--Container').fadeToggle(YApps.Helper.EffectTime);
	$('.YApps_Helper--LeadgenButton').fadeToggle(YApps.Helper.EffectTime);
	
    $('.YApps_Helper--MainButton_Button').toggleClass('YApps_Helper--MainButton_Button-Active');
    $('.YApps_Helper--MainButton_Container').toggleClass('YApps_Helper--MainButton_Container-Active');
	( YApps.Helper.ItemsStatus ) ? YApps.Widgets.ClearInterval( YApps.Helper.ShowIconsID ) : YApps.Helper.Pulse();
	
	if ( YApps.MobileDetect.mobile() ) $('.YApps_Helper--Cover').fadeToggle(YApps.Helper.EffectTime);
}
YApps.Helper.ToggleItem = function( i ) {
	
	var show = i*YApps.Helper.ItemEffect, hide = i*YApps.Helper.ItemEffect+YApps.Helper.ItemEffect;
	
	setTimeout( function() { $('.YApps_Helper--Item_Container').eq(i-1).addClass('YApps_Helper--Item_Active') }, show);
	setTimeout( function() { $('.YApps_Helper--Item_Container').eq(i-1).removeClass('YApps_Helper--Item_Active') }, hide);
	
}
YApps.Helper.Pulse = function() {
	
	YApps.Helper.PulseID = setInterval( function() {
		
		$('.YApps_Helper--MainButton_Pulse').removeClass('YApps_Helper--MainButton_Pulse-Acvive').addClass('YApps_Helper--MainButton_Pulse-Acvive');
		
	}, YApps.Helper.IconsInterval*1000);
}
YApps.Helper.Init = function() {
	
	if ( !!location.hash ) YApps.Helper.StartWidget(false, location.hash.replace(/^#/,''));
	
	YApps.Helper.Pulse();
	
	if ( YApps.Helper.LGPlateDraggable ) {
		
		var script = document.createElement('script');
		script.type = 'text/javascript';
		script.src = 'https://apps.yug-avto.ru/pub/libs/draggabilly/2.2.0/draggabilly.pkgd.min.js';
		script.onload = function() { 
			
			Draggabilly.prototype.positionDrag = Draggabilly.prototype.setLeftTop;
			
			YApps.Helper.LGPlate = $('.YApps_Helper--LeadgenButton-Draggable').draggabilly({ axis: YApps.Helper.LGPlateStick });
			YApps.Helper.LGPlate.css({cursor: 'all-scroll'});
		};
		document.getElementsByTagName('head')[0].appendChild(script);	
	}
}

window.addEventListener('hashchange',function() {
	
	if ( !!location.hash.replace(/^#/,'').length > 0 ) YApps.Helper.StartWidget(false, location.hash.replace(/^#/,''));
});
$(document).on('click', '.YApps_Helper--MainButton_Container', function() {
	
	YApps.Helper.Toggle();
	clearInterval( YApps.Helper.ShowIntervalID );
});
$(document).on('click', 'div[role="YApps_Helper--StartWidget"]', function() {
	
	YApps.Widgets.ShowStatus = true;
	YApps.Helper.StartWidget( $(this).data('action'), $(this).data('appkey') );
	if ( $(this).data('appkey') == 'LG' && !!YApps.Widgets.LG ) YApps.Widgets.ClearTimeout( YApps.Widgets.LG.ShowTimeoutID );
	clearInterval( YApps.Helper.ShowIntervalID );
});

$(document).on('click', 'div[data-role="YApps_Helper--Call"]', function() {
	
	YApps.AppPushGoal({
		Category: YApps.Helper.Goal.Category,
		Action: YApps.Helper.Goal.Action,
		Name: YApps.Helper.Goal.Name,
		Yandex: YApps.Helper.Goal.Yandex,
		CallTouch: {
			Flag: false
		}
	});
});

$(document).on('click', 'div.YApps--Cover', function() {
	
	$('div.YApps--Cover').fadeOut(YApps.Helper.EffectTime);
	$('div[role="YApps_Widget"]').fadeOut(YApps.Helper.EffectTime);
	
	YApps.Widgets.ShowStatus = true;
    if ( !!YApps.Widgets.CH ) if ( YApps.Widgets.CH.ShowTimeout ) YApps.Widgets.CH.Show( YApps.Widgets.CH.ShowTimeout );
    if ( !!YApps.Widgets.CI ) if ( YApps.Widgets.CI.ShowTimeout ) YApps.Widgets.CI.Show( YApps.Widgets.CI.ShowTimeout );
	
    if ( location.hash == '#'+YApps.Widgets.Open ) location.hash = '';
    
    $('div.YApps_Helper--Item_Container').removeClass('YApps_Helper--Item_Active');
    delete YApps.Widgets.Open;
});
$(document).on('click', '[role="YApps_Helper--GotoApp"]', function() { location.href = $(this).data('action') });
