<?php
session_start(); // Démarre la session pour accéder aux variables de session
require_once '../includes/config.php'; // Connexion à la base de données via PDO

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['utilisateur_id'])) {
    exit("Accès refusé.");
}

$utilisateur_id = $_SESSION['utilisateur_id']; // Récupère l'ID utilisateur connecté
$role = $_SESSION['role']; // Récupère le rôle (admin ou lecteur)

// Vérifie que l'ID d'emprunt est passé en paramètre GET
if (!isset($_GET['emprunt_id'])) {
    exit("Emprunt non spécifié.");
}

$emprunt_id = (int) $_GET['emprunt_id']; // Sécurise l'ID en le castant en entier

try {
    // Récupère les infos de l'emprunt et du livre associé
    $stmt = $pdo->prepare("
        SELECT e.*, l.id AS livre_id
        FROM emprunts e
        JOIN livres l ON e.livre_id = l.id
        WHERE e.id = :emprunt_id
    ");
    $stmt->execute(['emprunt_id' => $emprunt_id]);
    $emprunt = $stmt->fetch();

    // Vérifie que l'emprunt existe
    if (!$emprunt) {
        exit("Emprunt introuvable.");
    }

    // Si l'utilisateur est lecteur, il ne peut retourner que ses emprunts
    if ($role === 'lecteur' && $emprunt['utilisateur_id'] != $utilisateur_id) {
        exit("Vous n'êtes pas autorisé à retourner cet emprunt.");
    }

    // Si l'emprunt est déjà retourné, on bloque
    if ($emprunt['statut_emprunt'] !== 'actif') {
        exit("Cet emprunt a déjà été retourné.");
    }

    // Démarre une transaction pour garantir la cohérence des données
    $pdo->beginTransaction();

    // Met à jour l'emprunt : ajoute la date de retour réelle et change le statut
    $stmt_update = $pdo->prepare("
        UPDATE emprunts
        SET date_retour_reelle = NOW(),
            statut_emprunt = 'retourne'
        WHERE id = :emprunt_id
    ");
    $stmt_update->execute(['emprunt_id' => $emprunt_id]);

    // Réincrémente le stock disponible du livre
    $stmt_stock = $pdo->prepare("
        UPDATE livres
        SET nombre_exemplaires_disponibles = nombre_exemplaires_disponibles + 1
        WHERE id = :livre_id
    ");
    $stmt_stock->execute(['livre_id' => $emprunt['livre_id']]);

    // Valide les modifications
    $pdo->commit();

    // Redirection vers la page de gestion des emprunts (modifiée ici)
    if ($role === 'admin') {
        header("Location: manage.php?retour=success"); // <-- Ici on redirige vers manage.php dans le même dossier
    } else {
        header("Location: ../dashboard.php?retour=success");
    }
    exit;

} catch (PDOException $e) {
    // En cas d'erreur, annule la transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    exit("Erreur lors du retour : " . $e->getMessage());
}
