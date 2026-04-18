<?php
/**
 * データベース設定サンプル
 * このファイルを database.php にコピーして値を設定してください
 */

return [
    'host'     => 'localhost',
    'dbname'   => 'your_dbname',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset'  => 'utf8mb4',
    'options'  => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ],
];
