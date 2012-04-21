<?php

	if (isset($_REQUEST['lat']) && isset($_REQUEST['long'])) {
		file_put_contents("coords.dat", $_REQUEST['lat']." ".$_REQUEST['long']);
	}

	
	$dat = file_get_contents("coords.dat");

	if (isset($_REQUEST['format']) && $_REQUEST['format'] == "json") {
		$tmp = explode(" ", $dat);
		echo trim(json_encode($tmp));
	} else {
		echo trim($dat);
	}	

