<?php
session_start(); // Démarre la session utilisateur
require_once 'includes/config.php'; // Connexion à la base de données

// Redirige l'utilisateur non connecté vers la page de connexion
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: index.php");
    exit;
}

// Récupère le rôle de l'utilisateur et vérifie s'il est admin
$role = $_SESSION['role'] ?? 'lecteur';
$est_admin = ($role === 'admin');

try {
    // Récupère la liste de tous les livres avec leurs infos (catégorie, auteur)
    $stmt = $pdo->query("
        SELECT l.id, l.titre, l.isbn, l.nombre_exemplaires_disponibles,
               c.nom_categorie, CONCAT(a.prenom, ' ', a.nom) AS auteur
        FROM livres l
        LEFT JOIN categories c ON l.categorie_id = c.id
        LEFT JOIN auteurs a ON l.auteur_id = a.id
        ORDER BY l.titre ASC
    ");
    $livres = $stmt->fetchAll(); // Résultat sous forme de tableau
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage()); // En cas d'erreur SQL
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les livres</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Chargement de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-blue-200 min-h-screen">

<!-- En-tête de la page -->
<header class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
    <h1 class="text-xl font-bold">
        <?= $est_admin ? 'Gérer les livres' : 'Catalogue des livres' ?>
    </h1>
    <nav class="space-x-4">
        <a href="dashboard.php" class="hover:underline">Dashboard</a>
        <a href="logout.php" class="hover:underline">Déconnexion</a>
    </nav>
</header>

<!-- Contenu principal -->
<div class="max-w-6xl mx-auto mt-10 bg-white p-8 rounded-xl shadow-lg">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">📚 Liste des livres</h2>

    <?php if (empty($livres)): ?>
        <p class="text-gray-600">Aucun livre enregistré.</p>
    <?php else: ?>
        <!-- Tableau de tous les livres -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700">
                <thead class="bg-blue-100 text-gray-700 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-2">Titre</th>
                        <th class="px-4 py-2">Auteur</th>
                        <th class="px-4 py-2">ISBN</th>
                        <th class="px-4 py-2">Catégorie</th>
                        <th class="px-4 py-2">Disponibles</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($livres as $livre): ?>
                        <tr>
                            <td class="px-4 py-3"><?= htmlspecialchars($livre['titre']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($livre['auteur']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($livre['isbn']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($livre['nom_categorie']) ?></td>
                            <td class="px-4 py-3"><?= (int)$livre['nombre_exemplaires_disponibles'] ?></td>
                            <td class="px-4 py-3 flex gap-2 flex-wrap">
                                <!-- 🔍 Bouton Voir -->
                                <a href="books/view.php?id=<?= $livre['id'] ?>"
                                   class="bg-cyan-600 hover:bg-cyan-700 text-white px-3 py-1 rounded text-sm">
                                    🔍 Voir
                                </a>

                                <!-- et visibles seulement pour les admins -->
                                <?php if ($est_admin): ?>
                                    <a href="books/edit.php?id=<?= $livre['id'] ?>"
                                       class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm">
                                        ✏️ Modifier
                                    </a>
                                    <a href="books/delete.php?id=<?= $livre['id'] ?>"
                                       onclick="return confirm('Confirmer la suppression ?');"
                                       class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm">
                                        🗑️ Supprimer
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Bouton retour vers dashboard.php -->
    <div class="mt-8">
        <a href="dashboard.php"
           class="inline-block bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700 transition">
            ⬅️ Retour au tableau de bord
        </a>
    </div>
</div>

</body>
</html>
