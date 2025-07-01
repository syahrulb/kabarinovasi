<?php
// config/database_config.php
return [
    'host' => '127.0.0.1',
    'database' => 'kabari_inovasi',
    'username' => 'kabari_inovasi',
    'password' => 'HfLNyMPapaB2ZMH6',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
?>