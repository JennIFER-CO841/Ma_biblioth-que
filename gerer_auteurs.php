<?php
session_start(); // Démarre une session pour accéder aux variables de session
require_once './includes/config.php'; // Connexion à la base de données

// Vérifie si l'utilisateur est connecté ET qu'il est admin
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php"); // Redirige vers l'accueil si non autorisé
    exit;
}

// Récupère tous les auteurs de la base de données, triés par nom
$stmt = $pdo->query("SELECT * FROM auteurs ORDER BY nom");
$auteurs = $stmt->fetchAll(); // Récupère les résultats dans un tableau

// Récupère les messages éventuels passés en paramètre d'URL (succès ou erreur)
$message_succes = isset($_GET['suppression']) && $_GET['suppression'] === 'success';
$erreur_inconnu = isset($_GET['error']) && $_GET['error'] === 'inconnu_non_supprimable';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Auteurs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Framework Tailwind CSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-100 to-purple-100 min-h-screen">

<header class="bg-blue-600 text-white p-4 flex justify-between items-center">
    <h1 class="text-xl font-bold">📚 Gérer les Auteurs</h1>
    <nav class="space-x-4">
        <!-- Navigation vers l'accueil, dashboard et déconnexion -->
        <a href="index.php" class="hover:underline">🏠 Accueil</a>
        <a href="dashboard.php" class="hover:underline">Tableau de Bord</a>
        <a href="logout.php" class="hover:underline">Déconnexion</a>
    </nav>
</header>

<main class="max-w-5xl mx-auto mt-8 bg-white p-8 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-blue-700">Liste des Auteurs</h2>
        <!-- Bouton pour accéder à la page d'ajout d'un nouvel auteur -->
        <a href="authors/add.php"
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
            ➕ Ajouter un Auteur
        </a>
    </div>

    <!-- Affiche un message de succès si un auteur a été supprimé -->
    <?php if ($message_succes): ?>
        <div id="alert-message" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            ✅ Auteur supprimé avec succès. Les livres ont été réaffectés si nécessaire.
        </div>
    <!-- Affiche un avertissement si on essaie de supprimer l'auteur 'Inconnu' -->
    <?php elseif ($erreur_inconnu): ?>
        <div id="alert-message" class="mb-4 p-4 bg-yellow-100 text-yellow-700 rounded">
            ⚠️ L’auteur générique 'Inconnu' ne peut pas être supprimé.
        </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <!-- Tableau affichant la liste des auteurs -->
        <table class="w-full table-auto border border-gray-300 rounded-lg overflow-hidden">
            <thead class="bg-gray-100 text-gray-700">
            <tr>
                <th class="px-4 py-2 border">Nom</th>
                <th class="px-4 py-2 border">Prénom</th>
                <th class="px-4 py-2 border">Date de naissance</th>
                <th class="px-4 py-2 border">Actions</th>
            </tr>
            </thead>
            <tbody class="text-gray-800">
                <?php if (count($auteurs) > 0): ?>
                    <?php foreach ($auteurs as $auteur): ?>
                        <tr class="hover:bg-gray-50">
                            <!-- Affiche les informations de l'auteur en sécurisant l'affichage -->
                            <td class="px-4 py-2 border"><?= htmlspecialchars($auteur['nom']) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($auteur['prenom']) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($auteur['date_naissance'] ?? 'N/A') ?></td>
                            <!-- Boutons Modifier et Supprimer -->
                            <td class="px-4 py-2 border space-x-2">
                                <a href="authors/edit.php?id=<?= $auteur['id'] ?>"
                                   class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded-md">
                                    ✏️ Modifier
                                </a>
                                <a href="authors/delete.php?id=<?= $auteur['id'] ?>"
                                   onclick="return confirm('Supprimer cet auteur ?')"
                                   class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md">
                                    🗑️ Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Message si aucun auteur n'est trouvé dans la base -->
                    <tr>
                        <td colspan="4" class="text-center text-gray-500 py-4">Aucun auteur trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Bouton retour vers le dashboard placé en bas de la page -->
    <div class="mt-8">
        <a href="dashboard.php"
           class="inline-block bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700 transition">
            ⬅️ Retour au tableau de bord
        </a>
    </div>
</main>

<script>
// Script JS pour masquer automatiquement le message d'alerte après 30 secondes
document.addEventListener('DOMContentLoaded', () => {
    const alertMessage = document.getElementById('alert-message');
    if (alertMessage) {
        setTimeout(() => {
            alertMessage.style.transition = 'opacity 0.5s ease';
            alertMessage.style.opacity = '0';
            setTimeout(() => alertMessage.remove(), 500); // Supprime du DOM après la transition
        }, 30000);
    }
});
</script>

</body>
</html>
