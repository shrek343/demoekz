<?php
// ============================================================
//  ПОДКЛЮЧЕНИЕ К БАЗЕ (PDO + подготовленные запросы = защита от SQL-инъекций)
//  Подключается во всех страницах, где нужна база.
// ============================================================

$host    = 'MySQL-8.4';
$db      = 'demo';     // имя базы из database.sql
$user    = 'root';      // пользователь MySQL (XAMPP/OpenServer обычно root)
$pass    = '';          // пароль (XAMPP пустой; OpenServer иногда 'root')
$charset = 'utf8mb4';   // кодировка для русских букв

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // показывать ошибки
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // результат как ассоц. массив
    PDO::ATTR_EMULATE_PREPARES   => false,                  // настоящие подготовленные запросы
];
    $pdo = new PDO($dsn, $user, $pass, $options);
