<?
error_reporting(E_ALL);
include 'dbconfig.php';

mysql_connect('localhost',$user,$password);
@mysql_select_db($database) or die("Unable to select database $database");

$beginTime = date("Y-m-d H:i:s", time() - 60*60); // last hour
$endTime = date("Y-m-d H:i:s");

// $query = "SELECT * FROM $table";
$query = "SELECT * FROM $table WHERE timestamp BETWEEN '$beginTime' and '$endTime'";
$result = mysql_query($query) or die("Unable to run query $query");

$num=mysql_numrows($result);

$i=0;
while ($i < $num) {
	$timestamp = mysql_result($result,$i,"timestamp");
	$count = mysql_result($result,$i,"count");
	$kwhh = mysql_result($result,$i,"kwhh");

	$ts = date("H:i",strtotime($timestamp)); // convert timestamp to desired format in graphs
	echo "['$ts',$kwhh]";

	$i++;
	if ($i < $num) echo ",";
}

mysql_close();

?>