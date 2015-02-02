<?php
include('lib.twatch.php');
date_default_timezone_set('America/Los_Angeles');

TwatchConnect($socket);
TwatchClearSCreen($socket);
sleep(1);
TwatchBacklightOn($socket);
sleep(1);

while (1==1) {
	$timerows[1] = date("g:i:s a T");
	$timerows[2] = "";
	$timerows[3] = date("l, M jS");
	$timerows[4] = "";
	TwatchPrintRows($socket, $timerows, -1, 'center');
	sleep(1);
}

?>
