<?php

    class Export extends App {
        
        public static function PutCSV( $arR, $filename = 'export.csv' ) {

            $now = gmdate("D, d M Y H:i:s");
            header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
            header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
            header("Last-Modified: {$now} GMT");
        
            // force download
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");
        
            // disposition / encoding on response body
            header("Content-Disposition: attachment;filename={$filename}");
            header("Content-Transfer-Encoding: binary");

            $handle = fopen("php://output", 'w');
            foreach ( $arR as $k => $r ) {

                // if ( $k == 0 ) {
                    
                //     foreach ( $r as $i=>$c ) $headers[] = $i;
                //     fputcsv( $handle, $headers, ';' );
                // }
                foreach ( $r as $i=>$c ) $r[$i] = iconv('UTF-8', 'WINDOWS-1251', $c);
                fputcsv( $handle, $r, ';' );
            }

            fclose($handle);
        }
		
		public static function SaveCSV( $arR, $filename = 'export.csv' ) {

            $handle = fopen($_SERVER['DOCUMENT_ROOT'].'/upload/Export/'.$filename, 'w');
			
            foreach ( $arR as $k => $r ) {

                if ( $k == 0 ) {
                    
                    foreach ( $r as $i=>$c ) $headers[] = $i;
                    fputcsv( $handle, $headers, ';' );
                }
				
				foreach ( $r as $i=>$c ) $r[$i] = iconv('UTF-8', 'WINDOWS-1251', $c);

                fputcsv( $handle, $r, ';' );
            }

            fclose($handle);
			
			$res = Helper::getRes(1);
			$res->description .= '. Скачать файл можно по ссылке: <a href="'.'/upload/Export/'.$filename.'">'.$filename.'</a>';
			
			return $res;
        }

        public static function _SaveCSV( $arR, $filename = 'export.csv', $app = 'Export', $delimiter = ';', $utf8 = false ) {

            $handle = fopen($_SERVER['DOCUMENT_ROOT'].'/upload/'.$path.'/'.$filename, 'w');
			
            foreach ( $arR as $k => $r ) {

                if ( $k == 0 ) {
                    
                    foreach ( $r as $i=>$c ) $headers[] = $i;
                    fputcsv( $handle, $headers, $delimiter );
                }
				
				if ( !$utf8 ) foreach ( $r as $i=>$c ) $r[$i] = iconv('UTF-8', 'WINDOWS-1251', $c);

                fputcsv( $handle, $r, $delimiter );
            }

            fclose($handle);
			
			$res = Helper::getRes(1);
			$res->description .= '. Скачать файл можно по ссылке: <a href="'.'/upload/'.$path.'/'.$filename.'">'.$filename.'</a>';
			
			return $res;
        }
    }