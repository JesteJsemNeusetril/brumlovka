<?php
require "config.php";

$rows = "";
$options = "";

try {
	
	$pdo = new PDO(
		sprintf( "%s:dbname=%s;host=%s", DB_TYPE, DB_DBNAME, DB_HOST ),
		DB_USERNAME,
		DB_PASSWORD
	);
	$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
	
	$statement = $pdo->prepare( "SELECT day, min, max FROM forecast;" );
	$statement->execute();
	
	if ( $result = $statement->fetchAll( PDO::FETCH_OBJ ) ) {
		foreach ( $result as $date ) {
			$rows .= sprintf(
				"\t\t\t{ c: [ {v: \"%s\"}, {v: %d}, {v: %d}, {v: %F} ] },\n",
				$date->day,
				$date->min,
				$date->max,
				( $date->min + $date->max ) / 2
			);
			$options .= sprintf(
				"\t<option>%s</option>\n",
				$date->day
			);
		}
	} else {
		$msg = "<div>No data available.</div>\n";
	}
	
} catch ( PDOException $e ) {
	
	$msg = "<div>Couldn't obtain data from database.</div>\n";
	
}
?><!DOCTYPE html>

<html>

<head>

<title>Prague Weather Forecast</title>

<style type="text/css">
#chart {
	 height: 600px;
	 width: 1000px;
}
</style>

<script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
google.charts.load( "current", { "packages":[ "corechart" ] });
google.charts.setOnLoadCallback( drawChart );

function drawChart() {
	
	var data = new google.visualization.DataTable({
		cols: [
			{ id: "day", label: "Date", type: "string" },
			{ id: "min", label: "Low", type: "number" },
			{ id: "max", label: "High", type: "number" },
			{ id: "avg", label: "Average", type: "number" }
		],
		rows: [
<?= $rows ?>
		]
	});
	
	var options = {
		title: "Prague Weather Forecast",
		legend: { position: "bottom" }
	};
	
	var chart = new google.visualization.LineChart( document.getElementById( "chart" ) );
	
	chart.draw( data, options );
	
}

$( function () {
	
	$("#date").change( function () {
		
		$("#temperatures").text( "Checking " + $(this).val() );
		
		$.ajax({
			url: "ajax.php",
			data: { day: $(this).val() },
			type: "GET",
			dataType: "json"
		}).done( function ( data ) {
			if ( data.error ) {
				$("#temperatures").text( "Error: " + data.error );
			} else {
				$("#temperatures").text( "Min: " + data.min + " Max: " + data.max );
			}
		}).fail( function () {
			$("#temperatures").text( "Error in obtaining data" );
		});
		
	});
	
});
</script>

</head>

<body>

<?= $msg ?>

<div id="chart"></div>

<select id="date">
	<option selected="selected" disabled="disabled">SELECT DATE</option>
<?= $options ?>
</select>

<p id="temperatures"></p>

</body>

</html>
