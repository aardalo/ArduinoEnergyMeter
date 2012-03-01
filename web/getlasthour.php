<?php
	$tid = time();
	
	$fp = fopen('XBEELOG.txt', 'r');
	$arr = array();

	$row = 1;
	while (($data = fgetcsv($fp, 50, ",")) !== FALSE) {
        $row++;
		$items = count($data);
		if ($items >0) {
			$arr[$row] = $data[3]; 			// the 4th item in the line (CSV) is number of kWh/h 
		}
    }
	fclose($fp);
	
	$factor = 1;							
	$limit = $row - $factor * (24*60*60/5);	// read 1 days of 5 sec interval data
	if ($limit < 0) { $limit = 0; };
	
	for ($i = $limit; $i < $row; $i = $i + $factor){
		$ts = date("H:i",$tid - ($row - $i) * 5);
		echo "['$ts',$arr[$i]], ";
	}
	
?>
        