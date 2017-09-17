<?php
require "config.php";

$source = "https://query.yahooapis.com/v1/public/yql?q=select%20item.forecast.date%2C%20item.forecast.high%2C%20item.forecast.low%20from%20weather.forecast%20where%20woeid%20%3D%20796597%20and%20u%3D%22c%22&format=json&callback=";

$jsondata = file_get_contents( $source );

$obj = json_decode( $jsondata );

try {
	
	$pdo = new PDO(
		sprintf( "%s:dbname=%s;host=%s", DB_TYPE, DB_DBNAME, DB_HOST ),
		DB_USERNAME,
		DB_PASSWORD
	);
	$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	
} catch ( PDOException $e ) {
	
	echo "Database error";
	
}

if ( not $e ) {
	
	foreach ( $obj->query->results->channel as $day ) {
		
		echo sprintf( "%s\t%s\t%s\t",
			$day->item->forecast->date,
			$day->item->forecast->low,
			$day->item->forecast->high
		);
		
		try {
			
			$statement = $pdo->prepare( "INSERT IGNORE INTO forecast ( day, min, max ) VALUES ( ?, ?, ? );" );
			$statement->execute( array(
				$day->item->forecast->date,
				$day->item->forecast->low,
				$day->item->forecast->high
			) );
			
			echo "OK (inserted or date already there)\n";
			
		} catch ( PDOException $e ) {
			
			echo "FAIL (database error)\n";
			
		}
		
	}
	
}
