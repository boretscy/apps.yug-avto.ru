<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/locale/ru.js"></script>
<script>
  $("#data-table-stats").DataTable({
      "order": [[ 0, "desc" ]],
      "pageLength": 100
  });
  $('#datepicker_date1').datetimepicker({
	  locale: 'ru',
		format: 'L'
  });
  $('#datepicker_date2').datetimepicker({
	  locale: 'ru',
		format: 'L'
  });
</script>

<style>
ul.nav-stacked span {
	width: 280px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	text-align: right;
}
</style>

<div class="remodal" data-remodal-id="viewClientCard">
	<button data-remodal-action="close" class="remodal-close"></button>
	<div class="col-md-6 text-left">
		<!-- Widget: user widget style 1 -->
		<div class="box box-widget widget-user-2">
			<!-- Add the bg color to the header using any of the bg-* classes -->
			<div class="widget-user-header bg-aqua">
				<div class="widget-user-image">
					<img class="img-circle" src="/assets/img/avatar5.png" role="avatar">
				</div>
				<!-- /.widget-user-image -->
				<h3 class="widget-user-username" role="name"></h3>
				<h5 class="widget-user-desc" role="timestamp"></h5>
			</div>
			<div class="box-footer no-padding">
				<div class="row">
                <div class="col-sm-3 border-right">
                  <div class="description-block">
                    <span class="description-text">ID Matomo</span><h5 class="description-header" role="piwik"></h5>
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col -->
                <div class="col-sm-3 border-right">
                  <div class="description-block">
                    <span class="description-text">ID Yandex</span><h5 class="description-header" role="yandex"></h5>
                  </div>
                  <!-- /.description-block -->
                </div>
                <div class="col-sm-3 border-right">
                  <div class="description-block">
                    <span class="description-text">ID Google</span><h5 class="description-header" role="google"></h5>
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col -->
                <div class="col-sm-3">
                  <div class="description-block">
                    <span class="description-text">ID Carrot</span><h5 class="description-header" role="chat"></h5>
                  </div>
                  <!-- /.description-block -->
                </div>
                <!-- /.col -->
              </div>
				<h4 style="padding-left: 15px;">Данные</h4>
				<ul class="nav nav-stacked" role="ClientData"></ul>
				<h4 style="padding-left: 15px;">Социальные сети</h4>
				<ul class="nav nav-stacked" role="ClientSocial">
				</ul>
			</div>
		</div>
		<!-- /.widget-user -->
		
	</div>
	<div class="col-md-6 text-left">
		<div class="box box-primary">
            
			<div class="box-header with-border">
			  <h3 class="box-title">Точка входа</h3>
			</div>
         	
         	<div class="box-body">
         		<ul class="nav nav-stacked" role="initData">
				</ul>
				
				<h4 style="padding-left: 15px;">UTM-метки</h4>
				<ul class="nav nav-stacked" role="initUTM">
				</ul>
			</div>
			
		</div>
	</div>
</div>

