YApps.Widgets.CB = {};
YApps.Widgets.CB.WorkFlag = %%WIDGET.CB.WORKFLAG%%;
YApps.Widgets.CB.IdleTimeout = %%WIDGET.CB.IDLE_TIMEOUT%%;
YApps.Widgets.CB.IdleTimerID = null;
YApps.Widgets.CB.IdleTimerState = false;
YApps.Widgets.CB.Timer = {};
YApps.Widgets.CB.Timer.Await = %%WIDGET.CB.TIMER_AWAIT%%; //30
YApps.Widgets.CB.Timer.Timeout = %%WIDGET.CB.TIMER_TIMEOUT%%; //15
YApps.Widgets.CB.AwaitDays = %%WIDGET.CB.AWAIT_DAYS%%; // 1
YApps.Widgets.CB.Settings = {};
YApps.Widgets.CB.Settings.Title = {};
YApps.Widgets.CB.Settings.Title.Span = {};
YApps.Widgets.CB.Settings.Title.Description = {};
YApps.Widgets.CB.Settings.Title.Prologue = '%%WIDGET.CB.PROLOGUE%%';
YApps.Widgets.CB.Settings.Title.Span.Now = 'за '+YApps.Widgets.CB.Timer.Await+' секунд';
YApps.Widgets.CB.Settings.Title.Span.Proroque = '%%WIDGET.CB.TITLE_SPAN_PROROQUE%%';
YApps.Widgets.CB.Settings.Title.Description.Now = '%%WIDGET.CB.DESCRIPION_NOW%%';
YApps.Widgets.CB.Settings.Title.Description.Proroque = '%%WIDGET.CB.DESCRIPION_LATER%%';

YApps.Widgets.CB.Goal = {};
YApps.Widgets.CB.Goal.YandexNow = 'YApps_Goals-Widgets_CB-Send_Now';
YApps.Widgets.CB.Goal.YandexLater = 'YApps_Goals-Widgets_CB-Send_Later';
YApps.Widgets.CB.Goal.Category = 'Виджеты';
YApps.Widgets.CB.Goal.Name = 'ID: '+'%%WIDGET.CB.ID%%'+'. '+'%%WIDGET.CB.NAME%%';

YApps.Widgets.CB.ToggleStatus = function( el, action ) {
	
	if ( !YApps.Widgets.CB.WorkFlag ) action = 'Proroque';
	
	var span = ( action == 'Now' ) ? YApps.Widgets.CB.Settings.Title.Span.Now : YApps.Widgets.CB.Settings.Title.Span.Proroque;
	var desc = ( action == 'Now' ) ? YApps.Widgets.CB.Settings.Title.Description.Now : YApps.Widgets.CB.Settings.Title.Description.Proroque;
	
	$('.YApps_Widget--Callback_Container input[name="YApps_Widget--Form_Name-Status"]').val(action);
	( action == 'Now' ) ? $(el).slideUp(YApps.Widgets.EffectTime) : $(el).slideDown(YApps.Widgets.EffectTime);
	setTimeout( function() {
		$('.YApps_Widget--Form_Title span.YApps_Widget--Form_Title-Callback_Time').text( span );
		$('.YApps_Widget--Form_Callback-Field_Toggle').text( desc );
	}, YApps.Widgets.EffectTime);
}
YApps.Widgets.CB.Timer.Start = function( result ) {
	
	var show = ( $('.YApps_Widget--Callback_Container input[name="YApps_Widget--Form_Name-Status"]').val() != 'Now' ) ? true : false;
	
	if ( result.status == 'success' && !show ) {
			
		$('.YApps_Widget--Callback_Form .YApps_Widget--Form_Fields').slideUp(YApps.Widgets.EffectTime);
		$('.YApps_Widget--Callback_Timer').slideDown(YApps.Widgets.EffectTime);
		$('.YApps_Widget--Form_Callback-Field_Toggle').hide();
	
		YApps.Widgets.CB.Timer.S = Number( $('.YApps_Widget--Callback_Timer #YApps_Widget--Callbac_Seconds').text() );
		YApps.Widgets.CB.Timer.mS = Number( $('.YApps_Widget--Callback_Timer #YApps_Widget--Callbac_mSeconds').text() );
		
		YApps.Widgets.CB.Timer.ID = setInterval( function() {
			
			YApps.Widgets.CB.Timer.mS--;
			if ( YApps.Widgets.CB.Timer.mS < 0 ) {
				
				YApps.Widgets.CB.Timer.mS = 9;
				YApps.Widgets.CB.Timer.S--;
			}
			
			$('.YApps_Widget--Callback_Timer #YApps_Widget--Callbac_mSeconds').text( YApps.Widgets.CB.Timer.mS );
			$('.YApps_Widget--Callback_Timer #YApps_Widget--Callbac_Seconds').text( YApps.Widgets.CB.Timer.S );
			
			if ( YApps.Widgets.CB.Timer.S == 0 && YApps.Widgets.CB.Timer.mS == 0 ) YApps.Widgets.CB.Timer.Stop();
			
		}, 100);
	} 
	
	if ( result.status == 'error' ) show = true;
	
	return {status: result.status, show: show};
	
}
YApps.Widgets.CB.Timer.Stop = function() {
	
	YApps.Widgets.ClearInterval( YApps.Widgets.CB.Timer.ID );
	YApps.Widgets.CB.Timer.Reset();
}
YApps.Widgets.CB.Timer.Reset = function() {
	
	setTimeout( function() {
		
		$('.YApps_Widget--Callback_Form .YApps_Widget--Form_Fields').slideDown(YApps.Widgets.EffectTime);
		$('.YApps_Widget--Callback_Timer').slideUp(YApps.Widgets.EffectTime);
		YApps.Widgets.CB.Reset();
		
	}, YApps.Widgets.CB.Timer.Timeout*1000);
	
}

