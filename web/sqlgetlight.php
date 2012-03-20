<?
error_reporting(E_ALL);
include 'dbconfig.php';
mysql_connect('localhost',$user,$password);
@mysql_select_db($database) or die("Unable to select database $database");


$beginTime = date("Y-m-d H:i:s", time() - 60*60); // last hour
$endTime = date("Y-m-d H:i:s");
$table = 'utelys';

$query = "select * from $table where timestamp=(select max(timestamp) from $table)";
$result = mysql_query($query) or die("Unable to run query $query");

$num=mysql_numrows($result);

$i=0;
while ($i < $num) {

	$timestamp = mysql_result($result,$i,"timestamp");
	$value = mysql_result($result,$i,"value");

	echo "$value";
	$i++;
}

mysql_close();

?>