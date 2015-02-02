<?php
include('lib.twatch.php');
date_default_timezone_set('America/Los_Angeles');
$snmp = array(
	"curin" => 0, 
	"curout" => 0,
	"bpsin" => 0,
	"bpsout" => 0
);
global $snmp;
/*
	into cron:
	fetch -q -o irev-loads.xml http://sl.irev.net/
	fetch -q -o weather.xml http://presence.irev.net/weather/rss3.php
*/

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
	//TwatchClearSCreen($socket);
	for ($i=1;$i < 6; $i++) {
		irevloads($socket);
		snmp();
		usleep(1.00 * 1000000);
	}
	for ($i=1;$i < 6; $i++) {
		snmp();
		bw($socket);
		usleep(1.00 * 1000000);
	}
	for ($i=1;$i < 6; $i++) {
		clock($socket);
		snmp();
		usleep(1.00 * 1000000);
	}
	for ($i=1;$i < 6; $i++) {
		snmp();
		bw($socket);
		usleep(1.00 * 1000000);
	}
	for ($i=1;$i < 6; $i++) {
		inmhloads($socket);
		snmp();
		usleep(1.00 * 1000000);
	}
	for ($i=1;$i < 6; $i++) {
		snmp();
		bw($socket);
		usleep(1.00 * 1000000);
	}
	for ($i=1;$i < 6; $i++) {
		clock($socket);
		snmp();
		usleep(1.00 * 1000000);
	}
	for ($i=1;$i < 6; $i++) {
		snmp();
		bw($socket);
		usleep(1.00 * 1000000);
	}
}

function bw($socket) {
	global $snmp;
	$timerows[1] = date("g:i:sa T");
	if (preg_match("/ready/i",$snmp['ricoh'])) {
		$timerows[2] = date("l, M jS");
	} else {
		$timerows[2] = "Ricoh: ". $snmp['ricoh'];
	}
	$timerows[3] = "Kbps In:  " . sprintf("% 6s",$snmp['kbpsin']);
	$timerows[4] = "Kbps Out: " . sprintf("% 6s",$snmp['kbpsout']);
	TwatchPrintRows($socket, $timerows, -1, 'center');
}

function snmp() {
	global $snmp;
	$snmp['curin'] = snmpget("192.168.1.1", "public", "IF-MIB::ifInOctets.10");
	$snmp['curout'] = snmpget("192.168.1.1", "public", "IF-MIB::ifOutOctets.10");
	$snmp['curin'] = explode(" ",$snmp['curin'])[1];
	$snmp['curout'] = explode(" ",$snmp['curout'])[1];
	$diffin = $snmp['curin'] - $snmp['oldin'];
	$diffout = $snmp['curout'] - $snmp['oldout'];

	$snmp['kbpsin'] = number_format($diffin * 8 / 1000);
	$snmp['kbpsout'] = number_format($diffout * 8 / 1000);

	$snmp['oldin'] = $snmp['curin'];
	$snmp['oldout'] = $snmp['curout'];
	
	$snmp['ricoh'] = snmpget("192.168.1.3", "public", "SNMPv2-SMI::mib-2.43.16.5.1.2.1.1");
}	

function clock($socket) {
	$matches = array();
	$xml = simplexml_load_file("weather.xml");
	$weatherline = $xml->channel->item[0]->title;
	//echo "line:$weatherline\n";
	//87 degrees, 11% Humidity, Wind SSW at 12 MPH, barometer @ 29.75, Scattered Clouds Sky. 
	//63 degrees, 94% Humidity, Wind VARIABLE at 4 MPH, barometer @ 29.87, Light Rain Sky. 
	//82 degrees,9% NORTH at 0 MPH, barometer @ 29.97, Partly Cloudy Sky.
	preg_match("/(\d{2,3}) degrees, (\d{1,3}\%) Humidity, Wind ([A-Za-z]{1,8}) at (\d{1,3}) MPH, barometer \@ \d{1,2}\.\d{0,2}, (.*)/",$weatherline,$matches);
	//echo "degrees  1: " . $matches[1] ."\n";
	//echo "humid %  2: " . $matches[2] ."\n";
	//echo "wind dir 3: " . $matches[3] ."\n";
	//echo "wind mph 4: " . $matches[4] ."\n";
	//echo "sky      5: " . preg_replace("/ sky\./i","",$matches[5]) ."\n";
	$weatherline = $matches[1].chr(161). " " . $matches[2] . " " . $matches[4] ."mph ". $matches[3] . " " . preg_replace("/ sky\./i","",$matches[5]);
	$weather = wordwrap($weatherline,20,"\r\n");
	$weatherarray = explode("\r\n",$weather);
	$timerows[1] = date("g:i:sa T");
	$timerows[2] = date("l, M jS");
	$timerows[3] = $weatherarray[0];
	$timerows[4] = $weatherarray[1];
	TwatchPrintRows($socket, $timerows, -1, 'center');
	//echo "$timerows[1]\n$timerows[2]\n$timerows[3]\n$timerows[4]\n";
}

function irevloads($socket) {
	//$xml = simplexml_load_file("http://sl.irev.net/");
	$xml = simplexml_load_file("irev-loads.xml");
	$loadrows[1] = date("g:i:sa T");
	$loadrows[2] = date("l, M jS");
	$loadrows[3] = "iRev  1 minute: ".$xml->channel->item[0]->title;
	$loadrows[4] = "iRev 15 minute: ".$xml->channel->item[2]->title;
	TwatchPrintRows($socket, $loadrows, -1, 'center');
}

function inmhloads($socket) {
	$xml = simplexml_load_file("inmh-loads.xml");
	$loadrows[1] = date("g:i:sa T");
	$loadrows[2] = date("l, M jS");
	$loadrows[3] = "IVPS  1 minute:".$xml->channel->item[0]->title;
	$loadrows[4] = "IVPS 15 minute:".$xml->channel->item[2]->title;
	TwatchPrintRows($socket, $loadrows, -1, 'center');
}

?>
