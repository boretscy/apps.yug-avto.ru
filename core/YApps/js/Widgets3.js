let YappsWidgets = {};
YappsWidgets.form_timeout = "%% FORM_TIMEOUT %%";
YappsWidgets.cb_timeout = "%% CB_TIMEOUT %%";
YappsWidgets.lg_timeout_1 = "%% LG_TIMEOUT_1 %%";
YappsWidgets.lg_timeout_2 = "%% LG_TIMEOUT_2 %%";
YappsWidgets.ct_id = "%% CT_ID %%";
YappsWidgets.ct_s = "%% CT_S %%";
YappsWidgets.ya_id = "%% YA_ID %%";
YappsWidgets.css = '%% CSS %%';
YappsWidgets.html = "%% HTML %%";
YappsWidgets.cb = "%% CB %%";
YappsWidgets.lg = "%% LG %%";

let ya_style = document.createElement('style');
ya_style.type = 'text/css';
ya_style.innerHTML = YappsWidgets.css;
document.body.appendChild(ya_style);

let yapps = document.createElement('div');
yapps.className = "yapps";
yapps.innerHTML = YappsWidgets.html;
document.body.append(yapps);

YappsWidgets.eventCalllback = function (e) {
    let el = e.target,
    clearVal = el.dataset.phoneClear,
    pattern = el.dataset.phonePattern,
    matrix_def = "+7 ___ ___ __ __",
    matrix = pattern ? pattern : matrix_def,
    i = 0,
    def = matrix.replace(/\D/g, ""),
    val = e.target.value.replace(/\D/g, "");
    if (clearVal !== 'false' && e.type === 'blur') {
        if (val.length < matrix.match(/([\_\d])/g).length) {
            e.target.value = '';
            return;
        }
    }
    if (def.length >= val.length) val = def;
    e.target.value = matrix.replace(/./g, function (a) {
        return /[_\d]/.test(a) && i < val.length ? val.charAt(i++) : i >= val.length ? "" : a
    });
}
YappsWidgets.phone_inputs = document.querySelectorAll('[data-phone-pattern]');
for (let elem of YappsWidgets.phone_inputs) {
    for (let ev of ['input', 'blur', 'focus']) {
        elem.addEventListener(ev, YappsWidgets.eventCalllback);
    }
}
    
jQuery(".yapps-widgets-buttons-item").click( function() {
    if ( jQuery(this).data('type') == 'widget' ) {
        jQuery('[data-widget-type="'+jQuery(this).data('action')+'"]').css({display: 'block'});
        jQuery('.yapps-widgets-container').css({display: 'flex'});
        jQuery('.yapps-widgets-cover').css({display: 'block'});
        clearTimeout(YappsWidgets.CBTimeout);
        clearTimeout(YappsWidgets.LGTimeout_1);
        clearTimeout(YappsWidgets.LGTimeout_2);
    }
    if ( jQuery(this).data('type') == 'link' ) {
        jQuery('[data-widget-type]').css({display: 'none'});
        jQuery('.yapps-widgets-container').css({display: 'none'});
        jQuery('.yapps-widgets-cover').css({display: 'none'});
        YappsWidgets.LGTimeout_1 = setTimeout(() => {
            if ( jQuery.cookie('LGTimeout_1') != 'Y' && jQuery('[data-widget-type="lg"]').css('display') != 'block' && YappsWidgets.lg ) {
                jQuery('[data-widget-type="cb"]').css({display: 'none'});
                jQuery('.yapps-widgets-container').css({display: 'none'});
                jQuery('.yapps-widgets-cover').css({display: 'none'});
                jQuery('[data-widget-type="lg"]').css({display: 'block'});
                jQuery('.yapps-widgets-container').css({display: 'flex'});
                jQuery('.yapps-widgets-cover').css({display: 'block'});
                jQuery.cookie('LGTimeout_1', 'Y');
            }
        }, YappsWidgets.lg_timeout_1*1000);
        
        YappsWidgets.LGTimeout_2 = setTimeout(() => {
            if ( jQuery.cookie('LGTimeout_2') != 'Y' && jQuery('[data-widget-type="lg"]').css('display') != 'block' && YappsWidgets.lg ) {
                jQuery('[data-widget-type="cb"]').css({display: 'none'});
                jQuery('.yapps-widgets-container').css({display: 'none'});
                jQuery('.yapps-widgets-cover').css({display: 'none'});
                jQuery('[data-widget-type="lg"]').css({display: 'block'});
                jQuery('.yapps-widgets-container').css({display: 'flex'});
                jQuery('.yapps-widgets-cover').css({display: 'block'});
                jQuery.cookie('LGTimeout_2', 'Y');
            }
        }, YappsWidgets.lg_timeout_2*60*1000);
        
        YappsWidgets.CBTimeout = setTimeout(() => {
            if ( jQuery.cookie('CBTimeout') != 'Y' && jQuery('[data-widget-type="cb"]').css('display') != 'block' && YappsWidgets.cb ) {
                jQuery('[data-widget-type="lg"]').css({display: 'none'});
                jQuery('.yapps-widgets-container').css({display: 'none'});
                jQuery('.yapps-widgets-cover').css({display: 'none'});
                jQuery('[data-widget-type="cb"]').css({display: 'block'});
                jQuery('.yapps-widgets-container').css({display: 'flex'});
                jQuery('.yapps-widgets-cover').css({display: 'block'});
                jQuery.cookie('CBTimeout', 'Y');
            }
        }, YappsWidgets.cb_timeout*1000);
        window.open(jQuery(this).data('action'), '_blank');
    }
    return false;
});

