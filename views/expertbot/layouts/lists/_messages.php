<?php if ( $currentRoute->action == 'delete' ) $app->Expertbot->delMessage( $currentRoute->id ); ?>
<?php $types = $app->Expertbot->getMessageTypes(); ?>

  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1><?=$app->Expertbot->AppInfo()->ru_name?> <small>Сообщения</small></h1>
  </section>
  
  <!-- Main content -->
  <section class="content">
    
    <?php if ( $app->Expertbot->AppInfo()->maintenance ) include $_SERVER['DOCUMENT_ROOT'].'/upload/Apps/maintenance.php'; ?>
    <?php if ($POSTRes) HTML::Error($POSTRes); ?>
    
    <div class="row">
        <div class="col-md-12">

            <?php foreach ($types as $type) { ?>
            <?php $arRes = $app->Expertbot->getMessages( $type['id'] ); ?>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= $type['ru_name'];?></h3>
                    <p><?= $type['description'];?></p>
                </div>
                <div class="box-body">
                    <div class="col-xs-12">
                        <a href="/expertbot/messages/new/?type_id=<?= $type['id'];?>" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить сообщение в группу</a>
                    </div>
                </div>
                <div class="box-body">
                    <table class="table table-striped table-bordered table-sm" id="data-table-expertbot-sources">
                        <thead>
                            <tr>
                                <th style="width: 10%">ID</th>
                                <th style="width: 80%">Текст</th>
                                <th style="width: 10%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $arRes as $item ) { ?>
                            <tr>
                                <td><a href="/expertbot/messages/edit/<?=$item['id']?>/"><?= $item['id'];?></a></td>
                                <td><?= $item['text'];?></td>
                                <td class="text-right">
                                    <a href="/expertbot/messages/edit/<?=$item['id']?>/">
                                        <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                                    </a>
                                    <a href="/expertbot/messages/delete/<?=$item['id']?>/" role="delete">
                                        <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                                    </a>
                                </td>
                            </tr>
                            <?php } // foreach ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php } // foreach TYPES ?>
            
        </div>
    </div>
  
  </section>
  <!-- /.content -->
  