<?php 
session_start();
require_once 'includes/config.php';

// Vérifie que l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] !== 'admin') {
    exit("⛔ Accès refusé.");
}

// Récupère tous les utilisateurs depuis la base de données
$stmt = $pdo->query("SELECT * FROM utilisateurs ORDER BY id DESC");
$utilisateurs = $stmt->fetchAll();

// Récupère les messages passés dans l'URL (pour feedback après actions)
$message = $_GET['message'] ?? null;
$status = $_GET['status'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les utilisateurs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-blue-200 min-h-screen font-sans">

<!-- En-tête -->
<header class="bg-blue-700 text-white px-6 py-4 flex justify-between items-center shadow">
    <h1 class="text-xl font-semibold">👥 Gestion des Utilisateurs</h1>
    <nav class="space-x-4">
        <a href="index.php" class="hover:underline">🏠 Accueil</a>
        <a href="dashboard.php" class="hover:underline">📊 Tableau de bord</a>
        <a href="logout.php" class="hover:underline">🚪 Déconnexion</a>
    </nav>
</header>

<!-- Contenu principal -->
<main class="max-w-5xl mx-auto mt-10 bg-white rounded-lg shadow-lg p-8">
    <h2 class="text-2xl font-bold text-blue-600 mb-6">Liste des utilisateurs</h2>

    <!-- Affiche les messages de statut -->
    <?php if ($message): ?>
        <div id="alert-message" class="mb-4 p-4 rounded 
            <?= $status === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($utilisateurs)): ?>
        <p class="text-gray-600">Aucun utilisateur trouvé.</p>
    <?php else: ?>
        <!-- Tableau des utilisateurs -->
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border border-gray-200">
                <thead class="bg-gray-100 text-left text-sm font-medium text-gray-700">
                    <tr>
                        <th class="px-4 py-2 border-b">ID</th>
                        <th class="px-4 py-2 border-b">Nom</th>
                        <th class="px-4 py-2 border-b">Email</th>
                        <th class="px-4 py-2 border-b">Rôle</th>
                        <th class="px-4 py-2 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-800">
                    <?php foreach ($utilisateurs as $user): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-2 border-b"><?= $user['id'] ?></td>
                            <td class="px-4 py-2 border-b"><?= htmlspecialchars($user['nom_utilisateur']) ?></td>
                            <td class="px-4 py-2 border-b"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-4 py-2 border-b"><?= htmlspecialchars($user['role']) ?></td>
                            <td class="px-4 py-2 border-b space-x-3">
                                <!-- Lien pour modifier -->
                                <a href="utilisateurs/edit.php?id=<?= $user['id'] ?>" class="text-blue-600 hover:underline">✏️ Modifier</a>
                                <!-- Lien pour supprimer avec confirmation -->
                                <a href="utilisateurs/delete.php?id=<?= $user['id'] ?>"
                                   onclick="return confirm('Confirmer la suppression ?')"
                                   class="text-red-600 hover:underline">🗑️ Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>

    <!-- Bouton Retour vers le tableau de bord -->
    <div class="mt-8">
        <a href="dashboard.php" 
           class="inline-block bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700 transition">
            ⬅️ Retour au tableau de bord
        </a>
    </div>
</main>

<!-- Script pour faire disparaître les messages automatiquement -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const alertMessage = document.getElementById('alert-message');
    if (alertMessage) {
        setTimeout(() => {
            alertMessage.style.transition = 'opacity 0.5s ease';
            alertMessage.style.opacity = '0';
            setTimeout(() => alertMessage.remove(), 500);
        }, 30000); // 30 secondes
    }
});
</script>

</body>
</html>
