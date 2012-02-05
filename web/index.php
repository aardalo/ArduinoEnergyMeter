<html>
<head>
<script type='text/javascript' src='https://www.google.com/jsapi'></script>
<script type='text/javascript'>google.load('visualization', '1', {packages:['gauge','corechart']});</script>
<script type='text/javascript'>

    google.setOnLoadCallback(drawGauge);			// callback to start drawing the gauge
    google.setOnLoadCallback(drawLineChart);		// callback to start drawing the line chart

    var Ldata = new google.visualization.DataTable();	// set up data talbe for line chart
    var Gdata = new google.visualization.DataTable();	// set up data table for gauge
    var lastkWhh = 0.0;									// to store the last value from the meter

	ow = getTheWidth()-10;							// object width
	console.log("OW:",ow);
	oh = Math.round(getTheHeight()/2)-10; 			// object width
	console.log("OH:",oh);
    
	function updateLastkWhh() {					// called to read last figure from the log-file through web page
		request = new XMLHttpRequest();  
	    request.open('GET', 'getlastkwhh.php', false);   // must return only a integer or floating point number
	    request.send(null);  
	    lastkWhh = parseFloat(request.responseText);	// convert to float as this is what the datatable wants
	};

	setInterval(updateLastkWhh,5000);			// update every 5 sec - should be same interval as sensor

	function getTimeString() {					// make a hh:mm string as label for the line graph
		d = new Date();
		curr_hour = d.getHours();
		hs = curr_hour.toString();
		if (hs.length == 1) { hs = "0" + hs; };
		
		var curr_min = d.getMinutes();
		ms = curr_min.toString();
		if (ms.length == 1) { ms = "0" + ms; };

		return hs + ":" + ms;
	};	
	
    var Goptions = {
  		  max:15,
  			width: ow, height: oh,
//			width: 400, height: 200,
            redFrom: 10, redTo: 15,
            yellowFrom:5, yellowTo: 10,
            minorTicks: 5
          };
      
    var Loptions = {
    	    width: ow, height: oh,
//			width: 600, height: 400,
            title: 'Forbruk siste timer',
//			vAxis: {title: 'kWh/h'},
//			hAxis: {title: 'time'}
          };

	        
    function drawGauge() {
    	var Gchart = new google.visualization.Gauge(document.getElementById('chart_div'));
        Gdata.addColumn('string', 'Label');
        Gdata.addColumn('number', 'Value');
        Gdata.addRows([['kWh/h',<?php include 'getlastkwhh.php'?>]]);
    	Gchart.draw(Gdata, Goptions);

    	function updateGauge() {
    	    Gdata.setValue(0,1,lastkWhh);
			Gchart.draw(Gdata, Goptions);
		};

    	setInterval(updateGauge,5000);
    }
    
      function drawLineChart() {
    	var Lchart = new google.visualization.LineChart(document.getElementById('chartLine_div'));
        Ldata.addColumn('string', 'Time');
        Ldata.addColumn('number', 'kWh/h');
        
        Ldata.addRows([<?php include 'getlasthour.php'?>]);
		Lchart.draw(Ldata, Loptions);
		
	    function updateLineGraph() {
			Ldata.removeRow(0);
			Ldata.addRow([getTimeString(),lastkWhh]);
			Lchart.draw(Ldata, Loptions);
		};

		setInterval(updateLineGraph,24*5000); // 24 er samme faktor som i getlasthour.php - hvert 2. min
				
      };
      
      function getTheWidth() {
    	   var viewportwidth;
    	   var viewportheight;
    	    
    	   // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
    	    
    	   if (typeof window.innerWidth != 'undefined')
    	   {
    	        viewportwidth = window.innerWidth,
    	        viewportheight = window.innerHeight
    	   }
    	    
    	  // IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
    	   
    	   else if (typeof document.documentElement != 'undefined'
    	       && typeof document.documentElement.clientWidth !=
    	       'undefined' && document.documentElement.clientWidth != 0)
    	   {
    	         viewportwidth = document.documentElement.clientWidth,
    	         viewportheight = document.documentElement.clientHeight
    	   }
    	    
    	   // older versions of IE
    	    
    	   else
    	   {
    	         viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
    	         viewportheight = document.getElementsByTagName('body')[0].clientHeight
    	   }

    	   return(viewportwidth);
      };
      
      function getTheHeight() {
   	   var viewportwidth;
   	   var viewportheight;
   	    
   	   // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight
   	    
   	   if (typeof window.innerWidth != 'undefined')
   	   {
   	        viewportwidth = window.innerWidth,
   	        viewportheight = window.innerHeight
   	   }
   	    
   	  // IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)
   	   
   	   else if (typeof document.documentElement != 'undefined'
   	       && typeof document.documentElement.clientWidth !=
   	       'undefined' && document.documentElement.clientWidth != 0)
   	   {
   	         viewportwidth = document.documentElement.clientWidth,
   	         viewportheight = document.documentElement.clientHeight
   	   }
   	    
   	   // older versions of IE
   	    
   	   else
   	   {
   	         viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
   	         viewportheight = document.getElementsByTagName('body')[0].clientHeight
   	   }

   	   return(viewportheight);
     };
      
    </script>
</head>

<body>
	<div id='chart_div'></div>
	<div id='chartLine_div'></div>
</body>
</html>
