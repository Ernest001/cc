<?php

$startTime = microtime(true);

$fileh = $fileh_contents = fopen(getcwd() . "/wat.paths", "r");

$s3EndPoint = "https://commoncrawl.s3.amazonaws.com/";

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

    $fsave = fopen("urls.txt", "w+");

    $line  = trim(fgets($fileh));

    $pathChunks = explode("/", $line);
    $fileName = $pathChunks[count($pathChunks) - 1];


    /**
     * Start processing the WAT
     */
    echo $s3EndPoint . $line . "\n";
    // exit;

    $fh = gzopen($s3EndPoint . $line, "r");

    $links = [];

    while (!gzeof($fh)) {
        $_line  = gzgets($fh);

        if (substr($_line, 0, 11) !== '{"Container') {
            continue; //Not valid JSON//
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

        foreach ($linksObject as $link) {

            if (!isset($link['url'])) continue;

            if (!strstr($link['url'], "http://") and !strstr($link['url'], "https://")) continue;

            // $links[] = '"' . $link['url'] . '"' . ',"' . $fileName . '"';
            $linkCSV = '"' . $link['url'] . '"';
            // $links[] = $linkCSV;
            fwrite($fsave, $linkCSV . "\n");
            $linksCount++;
        }

        usleep(20);
    }

    fclose($fsave);

    echo "Found " . $linksCount . " so far \n";

    gzclose($fh);

    /**
     * Finish processing the WAT
     */

    $currentLine++;
}

// function generatorLinks($linksObject)
// {
//     foreach ($linksObject as $link) {
//         if (!isset($link['url'])) continue;

//         if (!strstr($link['url'], "http://") and !strstr($link['url'], "https://")) continue;

//         yield '"' . $link['url'] . '"';
//     }
// }

$endTime = microtime(true);

$spentTime = (int) $endTime - (int) $startTime;

echo "\n";

echo "Found " . $linksCount . " links \n";

echo "Process used " . memory_get_peak_usage() / 1024 / 1024 . " MiB and process took " . $spentTime . " seconds\n";

fclose($fileh);

/**
 * SELECT TABLE_NAME AS `Table`, ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024) AS `Size (MB)` FROM information_schema.TABLES WHERE TABLE_SCHEMA = "common_crawl" AND TABLE_NAME = "hosts_graph" ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC
 * 
 * LOAD DATA LOCAL INFILE '/home/ernest/common-crawl-src/urls.txt' INTO TABLE links FIELDS TERMINATED BY ','  
 * ENCLOSED BY '"'  LINES TERMINATED BY '\n' (href);
 */

/**
 * Loading all the domain ranks data into MySQL:
 *
 * LOAD DATA LOCAL INFILE '/home/ernest/common-crawl/cc-main-2021-feb-apr-may-host-ranks.txt' INTO TABLE hosts_graph   LINES TERMINATED BY '\n' IGNORE 1 LINES (harmonic_pos, harmonic_val, pr_pos, pr_val, host_rev);
 */