<script>
	
	$("#data-table-clients").DataTable({
	  "order": [[ 0, "desc" ]],
      "pageLength": 50
	});
	
	$('a[role="viewClientCard"]').click( function() {
		
		$('[data-remodal-id="viewClientCard"]').remodal().open();
		
		$('[data-remodal-id="viewClientCard"]').css('background-color', '#f7f7f7')
		
		var Client = JSON.parse( $(this).attr('data') ), html = '';
		console.log( Client );
		
		$('[role="avatar"]').attr('src', (Client.avatar !== 'null')?Client.avatar:'/assets/img/avatar5.png');
		$('[role="name"]').text( (Client.name)?Client.name:'Неизвестно' );
		$('[role="timestamp"]').text(Client.init_date);
		
		$('[role="piwik"]').html(Client.piwik_visitorId);
		$('[role="yandex"]').html(Client.yandex_visitorId);
		$('[role="google"]').html(Client.google_visitorId);
		$('[role="chat"]').html(Client.chat_visitorId);
		
		html += '<li><a>Имя <span class="pull-right">'+((Client.name)?Client.name:'Неизвестно')+'</span></a></li>';
		html += '<li><a>Телефон <span class="pull-right">'+((Client.phone)?Client.phone:'Неизвестно')+'</span></a></li>';
		html += '<li><a>Email <span class="pull-right">'+((Client.email)?Client.email:'Неизвестно')+'</span></a></li>';
		html += '<li><a>Страна <span class="pull-right">'+((Client.country)?Client.country:'Неизвестно')+'</span></a></li>';
		html += '<li><a>Регион <span class="pull-right">'+((Client.region)?Client.region:'Неизвестно')+'</span></a></li>';
		html += '<li><a>Город <span class="pull-right">'+((Client.city)?Client.city:'Неизвестно')+'</span></a></li>';
		html += '<li><a>Последний url <span class="pull-right">'+Client.last_url+'</span></a></li>';
		$('ul[role="ClientData"]').html( html );
		
		html = '';
		html += (Client.social_vk)?'<li><a href="'+Client.social_vk+'" target="_blank"><i class="fa fa-vk"></i> Vkontakte <span class="pull-right">'+Client.social_vk+'</span></a></li>':'';
		html += (Client.social_facebook)?'<li><a href="'+Client.social_facebook+'" target="_blank"><i class="fa fa-facebook"></i> Facebook <span class="pull-right">'+Client.social_facebook+'</span></a></li>':'';
		html += (Client.social_fourqsuare)?'<li><a href="'+Client.social_fourqsuare+'" target="_blank"><i class="fa fa-foursquare"></i> Fourqsuare <span class="pull-right">'+Client.social_fourqsuare+'</span></a></li>':'';
		html += (Client.social_googleplus)?'<li><a href="'+Client.social_googleplus+'" target="_blank"><i class="fa fa-google-plus"></i> Google+ <span class="pull-right">'+Client.social_googleplus+'</span></a></li>':'';
		html += (Client.social_pinterest)?'<li><a href="'+Client.social_pinterest+'" target="_blank"><i class="fa fa-pinterest"></i> Pinterest <span class="pull-right">'+Client.social_pinterest+'</span></a></li>':'';
		html += (Client.social_twitter)?'<li><a href="'+Client.social_twitter+'" target="_blank"><i class="fa fa-twitter"></i> Twitter <span class="pull-right">'+Client.social_twitter+'</span></a></li>':'';
		html += (Client.social_skype)?'<li><a href="'+Client.social_skype+'" target="_blank"><i class="fa fa-skype"></i> Skype <span class="pull-right">'+Client.social_skype+'</span></a></li>':'';
		$('ul[role="ClientSocial"]').html( html );
		
		html = '';
		
		html += '<li><a>Дата <span class="pull-right">'+Client.init_date+'</span></a></li>';
		html += '<li><a>URL <span class="pull-right">'+Client.init_url+'</span></a></li>';
		html += '<li><a>Сайт <span class="pull-right">'+Client.site.ru_name+'</span></a></li>';
		html += '<li><a href="/'+Client.app.url_key+'/">Приложение <span class="pull-right">'+Client.app.ru_name+'</span></a></li>';
		html += '<li><a>Источник <span class="pull-right">'+Client.init_referrer+'</span></a></li>';
		$('ul[role="initData"]').html( html );
		
		html = '';
		html += (Client.init_utm_campaign)?'<li><a href="'+Client.init_utm_campaign+'">Campaign <span class="pull-right">'+Client.init_utm_campaign+'</span></a></li>':'';
		html += (Client.init_utm_content)?'<li><a href="'+Client.init_utm_content+'">Content <span class="pull-right">'+Client.init_utm_content+'</span></a></li>':'';
		html += (Client.init_utm_medium)?'<li><a href="'+Client.init_utm_medium+'">Medium <span class="pull-right">'+Client.init_utm_medium+'</span></a></li>':'';
		html += (Client.init_utm_source)?'<li><a href="'+Client.init_utm_source+'">Source <span class="pull-right">'+Client.init_utm_source+'</span></a></li>':'';
		html += (Client.init_utm_term)?'<li><a href="'+Client.init_utm_term+'">Term <span class="pull-right">'+Client.init_utm_term+'</span></a></li>':'';
		$('ul[role="initUTM"]').html( html );
	
	  return false;
  });
	
</script>
