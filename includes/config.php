<?php
// Infos de connexion à la base
$host = 'localhost';
$dbname = 'ma_bibliotheque_vacances';
$user = 'root';
$pass = '';

// Chaîne de connexion (DSN)
$dsn = "mysql:host=$host;dbname=$dbname";

// Options de sécurité et d'affichage des erreurs
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Affiche les erreurs PDO sous forme d'exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Résultats sous forme de tableau associatif
    PDO::ATTR_EMULATE_PREPARES => false                 // Active les vraies requêtes préparées (plus sûr)
];

// Connexion avec gestion d'erreur
try {
    $pdo = new PDO($dsn, $user, $pass, $options); // Connexion réussie
} catch (PDOException $e) {
    echo 'Erreur de connexion à la base de données.'; // Message générique si échec
    exit;
}
?>
