<html>
    <head>
        <script type='text/javascript' src='https://www.google.com/jsapi'></script>
        <script type='text/javascript'>google.load('visualization', '1', {packages:['gauge','corechart']});</script>
        <script type='text/javascript'>

            google.setOnLoadCallback(drawGauge);                // callback to start drawing the gauge
            google.setOnLoadCallback(drawLMchart);             // callback to start drawing the last minute line chart
            google.setOnLoadCallback(drawLineChart);          // callback to start drawing the line chart
            google.setOnLoadCallback(drawTempChart);       // callback to start drawing the temperature gauge
            google.setOnLoadCallback(drawLightChart);        // callback to start drawing the temperature gauge


            var Ldata = new google.visualization.DataTable();	// set up data talbe for line chart
            var LMdata = new google.visualization.DataTable();	// set up data talbe for line chart
            var Gdata = new google.visualization.DataTable();	// set up data table for gauge
            var Tempdata = new google.visualization.DataTable();   // set up data table for temperature
            var Lightdata = new google.visualization.DataTable();   // set up data table for temperature

            var lastkWhh = 0.0;					// to store the last value from the meter
            var lasttemp = 0.0;                 // to store the last temperature read
            var lastlight = 0.0;                // to store the last light reading

            ow = getTheWidth()-10;				// object width
            oh = Math.round(getTheHeight()/2)-10; 		// object height

            function updateLastkWhh() {			// called to read last figure from the log-file through web page
                request = new XMLHttpRequest();
                request.open('GET', 'sqlgetkwhh.php', false); // must return only a integer or floating point number
                request.send(null);
                lastkWhh = parseFloat(request.responseText);// convert to float as this is what the datatable wants
            };

            setInterval(updateLastkWhh,5000);		// update every 5 sec - should be same interval as sensor

            function updateLastTemp() {			// called to read last figure from the log-file through web page
                request = new XMLHttpRequest();
                request.open('GET', 'sqlgettemp.php', false); // must return only a integer or floating point number
                request.send(null);
                lasttemp = parseFloat(request.responseText);// convert to float as this is what the datatable wants
            };

            setInterval(updateLastTemp,5000);		// update every 5 sec - should be same interval as sensor

            function updateLastLight() {			// called to read last figure from the log-file through web page
                request = new XMLHttpRequest();
                request.open('GET', 'sqlgetlight.php', false); // must return only a integer or floating point number
                request.send(null);
                lastlight = parseFloat(request.responseText);// convert to float as this is what the datatable wants
            };

            setInterval(updateLastLight,5000);		// update every 5 sec - should be same interval as sensor

            function getTimeStringhhmm() {			// make a hh:mm string as label for the line graph
                d = new Date();
                curr_hour = d.getHours();
                hs = curr_hour.toString();
                if (hs.length == 1) { hs = "0" + hs; };

                var curr_min = d.getMinutes();
                ms = curr_min.toString();
                if (ms.length == 1) { ms = "0" + ms; };

                return hs + ":" + ms;
            };

            function getTimeStringss() {					// make a hh:mm string as label for the line graph
                d = new Date();
                curr_hour = d.getHours();
                hs = curr_hour.toString();
                if (hs.length == 1) { hs = "0" + hs; };

                var curr_min = d.getMinutes();
                ms = curr_min.toString();
                if (ms.length == 1) { ms = "0" + ms; };

                var curr_sec = d.getSeconds();
                ss = curr_sec.toString();
                if (ss.length == 1) { ss = "0" + ss; };

                //return hs + ":" + ms + ":" + ss;
                return ss;
            };

            var Goptions = {							// Options for Gauge-graph
                max:15,
                width: ow/4, height: oh/2,
                redFrom: 10, redTo: 15,
                yellowFrom:5, yellowTo: 10,
                minorTicks: 5
            };

            var Tempoptions = {							// Options for Gauge-graph
                max:40, min:-15,
                width: ow/4, height: oh/2,
                redFrom: -15, redTo: 0,
                yellowFrom:0, yellowTo: 0,
                minorTicks: 10
            };

            var Lightoptions = {        				// Options for Gauge-graph
                max:1024, min:0,
                width: ow/4, height: oh/2,
                minorTicks: 10
            };


            var LMoptions = {							// Options for last minute graph
                width: ow/4, height: oh/2,
                animation: {duration: 1000, easing: "inAndOut"},
                curveType: "function",
                title: 'Forbruk siste minutter'
            };

            var Loptions = {							// Options for last hours graph
                width: ow, height: oh,
                title: 'Forbruk siste 24 timer'
                //			vAxis: {title: 'kWh/h'},
                //			hAxis: {title: 'time'}
            };


            function drawGauge() {
                var Gchart = new google.visualization.Gauge(document.getElementById('chart_div'));
                Gdata.addColumn('string', 'Label');
                Gdata.addColumn('number', 'Value');
                Gdata.addRows([['kWh/h',<?php include 'sqlgetkwhh.php' ?>]]);
                Gchart.draw(Gdata, Goptions);

                function updateGauge() {
                    Gdata.setValue(0,1,lastkWhh);
                    Gchart.draw(Gdata, Goptions);
                };

                setInterval(updateGauge,5000);
            };

            function drawTempChart() {
                var Tempchart = new google.visualization.Gauge(document.getElementById('chart_temp'));
                Tempdata.addColumn('string', 'Label');
                Tempdata.addColumn('number', 'Value');
                Tempdata.addRows([['Celcius',<?php include 'sqlgettemp.php' ?>]]);
                Tempchart.draw(Tempdata, Tempoptions);

                function updateTemp() {
                    Tempdata.setValue(0,1,lasttemp);
                    Tempchart.draw(Tempdata, Tempoptions);
                };

                setInterval(updateTemp,1000);
            };

            function drawLightChart() {
                var Lightchart = new google.visualization.Gauge(document.getElementById('chart_light'));
                Lightdata.addColumn('string', 'Label');
                Lightdata.addColumn('number', 'Value');
                Lightdata.addRows([['LDR value',<?php include 'sqlgetlight.php' ?>]]);
                Lightchart.draw(Lightdata, Lightoptions);

                function updateLight() {
                    Lightdata.setValue(0,1,lastlight);
                    Lightchart.draw(Lightdata, Lightoptions);
                };

                setInterval(updateLight,1000);
            };


            function drawLineChart() {
                var Lchart = new google.visualization.LineChart(document.getElementById('chartLine_div'));
                Ldata.addColumn('string', 'Time');
                Ldata.addColumn('number', 'kWh/h');

                Ldata.addRows([<?php include 'sqlgetlastday.php' ?>]);
                Lchart.draw(Ldata, Loptions);

                function updateLineGraph() {
                    if(Ldata.getNumberOfRows() > 1000 ) {
                        Ldata.removeRow(0);
                    }
                    Ldata.addRow([getTimeStringhhmm(),lastkWhh]);
                    Lchart.draw(Ldata, Loptions);
                };

                setInterval(updateLineGraph,86150); // ca every 17,23 datapoint... enough to populate 1000px wide
            };

            function drawLMchart(){
                var LMchart = new google.visualization.LineChart(document.getElementById('lm_div'));
                LMdata.addColumn('string', 'Time');
                LMdata.addColumn('number', 'kWh/h');

                LMdata.addRows([<?php include 'sqlgetlastminute.php' ?>]);
                LMchart.draw(LMdata, LMoptions);

                function updateLMGraph() {
                    if(LMdata.getNumberOfRows()>24){
                        LMdata.removeRow(0);
                    }
                    LMdata.addRow([getTimeStringss(),lastkWhh]);
                    LMchart.draw(LMdata, LMoptions);
                };

                setInterval(updateLMGraph,5000);
            };

            // Function to extract correct width and height

            function getTheWidth() {
                var viewportwidth;
                // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight

                if (typeof window.innerWidth != 'undefined')
                {
                    viewportwidth = window.innerWidth
                }

                // IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)

                else if (typeof document.documentElement != 'undefined'
                    && typeof document.documentElement.clientWidth !=
                    'undefined' && document.documentElement.clientWidth != 0)
                {
                    viewportwidth = document.documentElement.clientWidth
                }

                // older versions of IE

                else
                {
                    viewportwidth = document.getElementsByTagName('body')[0].clientWidth
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
        <table border="0">
            <tr>
            <td><div id='chart_div'></div></td>
            <td><div id='chart_temp'></div></td>
            <td><div id='chart_light'></div></td>
            <td><div id='lm_div'></div></td>
        </tr>
        <tr>
        <td colspan=4><div id='chartLine_div'></div></td>
    </tr>
</table>
</body>
</html>
