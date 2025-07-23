<?php
session_start(); // Démarre la session PHP

require_once '../includes/config.php'; // Connexion à la base de données

// Vérification du rôle : seulement les admins peuvent accéder à cette page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php'); // Redirige vers l'accueil si pas admin
    exit;
}

$errors = [];  // Tableau pour stocker les erreurs
$success = ""; // Message de succès

// Traitement du formulaire à la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère et nettoie les données du formulaire
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $date_naissance = $_POST['date_naissance'] ?: null; // Peut être null
    $biographie = trim($_POST['biographie']);

    // Validation obligatoire du nom et prénom
    if (empty($nom) || empty($prenom)) {
        $errors[] = "Nom et prénom sont obligatoires.";
    }

    // Si pas d'erreur, insertion dans la base
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO auteurs (nom, prenom, date_naissance, biographie) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $date_naissance, $biographie]);

        $success = "Auteur ajouté avec succès !"; // Message de confirmation
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Auteur</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CDN TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-100 to-blue-200 min-h-screen">

<!-- En-tête avec navigation -->
<header class="bg-blue-600 text-white py-4 px-6 flex justify-between items-center shadow-md">
    <h1 class="text-xl font-bold">Ajouter un Auteur</h1>
    <nav class="space-x-4">
        <a href="index.php" class="hover:underline">🏠 Accueil</a>
        <a href="gerer_auteurs.php" class="hover:underline">📚 Gérer les auteurs</a>
    </nav>
</header>

<!-- Contenu principal -->
<main class="max-w-xl mx-auto mt-10 bg-white p-8 rounded-lg shadow-lg">
    <h2 class="text-2xl font-semibold mb-6 text-blue-700">📄 Nouveau Auteur</h2>

    <!-- Affichage des erreurs -->
    <?php if ($errors): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php foreach ($errors as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Affichage du message de succès -->
    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire d'ajout d'auteur -->
    <form method="post" class="space-y-4">
        <div>
            <label for="nom" class="block text-sm font-medium text-gray-700">Nom :</label>
            <input type="text" name="nom" id="nom" required
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom :</label>
            <input type="text" name="prenom" id="prenom" required
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label for="date_naissance" class="block text-sm font-medium text-gray-700">Date de naissance :</label>
            <input type="date" name="date_naissance" id="date_naissance"
                   class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label for="biographie" class="block text-sm font-medium text-gray-700">Biographie :</label>
            <textarea name="biographie" id="biographie" rows="5"
                      class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
        </div>

        <button type="submit"
                class="w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded-md hover:bg-blue-700 transition">
            ➕ Ajouter l’auteur
        </button>
    </form>

    <!-- Bouton retour vers la gestion des auteurs -->
    <div class="mt-6">
        <a href="../gerer_auteurs.php"
           class="inline-block bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700 transition">
            ⬅️ Retour à la gestion des auteurs
        </a>
    </div>
</main>

</body>
</html>
