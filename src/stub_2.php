<?php

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

$pageSize = 10;

$maxLimit = $pageSize;

$start = $conn->query("SELECT MIN(id) as min_id FROM hosts_graph")->fetch_assoc();

$end = $conn->query("SELECT MAX(id) as max_id FROM hosts_graph")->fetch_assoc();

$offset = 0;

$prevOffset = $offset;

$j = 0;

while ($offset < $end['max_id']) {

    $sql1 = "SELECT id,host_rev FROM hosts_graph WHERE checked=0  and id BETWEEN $offset AND $maxLimit";

    echo $sql1 . PHP_EOL;

    $res = $conn->query($sql1);

    echo "..\n";

    $batchUpdate = [];
    $checkedIds = [];

    while ($results = $res->fetch_assoc()) {
        $domain = explode(".", $results['host_rev']);
        krsort($domain);
        $reversedDomain = implode(".", $domain);

        $batchUpdate[] = sprintf("(%d, '%s')", $results['id'], $reversedDomain);
        $checkedIds[] = $results['id'];
    }

    // print_r($batchUpdate); exit;

    $batchUpdateString = implode(',', $batchUpdate);

    $updateQuery = "insert into hosts_graph (id, host_rev) VALUES $batchUpdateString on duplicate key update (host_rev)";

    echo $updateQuery;
    $conn->query($updateQuery);

    $idsString = implode(",", $checkedIds);
    $conn->query("update hosts_graph set checked=1 where id in ($idsString)");

    $offset += $pageSize;

    $maxLimit = $offset + $pageSize;
    if ($j === 1) exit;
    $j++;
}
