<?php
// Démarre la session pour vérifier l'authentification et les permissions
session_start();

// Connexion à la base de données
require_once '../includes/config.php';

// Vérifie que l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] !== 'admin') {
    exit("⛔ Accès refusé.");
}

// Vérifie que l'ID est présent et valide
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../gerer_utilisateurs.php?message=ID+invalide&status=error");
    exit;
}

$utilisateur_id = intval($_GET['id']);

// Empêche l'admin connecté de se supprimer lui-même
if ($_SESSION['utilisateur_id'] === $utilisateur_id) {
    header("Location: ../gerer_utilisateurs.php?message=Impossible+de+supprimer+votre+propre+compte&status=error");
    exit;
}

// Récupère l'utilisateur à supprimer
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$utilisateur_id]);
$utilisateur = $stmt->fetch();

// Si l'utilisateur n'existe pas, retourne une erreur
if (!$utilisateur) {
    header("Location: ../gerer_utilisateurs.php?message=Utilisateur+introuvable&status=error");
    exit;
}

// Empêche la suppression de l'utilisateur spécial "Inconnu"
if (strtolower($utilisateur['nom_utilisateur']) === 'inconnu') {
    header("Location: ../gerer_utilisateurs.php?message=Impossible+de+supprimer+l%27utilisateur+Inconnu&status=error");
    exit;
}

try {
    // Tente de supprimer l'utilisateur
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
    $stmt->execute([$utilisateur_id]);

    // Redirige en cas de succès
    header("Location: ../gerer_utilisateurs.php?message=Utilisateur+supprimé+avec+succès&status=success");
    exit;

} catch (PDOException $e) {
    // Si erreur de clé étrangère (utilisateur ayant des emprunts)
    if ($e->getCode() === '23000') {
        // Cherche ou crée l'utilisateur "Inconnu"
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE nom_utilisateur = 'Inconnu' LIMIT 1");
        $stmt->execute();
        $inconnu = $stmt->fetch();

        if (!$inconnu) {
            // Crée "Inconnu" s'il n'existe pas
            $pdo->prepare("INSERT INTO utilisateurs (nom_utilisateur, email, role) VALUES ('Inconnu', 'inconnu@example.com', 'lecteur')")
                ->execute();
            $inconnu_id = $pdo->lastInsertId();
        } else {
            $inconnu_id = $inconnu['id'];
        }

        // Réaffecte tous les emprunts à "Inconnu"
        $pdo->prepare("UPDATE emprunts SET utilisateur_id = ? WHERE utilisateur_id = ?")
            ->execute([$inconnu_id, $utilisateur_id]);

        // Supprime à nouveau l'utilisateur initial
        $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?")->execute([$utilisateur_id]);

        header("Location: ../gerer_utilisateurs.php?message=Utilisateur+supprimé+et+emprunts+réaffectés&status=success");
        exit;
    } else {
        // Autre type d'erreur SQL
        header("Location: ../gerer_utilisateurs.php?message=Erreur+lors+de+la+suppression&status=error");
        exit;
    }
}
?>
