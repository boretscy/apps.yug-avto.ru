YApps.Widgets.QZ = {};
YApps.Widgets.QZ.SendData = {};
YApps.Widgets.QZ.SendData = {}
YApps.Widgets.QZ.SendData.SlideCurrent = 1;
YApps.Widgets.QZ.SendData.SlideCount = %%WIDGET.QZ.SLIDES_COUNT%%;

YApps.Widgets.QZ.Goal = {};
YApps.Widgets.QZ.Goal.Yandex = 'YApps_Goals-Widgets_QZ-Send_'+'%%WIDGET.QZ.ID%%';
YApps.Widgets.QZ.Goal.Category = 'Виджеты';
YApps.Widgets.QZ.Goal.Action = 'Квиз';
YApps.Widgets.QZ.Goal.Name = 'ID: '+'%%WIDGET.QZ.ID%%'+'. ';

YApps.Widgets.QZ.Reset = function() {
	
	$('.YApps_Widget--Form_Personal-Item').each( function(i, e) { YApps.Widgets.ToggleCheck( $(this), true ) });
	$('.YApps_Widget--Quiz_Container input[name="YApps_Widget--Form_Name-Name"]').val('').removeClass('YApps_Widget--Form_Error');
	$('.YApps_Widget--Quiz_Container input[name="YApps_Widget--Form_Name-Phone"]').val('').removeClass('YApps_Widget--Form_Error');
	$('.YApps_Widget--Quiz_Container input[name="YApps_Widget--Form_Name-Email"]').val('');
	$('.YApps_Widget--Quiz_Content input').val('');
	
	$('[role="YApps_Widget--Quiz-NextStep"]').show();
	$('[role="YApps_Widget--Quiz-PrevStep"]').hide();
	
	$('[role="YApps_Widget--Quiz-Select_Part"]').removeClass('YApps_Widget--Quiz-Part_Selected').find('use').attr('xlink:href', '#YApps-Widgets_UnSelect');
	$('.YApps_Widget--Quiz_Content input').val();
	
	$('[role="YApps_Widget--Quiz-NextStep"]').removeClass('YApps_Widget--Quiz_Button-Active YApps_Widget--Quiz_Button-DeActive').addClass('YApps_Widget--Quiz_Button-DeActive');
	
	YApps.Widgets.QZ.SendData.SlideCurrent = 1;
	YApps.Widgets.QZ.ToStep( YApps.Widgets.QZ.SendData.SlideCurrent );
	
};
YApps.Widgets.QZ.ToStep = function( s ) {
	
	$('.YApps_Widget--Quiz_Pagination').removeClass('YApps_Widget--Quiz_Pagination-Active').each( function(indx, e) {
		
		if ( indx < s ) $(e).addClass('YApps_Widget--Quiz_Pagination-Active');
	});
	
	$('.YApps_Widget--Quiz_Content').hide();
	$('.YApps_Widget--Quiz_Content[data-step="'+s+'"]').show();
	
	$('.YApps_Widget--Quiz_Content[data-type="1"], .YApps_Widget--Quiz_Content[data-type="2"]').each( function(i,e) {
		
		YApps.Widgets.QZ.SendData[$(e).data('name')] = $(e).find('.YApps_Widget--Quiz-Part_Selected').data('value');
	});
	
	$('.YApps_Widget--Quiz_Content[data-type="3"]').each( function(i,e) {
		
		YApps.Widgets.QZ.SendData[$(e).data('name')] = {};
		$(e).find('.YApps_Widget--Quiz-Part_Selected').each( function(iv,ev){
			
			YApps.Widgets.QZ.SendData[$(e).data('name')][$(ev).data('value')] = $(ev).data('value');
		});
		YApps.Widgets.QZ.SendData[$(e).data('name')] = JSON.stringify(YApps.Widgets.QZ.SendData[$(e).data('name')]);
		
	});
	
	$('.YApps_Widget--Quiz_Content[data-type="4"]').each( function(i,e) {
		
		$(e).find('input').each( function(iv,ev){ YApps.Widgets.QZ.SendData[$(ev).data('name')] = $(ev).val() });
	});
	
	// console.log(YApps.Widgets.QZ.SendData);
}
YApps.Widgets.QZ.Init = function() {
	
	Inputmask({'mask': '+7 (999) 999-99-99', showMaskOnHover: false }).mask('.YApps_Widget--Quiz_Container input[name="YApps_Widget--Form_Name-Phone"]');
}


