<?php

/**
 * Did not work
 */

ini_set('max_execution_time', 0);
ini_set('memory_limit', '2000M');

$file_contents = trim(file_get_contents('/home/ernest/work/Akash/common-crawl/dev-utils/text.json'));

$file_contents = rtrim($file_contents, ',');

echo ">>" . substr($file_contents, -10);

echo "LENGTH: " . strlen($file_contents) . "\n";

$json = <<<EOJ
{
  "items": [
        $file_contents
  ]
}
EOJ;

$jsonContents = json_decode($json, true);

$links = [];

$linkCount = 0;

foreach ($jsonContents["items"] as $value) {
    if (!isset($value['Envelope']))
        continue;
    if (!isset($value['Envelope']['Payload-Metadata']))
        continue;
    if (!isset($value['Envelope']['Payload-Metadata']['HTTP-Response-Metadata']))
        continue;
    if (!isset($value['Envelope']['Payload-Metadata']['HTTP-Response-Metadata']['HTML-Metadata']))
        continue;
    if (!isset($value['Envelope']['Payload-Metadata']['HTTP-Response-Metadata']['HTML-Metadata']['Links']))
        continue;

    $linksObject = $value['Envelope']['Payload-Metadata']['HTTP-Response-Metadata']['HTML-Metadata']['Links'];

    echo ".";

    $linkCount += count($linksObject);
}

echo "\nTotal links: " . $linkCount . "\n";
