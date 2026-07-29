YApps.Calc = {};
YApps.Calc.JData = JSON.parse('%%JSON.MODELS%%');
YApps.Calc.SetPrice = function(jData, mod, point) {

    //console.log( jData.mods[mod].points_disc_values.length );

    var Price = YApps.Formatter(jData.mods[mod].points_values[point]) + ' ₽';
    for (var i in jData.mods[mod].points_disc_values) {

        var DiscLength = 0;
        for (var t in jData.mods[mod].points_disc_values) { DiscLength++ }

        Price += '<small>';
        if (Number(jData.mods[mod].points_disc_values[i][point]) != 0) {
            Price += ' / ' + YApps.Formatter(Number(jData.mods[mod].points_disc_values[i][point])) + ' ₽';
            for (k = 1; k <= DiscLength; k++) Price += '*';
        }
        Price += '</small>';
    }
    $('.YApps_Calc--price_title span[role="YApps_Calc--Price"]').html(Price);
}

YApps.Calc.SetModSelect = function(jData, mod) {

    var htmlSelectMod = '';
    for (var i in jData.mods) {
        htmlSelectMod += '<option data-model="' + jData.mods[i].model_id + '" value="' + jData.mods[i].id + '">' + jData.mods[i].ru_name + '</option>';
    }
    $('select#YApps_Calc--Modification').attr('disabled', false).html(htmlSelectMod);
}

YApps.Calc.SetRange = function(jData, mod) {

    var htmlRangerCaptions = '';
    jData.points_ids.forEach(function(item, i, arr) {
        htmlRangerCaptions += '<div class="YApps_Calc--col ' + ((i == 0) ? 'active' : '') + '" role="YApps_Calc--input_slider-caption" data-id="' + item + '">' + jData.points[item].name + '</div>';
    });
    $('div[role="YApps_Calc--input_slider-caption"]').remove();
    $('.YApps_Calc--slider_container[role="YApps_Calc--Ranger"]').after(htmlRangerCaptions);
    $('input#YApps_Calc--Checkpoint').attr('data-mod', mod);
}

YApps.Calc.RenderMainWorks = function(jData, point) {

    var htmlMainWorks = '';
    jData.mainworks.forEach(function(item, i, arr) {
        htmlMainWorks += '<div class="YApps_Calc--col YApps_Calc--col70 Yapps_Calc--price_name">' + item.ru_name + '</div>';
        htmlMainWorks += '<div class="YApps_Calc--col YApps_Calc--col30 Yapps_Calc--price_value">' + jData.workvalues[Number(item.points_values[point]) - 1].value + '</div>';
        htmlMainWorks += '<hr class="YApps_Calc--hr" />';
    });
    htmlMainWorks += '<div class="YApps_Calc--disclamer">';
    jData.workvalues.forEach(function(item, i, arr) {
        htmlMainWorks += '<strong>"' + item.value + '"</strong> - ' + item.ru_name + '; ';
    });
    htmlMainWorks += '</div>';
    $('.YApps_Calc--price-container[role="YApps_Calc--MainWorks"]').html(htmlMainWorks);
}

YApps.Calc.RenderAddWorks = function(jData) {

    var htmlAddWorks = '';
    jData.addworks.forEach(function(item, i, arr) {
        htmlAddWorks += '<div class="YApps_Calc--col YApps_Calc--col70 Yapps_Calc--price_name">' + item.ru_name + '</div>';
        htmlAddWorks += '<div class="YApps_Calc--col YApps_Calc--col30 Yapps_Calc--price_value">';

        htmlAddWorks += YApps.Formatter(item.price) + ' ₽';
        for (var i in item.price_discount) {

            var DiscLength = 0;
            for (var t in item.price_discount) { DiscLength++ }

            htmlAddWorks += '<small>';
            if (Number(item.price_discount[i]) != 0) {
                htmlAddWorks += ' / ' + YApps.Formatter(item.price_discount[i]) + ' ₽';
                for (var k = 1; k <= DiscLength; k++) htmlAddWorks += '*';
            }
            htmlAddWorks += '</small>';
        }

        htmlAddWorks += '</div>';
        htmlAddWorks += '<hr class="YApps_Calc--hr" />';
    });
    $('.YApps_Calc--price-container[role="YApps_Calc--AddWorks"]').html(htmlAddWorks);

    var htmlDisclamer = jData.disclamer + '<br /><br />';
    jData.discounts.forEach(function(item, i, arr) {
        for (var k = 0; k <= i; k++) htmlDisclamer += '*';
        htmlDisclamer += ' ';
        htmlDisclamer += item.ru_name + '<br />';
    });

    $('.YApps_Calc--disclamer[role="YApps_Calc--disclamer_model"]').html(htmlDisclamer);
}

