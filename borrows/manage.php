<?php
session_start(); // Démarre la session PHP (permet de suivre l'utilisateur connecté)
require_once '../includes/config.php'; // Charge les paramètres de connexion à la base de données

// Vérifie que l'utilisateur est connecté et a le rôle admin
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] !== 'admin') {
    exit("Accès refusé."); // Stoppe l'exécution si l'utilisateur n'est pas admin
}

// Requête SQL pour récupérer la liste des emprunts
$stmt = $pdo->query("
    SELECT e.*, 
           l.titre AS titre_livre, 
           u.nom_utilisateur
    FROM emprunts e
    JOIN livres l ON e.livre_id = l.id -- Jointure : récupérer le titre du livre
    JOIN utilisateurs u ON e.utilisateur_id = u.id -- Jointure : récupérer le nom de l'utilisateur
    ORDER BY e.date_emprunt DESC -- Tri du plus récent au plus ancien
");
$emprunts = $stmt->fetchAll(); // Stocke tous les résultats sous forme de tableau associatif
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les emprunts</title>
    <!-- Importation de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-blue-200 min-h-screen text-gray-800">

<!-- En-tête de la page avec navigation -->
<header class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center shadow">
    <h1 class="text-2xl font-semibold">Gestion des emprunts</h1>
    <nav class="space-x-4">
        <a href="../index.php" class="hover:underline">Accueil</a>
        <a href="../dashboard.php" class="hover:underline">Tableau de bord</a>
        <a href="../logout.php" class="hover:underline">Déconnexion</a>
    </nav>
</header>

<!-- Contenu principal -->
<main class="max-w-6xl mx-auto mt-10 p-6 bg-white rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">Liste des emprunts</h2>

    <!-- Si aucun emprunt n'est trouvé -->
    <?php if (count($emprunts) === 0): ?>
        <p class="text-gray-600">Aucun emprunt trouvé.</p>
    <?php else: ?>
        <div class="overflow-x-auto">
            <!-- Tableau affichant les emprunts -->
            <table class="min-w-full text-sm border border-gray-200 shadow">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-2">Livre</th>
                        <th class="px-4 py-2">Emprunteur</th>
                        <th class="px-4 py-2">Date d'emprunt</th>
                        <th class="px-4 py-2">Retour prévu</th>
                        <th class="px-4 py-2">Retour réel</th>
                        <th class="px-4 py-2">Statut</th>
                        <th class="px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php foreach ($emprunts as $emprunt): ?>
                        <tr class="border-t hover:bg-blue-50">
                            <!-- Infos de l'emprunt -->
                            <td class="px-4 py-2"><?= htmlspecialchars($emprunt['titre_livre']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($emprunt['nom_utilisateur']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($emprunt['date_emprunt']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($emprunt['date_retour_prevue']) ?></td>
                            <td class="px-4 py-2">
                                <!-- Si retour réel existe, l'afficher, sinon un tiret -->
                                <?= $emprunt['date_retour_reelle'] ? htmlspecialchars($emprunt['date_retour_reelle']) : '—' ?>
                            </td>
                            <td class="px-4 py-2 capitalize">
                                <?= htmlspecialchars($emprunt['statut_emprunt']) ?>
                            </td>
                            <td class="px-4 py-2">
                                <!-- Bouton Retourner : visible uniquement si l'emprunt est encore actif -->
                                <?php if ($emprunt['statut_emprunt'] === 'actif'): ?>
                                    <a href="return.php?emprunt_id=<?= $emprunt['id'] ?>"
                                       class="text-green-600 hover:underline"
                                       onclick="return confirm('Confirmer le retour de ce livre ?');">
                                        Retourner
                                    </a>
                                <?php else: ?>
                                    <!-- Sinon aucune action -->
                                    <span class="text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Bouton retour vers le tableau de bord -->
    <div class="mt-6">
        <a href="../dashboard.php" 
           class="inline-block bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700 transition">
            ⬅️ Retour au tableau de bord
        </a>
    </div>
</main>

</body>
</html>
