<?php

$parallelProcesses = 10;

$file = "wat.paths";

// for ($i = 0; $i <= $lineCount; $i += $parallelProcesses) {

// }
$lineCount = exec("wc -l wat.paths");

$lineCount = (int) str_ireplace("wat.paths", "", $lineCount);


$spl = new \SplFileObject($file);
// $lineCount = $this->getLineCount($file);
$pageSize = 1000;
$totalPages = ($lineCount / $pageSize);

$processes = [];

for ($page = 1; $page <= $totalPages; $page++) {

    $currentLine = $page * $pageSize;

    $value = [
        "start" => $currentLine,
        "end" => $currentLine + $pageSize,
        "process_status" => "'created'"
    ];

    $processes[] = implode("," ,$value);

    // for ($i = $currentLine; $i > ($currentLine - $pageSize); $i--) {
    //     $spl->seek($i);

    //     if (strlen($spl->current()) === 0) {
    //         continue;
    //     }

    //     echo "Processing: " . $spl->current();
    // }
}

$values = "(" . implode('),(', $processes) . ")";

$insertSQL = "insert into processes_download_wat_files (start, end, process_status) values $values";

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

$conn->query($insertSQL);
