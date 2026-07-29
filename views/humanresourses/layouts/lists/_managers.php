<?php if ( $currentRoute->action == 'delete' ) $app->HumanResourses->delManager( $currentRoute->id ); ?>
<?php $arRes = $app->HumanResourses->getManagers( $userSites['sites_ids'] ) ?>

<?php if ($POSTRes) HTML::Error($POSTRes); ?>
  
<div class="row">
    
    <div class="col-md-12">
      
        <div class="box box-primary">
            
            <div class="box-header with-border"><h3 class="box-title">Специалисты отдела кадров</h3></div>
            
            <div class="box-body">
                <div class="col-xs-12">
                    <a href="/humanresourses/managers/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
                </div>
            </div>
            
            <div class="box-body">
            
                <table id="data-table-lands" class="table table-hover table-striped table-condensed dataTable">
                    <thead>
                    <tr>
                        <th style="width: 5%">ID</th>
                        <th style="width: 65%">ФИО</th>
                        <th style="width: 15%">Sort</th>
                        <th style="width: 15%">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php foreach( $arRes as $item ) { ?>
                        <tr>
                            <td><a href="/humanresourses/managers/edit/<?=$item['id']?>/"><?=$item['id']?></a></td>
                            <td><a href="/humanresourses/managers/edit/<?=$item['id']?>/"><?=$item['ru_name']?></a></td>
                            <td><?=$item['sort']?></td>
                            <td class="text-right">
                                <a href="/humanresourses/managers/edit/<?=$item['id']?>/">
                                    <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                                </a>
                                <a href="/humanresourses/managers/delete/<?=$item['id']?>/" role="delete">
                                    <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            
            </div>
            <!-- /.box-body -->
        </div>
      
    </div>
    
 </div>



