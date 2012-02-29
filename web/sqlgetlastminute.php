<?
include 'dbconfig.php';

mysql_connect(localhost,$user,$password);
@mysql_select_db($database) or die("Unable to select database $database");

$timeCalc = time();
$endTime = date("Y-m-d H:i:s",$timeCalc);
//echo "END: $timeCalc $endTime<br>";
$timeCalc -= 60*2;
$beginTime = date("Y-m-d H:i:s", $timeCalc); // last minute
//echo "BEGIN: $timeCalc $beginTime<br><br>";

// $query = "SELECT * FROM $table";
$query = "SELECT * FROM $table WHERE timestamp BETWEEN '$beginTime' and '$endTime'";
$result = mysql_query($query) or die("Unable to run query $query");

$num=mysql_numrows($result);

$i=0;
while ($i < $num) {
	$timestamp = mysql_result($result,$i,"timestamp");
	$count = mysql_result($result,$i,"count");
	$kwhh = mysql_result($result,$i,"kwhh");

	$ts = date("s",strtotime($timestamp)); // convert timestamp to desired format in graphs
	
	echo "['$ts',$kwhh]";
	
	$i++;
	if ($i < $num) echo ",";
}

mysql_close();

?>