jQuery(document).mouseup( function(e){ // событие клика по веб-документу
    var el = jQuery( '.yapps-widget' ); // тут указываем ID элемента
    if ( !el.is(e.target) && el.has(e.target).lengthour === 0 ) { // и не по его дочерним элементам
        jQuery('[data-widget-type]').css({display: 'none'});
        jQuery('.yapps-widgets-container').css({display: 'none'});
        jQuery('.yapps-widgets-cover').css({display: 'none'});
        YappsWidgets.LGTimeout_1 = setTimeout(() => {
            if ( jQuery.cookie('LGTimeout_1') != 'Y' && jQuery('[data-widget-type="lg"]').css('display') != 'block' && YappsWidgets.lg ) {
                jQuery('[data-widget-type="cb"]').css({display: 'none'});
                jQuery('.yapps-widgets-container').css({display: 'none'});
                jQuery('.yapps-widgets-cover').css({display: 'none'});
                jQuery('[data-widget-type="lg"]').css({display: 'block'});
                jQuery('.yapps-widgets-container').css({display: 'flex'});
                jQuery('.yapps-widgets-cover').css({display: 'block'});
                jQuery.cookie('LGTimeout_1', 'Y');
            }
        }, YappsWidgets.lg_timeout_1*1000);
        
        YappsWidgets.LGTimeout_2 = setTimeout(() => {
            if ( jQuery.cookie('LGTimeout_2') != 'Y' && jQuery('[data-widget-type="lg"]').css('display') != 'block' && YappsWidgets.lg ) {
                jQuery('[data-widget-type="cb"]').css({display: 'none'});
                jQuery('.yapps-widgets-container').css({display: 'none'});
                jQuery('.yapps-widgets-cover').css({display: 'none'});
                jQuery('[data-widget-type="lg"]').css({display: 'block'});
                jQuery('.yapps-widgets-container').css({display: 'flex'});
                jQuery('.yapps-widgets-cover').css({display: 'block'});
                jQuery.cookie('LGTimeout_2', 'Y');
            }
        }, YappsWidgets.lg_timeout_2*60*1000);
        
        YappsWidgets.CBTimeout = setTimeout(() => {
            if ( jQuery.cookie('CBTimeout') != 'Y' && jQuery('[data-widget-type="cb"]').css('display') != 'block' && YappsWidgets.cb ) {
                jQuery('[data-widget-type="lg"]').css({display: 'none'});
                jQuery('.yapps-widgets-container').css({display: 'none'});
                jQuery('.yapps-widgets-cover').css({display: 'none'});
                jQuery('[data-widget-type="cb"]').css({display: 'block'});
                jQuery('.yapps-widgets-container').css({display: 'flex'});
                jQuery('.yapps-widgets-cover').css({display: 'block'});
                jQuery.cookie('CBTimeout', 'Y');
            }
        }, YappsWidgets.cb_timeout*1000);
    }
});
jQuery(".yapps-widget-close").click( function() {
    jQuery('[data-widget-type]').css({display: 'none'});
    jQuery('.yapps-widgets-container').css({display: 'none'});
    jQuery('.yapps-widgets-cover').css({display: 'none'});
    YappsWidgets.LGTimeout_1 = setTimeout(() => {
        if ( jQuery.cookie('LGTimeout_1') != 'Y' && jQuery('[data-widget-type="lg"]').css('display') != 'block' && YappsWidgets.lg ) {
            jQuery('[data-widget-type="cb"]').css({display: 'none'});
            jQuery('.yapps-widgets-container').css({display: 'none'});
            jQuery('.yapps-widgets-cover').css({display: 'none'});
            jQuery('[data-widget-type="lg"]').css({display: 'block'});
            jQuery('.yapps-widgets-container').css({display: 'flex'});
            jQuery('.yapps-widgets-cover').css({display: 'block'});
            jQuery.cookie('LGTimeout_1', 'Y');
        }
    }, YappsWidgets.lg_timeout_1*1000);
    
    YappsWidgets.LGTimeout_2 = setTimeout(() => {
        if ( jQuery.cookie('LGTimeout_2') != 'Y' && jQuery('[data-widget-type="lg"]').css('display') != 'block' && YappsWidgets.lg ) {
            jQuery('[data-widget-type="cb"]').css({display: 'none'});
            jQuery('.yapps-widgets-container').css({display: 'none'});
            jQuery('.yapps-widgets-cover').css({display: 'none'});
            jQuery('[data-widget-type="lg"]').css({display: 'block'});
            jQuery('.yapps-widgets-container').css({display: 'flex'});
            jQuery('.yapps-widgets-cover').css({display: 'block'});
            jQuery.cookie('LGTimeout_2', 'Y');
        }
    }, YappsWidgets.lg_timeout_2*60*1000);
    
    YappsWidgets.CBTimeout = setTimeout(() => {
        if ( jQuery.cookie('CBTimeout') != 'Y' && jQuery('[data-widget-type="cb"]').css('display') != 'block' && YappsWidgets.cb ) {
            jQuery('[data-widget-type="lg"]').css({display: 'none'});
            jQuery('.yapps-widgets-container').css({display: 'none'});
            jQuery('.yapps-widgets-cover').css({display: 'none'});
            jQuery('[data-widget-type="cb"]').css({display: 'block'});
            jQuery('.yapps-widgets-container').css({display: 'flex'});
            jQuery('.yapps-widgets-cover').css({display: 'block'});
            jQuery.cookie('CBTimeout', 'Y');
        }
    }, YappsWidgets.cb_timeout*1000);
});

