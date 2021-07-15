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


echo "Connected successfully";
die();

$paths_fh = fopen("wat.paths", "r");
$cc_url =  "https://commoncrawl.s3.amazonaws.com/";

while (!feof($paths_fh)) {
    $line = fgets($paths_fh);
    $paths_file_full_url = $cc_url . $line;

    $file_contents = file_get_contents($paths_file_full_url);
    
    $sql = "insert into downloaded_files (name) values ($paths_file_full_url)";
}


// $file = 
//$ curl -O crawl-data/CC-MAIN-2017-04/segments/1484560279657.18/wat/CC-MAIN-20170116095119-00156-ip-10-171-10-70.ec2.internal.warc.wat.gz
//crawl-data/CC-MAIN-2021-21/segments/1620243988696.23/wat/CC-MAIN-20210505203909-20210505233909-00020.warc.wat.gz
