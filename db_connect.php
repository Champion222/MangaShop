<?php
$serverName = "192.168.6.139"; 
$connectionOptions = array(
    "Database" => "digital_store",
    "Uid" => "sa",
    "PWD" => "12345"
);
$conn = sqlsrv_connect($serverName, $connectionOptions);
?>