<?php $arRes = $app->Auction->getTemplates() ?>
<div class="box box-primary">
  
  <div class="box-header with-border"><h3 class="box-title">Шаблоны уведомлений</h3></div>
  
  <div class="box-body">
    <div class="col-xs-12">
      <a href="/auction/templates/edit/" class="btn btn-info btn-flat">Изменить</a>
    </div>
  </div>
  <hr />
  <div class="box-body">
    <div class="col-xs-12">
      <h3>СМС о начале торгов</h3>
      <blockquote><?=$arRes['sms_start']?></blockquote>
    </div>
  </div>
  <hr />
  <div class="box-body">
    <div class="col-xs-12">
      <h3>СМС победителю</h3>
      <blockquote><?=$arRes['sms_winner']?></blockquote>
    </div>
  </div>
  <hr />
  <div class="box-body">
    <div class="col-xs-12">
      <h3>Email о начале торгов</h3>
      <blockquote><?=$arRes['email_start']?></blockquote>
    </div>
  </div>
  <hr />
  <div class="box-body">
    <div class="col-xs-12">
      <h3>Email победителю</h3>
      <blockquote><?=$arRes['email_winner']?></blockquote>
    </div>
  </div>
  <hr />
  <div class="box-body">
    <div class="col-xs-12">
      <h3>Email 2-му и 3-му месту</h3>
      <blockquote><?=$arRes['email_winners']?></blockquote>
    </div>
  </div>
  
</div>