<?php
require "config.php";

$response = new stdClass();

$day = $_GET["day"];

$validDateForm = "/^[0-9]{1,2} (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) [0-9]{4}$/";

if ( preg_match( $validDateForm, $day ) ) {
	
	try {
		
		$pdo = new PDO(
			sprintf( "%s:dbname=%s;host=%s", DB_TYPE, DB_DBNAME, DB_HOST ),
			DB_USERNAME,
			DB_PASSWORD
		);
		$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		
		$statement = $pdo->prepare( "SELECT min, max FROM forecast WHERE day = ?;" );
		$statement->execute( array( $day ) );
		
		if ( $result = $statement->fetchObject() ) {
			$response = $result;
		} else {
			$response->error = "Date not found";
		}
		
	} catch ( PDOException $e ) {
		
		$response->error = "Couldn't obtain data from database";
		
	}
	
} else {
	
	$response->error = "Invalid date format";
	
}

header( "Content-type: application/json" );

echo json_encode( $response );
