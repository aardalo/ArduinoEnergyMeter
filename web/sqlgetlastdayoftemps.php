<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?
        include 'dbconfig.php';
        $table = 'temp';

        mysql_connect('localhost', $user, $password);
        @mysql_select_db($database) or die("Unable to select database $database");

        $beginTime = date("Y-m-d H:i:s", time() - 60 * 60 * 24); // last 24 hours
        $endTime = date("Y-m-d H:i:s");

        $query = "SELECT * FROM $table WHERE (timestamp BETWEEN '$beginTime' and '$endTime') AND sensor='utetemp'";
        $result = mysql_query($query) or die("Unable to run query $query");

        $num = mysql_numrows($result);

        $i = 0;

        while ($i < $num) {
            //$count = mysql_result($result,$i,"count");
            $value = mysql_result($result, $i, "value");

            if ($i % 17 == 0) { // 17 is every 1 in 17 datapoins = approx 1000 datapoints in a day
                $timestamp = mysql_result($result, $i, "timestamp");
                $ts = date("H:i", strtotime($timestamp)); // convert timestamp to desired format in graphs
                echo "['$ts',$value]";
                if (($i + 1) < $num)
                    echo ",";
            };

            $i++;
        }

        mysql_close();
        ?>
    </body>
</html>
