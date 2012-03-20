<?
$user="xbeegw";
$password="xbeegw";
$database="power";
$table="meter";

mysql_connect(localhost,$user,$password);
@mysql_select_db($database) or die("Unable to select database $database");

$beginTime = date("Y-m-d H:m:s", time() - 60*60); // last hour
$endTime = date("Y-m-d H:m:s");

echo "endTime: $endTime<br>";
echo "beginTime: $beginTime<br>";

// $query = "SELECT * FROM $table";
$query = "SELECT * FROM $table WHERE timestamp BETWEEN '$beginTime' and '$endTime'";
$result = mysql_query($query) or die("Unable to run query $query");

$num=mysql_numrows($result);

echo "<b><center>Database Output of $num rows</center></b><br><br>";
echo "<table>";

$i=0;
while ($i < $num) {

	$timestamp = mysql_result($result,$i,"timestamp");
	$count = mysql_result($result,$i,"count");
	$kwhh = mysql_result($result,$i,"kwhh");

	echo "<tr><td>$timestamp</td><td>$count</td><td>$kwhh</td></tr>";

	$i++;
}

echo "</table>";

mysql_close();

?>