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
        $get_id = !empty($_GET['vehicle_id']) ? trim((string)$_GET['vehicle_id']) : (!empty($currentRoute->id) ? trim((string)$currentRoute->id) : null);
        if ( !empty($get_id) && strpos($get_id, '?') !== false ) {
            $get_id = explode('?', $get_id)[0];
        }
        $rawEndpointUrl = !empty($get_id) ? $app->Cis->getVehicleAutoCRMUrl($get_id) : '';
        $arRes = !empty($get_id) ? $app->Cis->getVehicleAutoCRM( $get_id ) : [];
        if ( !is_array($arRes) ) {
            $arRes = [];
        }
        $vin = !empty($arRes['vin']) ? $arRes['vin'] : '';
        $arRes['title'] = 'Необработанные данные из API AutoCRM по автомобилю' . ($vin ? ': '.$vin : ($get_id ? ' (ID: '.$get_id.')' : ''));
        $arRes['raw_endpoint_url'] = $rawEndpointUrl;
        break;

    case 'dealersips':
        break;

    default: 
        $arRes['items'] = $app->Cis->getBrandsAutoCRM(); 
        $arRes['title'] = 'Доступные бренды';
        $arRes['dealerships'] = $app->Cis->getDealershipsAutoCRM();
        break;
} 
if ( !isset($arRes['items']) || !is_array($arRes['items']) ) {
    $arRes['items'] = [];
}
if ( !isset($arRes['dealerships']) || !is_array($arRes['dealerships']) ) {
    $arRes['dealerships'] = [];
}
?>

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
                    <input type="text" class="form-control" name="vehicle_id" placeholder="ID" value="<?= htmlspecialchars((string)($get_id ?? ''));?>">
                </div>
                <button type="submit" class="btn btn-primary">Отправить</button>
            </form>
            <hr />
            <?php if ( !empty($arRes['raw_endpoint_url']) ) { ?>
            <div class="form-group">
                <label>URL эндпоинта сырых данных (AutoCRM API):</label>
                <div class="input-group">
                    <input type="text" class="form-control" readonly value="<?= htmlspecialchars($arRes['raw_endpoint_url']); ?>" id="raw-endpoint-url">
                    <span class="input-group-btn">
                        <a href="<?= htmlspecialchars($arRes['raw_endpoint_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-default" title="Открыть URL в новой вкладке">
                            <i class="fa fa-external-link"></i> Открыть
                        </a>
                    </span>
                </div>
            </div>
            <hr />
            <?php } ?>
            <?php
            if ( !empty($arRes['id']) ) {
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