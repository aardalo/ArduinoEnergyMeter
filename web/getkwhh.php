<?php
	define('YOUR_EOL', ",");
	$fp = fopen('XBEELOG.txt', 'r');

	$pos = -1; $line = ''; $c = '';
	do {
		$line = $c . $line;
		fseek($fp, $pos--, SEEK_END);
		$c = fgetc($fp);
	} while ($c != YOUR_EOL);

	echo "['kWh/h',$line]";
	
	fclose($fp);
?>
        