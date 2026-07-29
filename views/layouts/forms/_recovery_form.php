
<div class="login-box">
  <div class="login-logo">
    <a href="/">Юг-Авто Apps</a>
  </div>
  <!-- /.login-logo -->
  
  <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
  
  <div class="login-box-body">
  	
  
    <p class="login-box-msg">Восстановление пароля.</p>

    <form method="post">
      <input type="hidden" name="form" value="recovery" />
      
	  <?php if ( $_GET['recovery_string'] ) { ?>
      
      <div class="form-group has-feedback">
        <input type="text" class="form-control" name="recovery_string" placeholder="Контрольная строка" value="<?=$_GET['recovery_string']?>">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      
      <?php } else { ?>
      
      <div class="form-group has-feedback">
        <input type="email" class="form-control" name="email" placeholder="Email">
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      
      <?php } ?>
      
      <div class="row">
        <div class="col-xs-2"></div>
        <div class="col-xs-8">
          <button type="submit" class="btn btn-danger btn-block btn-flat">Восстановить</button>
        </div>
        <div class="col-xs-2"></div>
        <!-- /.col -->
      </div>
    </form>
	<br /><br />
    <div class="row text-center">
      <a href="/">&larr; Авторизоваться</a><br>
      <a href="/?action=signup" class="text-center">Зарегистироваться &rarr;</a>
    </div>

  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->