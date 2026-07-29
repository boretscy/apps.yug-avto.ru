YApps.Widgets.LG = {};
YApps.Widgets.LG.InitTimeout = 1500;
YApps.Widgets.LG.Timer = {};
YApps.Widgets.LG.Timer.ID = false;
YApps.Widgets.LG.Timer.Flag = %%WIDGET.LG.TIMER_FLAG%%;
YApps.Widgets.LG.ShowTimeout = %%WIDGET.LG.SHOW_TIMEOUT%%; //sec
YApps.Widgets.LG.ShowSecond = %%WIDGET.LG.SHOW_SECOND%%; // min
YApps.Widgets.LG.ShowCount = %%WIDGET.LG.SHOW_COUNT%%; 
YApps.Widgets.LG.ShowSecondCount = %%WIDGET.LG.SHOW_COUNT_2%%; 

YApps.Widgets.LG.Goal = {};
YApps.Widgets.LG.Goal.Yandex = 'YApps_Goals-Widgets_LG-Send_'+'%%WIDGET.LG.ID%%';
YApps.Widgets.LG.Goal.Category = 'Виджеты';
YApps.Widgets.LG.Goal.Action = 'Генератор Клиентов';
YApps.Widgets.LG.Goal.Name = 'ID: '+'%%WIDGET.LG.ID%%'+'. ';

YApps.Widgets.LG.Timer.Start = function() {
	
	if ( !YApps.Widgets.LG.Timer.ID ) {
		
		YApps.Widgets.LG.Timer.D = %%WIDGET.LG.TIMER_DAYS%%;
		YApps.Widgets.LG.Timer.H = %%WIDGET.LG.TIMER_HOURS%%;
		YApps.Widgets.LG.Timer.M = %%WIDGET.LG.TIMER_MINUTS%%;
		YApps.Widgets.LG.Timer.S = %%WIDGET.LG.TIMER_SECONDS%%;
		
		YApps.Widgets.LG.Timer.ID = setInterval( function() {
			
			YApps.Widgets.LG.Timer.S--;
			if ( YApps.Widgets.LG.Timer.D == 0 && YApps.Widgets.LG.Timer.H == 0 && YApps.Widgets.LG.Timer.M == 0 && YApps.Widgets.LG.Timer.S == 0 ) YApps.Widgets.LG.Timer.Stop();
			
			if ( YApps.Widgets.LG.Timer.S < 0 ) {
				
				YApps.Widgets.LG.Timer.S = 59;
				YApps.Widgets.LG.Timer.M--;
				
				if ( YApps.Widgets.LG.Timer.M < 0 ) {
					
					YApps.Widgets.LG.Timer.M = 59;
					YApps.Widgets.LG.Timer.H--;
					
					if ( YApps.Widgets.LG.Timer.H < 0 ) {
						
						YApps.Widgets.LG.Timer.H = 23;
						YApps.Widgets.LG.Timer.D--;
					}
				}
			}
			
			$('#YApps_Widget--Leadgen_Timer-Days').text( YApps.Widgets.LG.Timer.D );
			$('#YApps_Widget--Leadgen_Timer-Hours').text( ((YApps.Widgets.LG.Timer.H<10)?'0':'')+YApps.Widgets.LG.Timer.H );
			$('#YApps_Widget--Leadgen_Timer-Minuts').text( ((YApps.Widgets.LG.Timer.M<10)?'0':'')+YApps.Widgets.LG.Timer.M );
			$('#YApps_Widget--Leadgen_Timer-Seconds').text( ((YApps.Widgets.LG.Timer.S<10)?'0':'')+YApps.Widgets.LG.Timer.S );
			
		}, 1000);
	}
	
}
YApps.Widgets.LG.Timer.Stop = function() {
	
	YApps.Widgets.ClearTimeout( YApps.Widgets.LG.Timer.ID );
	YApps.Widgets.LG.Timer.ID = false;
}
YApps.Widgets.LG.Reset = function() {
	
	if ( YApps.Widgets.LG.Timer.Flag ) YApps.Widgets.LG.Timer.Start();
	Inputmask({'mask': '+7 (999) 999-99-99', showMaskOnHover: false }).mask('.YApps_Widget--Leadgen_Container input[name="YApps_Widget--Form_Name-Phone"]');
}
YApps.Widgets.LG.Init = function() {
	
	setTimeout( function() { YApps.Widgets.LG.Reset() }, YApps.Widgets.InitTimeout);
}


YApps.Widgets.LG.ShowTimeoutID = setTimeout( function() {
		
	if ( !YApps.Cookie.Get('YAppsWidgetLG_Count') || Number(YApps.Cookie.Get('YAppsWidgetLG_Count')) < YApps.Widgets.LG.ShowCount ) {
		
		if ( !YApps.Cookie.Get('YAppsWidgetLG_Count') && YApps.Widgets.ShowStatus ) {
		
			YApps.Cookie.Set('YAppsWidgetLG_Count', 1, {path: '/', domain: '.'+location.host});
			
		} else if ( Number(YApps.Cookie.Get('YAppsWidgetLG_Count')) < YApps.Widgets.LG.ShowCount && YApps.Widgets.ShowStatus ) {
			
			YApps.Cookie.Set('YAppsWidgetLG_Count', Number(YApps.Cookie.Get('YAppsWidgetLG_Count')) + 1, {path: '/', domain: '.'+location.host})
		}
		
		YApps.Helper.StartWidget( 'YApps_Widget--Leadgen', 'LG' );
	}
	
}, YApps.Widgets.LG.ShowTimeout*1000);

