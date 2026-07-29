<?php if ( $currentRoute->action == 'delete' ) $app->Cis->yappsDelSeo404( $currentRoute->id ); ?>
<?php $arRes = $app->Cis->yappsGetSeo404s(); ?>

<div class="box box-primary">
            
    <div class="box-header with-border">
        <h3 class="box-title">Страницы 404</h3>
    </div>
        
        <div class="box-body">
          <div class="col-xs-12">
            <a href="/cis/seo-404/new/" class="btn btn-info btn-flat"><i class="fa fa-plus" aria-hidden="true"></i> Добавить</a>
          </div>
        </div>
            
    <div class="box-body">

        <table id="data-table-cis-seo-404" class="table table-hover table-striped table-condensed dataTable">
            <thead>
                <tr>
                    <th style="width: 10%">ID</th>
                    <th style="width: 20%">Сайт</th>
                    <th style="width: 60%">Путь</th>
                    <th style="width: 10%">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $arRes as $item ) { ?>
                <tr>
                    <td><a href="/cis/seo-404/edit/<?=$item['id']?>/"><?= $item['id'];?></a></td>
                    <td><a href="/cis/seo-404/edit/<?=$item['id']?>/"><?= $item['site']?></a></td>
                    <td><a href="/cis/seo-404/edit/<?=$item['id']?>/"><?= $item['uri'];?></a></td>
                    <td class="text-right">
                        <a href="/cis/seo-404/edit/<?=$item['id']?>/">
                            <span class="label label-primary hint--top" aria-label="Редактировать"><i class="fa fa-edit" aria-hidden="true"></i></span>
                        </a>&nbsp;&nbsp;
                        <a href="/cis/seo-404/delete/<?=$item['id']?>/" role="delete">
                            <span class="label label-danger hint--top" aria-label="Удалить"><i class="fa fa-remove" aria-hidden="true"></i></span>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
              
    </div>

</div>