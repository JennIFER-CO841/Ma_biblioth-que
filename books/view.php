<?php
session_start(); // Démarre la session pour gérer l'utilisateur connecté
require_once '../includes/config.php'; // Connexion à la base de données via PDO

// Si l'utilisateur n'est pas connecté, redirige vers la page de connexion
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: ../login.php");
    exit;
}

$utilisateur_id = $_SESSION['utilisateur_id']; // Récupère l'ID utilisateur connecté
$role = $_SESSION['role'] ?? 'lecteur'; // Récupère le rôle, ou 'lecteur' par défaut

// Vérifie que l'ID du livre est présent dans l'URL et qu'il est numérique
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php"); // Sinon, redirige vers la page d'accueil
    exit;
}

$livre_id = intval($_GET['id']); // Convertit l'ID en entier sécurisé

// Prépare la requête pour récupérer les détails du livre, de son auteur et catégorie
$stmt = $pdo->prepare("
    SELECT l.*, a.nom AS auteur_nom, a.prenom AS auteur_prenom, c.nom_categorie
    FROM livres l
    LEFT JOIN auteurs a ON l.auteur_id = a.id
    LEFT JOIN categories c ON l.categorie_id = c.id
    WHERE l.id = ?
");
$stmt->execute([$livre_id]); // Exécute la requête avec l'ID du livre
$livre = $stmt->fetch(); // Récupère les données du livre

// Si aucun livre trouvé, affiche un message d'erreur et stoppe l'exécution
if (!$livre) {
    echo "<p class='text-red-600 font-bold p-4'>❌ Livre introuvable.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($livre['titre']) ?> - Détails du Livre</title>
    <script src="https://cdn.tailwindcss.com"></script> <!-- Chargement de Tailwind CSS -->
</head>
<body class="bg-gradient-to-br from-blue-100 to-blue-200 min-h-screen text-gray-800">

<header class="bg-blue-600 text-white p-4 flex justify-between items-center">
    <h1 class="text-xl font-semibold">📖 Détails du Livre</h1>
    <nav class="space-x-4">
        <?php if (isset($_SESSION['utilisateur_id'])): ?>
            <!-- Affiche le nom de l'utilisateur connecté -->
            <span>👤 <?= htmlspecialchars($_SESSION['nom_complet'] ?? $_SESSION['nom_utilisateur']) ?></span>
            <a href="../dashboard.php" class="hover:underline">Tableau de Bord</a>
            <a href="../logout.php" class="hover:underline">Déconnexion</a>
        <?php else: ?>
            <!-- Sinon, propose un lien vers la page de connexion -->
            <a href="../login.php" class="hover:underline">Connexion</a>
        <?php endif; ?>
    </nav>
</header>

<main class="max-w-3xl mx-auto mt-10 bg-white p-6 rounded-lg shadow-md">
    <!-- Titre du livre -->
    <h2 class="text-2xl font-bold text-blue-700 mb-4"><?= htmlspecialchars($livre['titre']) ?></h2>

    <div class="space-y-2">
        <!-- Affiche les détails du livre avec protections HTML -->
        <p><strong>Auteur :</strong> <?= htmlspecialchars($livre['auteur_prenom'] . ' ' . $livre['auteur_nom']) ?></p>
        <p><strong>Catégorie :</strong> <?= htmlspecialchars($livre['nom_categorie'] ?? 'Non catégorisé') ?></p>
        <p><strong>ISBN :</strong> <?= htmlspecialchars($livre['isbn'] ?? 'Non défini') ?></p>
        <p><strong>Année :</strong> <?= intval($livre['annee_publication']) ?></p>
        <p><strong>Exemplaires disponibles :</strong> <?= intval($livre['nombre_exemplaires_disponibles'] ?? 0) ?> / <?= intval($livre['nombre_exemplaires_total']) ?></p>
        <p><strong>Résumé :</strong><br>
            <!-- nl2br convertit les sauts de ligne en <br> dans le résumé -->
            <span class="block bg-gray-50 p-3 rounded mt-1"><?= nl2br(htmlspecialchars($livre['resume'] ?? 'Aucune description.')) ?></span>
        </p>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <?php if ($role === 'admin'): ?>
            <!-- Pour admin, liens pour retourner à la gestion, modifier ou supprimer le livre -->
            <a href="../gerer_livres.php" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">⬅️ Retour</a>
            <a href="edit.php?id=<?= $livre_id ?>" class="px-4 py-2 bg-yellow-400 text-white rounded hover:bg-yellow-500">✏️ Modifier</a>
            <a href="delete.php?id=<?= $livre_id ?>" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" onclick="return confirm('Confirmer la suppression ?')">🗑️ Supprimer</a>
        <?php elseif ($role === 'lecteur' && $livre['nombre_exemplaires_disponibles'] > 0): ?>
            <!-- Pour lecteur si livre dispo, lien pour emprunter -->
            <a href="../borrows/borrow.php?livre_id=<?= $livre_id ?>" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">📚 Emprunter ce livre</a>
        <?php elseif ($role === 'lecteur'): ?>
            <!-- Si lecteur et pas dispo, message d'indisponibilité -->
            <span class="text-red-500 font-medium">⛔ Livre non disponible actuellement</span>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
