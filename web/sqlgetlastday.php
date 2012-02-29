<?
include 'dbconfig.php';

mysql_connect(localhost,$user,$password);
@mysql_select_db($database) or die("Unable to select database $database");

$beginTime = date("Y-m-d H:i:s", time() - 60*60*24); // last hour
$endTime = date("Y-m-d H:i:s");

// $query = "SELECT * FROM $table";
$query = "SELECT * FROM $table WHERE timestamp BETWEEN '$beginTime' and '$endTime'";
$result = mysql_query($query) or die("Unable to run query $query");

$num=mysql_numrows($result);

$i=0;
$avgkWhh = 0; // to store sum of kWhh for averaging

while ($i < $num) {
	//$count = mysql_result($result,$i,"count");
	$kwhh = mysql_result($result,$i,"kwhh");

	$avgkWhh = $avgkWhh + $kwhh;
	
	if ($i % 17 == 0) { // 17 is every 1 in 17 datapoins = approx 1000 datapoints in a day
		$timestamp = mysql_result($result,$i,"timestamp");
		$avgkWhh = round($avgkWhh / 17,2);
		$ts = date("H:i",strtotime($timestamp)); // convert timestamp to desired format in graphs
		echo "['$ts',$avgkWhh]"; // is now average for last 17*5s period
		if ($i+1 < $num) echo ",";
		$avgkWhh = 0;
	};
	
	$i++;
}

mysql_close();

?>