<?php
include('lib.twatch.php');
global $slashXML;
date_default_timezone_set('America/Los_Angeles');

echo "Connecting...[";
echo TwatchConnect($socket);
echo "]\n";
sleep(1);
echo "Reseting Screen...[";
echo TwatchClearSCreen($socket);
echo "]\n";
sleep(1);
echo "Backlight Flash...[";
echo TwatchBacklightOn($socket);
echo "]\n";

while (1==1) {
	echo bedbacklight($socket);
	echo fatclock($socket);
	sleep(5);
	echo TwatchClearSCreen($socket);
	echo "Clock...";
	echo clock($socket);
	echo "Weather...";
	echo weather($socket);
	sleep(5);
	echo "Clock...";
	echo clock($socket);
	echo "Loads...";
	echo irevloads($socket);
	sleep(5);
	//echo "Slashdot...";
	//echo slashdot($socket,GetSlashdotXML($slashXML));
}

function GetSlashdotXML($slashXML) {
	if (!isset($slashXML)) {
		echo "RSS: Loading...";
		$slashXML = simplexml_load_file("http://slashdot.org/index.rss");
		return $slashXML;
	} else {
		echo "RSS: Cached ";
	}
	$minute = date("i");
	if ($minute == 00 || $minute == 30) {
		echo "RSS: Refreshing...";
		$slashXML = simplexml_load_file("http://slashdot.org/index.rss");
		return $slashXML;
	}
}

function bedbacklight($socket) {
	$hour = date("G");
	if ($hour >= 20) {
		$result = TwatchBacklightOff($socket);
		return("Backlight: Off ($hour) [$result]\n");
	}
	if ($hour <= 6) {
		$result = TwatchBacklightOff($socket);
		return("Backlight: Off ($hour) [$result]\n");
	}
	if ($hour >= 7 && $hour <= 19) {
		$result = TwatchBacklightOn($socket);
		return("Backlight: On ($hour) [$result]\n");
	}
}

function fatclock($socket) {
	$result = TwatchScrollAscii($socket,date("g:i"),250000);
	//$result = TwatchPrint($socket,date("g:i"));
	return ($result);
}

function clock($socket) {
	for ($ticker = 1; $ticker <= 10; $ticker++) {
		echo "$ticker ";
		$timerows[1] = "";
		$timerows[2] = date("g:i:sa T");
		$timerows[3] = date("l, M jS");
		$timerows[4] = "";
		TwatchPrintRows($socket, $timerows, -1, 'center');
		sleep(1);
	}
	return ("$timerows[2]\n");
}

function weather($socket) {
	$xml = simplexml_load_file("http://presence.irev.net/weather/rss3.php");
	TwatchClearSCreen($socket);
	TwatchPosition($socket);
	$weather = $xml->channel->item[0]->title;
	$weather = preg_replace("/barometer/i","bars",$weather);
	//$weather = wordwrap($xml->channel->item[0]->title,20);
	TwatchPrint($socket,$weather);
	return ("$weather\n");
}

function irevloads($socket) {
	$xml = simplexml_load_file("http://sl.irev.net/");
	$loadrows[1] = "iRev.net Server Load";
	$loadrows[2] = " 1 minute: ".$xml->channel->item[0]->title;
	$loadrows[3] = " 5 minute: ".$xml->channel->item[1]->title;
	$loadrows[4] = "15 minute: ".$xml->channel->item[2]->title;
	TwatchPrintRows($socket, $loadrows, -1, 'left');
	return ($loadrows[2]."\n");
}

function slashdot($socket, $slashXML) {
	// only once and a while! $slash = simplexml_load_file("http://slashdot.org/index.rss");
	$slashdot[1] = $slashXML->item[0]->title;
	$slashdot[2] = $slashXML->item[1]->title;
	$slashdot[3] = $slashXML->item[2]->title;
	$slashdot[4] = $slashXML->item[3]->title;
	TwatchScrollRows($socket,$slashdot);
	return ("$slashdot[1]\n");
}

?>
