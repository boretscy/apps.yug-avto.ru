YApps.Widgets.MS = {};
YApps.Widgets.MS.Reset = function() {};

YApps.Widgets.MS.Goal = {};
YApps.Widgets.MS.Goal.Category = 'Виджеты';
YApps.Widgets.MS.Goal.EventAction = 'Переход во внешнее приложение';

YApps.Widgets.MS.IdleTimeout = %%WIDGET.MS.IDLE_TIMEOUT%%;
YApps.Widgets.MS.IdleTimerID = null;



$(document).on('click', '[role="YApps_Widget--MS_ToMess"]', function() {

    YApps.AppPushGoal({
        Category: YApps.Widgets.MS.Goal.Category,
        Action: YApps.Widgets.MS.Goal.EventAction,
        Name: $(this).data('name'),
        Yandex: $(this).data('goal'),
        CallTouch: {
            Flag: false,
        }
    });
});

$(document).bind('mousemove keydown scroll', function() {

    if ( $('body').hasClass('modal-open') && ( !YApps.Cookie.Get('YAppsWidgetsMS_Count') || Number(YApps.Cookie.Get('YAppsWidgetsMS_Count')) < 1 ) && YApps.Widgets.ShowStatus ) {

        YApps.Widgets.ClearTimeout( YApps.Widgets.MS.IdleTimerID );
        
        YApps.Widgets.MS.IdleTimerID = setTimeout( function() {

                YApps.Cookie.Set('YAppsWidgetsMS_Count', 1, {path: '/', domain: '.'+location.host});
                YApps.Helper.StartWidget('YApps_Widget--Messengers', 'MS');
        
        }, YApps.Widgets.MS.IdleTimeout*1000);
    }
});