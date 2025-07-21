<?php
session_start();
require_once '../includes/config.php';

// Accès réservé aux admins
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?error=access_denied");
    exit;
}

// Vérifie que l'ID est fourni et valide
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../gerer_auteurs.php?error=id_invalide");
    exit;
}

$auteur_id = intval($_GET['id']);

// Vérifie que l'auteur existe
$stmt = $pdo->prepare("SELECT * FROM auteurs WHERE id = ?");
$stmt->execute([$auteur_id]);
$auteur = $stmt->fetch();

if (!$auteur) {
    header("Location: ../gerer_auteurs.php?error=auteur_introuvable");
    exit;
}

// Ne pas supprimer l'auteur "Inconnu" lui-même
if (strtolower(trim($auteur['nom'])) === 'inconnu') {
    header("Location: ../gerer_auteurs.php?error=inconnu_non_supprimable");
    exit;
}

// Vérifie si des livres sont associés
$stmt = $pdo->prepare("SELECT COUNT(*) FROM livres WHERE auteur_id = ?");
$stmt->execute([$auteur_id]);
$livres_associes = $stmt->fetchColumn();

// Si des livres existent, on réassigne à "Inconnu"
if ($livres_associes > 0) {
    // Vérifie si un auteur "Inconnu" existe déjà
    $stmt = $pdo->prepare("SELECT id FROM auteurs WHERE nom = 'Inconnu' AND prenom = ''");
    $stmt->execute();
    $auteur_inconnu = $stmt->fetch();

    if (!$auteur_inconnu) {
        // Crée l'auteur "Inconnu"
        $stmt = $pdo->prepare("INSERT INTO auteurs (nom, prenom, date_naissance, biographie) VALUES ('Inconnu', '', NULL, 'Auteur générique')");
        $stmt->execute();
        $auteur_inconnu_id = $pdo->lastInsertId();
    } else {
        $auteur_inconnu_id = $auteur_inconnu['id'];
    }

    // Réassigne tous les livres à "Inconnu"
    $stmt = $pdo->prepare("UPDATE livres SET auteur_id = ? WHERE auteur_id = ?");
    $stmt->execute([$auteur_inconnu_id, $auteur_id]);
}

// Supprime l’auteur
$stmt = $pdo->prepare("DELETE FROM auteurs WHERE id = ?");
if ($stmt->execute([$auteur_id])) {
    header("Location: ../gerer_auteurs.php?suppression=success");
    exit;
} else {
    header("Location: ../gerer_auteurs.php?error=suppression_echec");
    exit;
}