YApps.Calc.ModRender = function(jData, mod) {

    //console.log( jData );

    // Init Range
    $('input#YApps_Calc--Checkpoint').val(0);
    $('div[role="YApps_Calc--input_slider-caption"]').removeClass('active').first().addClass('active');

    // Set Price
    YApps.Calc.SetPrice(jData, mod, 1);

    // Select Modification
    YApps.Calc.SetModSelect(jData, mod);

    // Slider Range
    YApps.Calc.SetRange(jData, mod);

    // Main Works
    YApps.Calc.RenderMainWorks(jData, 1);

    // Additional Works
    YApps.Calc.RenderAddWorks(jData);
}

YApps.Calc.StartRender = function(jData, model, tag) {

    var htmlCalcRender = '<!-- Yug-Avo Apps :: Maintenance Calculator (Author: Boretscy A) -->';

    //console.log( model );

    htmlCalcRender += '<div class="YApps_Calc--container">';
    htmlCalcRender += '<div class="YApps_Calc--title">Калькулятор технического обслуживания</div>';
    htmlCalcRender += '<div class="YApps_Calc--description">Выберите модель и модификацию</div>';
    htmlCalcRender += '<div class="YApps_Calc--row">';
    htmlCalcRender += '<div class="YApps_Calc--col YApps_Calc--col50">';
    htmlCalcRender += '<select class="YApps_Calc--input_select" id="YApps_Calc--Model">';
    htmlCalcRender += '<option>Выберите модель</option>';

    for (var i in jData) {
        htmlCalcRender += '<option value="' + jData[i].id + '" ';
        if (model == jData[i].id) htmlCalcRender += 'selected';
        htmlCalcRender += '>' + jData[i].ru_name + '</option>';
    }

    htmlCalcRender += '</select>';
    htmlCalcRender += '</div>';
    htmlCalcRender += '<div class="YApps_Calc--col YApps_Calc--col50">';
    htmlCalcRender += '<select class="YApps_Calc--input_select" id="YApps_Calc--Modification" disabled>';
    htmlCalcRender += '<option>Выберите модель</option>';
    htmlCalcRender += '</select>';
    htmlCalcRender += '</div>';
    htmlCalcRender += '</div>';
    htmlCalcRender += '<div class="YApps_Calc--row">';
    htmlCalcRender += '<div class="YApps_Calc--slider_label">Выберите пробег и срок эксплуатации</div>';
    htmlCalcRender += '<div class="YApps_Calc--col YApps_Calc--col70 YApps_Calc--slider_container" role="YApps_Calc--Ranger">';
    htmlCalcRender += '<input type="range" min="' + jData[model].points_ids[0] + '" max="' + jData[model].points_ids.length + '" step="1" value="' + jData[model].points_ids[0] + '" class="YApps_Calc--input_slider" id="YApps_Calc--Checkpoint" data-model="' + jData[model].id + '" data-mod="">';
    htmlCalcRender += '</div>';
    htmlCalcRender += '</div>';
    htmlCalcRender += '<div class="YApps_Calc--row Yapps_Calc--Item" role="YApps_Calc--CheckpointPrice">';
    htmlCalcRender += '<div class="YApps_Calc--row YApps_Calc--price_title"><svg class="YApps_Calc--Icon active" xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps_Calc-Coins"></use></svg> Стоимость технического обслуживания <span role="YApps_Calc--Price"></span></div>';
    htmlCalcRender += '</div>';
    htmlCalcRender += '<div class="YApps_Calc--row Yapps_Calc--Item">';
    htmlCalcRender += '<div class="YApps_Calc--price_title" role="YApps_Calc--MainWorks" data-expand="Y"><svg class="YApps_Calc--Icon" xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps_Calc-RepairTools"></use></svg> Работы по техническому обслуживанию <span class="triangle-down" role="YApps_Calc--Triangle"></span></div>';
    htmlCalcRender += '<div class="YApps_Calc--price-container" role="YApps_Calc--MainWorks">';
    htmlCalcRender += '</div>';
    htmlCalcRender += '</div>';

    htmlCalcRender += '<div class="YApps_Calc--row Yapps_Calc--Item">';
    htmlCalcRender += '<div class="YApps_Calc--price_title" role="YApps_Calc--AddWorks" data-expand="Y"><svg class="YApps_Calc--Icon" xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps_Calc-HandTools"></use></svg> Дополнительные работы <span class="triangle-down" role="YApps_Calc--Triangle"></span></div>';
    htmlCalcRender += '<div class="YApps_Calc--price-container" role="YApps_Calc--AddWorks"></div>';
    htmlCalcRender += '</div>';

    htmlCalcRender += '<div class="YApps_Calc--row Yapps_Calc--Item">';
    htmlCalcRender += '<div class="YApps_Calc--price_title" role="YApps_Calc--Form" data-expand="Y"><svg class="YApps_Calc--Icon active" xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps_Calc-Calendar"></use></svg> Записаться на техническое обслуживание <span class="triangle-up" role="YApps_Calc--Triangle"></span></div>';
    htmlCalcRender += '<div class="YApps_Calc--price-container" role="YApps_Calc--Form">';

    htmlCalcRender += '<div class="YApps_Calc--row">';

    htmlCalcRender += '<div class="YApps_Calc--col YApps_Calc--col50">';
    htmlCalcRender += '<input type="text" name="YApps_Calc--Form_NAME" class="YApps_Calc--Form_Text" placeholder="Ваше имя **" required />';
    htmlCalcRender += '</div>';

    htmlCalcRender += '<div class="YApps_Calc--col YApps_Calc--col50">';
    htmlCalcRender += '<input type="text" name="YApps_Calc--Form_PHONE" class="YApps_Calc--Form_Text" placeholder="Телефон **" required />';
    htmlCalcRender += '</div>';

    htmlCalcRender += '</div>';
    htmlCalcRender += '<div class="YApps_Calc--row">';

    htmlCalcRender += '<div class="YApps_Calc--col YApps_Calc--col50">';
    htmlCalcRender += '<input type="email" name="YApps_Calc--Form_EMAIL" class="YApps_Calc--Form_Text" placeholder="Email" />';
    htmlCalcRender += '</div>';

    htmlCalcRender += '<div class="YApps_Calc--col YApps_Calc--col50">';
    htmlCalcRender += '<input type="date" name="YApps_Calc--Form_DATE" class="YApps_Calc--Form_Text" placeholder="Дата" />';
    htmlCalcRender += '</div>';

    htmlCalcRender += '</div>';

    htmlCalcRender += '<div class="YApps_Calc--col YApps_Calc--col-top30">';
    htmlCalcRender += '<a href="#" class="YApps_Calc--Form_Submit" role="YApps_Calc--Form_Submit">Записаться</a>';
    htmlCalcRender += '</div>';

    htmlCalcRender += '<div class="YApps_Calc--disclamer"><sup>**</sup> - поля, обязательные для заполнения.<br />Отправляя заявку Вы соглашаетесь на обработку персональных данных и рекламную коммуникацию.</div>';

    htmlCalcRender += '</div>';
    htmlCalcRender += '</div>';

    htmlCalcRender += '<div class="YApps_Calc--disclamer" role="YApps_Calc--disclamer_model"></div>';
    htmlCalcRender += '</div>';

    htmlCalcRender += '<!-- / Yug-Avo Apps :: Maintenance Calculator (Author: Boretscy A) -->';

    $(tag).after(htmlCalcRender);

    return Object.keys(jData[model].mods)[0];
}

