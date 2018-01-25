<?
/*
	crontab, set for like every ten minutes, 2 minutes padding:
		2,12,22,32,42,52 * * * *        /usr/local/bin/php /home/presence/twatch/darksky-lynnwood.php > /dev/null 2>&1
*/
require_once("darksky-key.php");
$url = "https://api.darksky.net/forecast/$key/47.849280,-122.244862";

$jsonblob = file_get_contents($url);
$log = fopen("darksky-lynnwood.json","w");
fwrite($log,$jsonblob);
fclose($log);
$json = json_decode($jsonblob,1); // gives me an associative array

$summary = $json['currently']['summary'];
$temp = $json['currently']['temperature'];
$wind = $json['currently']['windSpeed'];
$next = $json['minutely']['summary'];
$remainder = $json['hourly']['summary'];

//$string = "Now: $summary, ".$temp."F, ".$wind."MPH\nHour: $next\n";
$string = "$summary, ".$temp."F, ".$wind."MPH\n$next\n";

$output = fopen("darksky-lynnwood.txt","w");
fwrite($output, $string);
fclose($output);
