<?php

$startTime = microtime(true);

/**
 * MYSQL
 */

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


/**
 * END OF MYSQL
 */
$fileh = $fileh_contents = fopen(getcwd() . "/wat.paths", "r");

$s3EndPoint = "https://commoncrawl.s3.amazonaws.com/";

$start = (int) $argv[1];
$end = (int) $argv[2];
$hc = 0;

$linksCount = 0;

$currentLine = 0;

if (file_exists("urls.txt")) unlink("urls.txt");

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

        $jsonContents = json_decode($_line, true);

        if (json_last_error()) {
            continue;
        }

        if (!isset($jsonContents['Envelope']))
            continue;
        if (!isset($jsonContents['Envelope']['WARC-Header-Metadata']))
            continue;
        if (!isset($jsonContents['Envelope']['WARC-Header-Metadata']['WARC-Target-URI']))
            continue;

        $warcTargetLink = $jsonContents['Envelope']['WARC-Header-Metadata']['WARC-Target-URI'];
        $warcTargetHost = str_ireplace("www.", "", parse_url($warcTargetLink)['host']);


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

        // echo "===============WARC TARGET LINK\n" . $warcTargetLink . "\n";

        $columns = "";


        $hosts = [];
        $hostsHashMap = [];
        // foreach ($linksObject as $link) {

        //     if (!isset($link['url'])) continue;

        //     if (!strstr($link['url'], "http://") and !strstr($link['url'], "https://")) continue;

        //     $baseURL = parse_url(trim($link['url']));

        //     if (!isset($baseURL['host'])) {
        //         continue;
        //     }

        //     if (strstr($warcTargetLink, sprintf("%s://%s", $baseURL['scheme'] ?? "http", $baseURL['host']))) continue;

        //     $thisHost = parse_url($link['url']);
        //     if (!isset($thisHost['host'])) continue;
        //     $host = explode(".", str_ireplace("www.", "", $thisHost['host']));
        //     krsort($host);
        //     $hosts[] = implode(".", $host);
        // }

        // $hosts = array_filter($hosts);

        // $hc += count($hosts);

        // $hostString = sprintf("'%s'", implode("','", $hosts));
        // $thisSQL = "select * from hosts_graph where host_rev in ($hostString)";
        // $hostsResults = $conn->query($thisSQL);

        // if ($hostsResults !== FALSE)
        //     while ($hostGraphResult = $hostsResults->fetch_assoc()) {
        //         $hostRevPieces = explode(".", $hostGraphResult['host_rev']);
        //         krsort($hostRevPieces);
        //         $hostsHashMap[implode(".", $hostRevPieces)] = $hostGraphResult['harmonicc_val'];
        //     }


        foreach ($linksObject as $link) {

            if (!isset($link['url'])) continue;

            if (!strstr($link['url'], "http://") and !strstr($link['url'], "https://")) continue;

            $baseURL = parse_url(trim($link['url']));

            if (!isset($baseURL['host'])) {
                continue;
            }

            if (strstr($warcTargetLink, sprintf("%s://%s", $baseURL['scheme'] ?? "http", $baseURL['host']))) continue;

            //Order: source, target, anchor text

            if (isset($link['alt'])) {
                $link['alt']  = '[alt] ' . $link['alt'];
            }

            if (isset($link['text'])) {
                $link['text'] = str_ireplace("'", "&#39;", $link['text']);
                $link['text'] = str_ireplace("\n", "", $link['text']);
            }

            $dataArray = [
                'source_url_id' => rand(1, 10000000),
                'source_domain_id' => rand(1, 10000000),
                'target_url_id' => rand(1, 10000000),
                'target_domain_id' => rand(1, 10000000),
                // 'url_source' => str_ireplace("'", "&#39;", $link['url']),
                // 'url_target' => str_ireplace("'", "&#39;", $warcTargetLink),
                'anchor_text' => $link['text'] ?? $link['alt'] ?? '',
                // 'source_harmonic_centrality' => $hostsHashMap[str_ireplace("www.", "", $baseURL['host'])] ?? '-',
                // 'target_harmonic_centrality' => $hostsHashMap[$warcTargetHost] ?? '-',//3.0829618E7
                'source_harmonic_centrality' => rand(1000000000, 10000000000) / 10000000,
                'target_harmonic_centrality' => rand(1000000000, 10000000000) / 10000000, //
            ];

            if (strlen($columns) == 0) {
                $columns = implode(",", array_keys($dataArray));
            }

            $dataCSV = sprintf("'%s'", implode("','", $dataArray));

            fwrite($fsave, $dataCSV . "\n");

            $linksCount++;
            // usleep(10);
        }
    }

    fclose($fsave);

    echo "Found " . $linksCount . " so far \n";

    gzclose($fh);

    /**
     * Finish processing the WAT
     */

    $currentLine++;
}

$endTime = microtime(true);

$spentTime = (int) $endTime - (int) $startTime;

echo "\n";

echo "Found " . $linksCount . " links \n";

echo "HC: " . $hc . " links \n";

echo "Process used " . memory_get_peak_usage() / 1024 / 1024 . " MiB and process took " . $spentTime . " seconds\n";

fclose($fileh);

echo <<<SQL

LOAD DATA LOCAL INFILE '/home/ernest/common-crawl-src/urls.txt' INTO TABLE urls FIELDS TERMINATED BY ',' ENCLOSED BY "'"  LINES TERMINATED BY '\n' ($columns);

SQL;
/**
 * 
 */
