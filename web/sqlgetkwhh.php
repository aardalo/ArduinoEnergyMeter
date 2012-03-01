<?
include 'dbconfig.php';
mysql_connect(localhost,$user,$password);
@mysql_select_db($database) or die("Unable to select database $database");

$beginTime = date("Y-m-d H:i:s", time() - 60*60); // last hour
$endTime = date("Y-m-d H:i:s");

$query = "SELECT * FROM $table WHERE timestamp=(SELECT MAX(timestamp) from $table)";
$result = mysql_query($query) or die("Unable to run query $query");

$num=mysql_numrows($result);

$i=0;
while ($i < $num) {

	$timestamp = mysql_result($result,$i,"timestamp");
	$count = mysql_result($result,$i,"count");
	$kwhh = mysql_result($result,$i,"kwhh");

	echo "$kwhh";
	$i++;
}

mysql_close();

?>