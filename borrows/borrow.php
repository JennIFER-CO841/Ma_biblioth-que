<?php
session_start(); // Démarre la session utilisateur
require_once '../includes/config.php'; // Inclusion du fichier de configuration (connexion à la BDD)

// Vérifie si l'utilisateur est connecté ET s'il est un lecteur
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] !== 'lecteur') {
    header('Location: ../index.php'); // Redirige vers l'accueil s'il n'est pas autorisé
    exit;
}

$user_id = $_SESSION['utilisateur_id']; // ID de l'utilisateur actuellement connecté

// Vérifie si un ID de livre est passé dans l'URL
if (!isset($_GET['livre_id']) || !is_numeric($_GET['livre_id'])) {
    echo "❌ Aucun livre sélectionné."; // Pas de livre à emprunter
    exit;
}

$livre_id = (int) $_GET['livre_id']; // Convertit en entier pour éviter les injections

try {
    // Vérifie si le livre existe ET s’il reste des exemplaires disponibles
    $stmt = $pdo->prepare("SELECT * FROM livres WHERE id = :id AND nombre_exemplaires_disponibles > 0");
    $stmt->execute(['id' => $livre_id]);
    $livre = $stmt->fetch();

    if (!$livre) {
        echo "⛔ Ce livre n'est pas disponible à l'emprunt."; // Plus de stock ou ID invalide
        exit;
    }

    // Insère une ligne dans la table des emprunts avec l'état "actif" et la date du jour
    $insert = $pdo->prepare("
        INSERT INTO emprunts (utilisateur_id, livre_id, date_emprunt, statut_emprunt)
        VALUES (:utilisateur_id, :livre_id, CURRENT_DATE, 'actif')
    ");
    $insert->execute([
        'utilisateur_id' => $user_id,
        'livre_id' => $livre_id
    ]);

    // Met à jour le nombre d'exemplaires disponibles (on en retire 1)
    $update = $pdo->prepare("
        UPDATE livres 
        SET nombre_exemplaires_disponibles = nombre_exemplaires_disponibles - 1
        WHERE id = :id
    ");
    $update->execute(['id' => $livre_id]);

    // Redirige vers le dashboard avec un message de succès
    header("Location: ../dashboard.php?message=emprunt_succes");
    exit;

} catch (PDOException $e) {
    // En cas d’erreur SQL ou PDO
    die("❌ Erreur lors de l'emprunt : " . $e->getMessage());
}
