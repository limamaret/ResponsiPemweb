<?php
require 'config.php';
$pdo->exec('DROP DATABASE IF EXISTS jobconnect');
$pdo->exec('CREATE DATABASE jobconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
echo "Database recreated\n";