<?php

$fh = fopen("/home/ernest/insta.php", "r");

while (!feof($fh)) {
    $_line  = fgets($fh);

    $jsonContents = json_decode($_line, true);

    if (json_last_error()) {
        // echo "ERROR\n\n";
        // echo $_line;
        // echo "\n";

        // echo json_last_error_msg();

        continue;
    }

    // echo $_line;

    // print_r($jsonContents);
    echo "\n\n";

    if (!isset($jsonContents['Envelope']))
        continue;
    if (!isset($jsonContents['Envelope']['WARC-Header-Metadata']))
        continue;
    if (!isset($jsonContents['Envelope']['WARC-Header-Metadata']['WARC-Target-URI']))
        continue;

    $warcTargetLink = $jsonContents['Envelope']['WARC-Header-Metadata']['WARC-Target-URI'];

    print_r($warcTargetLink);

    if (!isset($jsonContents['Envelope']))
        continue;
    if (!isset($jsonContents['Envelope']['Payload-Metadata']))
        continue;
    if (!isset($jsonContents['Envelope']['Payload-Metadata']['HTTP-Response-Metadata']))
        continue;
    if (!isset($jsonContents['Envelope']['Payload-Metadata']['HTTP-Response-Metadata']['HTML-Metadata']))
        continue;
    if (!isset($jsonContents['Envelope']['Payload-Metadata']['HTTP-Response-Metadata']['HTML-Metadata']['Links']))
        continue;

    $linksObject = $jsonContents['Envelope']['Payload-Metadata']['HTTP-Response-Metadata']['HTML-Metadata']['Links'];

    foreach ($linksObject as $link) {

        if (!isset($link['url'])) continue;

        if (!strstr($link['url'], "http://") and !strstr($link['url'], "https://")) continue;

        if (strstr($link['url'], $warcTargetLink)) continue;

        $linkCSV = '"' . $link['url'] . '"';

        fwrite($fsave, $linkCSV . "\n");

        $linksCount++;
    }

    usleep(20);
}
