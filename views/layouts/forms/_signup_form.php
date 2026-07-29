<div class="login-box">
  <div class="login-logo">
    <a href="/">Юг-Авто Apps</a>
  </div>
  <!-- /.login-logo -->
  
  <?php if ( $POSTRes ) HTML::Error( $POSTRes ); ?>
  
  <div class="login-box-body">
    <p class="login-box-msg">Регистрация нового пользователя.</p>

    <form method="post">
      <input type="hidden" name="form" value="signup" />
      <div class="form-group has-feedback">
        <input type="text" class="form-control" name="name" placeholder="Ф.И.О.">
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="email" class="form-control" name="email" placeholder="Email">
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" name="passwd" placeholder="Пароль">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input type="password" class="form-control" name="confim_passwd" placeholder="Подтверждение пароля">
        <span class="glyphicon glyphicon-ok form-control-feedback"></span>
      </div>
      <div class="row">
        <div class="col-xs-2"></div>
        <div class="col-xs-8">
          <button type="submit" class="btn btn-danger btn-block btn-flat">Зарегистироваться</button>
        </div>
        <div class="col-xs-2"></div>
      </div>
    </form>
    <br /><br />
    <div class="row text-center">
    <a href="/" class="text-center">&larr; Авторизоваться</a>
    </div>

  </div>
  <!-- /.login-box-body -->
</div>
<!-- /.login-box -->