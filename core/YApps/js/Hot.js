YApps.Hot = {}; YApps.Hot.JData = {}; YApps.Hot.Swiper = {};
YApps.Hot.DCs = false; YApps.Hot.Models = false; YApps.Hot.Tag = false; YApps.Hot.Title = false;
YApps.Hot.JData.Result = %%JSON.RESULT%%;
YApps.Hot.JData.Slider = %%JSON.SLIDER%%;
YApps.Hot.JData.DCs = %%JSON.DCS%%;
YApps.Hot.JData.Models = %%JSON.MODELS%%;
YApps.Hot.JData.Settings = %%JSON.SETTINGS%%;
YApps.Hot.JData.ModelsCounts = [];


YApps.Hot.Html = '%%HOT.STARTHTML%%';
YApps.Hot.Svg = '%%HOT.SVG%%';

YApps.Hot.Item = {};
YApps.LoadScripts( {Inputmask: false, MobileDetect: true} );
YApps.Hot.Init = function() {
    
	if ( YApps.Hot.Tag = YApps.Hot.SearchStart() ) {

        if ( Object.keys(YApps.Hot.JData.Result).length > 0) {
            
            YApps.LoadScripts( {MobileDetect: true, Swiper: false} );
            
            YApps.Hot.Title = $(YApps.Hot.Tag).data('title') || false;

            // Ой костыль!!!
            YApps.Hot.ViewBlock = $(YApps.Hot.Tag).data('block') || false;

            $(YApps.Hot.Tag).after( YApps.Hot.Html );
            $('div#YApps_SVG').append(YApps.Hot.Svg);
            
            YApps.Hot.ClearItems();
            
            if ( !!YApps.Hot.JData.Slider ) YApps.Hot.RenderSlider();
            
            if ( !YApps.Hot.ViewBlock ) { // Ой костыль!!!
                
                YApps.Hot.RenderDCs();
                YApps.Hot.RenderModels();
                YApps.Hot.RenderBannerList();
                YApps.Hot.RenderItems();

                if ( !!location.hash ) YApps.Hot.Hash = location.hash.replace(/^#/,'').split('_');
                
                // switch ( location.hash.split('/')[1] ) {
            
                //     case 'yappshot-model':
                //         YApps.Hot.HashModel = YApps.Hot.FindModel( location.hash.split('/')[2] );
                //         if ( YApps.Hot.HashModel ) YApps.Hot.ShowItemsByModel( YApps.Hot.HashModel );
                //         break;
                    
                //     case 'yappshot-dc':
                //         YApps.Hot.HashDC = YApps.Hot.FindDC( location.hash.split('/')[2] );
                //         if ( YApps.Hot.HashDC ) YApps.Hot.ShowItemsByDC( YApps.Hot.HashDC );
                //         break;

                //     case 'yappshot-item':
                //         YApps.Hot.HashItem = location.hash.split('/')[2] || false;
                //         YApps.Hot.ShowItem( YApps.Hot.HashItem );
                //         break;
                // }
            }
        }
	}
}

YApps.Hot.FindModel = function( key ) {
	
	for ( var i in YApps.Hot.JData.Models ) if ( YApps.Hot.JData.Models[i].ru_name == key.replace('%20', ' ') ) return Number(YApps.Hot.JData.Models[i].id);
	return false;
}

YApps.Hot.FindDC = function( key ) {
	
	for ( var i in YApps.Hot.JData.DCs ) if ( YApps.Hot.JData.DCs[i].url_key == key.replace('%20', ' ') ) return Number(YApps.Hot.JData.DCs[i].id);
	return false;
}

YApps.Hot.ClearItems = function() {
	
	var D = false, M = false;
	
	if ( typeof $(YApps.Hot.Tag).data('dc') != 'undefined' ) D = String($(YApps.Hot.Tag).data('dc')).split(',');
    if ( typeof $(YApps.Hot.Tag).data('model') != 'undefined' ) M = String($(YApps.Hot.Tag).data('model')).split(',');
    if (YApps.Hot.Title) $('div.YApps_Hot--Title').text(YApps.Hot.Title);

    // Ой костыль!!!!
    if ( !!YApps.Hot.ViewBlock ) $('div.YApps_Hot--Slider').siblings().remove();
    if ( !!YApps.Hot.ViewBlock && !YApps.Hot.JData.Slider ) $('div.YApps_Hot--Container').html('');
	
	if ( D ) {
		
		for ( var i in YApps.Hot.JData.Result ) if ( !$.inArray(Number(YApps.Hot.JData.Result[i].dc_id, D)) ) delete YApps.Hot.JData.Result[i];
		for ( var i in YApps.Hot.JData.DCs ) if ( !$.inArray(Number(YApps.Hot.JData.DCs[i].id), D) ) delete YApps.Hot.JData.DCs[i];
		for ( var i in YApps.Hot.JData.Result ) if ( !$.inArray(Number(YApps.Hot.JData.Result[i].model_id), Object.keys(YApps.Hot.JData.Models)) ) delete YApps.Hot.JData.Models[Number(YApps.Hot.JData.Result[i].model_id)];
	}
	
	if ( M ) {
		
        for ( var i in YApps.Hot.JData.Result ) if ( !$.inArray(Number(YApps.Hot.JData.Result[i].model_id), M) ) delete YApps.Hot.JData.Result[i];
		for ( var i in YApps.Hot.JData.Models ) if ( !$.inArray(Number(YApps.Hot.JData.Models[i].id), M) ) delete YApps.Hot.JData.Models[i];
	}
	
	YApps.Hot.JData.ModelsCounts = [];
	for ( var i in YApps.Hot.JData.Result ) YApps.Hot.JData.ModelsCounts[String(YApps.Hot.JData.Result[i].model_id)] = ( YApps.Hot.JData.ModelsCounts[String(YApps.Hot.JData.Result[i].model_id)] ) ? YApps.Hot.JData.ModelsCounts[String(YApps.Hot.JData.Result[i].model_id)] + 1 : 1;
	
	YApps.Hot.JData.ResultArr = [];
	for ( var i in YApps.Hot.JData.Result ) { YApps.Hot.JData.ResultArr.push(YApps.Hot.JData.Result[i]) }
	YApps.Hot.JData.ResultArr.sort(function(a, b) {
		return a['spec_price'] - b['spec_price'];
	});
}

YApps.Hot.SearchStart = function() {
	
    var $T = $('YAppsHot');

    if ( $T.length == 0 ) $T = $('#YAppsHot');
    if ( $T.length == 0 ) $T = $('.YAppsHot');
    if ( $T.length == 0 ) $T = $('[href="YAppsHot"]');
	
	return ( $T.length > 0 ) ? $T : false;
}

YApps.Hot.StartSwiper = function() {
    
    YApps.Hot.Swiper.Thumbs = new Swiper('.YApps_Hot--Item_View--Info_Slider-Thumbs', {
        spaceBetween: 5,
        slidesPerView: 5,
        freeMode: true,
    });
	YApps.Hot.Swiper.Top = new Swiper('.YApps_Hot--Item_View--Info_Slider-Top', {
        navigation: {
          nextEl: '.YApps_Hot--Item_View--Info_Slider-Top .swiper-button-next',
          prevEl: '.YApps_Hot--Item_View--Info_Slider-Top .swiper-button-prev',
        },
        spaceBetween: 0,
        thumbs: {
            swiper: YApps.Hot.Swiper.Thumbs
        }
    });
}

YApps.Hot.RenderSlider = function() {

    if ( !!YApps.Hot.JData.Slider ) {
        var html = '<div class="swiper-wrapper">', title;
        YApps.Hot.JData.Slider.forEach( function(item, i, arr) {
            if ( typeof YApps.Hot.JData.Result[item] == 'object' ) {

                title = YApps.Hot.JData.Models[YApps.Hot.JData.Result[item].model_id].ru_name+' '+YApps.Hot.JData.Result[item].complectation+', '+YApps.Hot.JData.Result[item].year

                html += '<div class="swiper-slide" role="YApps_Hot--Show_Item" data-item="'+YApps.Hot.JData.Result[item].id+'">';
                html += '<img src="https://apps.yug-avto.ru'+YApps.Hot.JData.Result[item].images[0].url+'" />';
                html += '<div class="YApps_Hot--Slider_Title">'+title+'</div>';
                /* html += '<div class="YApps_Hot--Slider_Price">'+YApps.Formatter(Number(YApps.Hot.JData.Result[item].spec_price))+' ₽</div>';*/ 
                html += '<div class="YApps_Hot--Slider_DC">'+YApps.Hot.JData.DCs[YApps.Hot.JData.Result[item].dc_id].ru_name.replace(' PKW', '').replace(' NFZ', '')+'</div>';
                html += '</div>';
            }
        });
        html += '</div>';
        html += '<div class="swiper-button-next"></div>';
        html += '<div class="swiper-button-prev"></div>';
        
        $('.YApps_Hot--Slider').html( html );
        
        setTimeout( function () {
            
            // var slides = ( YApps.MobileDetect.mobile() ) ? 0 : %%JSON.SLIDES%%;
            // YApps.Hot.Slider = new Swiper('.YApps_Hot--Slider', {
            //     navigation: {
            //         nextEl: '.YApps_Hot--Slider .swiper-button-next',
            //         prevEl: '.YApps_Hot--Slider .swiper-button-prev',
            //     },
            //     spaceBetween: 15,
            //     slidesPerView: slides,
            //     loop: true,
            //     autoplay: {
            //         delay: 2500,
            //         disableOnInteraction: false,
            //     }
            // });
        }, 1000);
    }
}
YApps.Hot.RenderDC = function( jData ) {
	
	return '<li><a href="#" role="YApps_Hot--ToggleDC" data-id="'+jData.id+'">'+jData.ru_name.replace(' PKW', '').replace(' NFZ', '')+' ('+jData.count+')'+'</a></li>';
}
YApps.Hot.RenderDC_M = function( jData ) {
	
	return '<option value="'+jData.id+'">'+jData.ru_name+'</option>';
}

YApps.Hot.RenderDCs = function() {
	
	
	if ( Object.keys(YApps.Hot.JData.DCs).length <= 1 ) {
		
		$('div.YApps_Hot--DCs').remove();
		$('div.YApps_Hot--DCs_Mobile').remove();
		return true;
	}
	
	var h = ''; var hm = '';
	for ( var i in YApps.Hot.JData.DCs ) {
		
		if ( Number(YApps.Hot.JData.DCs[i].count) > 0 ) {
			h += YApps.Hot.RenderDC( YApps.Hot.JData.DCs[i] );
			hm += YApps.Hot.RenderDC_M( YApps.Hot.JData.DCs[i] );
		}
	}
	
	$('div.YApps_Hot--DCs ul').prepend( h );
	$('div.YApps_Hot--DCs_Mobile select').append( hm );
}

YApps.Hot.RenderModel = function( jData ) {
	
	var h = '';
	
	if ( YApps.Hot.JData.ModelsCounts[jData.id] > 0 ) {
	
		h += '<li style="width: ';
		h += 100 / Object.keys(YApps.Hot.JData.Models).length;
		h += '%">';
		h += '<a href="#" role="YApps_Hot--ToggleModel" data-id="'+jData.id+'">';
		h += '<img src="'+jData.image_link+'" />';
		h += '<div class="YApps_Hot--Model_Name">'+jData.ru_name+'</div>';
		h += '<div class="YApps_Hot--Model_Count">'+YApps.Hot.JData.ModelsCounts[Number(jData.id)]+'</div>';
		h += '</a>';
		h += '</li>';
	}
	
	return h;
}

YApps.Hot.RenderModel_M = function( jData ) {
	
	return '<option value="'+jData.id+'">'+jData.ru_name+' - ('+jData.count+')</option>';
}

YApps.Hot.RenderModels = () => {
	
	if ( Object.keys(YApps.Hot.JData.Models).length <= 1 ) {
		
		$('div.YApps_Hot--Models').remove();
		$('div.YApps_Hot--Models_Mobile').remove();
		return true;
	}
	
	var h = ''; var hm = '';
	for ( var i in YApps.Hot.JData.Models ) {

		h += YApps.Hot.RenderModel( YApps.Hot.JData.Models[i] );
		hm += YApps.Hot.RenderModel_M( YApps.Hot.JData.Models[i] );
	}
	
	console.log(h)

	$('div.YApps_Hot--Models ul').html( h );
	$('div.YApps_Hot--Models_Mobile select').append( hm );
}

YApps.Hot.RenderBannerList = function() {
	
	if ( YApps.Hot.JData.Settings.banner_list == '' ) {
		
		$('div.YApps_Hot--Banner_List').remove();
		
	} else {
	
		var h = '<a href="'+YApps.Hot.JData.Settings.banner_list_link+'" target="_blank">';
		h += '<style>.YApps_Hot--Banner {background:url(https://apps.yug-avto.ru'+YApps.Hot.JData.Settings.banner_list+') no-repeat;}</style>';
		h += '<style>@media (max-width:479px) { .YApps_Hot--Banner {background:url(https://apps.yug-avto.ru'+YApps.Hot.JData.Settings.banner_list_m+') no-repeat;} }</style>';
		h += '<div class="YApps_Hot--Banner"></div>';
		h += '</a>';
		
		$('div.YApps_Hot--Banner_List').html( h );
	}
}

YApps.Hot.RenderItem = function( jData ) {
	
	jData.Title = YApps.Hot.JData.Models[jData.model_id].ru_name+', '+jData.engine_volume+', '+jData.gearbox+', '+jData.complectation;
	YApps.Hot.JData.Phone = ( window['calltouch_phone_'+YApps.Hot.JData.Settings.calltouch_sess] ) ? YApps.FormatPhone( window['calltouch_phone_'+YApps.Hot.JData.Settings.calltouch_sess] ) : YApps.FormatPhone( YApps.Hot.JData.DCs[jData.dc_id].phone );
	
	
	var h = '';
	h += '<div class="YApps_Hot--Item" data-dc="'+jData.dc_id+'" data-model="'+jData.model_id+'" data-item="'+jData.id+'">';
	h += '<div class="YApps_Hot--Item_Image">';
	h += '<a href="#" role="YApps_Hot--Show_Item" data-title="'+jData.Title+'" data-price="'+YApps.Formatter(Number(jData.spec_price))+' ₽" data-dc="'+jData.dc_id;
	h += '" data-model="'+jData.model_id+'" data-item="'+jData.id+'">';
	h += '<img src="https://apps.yug-avto.ru'+jData.images[0].url+'" /></a></div>';
	h += '<div class="YApps_Hot--Item_CenterBlock">';
	h += '<div class="YApps_Hot--Item_Title"><a href="#" role="YApps_Hot--Show_Item" data-title="'+jData.Title+'" data-price="'+YApps.Formatter(Number(jData.spec_price))+' ₽"';
	h += ' data-dc="'+jData.dc_id+'" data-model="'+jData.model_id+'" data-item="'+jData.id+'">'+jData.Title+'</a></div>';
	h += '<div class="YApps_Hot--Item_Text">Двигатель: '+jData.engine_volume+' л., '+jData.engine_power+' л.с., '+jData.engine_type+';';
	h += '<br> Цвет кузова: '+jData.color+'; Год: '+jData.year+'</div></div>';
	h += '<div class="YApps_Hot--Item_RightBlock">';
	// h += '<span class="YApps_Hot--Item_OldPrice"><strike>'+YApps.Formatter(Number(jData.price))+' ₽</strike></span>';
	// h += '<span class="YApps_Hot--Item_NewPrice"><strong>'+YApps.Formatter(Number(jData.spec_price))+' ₽</strong></span>';
	h += '<span class="YApps_Hot--Item_NewPrice"><strong>Цена по запросу</strong></span>';
	h += '<div class="YApps--Clear" style="height: 10px;"></div>';
	h += '<a href="tel:'+YApps.Hot.JData.Phone+'" class="YApps_Hot--Button" data-title="'+jData.Title+'" data-price="'+YApps.Formatter(Number(jData.price))+' ₽" data-dc="'+jData.dc_id+'" data-model="'+jData.model_id+'" data-item="'+jData.id+'" style="margin-right: 10px">Позвонить</a>';
	h += '<a href="#" class="YApps_Hot--Button" role="YApps_Hot--Show_Item" data-title="'+jData.Title+'" data-price="'+YApps.Formatter(Number(jData.price))+' ₽" data-dc="'+jData.dc_id+'" data-model="'+jData.model_id+'" data-item="'+jData.id+'">'+YApps.Hot.JData.Settings.button_shorttext+'</a>';
	h += '</div></div><div class="YApps--Clear"></div>';
	
	return h;
}
YApps.Hot.RenderItems = function() {
	
	var h = '';
	YApps.Hot.JData.ResultArr.forEach( function(item, i, arr) { h += YApps.Hot.RenderItem( item ) });
	
	$('div.YApps_Hot--Items').html( h );
}

YApps.Hot.RenderSwiper = function( jData ) {
	
	return '<div class="swiper-slide" style="background-image:url(https://apps.yug-avto.ru'+jData.url+')"></div>'
}

YApps.Hot.RenderBannersItem = function() {
	
	var h = '';
	h += '<li><a href="'+YApps.Hot.JData.Settings.banner_item1_link+'" target="_blank"><img src="https://apps.yug-avto.ru'+YApps.Hot.JData.Settings.banner_item1+'" /></a></li>';
	h += '<li><a href="'+YApps.Hot.JData.Settings.banner_item2_link+'" target="_blank"><img src="https://apps.yug-avto.ru'+YApps.Hot.JData.Settings.banner_item2+'" /></a></li>';
	h += '<li><a href="'+YApps.Hot.JData.Settings.banner_item3_link+'" target="_blank"><img src="https://apps.yug-avto.ru'+YApps.Hot.JData.Settings.banner_item3+'" /></a></li>';
	h += '<li><a href="'+YApps.Hot.JData.Settings.banner_item4_link+'" target="_blank"><img src="https://apps.yug-avto.ru'+YApps.Hot.JData.Settings.banner_item4+'" /></a></li>';
	
	return h;
}

YApps.Hot.ShowThanks = function( res = true) {
	
	$('.YApps_Hot--Item_View--Info_Form input').removeClass('error');
	if ( res ) $('.YApps_Hot--Item_View--Info_Form').hide();
	( res ) ? $('.YApps_Hot--Item_View--Info_Form-Thanks').fadeIn(300) : $('.YApps_Hot--Item_View--Info_Form-Error').fadeIn(300);
}
YApps.Hot.ShowForm = function() {
	
	$('.YApps_Hot--Item_View--Info_Form input').removeClass('error');
	$('.YApps_Hot--Item_View--Info_Form').fadeIn(300);
	$('.YApps_Hot--Item_View--Info_Form-Thanks').hide(300);
	$('.YApps_Hot--Item_View--Info_Form-Error').hide(300);
}

YApps.Hot.ShowItem = function( jData ) {
    
    if ( !!YApps.Hot.ViewBlock ) window.location = YApps.Hot.JData.Settings.default_url+'#/yappshot-item/'+jData.id;

	YApps.Hot.ActiveItem = {};
	YApps.Hot.ActiveItem.id = jData.id;
	// YApps.Hot.ActiveItem.title = YApps.Hot.JData.Settings.brand_name+' '+jData.Title+', '+YApps.Formatter(Number(jData.spec_price))+' ₽';
	YApps.Hot.ActiveItem.title = YApps.Hot.JData.Settings.brand_name+' '+jData.Title;
	
	var h = '';
    jData.images.forEach( function(item, i, arr) { h += YApps.Hot.RenderSwiper( item );} );
	$('div.YApps_Hot--Item_View--Info_Slider-Top div.swiper-wrapper').html( h );
	$('div.YApps_Hot--Item_View--Info_Slider-Thumbs div.swiper-wrapper').html( h );
	
	$('td[role="YApps_Hot--Year"]').text( jData.year );
	$('td[role="YApps_Hot--Engine"]').html( jData.engine_volume+' л., '+jData.engine_power+' л.с., '+jData.engine_type );
	$('td[role="YApps_Hot--Gearbox"]').text( jData.gearbox );
	$('td[role="YApps_Hot--Color"]').text( jData.color );
	$('td[role="YApps_Hot--Vin"]').text( jData.vin );
	h = '';
	if ( YApps.MobileDetect.mobile() ) {
		h += '<a href="#" role="YApps_Hot--BuildRoute" data-lat="'+YApps.Hot.JData.DCs[jData.dc_id].coords_lat+'" data-lon="'+YApps.Hot.JData.DCs[jData.dc_id].coords_lon+'" data-name="'+YApps.Hot.JData.DCs[jData.dc_id].ru_name.replace(' PKW', '').replace(' NFZ', '')+'" data-dc="'+jData.dc_id+'">'+YApps.Hot.JData.DCs[jData.dc_id].ru_name.replace(' PKW', '').replace(' NFZ', '')+'</a> '
	} else {
		h += '<a href="'+YApps.Hot.JData.DCs[jData.dc_id].link+'">'+YApps.Hot.JData.DCs[jData.dc_id].ru_name.replace(' PKW', '').replace(' NFZ', '')+'</a>';
	}
	$('td[role="YApps_Hot--DC"]').html( h );
	
	YApps.Hot.JData.Phone = ( window['calltouch_phone_'+YApps.Hot.JData.Settings.calltouch_sess] ) ? YApps.FormatPhone( window['calltouch_phone_'+YApps.Hot.JData.Settings.calltouch_sess] ) : YApps.FormatPhone( YApps.Hot.JData.DCs[jData.dc_id].phone );
	
	h = '';
	h += '<a href="tel:'+YApps.Hot.JData.Phone+'">';
	h += '<svg xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps_Hot--Phone"></use></svg> ';
	h += YApps.Hot.JData.Phone+'</a>';
	$('div.YApps_Hot--Item_View--Info_Phone a').remove();
	$('div.YApps_Hot--Item_View--Info_Phone p:last').after( h );
	
	$('a.YApps_Hot--Form_Button').text( YApps.Hot.JData.Settings.button_longtext );
	$('a.YApps_Hot--Form_Button').attr( 'data-id', jData.id );
	
	if ( YApps.Hot.JData.Settings.banner_item1 == '' ) {
		
		$('div.YApps_Hot--Item_View--Banners').remove();
		
	} else {
		
		$('div.YApps_Hot--Item_View--Banners ul').html( YApps.Hot.RenderBannersItem() );
	}
	
	$('div.YApps_Hot--Item_View--Complectation_Title span').text( jData.complectation );
	$('div.YApps_Hot--Item_View--Complectation ul').remove();
	$('div.YApps_Hot--Item_View--Complectation_Title').after( jData.complect_list );
	$('div.YApps_Hot--Item_View--Additional').remove();
	
	if ( jData.additional_list != '<ul></ul>' ) {
		
		$('div.YApps_Hot--Item_View--Complectation').after( '<div class="YApps--Clear"></div><div class="YApps_Hot--Item_View--Additional"><div class="YApps_Hot--Item_View--Additional_Title">Дополнительное оборудование</div></div>' );
		$('div.YApps_Hot--Item_View--Additional').append( jData.additional_list );
	
	}
	
	$('.YApps-Hot_Personal a[role="YApps-Hot_Personal"]').attr('href', YApps.Hot.JData.Settings.terms_personal);
	$('.YApps-Hot_Personal a[role="YApps-Hot_Communicarions"]').attr('href', YApps.Hot.JData.Settings.term_communications);
    
    YApps.Hot.Title = $('.YApps_Hot--Title').text();
    if ( !!$('.YApps_Hot--Slider').height() ) $('.YApps_Hot--Item_View').css('top', $('.YApps_Hot--Slider').height()+$('.YApps_Hot--Title').height()+30);

	$('.YApps_Hot--Item_View').fadeIn(300);
	// $('.YApps_Hot--Title').html( YApps.Hot.JData.Settings.brand_name+' '+jData.Title+'<span>'+YApps.Formatter(Number(jData.spec_price))+' ₽ <sup>*</sup></span>' );
	$('.YApps_Hot--Title').html( YApps.Hot.JData.Settings.brand_name+' '+jData.Title+'<span>Цена по запросу <sup>*</sup></span>' );
	
	YApps.Hot.ShowForm();
    
    $('html, body').animate({ scrollTop: $('div.YApps_Hot--Title').offset().top }, 300);
    
	Inputmask({'mask': '+7 (999) 999-99-99', showMaskOnHover: false }).mask('input[name="YApps_Hot--Form_Phone"]');
    YApps.Hot.StartSwiper();
    
    location.hash = '/yappshot-item/'+jData.id;
}

YApps.Hot.CloseItem = function() {
	
	$('.YApps_Hot--Item_View').fadeOut(300, function() { location.hash = '' });
    $('.YApps_Hot--Title').html( YApps.Hot.Title );
	
    // $('html, body').animate({ scrollTop: $('div.YApps_Hot--Container').offset().top-30 }, 300);
}

YApps.Hot.ShowItemsByModel = function(id) {
	
	$('div.YApps_Hot--Item').hide();
	$('div.YApps_Hot--Item[data-model="'+id+'"]').show();
}

YApps.Hot.ShowItemsByDC = function(id) {
	
	$('div.YApps_Hot--Item').hide();
	if ( id == 'All' ) $('div.YApps_Hot--Item').show();
	if ( id != 'All' ) $('div.YApps_Hot--Item[data-dc="'+id+'"]').show();
}

YApps.Hot.RouteBuild = function( id ) {
	
	if ( typeof YApps.Widgets != 'undefined' ) {
		
		if ( YApps.Widgets.NV.ToNav ) {
			
			YApps.Widgets.SN.Set( YApps.Hot.JData.DCs[id].ru_name.replace(' PKW', '').replace(' NFZ', ''), YApps.Hot.JData.DCs[id].coords_lat, YApps.Hot.JData.DCs[id].coords_lon );
			YApps.Widgets.ShowStatus = true;
			YApps.Helper.StartWidget( 'YApps_Widget--SelectNavi', 'SN' );
			
			YApps.AppPushGoal({
				Category: 'Горячие предложения месяца',
				Action: 'Маршрут в '+YApps.Hot.JData.DCs[id].ru_name,
				Name: 'Через виджет',
				Yandex: 'YApps_Goals-Hot_Route',
				CallTouch: { Flag: false}
			});
		}
	
	} else {
		
		if (YApps.MobileDetect.mobile() == 'iPhone' || YApps.MobileDetect.mobile() == 'iPad') {
			setTimeout(function () { window.open('http://maps.apple.com/?sspn='+YApps.Hot.JData.DCs[id].coords_lat+','+YApps.Hot.JData.DCs[id].coords_lon); }, 500);
		} else {
			setTimeout(function () { window.open('https://maps.google.com/?daddr='+YApps.Hot.JData.DCs[id].coords_lat+','+YApps.Hot.JData.DCs[id].coords_lon); }, 500);
		}
		
		window.open('yandexnavi://build_route_on_map?lat_to='+YApps.Hot.JData.DCs[id].coords_lat+'&lon_to='+YApps.Hot.JData.DCs[id].coords_lon);
		
		YApps.AppPushGoal({
			Category: 'Горячие предложения месяца',
			Action: 'Маршрут в '+YApps.Hot.JData.DCs[id].ru_name,
			Name: 'Из приложения',
			Yandex: 'YApps_Goals-Hot_Route',
			CallTouch: { Flag: false}
		})
	}
}


$(document).on('click', '[role="YApps_Hot--Show_Item"]', function() {
	
	YApps.Hot.ShowItem( YApps.Hot.JData.Result[Number($(this).data('item'))] );
	return false;
});
$(document).on('click', '.YApps--Close', function() {
	
    YApps.Hot.CloseItem();
    return false;
});
$(document).on('click', 'a[role="YApps_Hot--ToggleModel"]', function() {
	
	YApps.Hot.ShowItemsByModel( $(this).data('id') );
	return false;
});
$(document).on('change', 'select[role="YApps_Hot--ToggleModel"]', function() {
	
	YApps.Hot.ShowItemsByModel( $(this).val() );
	return false;
});
$(document).on('click', 'a[role="YApps_Hot--ToggleDC"]', function() {
	
	YApps.Hot.ShowItemsByDC( $(this).data('id') );
	$(this).parent().siblings('li').removeClass('active');
	if ( $(this).data('id') != 'All' ) $(this).parent().addClass('active');
	return false;
});

$(document).on('change', 'select[role="YApps_Hot--ToggleDC"]', function() {
	
	YApps.Hot.ShowItemsByDC( $(this).val() );
	return false;
});

$(document).on('click', 'a[role="YApps_Hot--BuildRoute"]', function() {
	
	YApps.Hot.RouteBuild( Number($(this).data('dc')) );
});

$(document).on('click', '.YApps_Hot--Form_Button', function() {

	$('YApps_Hot--Item_View--Info_Form input').attr('readonly', true);
	var formSend = false;

    if ( $('input[name="YApps_Hot--Form_Name"]').val() && $('input[name="YApps_Hot--Form_Phone"]').val() ) formSend = true;
    
    $('.YApps_Hot--Item_View .YApps--Form_Personal-Item').each( function(i,e) {

		if ( !$(e).hasClass('YApps--Form_Personal-Item_Checked') ) {
			
			formSend = false;
			$(e).addClass('YApps--Form_Personal-Item_Error');
		}
	});

	if ( formSend ) {

		YApps.Hot.SendData = {};

		YApps.Hot.SendData.Name = $('input[name="YApps_Hot--Form_Name"]').val();
		YApps.Hot.SendData.Phone = $('input[name="YApps_Hot--Form_Phone"]').val();
		YApps.Hot.SendData.ItemID = $(this).data('id');
		YApps.Hot.SendData.AppName = 'Hot';
		YApps.Hot.SendData.EventName = 'Отправка формы';
		YApps.Hot.SendData.EventCategory = 'Горячие предложения месяца';
		
		YApps.Hot.SendData.PiwikVisitorID = YApps.Cookie.GetMatomoID();
		YApps.Hot.SendData.YandexVisitorID = YApps.Cookie.Get('_ym_uid');
		YApps.Hot.SendData.GoogleVisitorID = YApps.Cookie.Get('_ga');
		
		
		YApps.AppPushStatN( YApps.Hot.SendData ).then(
			(success) => {
				
				$('YApps_Hot--Item_View--Info_Form input').removeClass('error');
				YApps.Hot.ShowThanks();
				
				YApps.AppPushGoal({
					Category: YApps.Hot.SendData.EventCategory,
					IDToneCategory: 'car',
					YAIDTone: 'car_submit',
					Action: YApps.Hot.SendData.EventName,
					Name: 'Send',
					Yandex: 'YApps_Goals-Hot_Send',
					CallTouch: {
						Flag: true,
						Phone: YApps.Hot.SendData.Phone
					}
				});
			},
			(error) => {
				
				YApps.Hot.ShowThanks( false );
			}
		);

	} else {

		if ( !$('input[name="YApps_Hot--Form_Name"]').val() ) $('input[name="YApps_Hot--Form_Name"]').addClass('error');
		if ( !$('input[name="YApps_Hot--Form_Phone"]').val() ) $('input[name="YApps_Hot--Form_Phone"]').addClass('error');
	}

	$('input[name="YApps_Hot--Form_Name"]').attr('readonly', false);
	$('input[name="YApps_Hot--Form_Phone"]').attr('readonly', false);

	return false;
});

$(window).on('load', function() {

    YApps.Hot.Slider = new Swiper('.YApps_Hot--Slider', {
        navigation: {
            nextEl: '.YApps_Hot--Slider .swiper-button-next',
            prevEl: '.YApps_Hot--Slider .swiper-button-prev',
        },
        spaceBetween: 15,
        slidesPerView: ( YApps.MobileDetect.mobile() ) ? 1 : %%JSON.SLIDES%%,
        loop: true,
        autoplay: {
            delay: 3500,
            disableOnInteraction: false,
        }
    });

    switch ( location.hash.split('/')[1] ) {
            
        case 'yappshot-model':
            YApps.Hot.HashModel = YApps.Hot.FindModel( location.hash.split('/')[2] );
            if ( YApps.Hot.HashModel ) YApps.Hot.ShowItemsByModel( YApps.Hot.HashModel );
            break;
        
        case 'yappshot-dc':
            YApps.Hot.HashDC = YApps.Hot.FindDC( location.hash.split('/')[2] );
            if ( YApps.Hot.HashDC ) YApps.Hot.ShowItemsByDC( YApps.Hot.HashDC );
            break;

        case 'yappshot-item':
            YApps.Hot.HashItem = location.hash.split('/')[2] || false;
            if ( YApps.Hot.HashItem ) YApps.Hot.ShowItem( YApps.Hot.JData.Result[YApps.Hot.HashItem] );
            break;
    }
});

window.addEventListener('hashchange', function() { if ( location.hash == '#/' ) YApps.Hot.CloseItem(); });

YApps.Hot.Init();
