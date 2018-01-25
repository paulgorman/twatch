<?

$xml = simplexml_load_file("http://sl.irev.net/");

#$one = (string) $xml->channel->item[0]->description.": ".$xml->channel->item[0]->title;
#$five = (string) $xml->channel->item[1]->description.": ".$xml->channel->item[1]->title;
#$fifteen = (string) $xml->channel->item[2]->description.": ".$xml->channel->item[2]->title;

$one = $xml->channel->item[0]->title;
$five = $xml->channel->item[1]->title;
$fifteen = $xml->channel->item[2]->title;

echo " 1 minute: $one\n";
echo " 5 minute: $five\n";
echo "15 minute: $fifteen\n";

