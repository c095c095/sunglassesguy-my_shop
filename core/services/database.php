<?php

include_once __DIR__ . "/../config/config_database.php";

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (mysqli_connect_error()) {
    echo "<p>Can't Connect to database.</p>";
    die();
}

mysqli_set_charset($conn, "utf8mb4");