YApps.Widgets.CB.Reset = function() {
	
	YApps.Widgets.CB.ToggleStatus( $('#'+$('.YApps_Widget--Form_Callback-Field_Toggle').data('id')), ((YApps.Widgets.CB.WorkFlag)?'Now':'Proroque') );
	$('.YApps_Widget--Form_Personal-Item').each( function(i, e) { YApps.Widgets.ToggleCheck( $(this), true ) });
	$('.YApps_Widget--Callback_Container input[name="YApps_Widget--Form_Name-DateTime"]').val('');
	$('.YApps_Widget--Callback_Timer #YApps_Widget--Callbac_Seconds').text( YApps.Widgets.CB.Timer.Await );
	$('.YApps_Widget--Callback_Timer #YApps_Widget--Callbac_mSeconds').text( 0 );
	$('.YApps_Widget--Callback_Container input[name="YApps_Widget--Form_Name-Phone"]').val('').removeClass('YApps_Widget--Form_Error');
	if (YApps.Widgets.CB.WorkFlag) $('.YApps_Widget--Form_Callback-Field_Toggle').show();
	
	Inputmask({'mask': '+7 (999) 999-99-99', showMaskOnHover: false }).mask('.YApps_Widget--Callback_Container input[name="YApps_Widget--Form_Name-Phone"]');
	YApps.Widgets.CB.Flatplickr = new flatpickr('input[name="YApps_Widget--Form_Name-DateTime"]', {
			locale: "ru",
			altInput: true,
			enableTime: true,
			time_24hr: true,
			minuteIncrement: 15,
			defaultHour: 9,
			minDate: "today",
			maxDate: new Date().fp_incr(YApps.Widgets.CB.AwaitDays)
		});
}
YApps.Widgets.CB.Init = function() {
	
	var now = new Date();
	if ( now.getDay() == 5 ) YApps.Widgets.CB.AwaitDays += 2;
	if ( now.getDay() == 6 ) YApps.Widgets.CB.AwaitDays += 1;
	
	setTimeout( function() { YApps.Widgets.CB.Reset() }, 500);
}

$(document).on('click', '.YApps_Widget--Form_Callback-Field_Toggle', function() {
	
	var action = ( $('#'+$(this).data('id')).is(':visible') ) ? 'Now' : 'Proroque';
	YApps.Widgets.CB.ToggleStatus( $('#'+$(this).data('id')), action );
});
setTimeout( function() {
	
	$(document).on('mouseover', '.YApps--Trigger', function() {
		
		var curTime = new Date().getTime();
		
		if ( !YApps.Cookie.Get('YAppsWidgetsCB_Timeout') && Number(YApps.Cookie.Get('YAppsWidgetsCB_Timeout'))+YApps.Widgets.CB.IdleTimeout*60*1000 <= curTime ) {
			
			YApps.Cookie.Set('YAppsWidgetsCB_Timeout', curTime, {path: '/'});
			$('div.YApps_Widget--Callback_Container').siblings('div[role="YApps_Widget"]').fadeOut(YApps.Widgets.EffectTime);
			$('div.YApps--Cover').fadeIn(YApps.Widgets.EffectTime);
			$('div.YApps_Widget--Callback_Container').fadeIn(YApps.Widgets.EffectTime);
			YApps.Widgets.CB.Reset(); YApps.Widgets.ShowStatus = false;
		}
	});
	
}, 15000);

