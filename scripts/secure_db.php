<?php
    $host = '127.0.0.1';
    $port = '3307';
    $user = 'root'; 
    $pass = ''; 
    
    try {
        $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass);
        $pdo->exec("CREATE USER IF NOT EXISTS 'sgen_admin'@'%' IDENTIFIED BY 'SgenSupport2026!'");
        $pdo->exec("GRANT ALL PRIVILEGES ON sgen_db.* TO 'sgen_admin'@'%' WITH GRANT OPTION");
        $pdo->exec("GRANT ALL PRIVILEGES ON sgen_db.* TO 'sgen_admin'@'localhost' IDENTIFIED BY 'SgenSupport2026!'");
        $pdo->exec("FLUSH PRIVILEGES");
        echo "Database SECURED with enterprise user";
    } catch (Exception $e) { echo "Security Setup Warning: " . $e->getMessage(); }
    ?>