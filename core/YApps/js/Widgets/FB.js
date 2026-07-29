YApps.Widgets.FB = {};
YApps.Widgets.FB.Reset = function() {};
$(document).on('click', 'a[role="YApps_Widget--StartFeedbackForm"]', function() {
	
	YApps.Widgets.ShowStatus = true;
	YApps.Helper.StartWidget( 'YApps_Widget--Chat', 'CH' );
	TalkMe("openReviewsTab", true);
	return false;
});