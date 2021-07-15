<?php

$parallelProcesses = 10;

$runningProcessSQL = "select count(*) as c from processes_download_wat_files where process_status='processing'";

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

$runningProcessesArr = $conn->query($runningProcessSQL)->fetch_assoc();

if ($runningProcessesArr['c'] === $parallelProcesses) {
    echo "Maximum parallel processes\n";
    exit;
}

$diffMaxProcesses = $parallelProcesses - $runningProcessesArr['c'];

$moreProcessesSQL = "select * from processes_download_wat_files where  process_status='failed' or process_status='created' limit " . $diffMaxProcesses;
$moreProcesses = $conn->query($moreProcessesSQL);

$processIds = [];

while ($process = $moreProcesses->fetch_assoc()) {
    # code...

    if (!$process) {
        echo "No more processes to run\n";
        exit;
    }

    $processIds[] = $process['id'];

    $dir = getcwd();

    $command = "cd $dir && php process_files_v2.php " . $process['start'] . " " . $process['end'] . "\n";

    echo $command;

    exec($command);
}

$ids = implode(",", $processIds);

$updateToProcessing = "update processes_download_wat_files set process_status='processing' where id in ($ids)";

$conn->query($updateToProcessing);