YApps.Widgets.LG.ShowSecondID = setTimeout( function() {
		
	if ( !YApps.Cookie.Get('YAppsWidgetLG_Count2') || Number(YApps.Cookie.Get('YAppsWidgetLG_Count2')) < YApps.Widgets.LG.ShowSecondCount ) YApps.Helper.StartWidget( 'YApps_Widget--Leadgen', 'LG' );
	if ( !YApps.Cookie.Get('YAppsWidgetLG_Count2') ) {
		
		YApps.Cookie.Set('YAppsWidgetLG_Count2', 1, {path: '\/', domain: '.'+location.host});
		
	} else if ( Number(YApps.Cookie.Get('YAppsWidgetLG_Count2')) < YApps.Widgets.LG.ShowSecondCount ) {
		
		YApps.Cookie.Set('YAppsWidgetLG_Count2', Number(YApps.Cookie.Get('YAppsWidgetLG_Count2')) + 1, {path: '/', domain: '.'+location.host});
	}
	
}, YApps.Widgets.LG.ShowSecond*60*1000);

$(document).on('click', 'a[role="YApps_Widget--Form_Send"][data-appkey="LG"]', function() {
	
	$('.YApps_Widget--Leadgen_Container .YApps_Widget--Form_Personal-Item').each( function(i,e) { $(e).removeClass('YApps_Widget--Form_Personal-Item_Error') });
	$('.YApps_Widget--Leadgen_Container input[name="YApps_Widget--Form_Name-Phone"]').removeClass('YApps_Widget--Form_Error');
	
	YApps.Widgets.LG.SendData = {};
	
	YApps.Widgets.LG.SendData.Id = '%%WIDGET.LG.ID%%';
	YApps.Widgets.LG.SendData.AppName = 'Widgets';
	YApps.Widgets.LG.SendData.EventCategory  = 'Генератор клиентов';
	YApps.Widgets.LG.SendData.EventType  = 'LG';
    YApps.Widgets.LG.SendData.EventAction = 'Отправка данных';
	YApps.Widgets.LG.SendData.EventName = 'YAppsWidgetLG_Send';
	YApps.Widgets.LG.SendData.Phone = ( YApps.FormatPhoneIn($('.YApps_Widget--Leadgen_Container input[name="YApps_Widget--Form_Name-Phone"]').val()).length == 11 ) ? YApps.FormatPhoneIn($('.YApps_Widget--Leadgen_Container input[name="YApps_Widget--Form_Name-Phone"]').val()) : false;
	YApps.Widgets.LG.SendData.Name = ( $('.YApps_Widget--Leadgen_Container input[name="YApps_Widget--Form_Name-Name"]').val() ) ? $('.YApps_Widget--Leadgen_Container input[name="YApps_Widget--Form_Name-Name"]').val() : false;
	YApps.Widgets.LG.SendData.Personal = $('.YApps_Widget--Leadgen_Container input[name="YApps_Widget--Form_Name-Personal"]').val();
	YApps.Widgets.LG.SendData.Communications = $('.YApps_Widget--Leadgen_Container input[name="YApps_Widget--Form_Name-Communications"]').val();
	YApps.Widgets.LG.SendData.Flag = true;
	
	YApps.Widgets.LG.SendData.PiwikVisitorID = YApps.Cookie.GetMatomoID();
    YApps.Widgets.LG.SendData.YandexVisitorID = YApps.Cookie.Get('_ym_uid');
    YApps.Widgets.LG.SendData.GoogleVisitorID = YApps.Cookie.Get('_ga');
	
	
    $('.YApps_Widget--Leadgen_Container .YApps--Form_Personal-Item').each( function(i,e) {
		
		if ( !$(e).hasClass('YApps--Form_Personal-Item_Checked') ) {
			
			YApps.Widgets.LG.SendData.Flag = false;
			$(e).addClass('YApps--Form_Personal-Item_Error');
		}
	});
	
	if ( !YApps.Widgets.LG.SendData.Phone ) {
		
		YApps.Widgets.LG.SendData.Flag = false;
		$('.YApps_Widget--Leadgen_Container input[name="YApps_Widget--Form_Name-Phone"]').addClass('YApps_Widget--Form_Error');
	}
	
	if ( !YApps.Widgets.LG.SendData.Name ) {
		
		YApps.Widgets.LG.SendData.Flag = false;
		$('.YApps_Widget--Leadgen_Container input[name="YApps_Widget--Form_Name-Name"]').addClass('YApps_Widget--Form_Error');
	}
	
	YApps.Widgets.FormCover( 'LG', false );
	
	if ( YApps.Widgets.LG.SendData.Flag ) {
		
		YApps.Widgets.FormCover( 'LG' );
		YApps.AppPushStatN( YApps.Widgets.LG.SendData ).then(
			(success) => {
				
				YApps.Widgets.FormResult('LG', {show: true}, success);
				YApps.Widgets.FormCover( 'LG', false );
				
				YApps.AppPushGoal({
					Category: YApps.Widgets.LG.Goal.Category,
					IDToneCategory: 'special',
					YAIDTone: 'special_submit',
					Action: YApps.Widgets.LG.Goal.Action,
					Name: YApps.Widgets.LG.Goal.Name,
					Yandex: YApps.Widgets.LG.Goal.Yandex,
					CallTouch: {
						Flag: true,
						Phone: YApps.Widgets.LG.SendData.Phone,
						Name: YApps.Widgets.LG.SendData.Name
					}
				});
			},
			(error) => {
				
				YApps.Widgets.FormResult('LG', {show: true}, error);
				YApps.Widgets.FormCover( 'LG', false );
			}
		);
		
		setTimeout( function() {
			
			YApps.Widgets.FormCover( 'LG', false );
			YApps.Widgets.FormReset('LG');
			
		}, YApps.Widgets.ResultTimeout*1000);
	}
	
	YApps.Widgets.FormCover( 'LG', false );
	return false;
});