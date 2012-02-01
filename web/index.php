<html>
 <head>
	<meta http-equiv="refresh" content="5">
    <script type='text/javascript' src='https://www.google.com/jsapi'></script>
    <script type='text/javascript'>
      google.load('visualization', '1', {packages:['gauge']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Label');
        data.addColumn('number', 'Value');
        data.addRows([
          <?php include 'getkwhh.php'?>
        ]);

        var options = {
		  max:15,
          width: 400, height: 200,
          redFrom: 10, redTo: 15,
          yellowFrom:5, yellowTo: 10,
          minorTicks: 5
        };

        var chart = new google.visualization.Gauge(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>
	
	<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Time');
        data.addColumn('number', 'kWh/h');
        data.addRows([
		  <?php include 'getlasthour.php'?>
        ]);

        var options = {
		  width: 600, height: 400,
          title: 'Forbruk siste timer',
		  vAxis: {title: 'kWh/h'},
		  hAxis: {title: 'time'}
        };

        var chart = new google.visualization.LineChart(document.getElementById('chartLine_div'));
        chart.draw(data, options);
      }
    </script>
  </head>

 <body>
	<p>Strømforbruk</p>
    <div id='chart_div'></div>
	<p>Siste timen:</p>
	<div id='chartLine_div'></div>
 </body>
</html>