<?php
$content = file_get_contents('config/database.php');
$content = str_replace(
    "PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA')",
    "(defined('Pdo\\\Mysql::ATTR_SSL_CA') ? constant('Pdo\\\Mysql::ATTR_SSL_CA') : constant('PDO::MYSQL_ATTR_SSL_CA')) => env('MYSQL_ATTR_SSL_CA')",
    $content
);
file_put_contents('config/database.php', $content);
echo "Fix applied to database.php config\n";