jQuery(".yapps-widget-form-terms").click( function() {
// jQuery(document).on('click', '.yapps-widget-form-terms', function() {
    jQuery(this).find('.yapps-widget-form-terms-check').toggleClass('checked');
    // return false;
});

jQuery(".yapps-widget-form-time-toggle-item").click( function() {
// jQuery(document).on('click', '.yapps-widget-form-time-toggle-item', function() {
    jQuery('.yapps-widget-form-time-toggle-item').removeClass('active');
    jQuery(this).addClass('active');
    if ( jQuery(this).data('action') == 'later' ) {
        jQuery('.yapps-widget-form-time').addClass('active');
        jQuery('input[name="time"]').val( jQuery(this).parent().parent().find('select[name="time-hour"]').val()+':'+jQuery(this).parent().parent().find('select[name="time-minuts"]').val() )
    } else {
        jQuery('.yapps-widget-form-time').removeClass('active');
        jQuery('input[name="time"]').val('now');
    }
});
jQuery(document).on('change', '.yapps-widget[data-widget-type="cb"] select', function() {
    jQuery('.yapps-widget[data-widget-type="cb"] input[name="time"]').val( jQuery('.yapps-widget[data-widget-type="cb"] select[name="time-hour"]').val()+':'+jQuery('.yapps-widget[data-widget-type="cb"] select[name="time-minuts"]').val() )
});

let day, hour, minuts, seconds;
day = Number(jQuery('[data-timer="d"]').data('value'));
hour = Number(jQuery('[data-timer="h"]').data('value'));
minuts = Number(jQuery('[data-timer="m"]').data('value'));
seconds = Number(jQuery('[data-timer="s"]').data('value'));

