<?php
$server = "(localdb)\\MSSQLLocalDB";
$database = "friendlink";

$dsn = "odbc:Driver={ODBC Driver 17 for SQL Server};Server=$server;Database=$database;Trusted_Connection=yes;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
