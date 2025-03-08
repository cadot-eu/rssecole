<?php

$dsn = 'pgsql:host=127.0.0.1;dbname=app;user=app;password=!ChangeMe!';
$pdo = new PDO($dsn);

// Exécuter une requête SQL
$stmt = $pdo->query('SELECT * FROM your_table LIMIT 10');
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Afficher les résultats
print_r($results);