let timer = setInterval(() => {
    seconds--;
    if ( seconds<0 ) {
        seconds = 59;
        minuts--;
        if ( minuts<0 ) {
            minuts = 59;
            hour--;
            if ( hour<0 ) {
                hour = 23;
                day--
                if ( day < 0 ) {
                    day = 0;
                    hour = 0;
                    minuts = 0;
                    seconds = 0;
                    clearInterval(timer);
                }
            }
        }
    }
    jQuery('[data-timer="d"] .yapps-widget-timer-item-value').text(day);
    jQuery('[data-timer="h"] .yapps-widget-timer-item-value').text(hour);
    jQuery('[data-timer="m"] .yapps-widget-timer-item-value').text(minuts);
    jQuery('[data-timer="s"] .yapps-widget-timer-item-value').text(seconds);
}, 1000);


jQuery(document).on('click', '[role="YappsSendWidgetForm"]', function() {
    let SendData = {};
    let Flag = true;
    let Form = jQuery(this).parent();
    jQuery(Form).find('input').each( function() {
        jQuery(this).removeClass('error');
        SendData[jQuery(this).attr('name')] = jQuery(this).val();
        if ( !SendData[jQuery(this).attr('name')] ) {
            jQuery(this).addClass('error');
            Flag = false;
        }
    });
    jQuery(Form).find('.yapps-widget-form-terms-check').each( function() {
        jQuery(this).removeClass('error');
        if ( !jQuery(this).hasClass('checked') ) {
            jQuery(this).addClass('error');
            jQuery(this).siblings().addClass('error');
            Flag = false;
        }
    });
    if ( Flag ) {
        jQuery(Form).find('.yapps-widget-cover').css({display: 'flex'});
        SendData.Id = jQuery(Form).data('widget');
        SendData.AppName = 'Widgets3';
        SendData.EventName = jQuery(Form).parent().data('event-name');
        SendData.Source = location.href;
        SendData.CT_site_id = YappsWidgets.ct_id;
        SendData.CT_subject = 'Форма виджета '+SendData.EventName;
        SendData.CT_sessionId = window.call_value;
        SendData.CT_fio = SendData['yapps-widget-form-name'];
        SendData.CT_phoneNumber = SendData['yapps-widget-form-phone'].replace(/[^\d;]/g, '');
        SendData.CT_requestUrl = location.href;
        SendData.CT_api_url = 'https://api.calltouch.ru/calls-service/RestAPI/requests/'+YappsWidgets.ct_id+'/register/';
        jQuery.ajax({
            type: 'POST',
            crossDomain: true,
			url: 'https://apps.yug-avto.ru/API/stat/?token=ef6541490c8bb9d481d37020b6a1953e',
			data: SendData,
			success: function(data) {
                if ( JSON.parse( data ).status == 'success' ) {
                    jQuery(Form).find('.yapps-widget-cover').css({display: 'none'});
                    jQuery(Form).find('.yapps-widget-success').css({display: 'flex'});
                } else {
                    jQuery(Form).find('.yapps-widget-cover').css({display: 'none'});
                    jQuery(Form).find('.yapps-widget-error').css({display: 'flex'});
                }
                ym(YappsWidgets.ya_id,'reachGoal',jQuery(Form).data('goal'));
                let ct_site_id = YappsWidgets.ct_id;
                let ct_data = {             
                    fio: SendData['yapps-widget-form-name'],
                    phoneNumber: SendData['yapps-widget-form-phone'].replace(/[^\d;]/g, ''),
                    subject: 'Форма виджета '+SendData.EventName,
                    requestUrl: location.href,
                    sessionId: window['call_value_'+YappsWidgets.ct_s]
                };
                jQuery.ajax({  
                    url: 'https://api.calltouch.ru/calls-service/RestAPI/requests/'+ct_site_id+'/register/',      
                    dataType: 'json',         
                    type: 'POST',          
                    data: ct_data
                });
                setTimeout(() => {
                    jQuery(Form).find('input').each( function() {
                        jQuery(this).removeClass('error');
                        jQuery(this).val('');
                    });
                    jQuery(Form).find('.yapps-widget-form-terms-check').each( function() {
                        jQuery(this).removeClass('error checked');
                    });
                    jQuery(Form).find('.yapps-widget-success').css({display: 'none'});
                    jQuery(Form).find('.yapps-widget-error').css({display: 'none'});
                }, YappsWidgets.form_timeout*1000);
                setTimeout(() => {
                    jQuery('[data-widget-type]').css({display: 'none'});
                    jQuery('.yapps-widgets-container').css({display: 'none'});
                    jQuery('.yapps-widgets-cover').css({display: 'none'});
                }, 5000);
            },
			error: function() {
                jQuery(Form).find('.yapps-widget-cover').css({display: 'none'});
                jQuery(Form).find('.yapps-widget-error').css({display: 'flex'});
                setTimeout(() => {
                    jQuery(Form).find('input').each( function() {
                        jQuery(this).removeClass('error');
                        jQuery(this).val('');
                    });
                    jQuery(Form).find('.yapps-widget-form-terms-check').each( function() {
                        jQuery(this).removeClass('error checked');
                    });
                    jQuery(Form).find('.yapps-widget-success').css({display: 'none'});
                    jQuery(Form).find('.yapps-widget-error').css({display: 'none'});
                }, YappsWidgets.form_timeout*1000);
                setTimeout(() => {
                    jQuery('[data-widget-type]').css({display: 'none'});
                    jQuery('.yapps-widgets-container').css({display: 'none'});
                    jQuery('.yapps-widgets-cover').css({display: 'none'});
                }, 5000);
            }
        });
    }
});

