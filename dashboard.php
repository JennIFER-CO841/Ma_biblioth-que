<?php
session_start(); 
require_once 'includes/config.php';

// Vérifie que l'utilisateur est connecté, sinon redirige vers l'accueil
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['utilisateur_id'];
$user_role = $_SESSION['role'];

try {
    $livres = [];       // Pour stocker tous les livres (si admin)
    $mes_emprunts = []; // Pour stocker les emprunts actifs du lecteur

    // Si admin : récupérer tous les livres avec leur catégorie et nombre d'exemplaires
    if ($user_role === 'admin') {
        $stmt_livres = $pdo->query("
            SELECT l.titre, l.isbn, l.nombre_exemplaires_disponibles, c.nom_categorie
            FROM livres l
            LEFT JOIN categories c ON l.categorie_id = c.id
            ORDER BY l.titre
        ");
        $livres = $stmt_livres->fetchAll();
    }

    // Si lecteur : récupérer uniquement ses emprunts actifs
    if ($user_role === 'lecteur') {
        $stmt_emprunts = $pdo->prepare("
            SELECT e.id AS emprunt_id, l.titre, l.isbn, e.date_emprunt
            FROM emprunts e
            JOIN livres l ON e.livre_id = l.id
            WHERE e.utilisateur_id = :user_id AND e.statut_emprunt = 'actif'
            ORDER BY e.date_emprunt DESC
        ");
        $stmt_emprunts->execute(['user_id' => $user_id]);
        $mes_emprunts = $stmt_emprunts->fetchAll();
    }

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Tableau de Bord</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-blue-200 min-h-screen">

<!-- En-tête -->
<header class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shadow">
    <h1 class="text-xl font-semibold">Mon Tableau de Bord</h1>
    <nav class="space-x-4">
        <a href="index.php" class="hover:underline font-medium">Accueil</a>
        <a href="logout.php" class="hover:underline font-medium">Déconnexion</a>
    </nav>
</header>

<!-- Contenu principal -->
<main class="max-w-6xl mx-auto mt-8 p-6 bg-white rounded-xl shadow">

    <!-- Section ADMIN -->
    <?php if ($user_role === 'admin'): ?>
        <div>
            <h2 class="text-2xl font-bold text-gray-700 mb-4">📚 Tous les livres</h2>
            <?php if (empty($livres)): ?>
                <p class="text-gray-600">Aucun livre enregistré.</p>
            <?php else: ?>
               <table class="w-full text-left border border-gray-300 text-sm shadow-sm">
                    <thead class="bg-blue-100">
                        <tr>
                            <th class="p-2">Titre</th>
                            <th class="p-2">ISBN</th>
                            <th class="p-2">Catégorie</th>
                            <th class="p-2">Exemplaires disponibles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($livres as $livre): ?>
                            <tr class="border-t">
                                <td class="p-2"><?= htmlspecialchars($livre['titre']) ?></td>
                                <td class="p-2"><?= htmlspecialchars($livre['isbn']) ?></td>
                                <td class="p-2"><?= htmlspecialchars($livre['nom_categorie'] ?? 'N/A') ?></td>
                                <td class="p-2"><?= $livre['nombre_exemplaires_disponibles'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Bouton ajout de livre -->
                <a href="books/add.php" class="inline-block mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                    ➕ Ajouter un livre
                </a>
            <?php endif; ?>
        </div>

        <!-- Boutons de gestion -->
        <div class="mt-10">
            <h2 class="text-2xl font-bold text-gray-700 mb-4">🔧 Gestion (Admin)</h2>
            <div class="flex flex-wrap gap-3">
                <a href="gerer_livres.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">📘 Gérer les livres</a>
                <a href="gerer_auteurs.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">👤 Gérer les auteurs</a>
                <a href="gerer_categories.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">📂 Gérer les catégories</a>
                <a href="gerer_utilisateurs.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">🧑‍🤝‍🧑 Gérer les utilisateurs</a>
                <a href="borrows/manage.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">📋 Gérer les emprunts</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Section LECTEUR -->
    <?php if ($user_role === 'lecteur'): ?>
        <div class="mt-10">
            <h2 class="text-2xl font-bold text-gray-700 mb-4">📖 Mes livres empruntés</h2>
            <?php if (empty($mes_emprunts)): ?>
                <p class="text-gray-600">Vous n'avez actuellement aucun livre emprunté.</p>
            <?php else: ?>
                <!-- Tableau des emprunts actifs -->
                <table class="w-full text-left border border-gray-300 text-sm shadow-sm">
                    <thead class="bg-blue-100">
                        <tr>
                            <th class="p-2">Titre</th>
                            <th class="p-2">ISBN</th>
                            <th class="p-2">Date d'emprunt</th>
                            <th class="p-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mes_emprunts as $emprunt): ?>
                            <tr class="border-t">
                                <td class="p-2"><?= htmlspecialchars($emprunt['titre']) ?></td>
                                <td class="p-2"><?= htmlspecialchars($emprunt['isbn']) ?></td>
                                <td class="p-2"><?= htmlspecialchars($emprunt['date_emprunt']) ?></td>
                                <td class="p-2">
                                    <!-- Lien pour retourner le livre -->
                                    <a href="borrows/return.php?emprunt_id=<?= $emprunt['emprunt_id'] ?>"
                                       class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                        Retourner
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- Bouton "Voir tous les livres" conservé -->
            <div class="mt-4">
                <a href="gerer_livres.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    📚 Voir tous les livres
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bouton retour accueil -->
    <div class="mt-10">
        <a href="index.php" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700 transition">
            ⬅️ Retour à l'accueil
        </a>
    </div>

</main>
</body>
</html>