YApps.Calc.ModelRender = function(jData, model) {

    var htmlCalcRender = '';
    for (var i in jData) {
        htmlCalcRender += '<option value="' + jData[i].id + '" ';
        if (model == jData[i].id) htmlCalcRender += 'selected';
        htmlCalcRender += '>' + jData[i].ru_name + '</option>';
    }
    $('select#YApps_Calc--Model').html(htmlCalcRender);


    return Object.keys(jData[model].mods)[0];
}

YApps.Calc.SearchStart = function() {
	
    var $T = $('YAppsCalc');

    if ( $T.length == 0 ) $T = $('#YAppsCalc');
    if ( $T.length == 0 ) $T = $('.YAppsCalc');
    if ( $T.length == 0 ) $T = $('[href="YAppsCalc"]');
	
	return ( $T.length > 0 ) ? $T : false;
}

$(document).ready(function() {

    if ( YApps.Calc.Tag = YApps.Calc.SearchStart() ) {

        $('head').prepend('<link href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.3.2/flatpickr.css" rel="stylesheet">');

        var StartModel = (typeof $(YApps.Calc.Tag).attr('data-model') != 'undefined') ? $(YApps.Calc.Tag).attr('data-model') : Object.keys(YApps.Calc.JData)[0];
        var mod = YApps.Calc.StartRender(YApps.Calc.JData, StartModel, YApps.Calc.Tag);

        YApps.Calc.ModRender(YApps.Calc.JData[StartModel], mod);

        $.getScript('https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.3.2/flatpickr.min.js', function() {
            $.getScript('https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.3.2/l10n/ru.js', function() {
                var calendar = new flatpickr('input[name="YApps_Calc--Form_DATE"]', {
                    locale: "ru",
                    altInput: true,
                    enableTime: true,
                    time_24hr: true
                });
            });
        });

        if (typeof window.Inputmask != 'undefined') {

            $('input[name="YApps_Calc--Form_PHONE"]').inputmask({ 'mask': '+7 (999) 999-99-99', showMaskOnHover: false });

        } else {

            $.getScript('https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/3.3.4/jquery.inputmask.bundle.min.js', function() {
                $('input[name="YApps_Calc--Form_PHONE"]').inputmask({ 'mask': '+7 (999) 999-99-99', showMaskOnHover: false });
            });
        }
    }
});

