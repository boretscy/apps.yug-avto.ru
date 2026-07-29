<?php

    $URL = ( $_GET['r'] ) ?: $_SERVER['HTTP_REFERER'];
    echo json_encode( $app->Widgets->getVueData($user, $URL) );