$(document).on('click', '.YApps_Widget--Quiz_Button-Active[role="YApps_Widget--Quiz-NextStep"]', function() {	
	
	YApps.Widgets.QZ.SendData.SlideCurrent++;
	
	if ( !!$('.YApps_Widget--Quiz_Content[data-step="'+YApps.Widgets.QZ.SendData.SlideCurrent+'"]').attr('required') 
		&& !$('.YApps_Widget--Quiz_Content[data-step="'+YApps.Widgets.QZ.SendData.SlideCurrent+'"] [role="YApps_Widget--Quiz-Select_Part"]').is('.YApps_Widget--Quiz-Part_Selected') )
			$(this).removeClass('YApps_Widget--Quiz_Button-Active YApps_Widget--Quiz_Button-DeActive').addClass('YApps_Widget--Quiz_Button-DeActive');
	
	YApps.Widgets.QZ.ToStep( YApps.Widgets.QZ.SendData.SlideCurrent );
	if ( YApps.Widgets.QZ.SendData.SlideCurrent >= YApps.Widgets.QZ.SendData.SlideCount ) $('[role="YApps_Widget--Quiz-NextStep"]').hide();
	if ( YApps.Widgets.QZ.SendData.SlideCurrent > 1 ) $('[role="YApps_Widget--Quiz-PrevStep"]').show().css("display","inline-block");
	
	return false;
});
$(document).on('click', '[role="YApps_Widget--Quiz-PrevStep"]', function() {
	
	YApps.Widgets.QZ.SendData.SlideCurrent--;
		
	if ( !!$('.YApps_Widget--Quiz_Content[data-step="'+YApps.Widgets.QZ.SendData.SlideCurrent+'"]').attr('required') 
		&& $('.YApps_Widget--Quiz_Content[data-step="'+YApps.Widgets.QZ.SendData.SlideCurrent+'"] .YApps_Widget--Quiz-Part_Selected[role="YApps_Widget--Quiz-Select_Part"]').length==0 )
			$('[role="YApps_Widget--Quiz-NextStep"]').removeClass('YApps_Widget--Quiz_Button-Active YApps_Widget--Quiz_Button-DeActive').addClass('YApps_Widget--Quiz_Button-DeActive');
	
	if ( !!$('.YApps_Widget--Quiz_Content[data-step="'+YApps.Widgets.QZ.SendData.SlideCurrent+'"]').attr('required') 
		&& $('.YApps_Widget--Quiz_Content[data-step="'+YApps.Widgets.QZ.SendData.SlideCurrent+'"] .YApps_Widget--Quiz-Part_Selected[role="YApps_Widget--Quiz-Select_Part"]').length>0 )
			$('[role="YApps_Widget--Quiz-NextStep"]').removeClass('YApps_Widget--Quiz_Button-Active YApps_Widget--Quiz_Button-DeActive').addClass('YApps_Widget--Quiz_Button-Active');
	
	if ( !!!$('.YApps_Widget--Quiz_Content[data-step="'+YApps.Widgets.QZ.SendData.SlideCurrent+'"]').attr('required')  )
		$('[role="YApps_Widget--Quiz-NextStep"]').removeClass('YApps_Widget--Quiz_Button-DeActive YApps_Widget--Quiz_Button-Active').addClass('YApps_Widget--Quiz_Button-Active');
		
	if ( !$('.YApps_Widget--Quiz_Content[data-step="'+YApps.Widgets.QZ.SendData.SlideCurrent+'"] input[required]').val()  )
		$('[role="YApps_Widget--Quiz-NextStep"]').removeClass('YApps_Widget--Quiz_Button-DeActive YApps_Widget--Quiz_Button-Active').addClass('YApps_Widget--Quiz_Button-Active');
	
	YApps.Widgets.QZ.ToStep( YApps.Widgets.QZ.SendData.SlideCurrent );
	( YApps.Widgets.QZ.SendData.SlideCurrent > 1 ) ?  $('[role="YApps_Widget--Quiz-PrevStep"]').show().css("display","inline-block") :  $('[role="YApps_Widget--Quiz-PrevStep"]').hide();
	if ( YApps.Widgets.QZ.SendData.SlideCurrent <= YApps.Widgets.QZ.SendData.SlideCount ) $('[role="YApps_Widget--Quiz-NextStep"]').show().css("display","inline-block");
	
	return false;
});
$(document).on('click', '[role="YApps_Widget--Quiz-Select_Part"]', function() {
	
	var Parent = $(this).parents('.YApps_Widget--Quiz_Content');
	
	$(this).toggleClass('YApps_Widget--Quiz-Part_Selected').find('use').attr('xlink:href', '#YApps-Widgets_Select');
	( $(this).hasClass('YApps_Widget--Quiz-Part_Selected') ) ? $(this).find('use').attr('xlink:href', '#YApps-Widgets_Select') : $(this).find('use').attr('xlink:href', '#YApps-Widgets_UnSelect');
	if ( $(Parent).data('type') != '3') $(this).siblings('[role="YApps_Widget--Quiz-Select_Part"]').removeClass('YApps_Widget--Quiz-Part_Selected').find('use').attr('xlink:href', '#YApps-Widgets_UnSelect');
	
	$('[role="YApps_Widget--Quiz-NextStep"]').removeClass('YApps_Widget--Quiz_Button-Active YApps_Widget--Quiz_Button-DeActive').addClass('YApps_Widget--Quiz_Button-Active');
	
	/*
	if ( !!$(Parent).attr('required') && !$(this).children('.YApps_Widget--Quiz-Part_Selected') )
		$('[role="YApps_Widget--Quiz-NextStep"]').removeClass('YApps_Widget--Quiz_Button-Active').addClass('YApps_Widget--Quiz_Button-DeActive');
		*/
});
$(document).on('input', '.YApps_Widget--Quiz_Content[data-type="4"]', function(e) {
	
	YApps.Widgets.QZ.SendData.Type4InputFlag = ( $(e.target).val().length > 3 );
	
	if ( YApps.Widgets.QZ.SendData.Type4InputFlag ) {
		
		YApps.Widgets.QZ.SendData[$(e.target).data('name')] = $(e.target).val();
		$('.YApps_Widget--Quiz_Content[data-type="4"] input[required]').each( function(iv,ev) {
			
			if ( $(ev).val().length <= 3 ) YApps.Widgets.QZ.SendData.Type4InputFlag = false;
		});
	
	}
	
	( YApps.Widgets.QZ.SendData.Type4InputFlag ) ? $('[role="YApps_Widget--Quiz-NextStep"]').removeClass('YApps_Widget--Quiz_Button-DeActive YApps_Widget--Quiz_Button-Active').addClass('YApps_Widget--Quiz_Button-Active') : $('[role="YApps_Widget--Quiz-NextStep"]').removeClass('YApps_Widget--Quiz_Button-Active YApps_Widget--Quiz_Button-DeActive').addClass('YApps_Widget--Quiz_Button-DeActive');
});