$(document).on('change', 'select#YApps_Calc--Model', function() {

    var mod = YApps.Calc.ModelRender(YApps.Calc.JData, $(this).val());
    YApps.Calc.ModRender(YApps.Calc.JData[$(this).val()], mod);
});

$(document).on('change', 'select#YApps_Calc--Modification', function() {

    var model = $(this).children('option:selected').data('model');
    var mod = $(this).val();

    YApps.Calc.SetPrice(YApps.Calc.JData[model], mod, 1);
    YApps.Calc.RenderMainWorks(YApps.Calc.JData[model], 1);

    $('input#YApps_Calc--Checkpoint').val(0);
    $('div[role="YApps_Calc--input_slider-caption"]').removeClass('active').first().addClass('active');
});

$(document).on('click', 'div.YApps_Calc--price_title[data-expand="Y"]', function() {

    $(this).children('span[role="YApps_Calc--Triangle"]').toggleClass('triangle-up triangle-down');
    $('div.YApps_Calc--price-container[role="' + $(this).attr('role') + '"]').slideToggle(500);
    $(this).children('.YApps_Calc--Icon').toggleClass('active');
});


$(document).on('change', 'input#YApps_Calc--Checkpoint', function() {

    var model = $(this).data('model');
    var mod = $(this).data('mod');
    var point = $(this).val();

    $('div[role="YApps_Calc--input_slider-caption"]').removeClass('active');
    $('div[role="YApps_Calc--input_slider-caption"][data-id="' + $(this).val() + '"]').addClass('active');

    YApps.Calc.SetPrice(YApps.Calc.JData[model], mod, point);
    YApps.Calc.RenderMainWorks(YApps.Calc.JData[model], point);
});
/*
$(window).on('beforeunload', function() {

    $('input.YApps_Calc--Form_Text').each(function() {

        if ($(this).val()) YApps_Calc_Sended.Incomplite = true;
    });

    if (!YApps_Calc_Sended.Complite && YApps_Calc_Sended.Incomplite && $('div').is('#YApps_Calc')) {

        YApps.SendData = {};

        YApps.SendData.Name = $('input[name="YApps_Calc--Form_NAME"]').val();
        YApps.SendData.Phone = $('input[name="YApps_Calc--Form_PHONE"]').val();
        YApps.SendData.Email = $('input[name="YApps_Calc--Form_EMAIL"]').val();
        YApps.SendData.ModelID = $('select#YApps_Calc--Model').val();
        YApps.SendData.ModID = $('select#YApps_Calc--Modification').val();
        YApps.SendData.Date = $('input[name="YApps_Calc--Form_DATE"]').val();
        YApps.SendData.AppName = 'Calc';
        YApps.SendData.EventName = 'Незавершенная форма';
        YApps.SendData.EventCategory = 'Калькулятор ТО';

        //console.log( YApps.SendData );
        YApps.AppPushStat(YApps.SendData);

        if (typeof window.ga != 'undefined') ga('send', 'event', YApps.SendData.EventCategory, YApps.SendData.EventAction, YApps.SendData.EventName);
        if (typeof window.Piwik != 'undefined') _paq.push(["trackEvent", YApps.SendData.EventCategory, YApps.SendData.EventAction, YApps.SendData.EventName]);
        if (typeof window.yaCounter%%SITE.YANDEXID%% != 'undefined') yaCounter%%SITE.YANDEXID%%.reachGoal("YApps_Calc--SendForm");
    }

});
*/
$(document).on('click', '.YApps_Calc--Form_Submit', function() {

    $('input.YApps_Calc--Form_Text').attr('readonly', true);
    var formSend = false;

    if ($('input[name="YApps_Calc--Form_NAME"]').val() && $('input[name="YApps_Calc--Form_PHONE"]').val()) {

        formSend = true;
        if ($('input[name="YApps_Calc--Form_EMAIL"]').val())
            if (!YApps.CheckEmail($('input[name="YApps_Calc--Form_EMAIL"]').val())) {

                formSend = false;
                $('input[name="YApps_Calc--Form_EMAIL"]').addClass('error');
            }
    }

    if (formSend) {

        YApps.SendData = {};

        YApps.SendData.Name = $('input[name="YApps_Calc--Form_NAME"]').val();
        YApps.SendData.Phone = $('input[name="YApps_Calc--Form_PHONE"]').val();
        YApps.SendData.Email = $('input[name="YApps_Calc--Form_EMAIL"]').val();
        YApps.SendData.ModelID = $('select#YApps_Calc--Model').val();
        YApps.SendData.ModID = $('select#YApps_Calc--Modification').val();
        YApps.SendData.Date = $('input[name="YApps_Calc--Form_DATE"]').val();
        YApps.SendData.AppName = 'Calc';
        YApps.SendData.EventName = 'Отправка формы';
        YApps.SendData.EventCategory = 'Калькулятор ТО';

        //console.log( YApps.SendData );
        YApps.AppPushStat(YApps.SendData);

        if (typeof window.ga != 'undefined') ga('send', 'event', YApps.SendData.EventCategory, YApps.SendData.EventAction, YApps.SendData.EventName);
        // if (typeof window.Piwik != 'undefined') _paq.push(["trackEvent", YApps.SendData.EventCategory, YApps.SendData.EventAction, YApps.SendData.EventName]);
        if (typeof window.yaCounter%%SITE.YANDEXID%% != 'undefined') yaCounter%%SITE.YANDEXID%%.reachGoal("YApps_Calc--SendForm");

        $('input.YApps_Calc--Form_Text').removeClass('error');

        $(this).parent().parent().slideUp(500).siblings('.YApps_Calc--price_title').html('<svg class="YApps_Calc--Icon" xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#YApps_Calc-Calendar"></use></svg> Спасибо за вашу заявку! Мы свяжемся с вами в ближайшее время. <span class="triangle-down" role="YApps_Calc--Triangle"></span></div>');

        YApps.Calc.Sended.Complite = true;

    } else {

        if (!$('input[name="YApps_Calc--Form_NAME"]').val()) $('input[name="YApps_Calc--Form_NAME"]').addClass('error');
        if (!$('input[name="YApps_Calc--Form_PHONE"]').val()) $('input[name="YApps_Calc--Form_PHONE"]').addClass('error');
    }

    $('input[name="YApps_Calc--Form_NAME"]').attr('readonly', false);
    $('input[name="YApps_Calc--Form_PHONE"]').attr('readonly', false);
    $('input[name="YApps_Calc--Form_EMAIL"]').attr('readonly', false);

    return false;
});