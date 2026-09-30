<?php
// Database Configuration
return [
    'host'     => getenv('ESG_DB_HOST') ?: '127.0.0.1',
    'port'     => (int)(getenv('ESG_DB_PORT') ?: 3306),
    'dbname'   => getenv('ESG_DB_NAME') ?: 'esg_db',
    'username' => getenv('ESG_DB_USER') ?: 'root',
    'password' => getenv('ESG_DB_PASSWORD') !== false ? getenv('ESG_DB_PASSWORD') : '',
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];
