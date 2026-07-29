YApps.Widgets.CI = {};
YApps.Widgets.CI.Timeout = %%WIDGET.CI.TIMEOUT%%;
YApps.Widgets.CI.Timeout2 = 5;
YApps.Widgets.CI.ShowTimeout = false;
YApps.Widgets.CI.ShowCount = 1;

YApps.Widgets.CI.Goal = {};
YApps.Widgets.CI.Goal.Yandex = 'YApps_Goals-Widgets_CI-Send';
YApps.Widgets.CI.Goal.Category = 'Виджеты';
YApps.Widgets.CI.Goal.Action = 'Вовлечение';
YApps.Widgets.CI.Goal.Name = 'ID: '+'%%WIDGET.CI.ID%%'+'. ';

YApps.Widgets.CI.Current = 'List';
YApps.Widgets.CI.Content = {
    List: {
        title: '%%WIDGET.CI.LIST.TITLE%%',
        text: '%%WIDGET.CI.LIST.TEXT%%',
        level: %%WIDGET.CI.LIST.LEVEL%%
    },
    Model: {
        title: '%%WIDGET.CI.MODEL.TITLE%%',
        text: '%%WIDGET.CI.MODEL.TEXT%%',
        level: %%WIDGET.CI.MODEL.LEVEL%%
    },
    Item: {
        title: '%%WIDGET.CI.ITEM.TITLE%%',
        text: '%%WIDGET.CI.ITEM.TEXT%%',
        level: %%WIDGET.CI.ITEM.LEVEL%%
    }
};

YApps.Widgets.CI.SetContent = function() {

    YApps.Widgets.CI.L = window.location.pathname.split('/').length - 1;
    YApps.Widgets.CI.Current = 'List';
    if ( YApps.Widgets.CI.L > YApps.Widgets.CI.Content.List.level && YApps.Widgets.CI.L <= YApps.Widgets.CI.Content.Model.level ) YApps.Widgets.CI.Current = 'Model';
    if ( YApps.Widgets.CI.L > YApps.Widgets.CI.Content.Model.level && YApps.Widgets.CI.L <= YApps.Widgets.CI.Content.Item.level ) YApps.Widgets.CI.Current = 'Item';
}
YApps.Widgets.CI.PathHandler = function() {
    this.old = window.location.pathname;
    this.Check;

    var that = this;
    var detect = function() {
        if ( that.old!=window.location.pathname ) {
            
            YApps.Widgets.CI.RenderContent();
            that.old = window.location.pathname;
        }
    };
    this.Check = setInterval( function() { detect() }, 50);
}
YApps.Widgets.CI.RenderContent = function() {

    YApps.Widgets.CI.SetContent();
    $('.YApps_Widget--CallInvolve_Container .YApps_Widget--Form_Title').text( YApps.Widgets.CI.Content[YApps.Widgets.CI.Current].title );
    $('.YApps_Widget--CallInvolve_Container .YApps_Widget--Form_Text').text( YApps.Widgets.CI.Content[YApps.Widgets.CI.Current].text );
}

YApps.Widgets.CI.Reset = function() {
	
	$('.YApps_Widget--Form_Personal-Item').each( function(i, e) { YApps.Widgets.ToggleCheck( $(this), true ) });
	$('.YApps_Widget--CallInvolve_Container input[name="YApps_Widget--Form_Name-Phone"]').val('').removeClass('YApps_Widget--Form_Error');
	
	Inputmask({'mask': '+7 (999) 999-99-99', showMaskOnHover: false }).mask('.YApps_Widget--CallInvolve_Container input[name="YApps_Widget--Form_Name-Phone"]');
}
YApps.Widgets.CI.Init = function() {
	
	setTimeout( function() { YApps.Widgets.CI.Reset() }, 500);
    YApps.Widgets.CI.RenderContent();
    YApps.Widgets.CI.PathDetection = new YApps.Widgets.CI.PathHandler();
}

