<?php switch ( $currentRoute->action ) {
    case 'models': 
        $arRes = $app->Cis->getModelsAutoCRM( $currentRoute->id );
        $arRes['title'] = 'Доступные модели бренда '.$arRes['brand']['name'];
        break;
    
    case 'vehicles':
        $arRes['items'] = $app->Cis->getVehiclesAutoCRM($_GET['brand'], $_GET['model']);
        $arRes['title'] = 'Автомобили в бд';
        break;

    case 'vehicle':
        $get_id = $currentRoute->id ?: $_GET['vehicle_id'];
        $arRes = $app->Cis->getVehicleAutoCRM( $get_id );
        $arRes['title'] = 'Необработанные данные из API AutoCRM по автомобилю: '.$arRes['vin'];
        break;

    case 'dealersips':
        break;

    default: 
        $arRes['items'] = $app->Cis->getBrandsAutoCRM(); 
        $arRes['title'] = 'Доступные бренды';
        $arRes['dealerships'] = $app->Cis->getDealershipsAutoCRM();
        break;
} ?>

<div class="box box-primary">
            
    <div class="box-header with-border">
        <h3 class="box-title"><?= $arRes['title'];?></h3>
    </div>
            
    <div class="box-body">

        <?php if ( $currentRoute->action == 'vehicle' ) {
            ?>
            <form>
                <div class="form-group">
                    <label style="width: 100%;">ID</label>
                    <input type="text" class="form-control" name="vehicle_id" placeholder="ID" value="<?= $_GET['vehicle_id'];?>">
                </div>
                <button type="submit" class="btn btn-primary">Отправить</button>
            </form>
            <hr />
            <?php
            if ( $arRes['id'] ) {
                Helper::sp( $arRes );
            }
        } else { ?>
        <table id="data-table-cis" class="table table-hover table-striped table-condensed dataTable">
            <thead>
                <tr>
                    <th style="width: 50%">Название</th>
                    <th style="width: 50%"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $arRes['items'] as $item ) { ?>
                <tr>
                    <?php if ( $currentRoute->action == 'vehicles' ) $item['name'] = $item['brand']['name'].' '.$item['model']['name'].', VIN: '.$item['vin']; ?>
                    <td><?= $item['name'];?></td>
                    <?php switch ( $currentRoute->action ) {
                        case 'models':
                            ?>
                            <td><a href="/cis/autocrm/vehicles/?brand=<?= $arRes['brand']['id'];?>&model=<?= $item['id'];?>">Посмотреть авто</a></td>
                            <?php 
                            break;

                        case 'vehicles':
                            ?>
                            <td><a href="/cis/autocrm/vehicle/<?= $item['id'];?>/">Посмотреть raw-данные</a> | <a href="/cis/vehicles/edit/<?= $item['id'];?>/">Посмотреть авто</a></td>
                            <?php
                            break;

                        case 'vehicle':
                            ?>
                            <td><a href="/cis/autocrm/vehicle/<?= $item['ext_id'];?>/">Посмотреть</a></td>
                            <?php
                            break;

                        case 'dealersips':
                            break;

                        default:
                            ?>
                            <td><a href="/cis/autocrm/models/<?= $item['id'];?>/">Посмотреть модели</a></td>
                            <?php 
                            break;
                    } ?>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>
        <?php if ($currentRoute->action == 'models' ) Helper::sp( $arRes ); ?> 
              
    </div>

</div>

<?php if ( !$currentRoute->action ) { ?>
<div class="box box-primary">
            
    <div class="box-header with-border">
        <h3 class="box-title">Дилерские центры</h3>
    </div>
            
    <div class="box-body">

        <table id="data-table-cis-dc" class="table table-hover table-striped table-condensed dataTable">
            <thead>
                <tr>
                    <th style="width: 50%">ID</th>
                    <th style="width: 50%">Название</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $arRes['dealerships'] as $item ) { ?>
                <tr>
                    <td><?= $item['id'];?></td>
                    <td><?= $item['name'];?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
              
    </div>

</div>
<?php } ?>