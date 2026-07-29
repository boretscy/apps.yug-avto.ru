<?php

	if ( $app->User->isAdministrator( $user ) ) {
				
		switch ( $route->id ) {

			case 'LastVisits':
				
				$APIRes = $app->Dashboard->getPiwikVisits( $_GET['SiteID'], $app->Dashboard->Sets()->piwik_period, $app->Dashboard->Sets()->piwik_date );
				Export::PutCSV( $APIRes, 'ExportCSV_Piwik_'.$app->Dashboard->Sets()->piwik_period.'_on_'.$app->Dashboard->Sets()->piwik_date.'.csv' );
				break;
			
			case 'Visit':
				
				echo Helper::getRes(101)->description;
				break;
		}
	}