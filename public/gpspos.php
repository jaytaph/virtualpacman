<?php

	if (isset($_REQUEST['lat']) && isset($_REQUEST['long'])) {
		file_put_contents("coords.dat", $_REQUEST['lat']." ".$_REQUEST['long']);
	}

	echo file_get_contents("coords.dat");
