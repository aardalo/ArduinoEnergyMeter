<?php
	$tid = time();
	
	$fp = fopen('XBEELOG.txt', 'r');
	$arr = array();

	$row = 1;
	while (($data = fgetcsv($fp, 50, ",")) !== FALSE) {
        $row++;
		$items = count($data);
		if ($items >0) {
			$arr[$row] = $data[3]; // nr 4 er kWh/h
		}
    }
	fclose($fp);
	
	$limit = $row - (90 / 5);		// Reads last 90/5 lines
	
	for ($i = $limit; $i < $row; $i++){
		$ts = date("s",$tid - ($row - $i) * 5);
		echo "['$ts',$arr[$i]], ";
	}
	
?>
        