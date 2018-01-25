<?php
/*
 * Server-Load RSS Feed V1.00 
 * By Markus Junginger, http://jars.de
 * modified by Markus Knigge for the windows sidebar server gadget
 */
date_default_timezone_set('America/Los_Angeles');
$string = "<rss version='2.0'><channel><title>CentOS Server Load</title><description>CentOS Server Load</description><pubDate>";
$string .= date("r"); 
$string .= "</pubDate>\n";
if (function_exists('sys_getloadavg')) {
        $loadArray = sys_getloadavg();
        $minute[0] = "Average load past 1 minute";
        $minute[1] = "Average load past 5 minutes";
        $minute[2] = "Average load past 15 minutes";
}
for ($i=0;$i<3;$i++) {
	$string .="\n<item><title>";
	$string .= number_format(round($loadArray[$i],2),2);
	$string .="</title><description><![CDATA[";
	$string .= $minute[$i];
	$string .="]]></description><pubDate>";
	$string .= date("r");
	$string .= "</pubDate></item>";
}
$string .="\n</channel></rss>";

echo $string;
