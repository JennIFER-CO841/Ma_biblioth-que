<?php
session_start(); // Démarrage de la session
require_once '../includes/config.php'; // Inclusion de la configuration (connexion à la base de données)

// Vérifie que l'utilisateur est connecté et qu'il a un rôle d'admin
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] !== 'admin') {
    exit("⛔ Accès refusé."); // Bloque l'accès si ce n’est pas un admin
}

$message = ""; // Initialisation du message de retour

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage et récupération du nom de la catégorie
    $nom = trim($_POST['nom_categorie']);

    // Vérifie que le champ n'est pas vide
    if (!empty($nom)) {
        // Vérifie si une catégorie avec le même nom existe déjà
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE nom_categorie = ?");
        $stmt->execute([$nom]);

        if ($stmt->fetch()) {
            // La catégorie existe déjà
            $message = "❌ Cette catégorie existe déjà.";
        } else {
            // Ajoute la nouvelle catégorie dans la base de données
            $stmt = $pdo->prepare("INSERT INTO categories (nom_categorie) VALUES (?)");
            if ($stmt->execute([$nom])) {
                // Succès
                $message = "✅ Catégorie ajoutée avec succès.";
            } else {
                // Erreur lors de l'insertion
                $message = "❌ Erreur lors de l'ajout.";
            }
        }
    } else {
        // Le champ est vide
        $message = "❌ Le nom de la catégorie est requis.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Catégorie</title>
    <!-- Chargement de Tailwind CSS pour le design -->
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<!-- Style de fond et mise en page globale -->
<body class="bg-gradient-to-br from-blue-100 to-blue-200 min-h-screen font-sans">

<!-- En-tête avec titre -->
<header class="bg-blue-600 text-white py-4 px-6 shadow">
    <h1 class="text-xl font-semibold">📂 Ajouter une Catégorie</h1>
</header>

<!-- Conteneur principal -->
<main class="max-w-xl mx-auto mt-10 bg-white p-8 rounded-lg shadow-md">

    <!-- Affiche un message de succès ou d'erreur s’il existe -->
    <?php if (!empty($message)): ?>
        <div class="mb-4 p-3 rounded 
            <?= str_starts_with($message, '✅') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire d'ajout de catégorie -->
    <form method="post" class="space-y-6">
        <!-- Champ pour le nom de la catégorie -->
        <div>
            <label for="nom_categorie" class="block text-gray-700 font-medium">Nom de la catégorie :</label>
            <input type="text" name="nom_categorie" id="nom_categorie" required
                   class="mt-2 w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <!-- Bouton de soumission -->
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded transition">
            ➕ Ajouter
        </button>
    </form>

    <!-- Lien pour retourner à la page de gestion des catégories -->
    <div class="mt-6">
        <a href="../gerer_categories.php" class="text-blue-600 hover:underline">⬅️ Retour à la gestion des catégories</a>
    </div>
</main>

</body>
</html>