$(document).on('click', 'a[role="YApps_Widget--Form_Send"][data-appkey="CB"]', function() {
	
	$('.YApps_Widget--Callback_Container .YApps_Widget--Form_Personal-Item').each( function(i,e) { $(e).removeClass('YApps_Widget--Form_Personal-Item_Error') });
	$('.YApps_Widget--Callback_Container input[name="YApps_Widget--Form_Name-Phone"]').removeClass('YApps_Widget--Form_Error');
	
	YApps.Widgets.CB.SendData = {};
	
	YApps.Widgets.CB.SendData.Id = '%%WIDGET.CB.ID%%';
	YApps.Widgets.CB.SendData.AppName = 'Widgets';
	YApps.Widgets.CB.SendData.EventCategory  = 'Обратный звонок';
	YApps.Widgets.CB.SendData.EventType  = 'CB';
	if ( $('input[name="YApps_Widget--Form_Name-Status"]').val() == 'Now' ) YApps.Widgets.CB.SendData.EventAction = 'Заказ немедленного звонка';
	if ( $('input[name="YApps_Widget--Form_Name-Status"]').val() == 'Proroque' ) YApps.Widgets.CB.SendData.EventAction = 'Заказ отложенного звонка';
	 YApps.Widgets.CB.SendData.EventName = 'YAppsWidgetCB_Send';
	if ( $('input[name="YApps_Widget--Form_Name-Status"]').val() == 'Proroque' ) YApps.Widgets.CB.SendData.DateTime = $('input[name="YApps_Widget--Form_Name-DateTime"]').val();
	YApps.Widgets.CB.SendData.Phone = ( YApps.FormatPhoneIn($('.YApps_Widget--Callback_Container input[name="YApps_Widget--Form_Name-Phone"]').val()).length == 11 ) ? YApps.FormatPhoneIn($('.YApps_Widget--Callback_Container input[name="YApps_Widget--Form_Name-Phone"]').val()) : false;
	YApps.Widgets.CB.SendData.Personal = $('.YApps_Widget--Callback_Container input[name="YApps_Widget--Form_Name-Personal"]').val();
	YApps.Widgets.CB.SendData.Communications = $('.YApps_Widget--Callback_Container input[name="YApps_Widget--Form_Name-Communications"]').val();
	YApps.Widgets.CB.SendData.Flag = true;
	
	YApps.Widgets.CB.SendData.PiwikVisitorID = YApps.Cookie.GetMatomoID();
    YApps.Widgets.CB.SendData.YandexVisitorID = YApps.Cookie.Get('_ym_uid');
    YApps.Widgets.CB.SendData.GoogleVisitorID = YApps.Cookie.Get('_ga');
    
    $('.YApps_Widget--Callback_Container .YApps--Form_Personal-Item').each( function(i,e) {

		if ( !$(e).hasClass('YApps--Form_Personal-Item_Checked') ) {
			
			YApps.Widgets.CB.SendData.Flag = false;
			$(e).addClass('YApps--Form_Personal-Item_Error');
		}
	});
	
	if ( !YApps.Widgets.CB.SendData.Phone ) {
		
		YApps.Widgets.CB.SendData.Flag = false;
		$('.YApps_Widget--Callback_Container input[name="YApps_Widget--Form_Name-Phone"]').addClass('YApps_Widget--Form_Error');
	}
	
	if ( YApps.Widgets.CB.SendData.Flag ) {
		
		YApps.Widgets.FormCover( 'CB' );
		YApps.AppPushStatN( YApps.Widgets.CB.SendData ).then(
			(success) => {
				
				YApps.Widgets.FormResult('CB', YApps.Widgets.CB.Timer.Start(success), success);
				YApps.Widgets.FormCover( 'CB', false );
				
				YApps.AppPushGoal({
					Category: YApps.Widgets.CB.Goal.Category,
					IDToneCategory: 'callback',
					YAIDTone: 'callback_submit',
					Action: YApps.Widgets.CB.SendData.EventAction,
					Name: YApps.Widgets.CB.Goal.Name,
					Yandex: ( $('input[name="YApps_Widget--Form_Name-Status"]').val() == 'Now' ) ? YApps.Widgets.CB.Goal.YandexNow : YApps.Widgets.CB.Goal.YandexLater,
					CallTouch: {
						Flag: true,
						Phone: YApps.Widgets.CB.SendData.Phone
					}
				});
			},
			(error) => {
				
				YApps.Widgets.FormResult('CB', YApps.Widgets.CB.Timer.Start(error), error);
				YApps.Widgets.FormCover( 'CB', false );
			}
		);
		
		setTimeout( function() {
			
			if ( $('input[name="YApps_Widget--Form_Name-Status"]').val() != 'Now' ) {
				
				YApps.Widgets.FormCover( 'CB', false );
				YApps.Widgets.FormReset('CB');
			}
			
		}, YApps.Widgets.ResultTimeout*1000);
	}
	
	YApps.Widgets.FormCover( 'CB', false );
	return false;
});
//$(document).ready( function() { console.log( YApps.Widgets.CB ) });
