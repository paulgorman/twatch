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
// 201801:
	* * * * * /bin/wget -O /home/presence/twatch/lynnwood.txt http://gallery.irev.net/weather/darksky-lynnwood.txt > /dev/null 2>&1
	* * * * * /bin/wget -O /home/presence/twatch/irev-loads.xml http://sl.irev.net/ > /dev/null 2>&1
	* * * * * /bin/php /home/presence/twatch/sl.php > /home/presence/twatch/sl.xml
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
		usleep(1.00 * 1000000);
	}
	for ($i=1;$i < 6; $i++) {
		clock($socket);
		usleep(1.00 * 1000000);
	}
	for ($i=1;$i < 6; $i++) {
		inmhloads($socket);
		usleep(1.00 * 1000000);
	}
	for ($i=1;$i < 6; $i++) {
		clock($socket);
		usleep(1.00 * 1000000);
	}
}

function bw($socket) {
	global $snmp;
	$timerows[1] = date("g:i:sa T");
	if (preg_match("/ready/i",$snmp['ricoh'])) {
		$timerows[2] = date("l, M jS");
	} else {
		$timerows[2] = preg_replace("/string/i","",$snmp['ricoh']);
		$timerows[2] = preg_replace("/[^a-z0-9A-Z ]/","",$timerows[2]);
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
	$weather = file_get_contents("lynnwood.txt");
	$weather = preg_replace("/for the hour./i","next hr.",$weather);
	$weatherarray = explode("\n",$weather);
	$weatherforecastdeduped = $weatherarray[1];
	foreach (preg_split("/[\s,]+/",$weatherarray[0]) as $key => $word) {
		// don't repeat the word "rain" on the second line when rain's getting worse or clearing up
		$weatherforecastdeduped = preg_replace("/$word/i","",$weatherforecastdeduped);
	}
	$weatherforecastdeduped = ucfirst(preg_replace("/\s{2,}/","",$weatherforecastdeduped));
	$timerows[1] = date("g:i:sa T");
	$timerows[2] = date("l, M jS");
	$timerows[3] = substr($weatherarray[0],0,20);
	//$timerows[4] = substr($weatherarray[1],0,20);
	$timerows[4] = substr($weatherforecastdeduped,0,20);
	TwatchPrintRows($socket, $timerows, -1, 'center');
	//$timerows[4] = $weatherarray[1];
	//TwatchScrollRows($socket, $timerows, 100000, 0, ' ', 'center');
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
	$xml = simplexml_load_file("sl.xml");
	$loadrows[1] = date("g:i:sa T");
	$loadrows[2] = date("l, M jS");
	$loadrows[3] = "VPS  1 minute:".$xml->channel->item[0]->title;
	$loadrows[4] = "VPS 15 minute:".$xml->channel->item[2]->title;
	TwatchPrintRows($socket, $loadrows, -1, 'center');
}

?>