$(document).on('click', 'a[role="YApps_Widget--Form_Send"][data-appkey="QZ"]', function() {
	
	$('.YApps_Widget--Quiz_Container .YApps_Widget--Form_Personal-Item').each( function(i,e) { $(e).removeClass('YApps_Widget--Form_Personal-Item_Error') });
	$('.YApps_Widget--Quiz_Container input[name="YApps_Widget--Form_Name-Phone"]').removeClass('YApps_Widget--Form_Error');
	
	YApps.Widgets.QZ.SendData.Id = '%%WIDGET.QZ.ID%%';
	YApps.Widgets.QZ.SendData.AppName = 'Widgets';
	YApps.Widgets.QZ.SendData.EventCategory  = 'Квиз';
	YApps.Widgets.QZ.SendData.EventType  = 'QZ';
    YApps.Widgets.QZ.SendData.EventAction = 'Отправка данных';
	YApps.Widgets.QZ.SendData.EventName = 'YAppsWidgetQZ_Send';
	YApps.Widgets.QZ.SendData.Phone = ( YApps.FormatPhoneIn($('.YApps_Widget--Quiz_Container input[name="YApps_Widget--Form_Name-Phone"]').val()).length == 11 ) ? YApps.FormatPhoneIn($('.YApps_Widget--Quiz_Container input[name="YApps_Widget--Form_Name-Phone"]').val()) : false;
	YApps.Widgets.QZ.SendData.Name = $('.YApps_Widget--Quiz_Container input[name="YApps_Widget--Form_Name-Name"]').val() || false;
	YApps.Widgets.QZ.SendData.Email = $('.YApps_Widget--Quiz_Container input[name="YApps_Widget--Form_Name-Email"]').val() || false;
	YApps.Widgets.QZ.SendData.Personal = $('.YApps_Widget--Quiz_Container input[name="YApps_Widget--Form_Name-Personal"]').val();
	YApps.Widgets.QZ.SendData.Communications = $('.YApps_Widget--Quiz_Container input[name="YApps_Widget--Form_Name-Communications"]').val();
	YApps.Widgets.QZ.SendData.Flag = true;
	
	YApps.Widgets.QZ.SendData.PiwikVisitorID = YApps.Cookie.GetMatomoID();
    YApps.Widgets.QZ.SendData.YandexVisitorID = YApps.Cookie.Get('_ym_uid');
    YApps.Widgets.QZ.SendData.GoogleVisitorID = YApps.Cookie.Get('_ga');
	
	console.log(YApps.Widgets.QZ.SendData); 
	
	$('.YApps_Widget--Quiz_Container .YApps--Form_Personal-Item').each( function(i,e) {
		
		if ( !$(e).hasClass('YApps--Form_Personal-Item_Checked') ) {
			
			YApps.Widgets.QZ.SendData.Flag = false;
			$(e).addClass('YApps--Form_Personal-Item_Error');
		}
	});
	
	if ( !YApps.Widgets.QZ.SendData.Phone ) {
		
		YApps.Widgets.QZ.SendData.Flag = false;
		$('.YApps_Widget--Quiz_Container input[name="YApps_Widget--Form_Name-Phone"]').addClass('YApps_Widget--Form_Error');
	}
	
	if ( !YApps.Widgets.QZ.SendData.Name ) {
		
		YApps.Widgets.QZ.SendData.Flag = false;
		$('.YApps_Widget--Quiz_Container input[name="YApps_Widget--Form_Name-Name"]').addClass('YApps_Widget--Form_Error');
	}
	
	YApps.Widgets.FormCover( 'QZ', false );
	
	if ( YApps.Widgets.QZ.SendData.Flag ) {
		
		YApps.Widgets.FormCover( 'QZ' );
		YApps.AppPushStatN( YApps.Widgets.QZ.SendData ).then(
			(success) => {
				
				YApps.Widgets.FormResult('QZ', {show: true}, success);
				YApps.Widgets.FormCover( 'QZ', false );
				
				YApps.AppPushGoal({
					Category: YApps.Widgets.QZ.Goal.Category,
					IDToneCategory: 'car-selection',
					IDToneLabel: $('.YApps_Widget--Quiz_Content[data-type="1"] .YApps_Widget--Quiz-Part_Selected').text() || false,
					YAIDTone: 'car-selection_submit',
					Action: YApps.Widgets.QZ.Goal.Action,
					Name: YApps.Widgets.QZ.Goal.Name,
					Yandex: YApps.Widgets.QZ.Goal.Yandex,
					CallTouch: {
						Flag: true,
						Phone: YApps.Widgets.QZ.SendData.Phone,
						Name: YApps.Widgets.QZ.SendData.Name
					}
				});
			},
			(error) => {
				
				YApps.Widgets.FormResult('QZ', {show: true}, error);
				YApps.Widgets.FormCover( 'QZ', false );
			}
		);
		
		setTimeout( function() {
			
			YApps.Widgets.FormCover( 'QZ', false );
			YApps.Widgets.FormReset('QZ');
			
		}, YApps.Widgets.ResultTimeout*1000);
	}
	
	YApps.Widgets.FormCover( 'QZ', false );
	return false;
});
