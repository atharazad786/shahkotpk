<?php
$mysqli = new mysqli("localhost", "root", "", "", 3306);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

$mysqli->query("DROP DATABASE IF EXISTS shahkotp_skt");
$mysqli->query("CREATE DATABASE shahkotp_skt");
$mysqli->select_db("shahkotp_skt");

$sql = file_get_contents("shahkotp_skt.sql");

if ($mysqli->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
    echo "Successfully imported.";
} else {
    echo "Error importing: " . $mysqli->error;
}
$mysqli->close();
?>