YappsWidgets.LGTimeout_1 = setTimeout(() => {
    if ( jQuery.cookie('LGTimeout_1') != 'Y' && jQuery('[data-widget-type="lg"]').css('display') != 'block' && YappsWidgets.lg ) {
        jQuery('[data-widget-type="cb"]').css({display: 'none'});
        jQuery('.yapps-widgets-container').css({display: 'none'});
        jQuery('.yapps-widgets-cover').css({display: 'none'});
        jQuery('[data-widget-type="lg"]').css({display: 'block'});
        jQuery('.yapps-widgets-container').css({display: 'flex'});
        jQuery('.yapps-widgets-cover').css({display: 'block'});
        jQuery.cookie('LGTimeout_1', 'Y');
    }
}, YappsWidgets.lg_timeout_1*1000);

YappsWidgets.LGTimeout_2 = setTimeout(() => {
    if ( jQuery.cookie('LGTimeout_2') != 'Y' && jQuery('[data-widget-type="lg"]').css('display') != 'block' && YappsWidgets.lg ) {
        jQuery('[data-widget-type="cb"]').css({display: 'none'});
        jQuery('.yapps-widgets-container').css({display: 'none'});
        jQuery('.yapps-widgets-cover').css({display: 'none'});
        jQuery('[data-widget-type="lg"]').css({display: 'block'});
        jQuery('.yapps-widgets-container').css({display: 'flex'});
        jQuery('.yapps-widgets-cover').css({display: 'block'});
        jQuery.cookie('LGTimeout_2', 'Y');
    }
}, YappsWidgets.lg_timeout_2*60*1000);

YappsWidgets.CBTimeout = setTimeout(() => {
    if ( jQuery.cookie('CBTimeout') != 'Y' && jQuery('[data-widget-type="cb"]').css('display') != 'block' && YappsWidgets.cb ) {
        jQuery('[data-widget-type="lg"]').css({display: 'none'});
        jQuery('.yapps-widgets-container').css({display: 'none'});
        jQuery('.yapps-widgets-cover').css({display: 'none'});
        jQuery('[data-widget-type="cb"]').css({display: 'block'});
        jQuery('.yapps-widgets-container').css({display: 'flex'});
        jQuery('.yapps-widgets-cover').css({display: 'block'});
        jQuery.cookie('CBTimeout', 'Y');
    }
}, YappsWidgets.cb_timeout*1000);

setTimeout(() => {
    setInterval(() => {
        jQuery('.yapps-widgets-buttons-item[data-action="cb"]').addClass('show');
        setTimeout(() => {
            jQuery('.yapps-widgets-buttons-item[data-action="cb"]').removeClass('show');
        }, 3000);
    }, 15000);
}, 15000);



// $(document).mouseup( function(e){ // событие клика по веб-документу
//     var div = $( "#popup" ); // тут указываем ID элемента
//     if ( !div.is(e.target) // если клик был не по нашему блоку
//         && div.has(e.target).length === 0 ) { // и не по его дочерним элементам
//         div.hide(); // скрываем его
//     }
// });