$(document).on('click', 'a[role="YApps_Widget--Form_Send"][data-appkey="CI"]', function() {
	
	$('.YApps_Widget--CallInvolve_Container .YApps_Widget--Form_Personal-Item').each( function(i,e) { $(e).removeClass('YApps_Widget--Form_Personal-Item_Error') });
	$('.YApps_Widget--CallInvolve_Container input[name="YApps_Widget--Form_Name-Phone"]').removeClass('YApps_Widget--Form_Error');
	
	YApps.Widgets.CI.SendData = {};
	
	YApps.Widgets.CI.SendData.Id = '%%WIDGET.CI.ID%%';
	YApps.Widgets.CI.SendData.AppName = 'Widgets';
	YApps.Widgets.CI.SendData.EventCategory  = 'Вовлечение';
	YApps.Widgets.CI.SendData.EventType  = 'CI';
    YApps.Widgets.CI.SendData.EventAction = 'Отправка данных';
	YApps.Widgets.CI.SendData.EventName = 'YAppsWidgetCI_Send';
	YApps.Widgets.CI.SendData.Phone = ( YApps.FormatPhoneIn($('.YApps_Widget--CallInvolve_Container input[name="YApps_Widget--Form_Name-Phone"]').val()).length == 11 ) ? YApps.FormatPhoneIn($('.YApps_Widget--CallInvolve_Container input[name="YApps_Widget--Form_Name-Phone"]').val()) : false;
	YApps.Widgets.CI.SendData.Personal = $('.YApps_Widget--CallInvolve_Container input[name="YApps_Widget--Form_Name-Personal"]').val();
	YApps.Widgets.CI.SendData.Communications = $('.YApps_Widget--CallInvolve_Container input[name="YApps_Widget--Form_Name-Communications"]').val();
	YApps.Widgets.CI.SendData.Flag = true;
	
	YApps.Widgets.CI.SendData.PiwikVisitorID = YApps.Cookie.GetMatomoID();
    YApps.Widgets.CI.SendData.YandexVisitorID = YApps.Cookie.Get('_ym_uid');
    YApps.Widgets.CI.SendData.GoogleVisitorID = YApps.Cookie.Get('_ga');
    
    $('.YApps_Widget--CallInvolve_Container .YApps--Form_Personal-Item').each( function(i,e) {

		if ( !$(e).hasClass('YApps--Form_Personal-Item_Checked') ) {
			
			YApps.Widgets.CI.SendData.Flag = false;
			$(e).addClass('YApps--Form_Personal-Item_Error');
		}
	});
	
	if ( !YApps.Widgets.CI.SendData.Phone ) {
		
		YApps.Widgets.CI.SendData.Flag = false;
		$('.YApps_Widget--CallInvolve_Container input[name="YApps_Widget--Form_Name-Phone"]').addClass('YApps_Widget--Form_Error');
	}
	
	if ( YApps.Widgets.CI.SendData.Flag ) {
		
		YApps.Widgets.FormCover( 'CI' );
		YApps.AppPushStatN( YApps.Widgets.CI.SendData ).then(
			(success) => {
                
                YApps.Widgets.FormResult('CI', {show: true}, success);
				YApps.Widgets.FormCover( 'CI', false );
				
				YApps.AppPushGoal({
					Category: YApps.Widgets.CI.Goal.Category,
					IDToneCategory: 'callback',
					YAIDTone: 'callback_submit',
					Action: YApps.Widgets.CI.Goal.Action,
					Name: YApps.Widgets.CI.Goal.Name,
					Yandex: YApps.Widgets.CI.Goal.Yandex,
					CallTouch: {
						Flag: true,
						Phone: YApps.Widgets.CI.SendData.Phone
					}
				});
			},
			(error) => {
				
				YApps.Widgets.FormCover( 'CI', false );
			}
		);
		
		setTimeout( function() {
			
			YApps.Widgets.FormCover( 'CI', false );
			YApps.Widgets.FormReset('CI');
			
		}, YApps.Widgets.ResultTimeout*1000);
	}
	
	YApps.Widgets.FormCover( 'CI', false );
	return false;
});

YApps.Widgets.CI.Timeout1ID = setTimeout( function() {
		
	if ( !YApps.Cookie.Get('YAppsWidgetCI_Count') || Number(YApps.Cookie.Get('YAppsWidgetCI_Count')) < YApps.Widgets.CI.ShowCount ) {
		
		if ( !YApps.Cookie.Get('YAppsWidgetCI_Count') && YApps.Widgets.ShowStatus ) {
		
			YApps.Cookie.Set('YAppsWidgetCI_Count', 1, {path: '/', domain: '.'+location.host});
			
		} else if ( Number(YApps.Cookie.Get('YAppsWidgetCI_Count')) < YApps.Widgets.CI.ShowCount && YApps.Widgets.ShowStatus ) {
			
			YApps.Cookie.Set('YAppsWidgetCI_Count', Number(YApps.Cookie.Get('YAppsWidgetCI_Count')) + 1, {path: '/', domain: '.'+location.host})
		}
		
		YApps.Helper.StartWidget( 'YApps_Widget--CallInvolve', 'CI' );
	}
	
}, YApps.Widgets.CI.Timeout*1000);

YApps.Widgets.CI.Timeout2ID = setTimeout( function() {
		
	if ( !YApps.Cookie.Get('YAppsWidgetCI_Count2') || Number(YApps.Cookie.Get('YAppsWidgetCI_Count2')) < YApps.Widgets.CI.ShowSecondCount ) YApps.Helper.StartWidget( 'YApps_Widget--CallInvolve', 'CI' );
	if ( !YApps.Cookie.Get('YAppsWidgetCI_Count2') ) {
		
		YApps.Cookie.Set('YAppsWidgetCI_Count2', 1, {path: '\/', domain: '.'+location.host});
		
	} else if ( Number(YApps.Cookie.Get('YAppsWidgetCI_Count2')) < YApps.Widgets.CI.ShowSecondCount ) {
		
		YApps.Cookie.Set('YAppsWidgetCI_Count2', Number(YApps.Cookie.Get('YAppsWidgetCI_Count2')) + 1, {path: '/', domain: '.'+location.host});
	}
	
}, YApps.Widgets.CI.Timeout2*60*1000);






// YApps.Widgets.CI.Show = function( timeout ) {
	
// 	YApps.Widgets.CI.Timeout1ID = setTimeout( function() {
    
//         if ( !YApps.Widgets.ShowStatus ) YApps.Widgets.CI.ShowTimeout = 5000;
// 		YApps.Helper.StartWidget('YApps_Widget--CallInvolve', 'CI');
		
// 	}, timeout);

// 	YApps.Widgets.CI.Timeout1ID = setTimeout( function() {
    
//         if ( !YApps.Widgets.ShowStatus ) YApps.Widgets.CI.ShowTimeout = 5000;
// 		YApps.Helper.StartWidget('YApps_Widget--CallInvolve', 'CI');
		
// 	}, timeout);
// }

// YApps.Widgets.CI.Show( YApps.Widgets.CI.Timeout*1000 );


//$(document).ready( function() { console.log( YApps.Widgets.CI ) });
