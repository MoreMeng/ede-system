<?php
                         // config/db.php
$host     = '127.0.0.1'; // ✅ ใช้ IP แทน localhost
$dbname   = 'ede_system';
$username = 'root';
$password = '';

try {
    $dsn     = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_PERSISTENT               => true,
        PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE       => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND       => "SET NAMES utf8mb4, sql_mode='STRICT_TRANS_TABLES'",
        PDO::ATTR_TIMEOUT                  => 5,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
    ];

    $pdo = new PDO( $dsn, $username, $password, $options );

} catch ( PDOException $e ) {
    die( "Connection failed: " . $e->getMessage() );
}
