<?php
session_start(); // Démarrage de la session
require_once '../includes/config.php'; // Inclusion du fichier de configuration (connexion à la base de données)

// Vérifie que l'utilisateur est connecté et qu'il est admin
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] !== 'admin') {
    exit("⛔ Accès refusé."); // Bloque l'accès si l'utilisateur n'est pas un admin
}

// Vérifie que l'ID est passé en GET et est bien un entier
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit("❌ ID invalide.");
}

// Conversion de l'ID en entier sécurisé
$categorie_id = intval($_GET['id']);

// Vérifie si la catégorie à supprimer existe bien en base
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$categorie_id]);
$categorie = $stmt->fetch();

if (!$categorie) {
    exit("❌ Catégorie introuvable."); // Empêche la suppression d'une catégorie inexistante
}

// Avant suppression, dissocie tous les livres liés à cette catégorie
$update = $pdo->prepare("UPDATE livres SET categorie_id = NULL WHERE categorie_id = ?");
$update->execute([$categorie_id]);

// Supprime ensuite la catégorie elle-même
$delete = $pdo->prepare("DELETE FROM categories WHERE id = ?");
$success = $delete->execute([$categorie_id]);

// Crée un message de succès ou d’erreur selon le résultat
$message = $success
    ? "✅ Catégorie supprimée avec succès. Les livres liés ont été dissociés."
    : "❌ Une erreur est survenue lors de la suppression.";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suppression Catégorie</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Inclusion de TailwindCSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<!-- Mise en page centrée et fond dégradé -->
<body class="bg-gradient-to-br from-red-100 to-red-200 min-h-screen flex items-center justify-center font-sans">

    <!-- Boîte de confirmation et message -->
    <div class="bg-white shadow-lg rounded-lg p-8 max-w-xl text-center">
        <!-- Titre -->
        <h1 class="text-2xl font-semibold text-red-600 mb-4">🗑️ Suppression Catégorie</h1>

        <!-- Affiche le message de succès ou d'erreur -->
        <div class="<?= $success ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100' ?> p-4 rounded mb-6">
            <?= htmlspecialchars($message) ?>
        </div>

        <!-- Lien retour à la page de gestion des catégories -->
        <a href="../gerer_categories.php"
           class="inline-block bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2 rounded transition">
            ⬅️ Retour à la gestion des catégories
        </a>
    </div>

</body>
</html>
