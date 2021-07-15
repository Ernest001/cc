<?php

$fileh = $fileh_contents = fopen(getcwd() . "/wat.paths", "r");

$s3EndPoint = "https://commoncrawl.s3.amazonaws.com/";

$servername = "localhost";
$username = "ernest";
$password = "drinkYourJuic3Kid";
$database = "common_crawl";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$start = (int) $argv[1];
$end = (int) $argv[2];

$linksCount = 0;

$currentLine = 0;

echo "Starting processing at line $start and finishing at $end\n";

while (!feof($fileh)) {

    echo $currentLine . "\n";

    if ($currentLine < $start) {
        $currentLine++;
        continue;
    }

    if ($currentLine === $end) {
        echo "Finished processing\n";
        break;
    }

    if ($currentLine === $start) {
        echo "Started processing\n";
    }


    $line  = trim(fgets($fileh));

    $pathChunks = explode("/", $line);
    $fileName = $pathChunks[count($pathChunks) - 1];


    /**
     * Start processing the WAT
     */
    echo $s3EndPoint . $line . "\n";
    // exit;

    $fh = gzopen($s3EndPoint . $line, "r");

    while (!gzeof($fh)) {
        $_line  = gzgets($fh);

        if (substr($_line, 0, 11) !== '{"Container') {
            continue; //Not valid JSON
        }

        $jsonContents = json_decode($_line, true);

        if ($jsonContents === null) {
            echo "ERROR\n\n";
            echo $_line;
            echo "\n";
            die();
        }

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

        $links = [];

        foreach ($linksObject as $link) {

            if (!isset($link['url'])) continue;

            if (!strstr($link['url'], "http://") and !strstr($link['url'], "https://")) continue;

            $links[] = implode("','", [
                $link['url'],
                $fileName
            ]);
        }

        $linksCount += count($links);
        $valuesStr = "('" . implode("),(", $links) . "')";

        $linksToSave = "insert ignore into links (href, source_document) values $valuesStr";
        $conn->query($linksToSave);

        usleep(20);
    }

    echo "Found " . $linksCount . " so far \n";

    gzclose($fh);

    /**
     * Finish processing the WAT
     */

    $currentLine++;
}


$end = microtime(true);

$spentTime = $end - $start;


echo "\n";

echo "Found " . $linksCount . " links \n";

echo "Process used ". memory_get_peak_usage() / 1024 / 1024 . " MiB and process took " . $spentTime . " seconds";

fclose($fileh);
