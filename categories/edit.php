<?php
session_start(); // Démarre la session utilisateur
require_once '../includes/config.php'; // Inclusion de la configuration (connexion à la BDD)

// Vérifie que l'utilisateur est connecté ET qu'il est admin
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] !== 'admin') {
    exit("⛔ Accès refusé."); // Blocage si non autorisé
}

// Vérifie que l'ID de la catégorie est fourni et valide
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit("❌ ID de catégorie invalide.");
}

$categorie_id = intval($_GET['id']); // Sécurise l'ID en entier
$message = ""; // Message à afficher selon l’action

// Récupère les informations de la catégorie à modifier
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$categorie_id]);
$categorie = $stmt->fetch();

if (!$categorie) {
    exit("❌ Catégorie introuvable."); // Si l'ID n'existe pas
}

// Traitement du formulaire (si soumis en POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom_categorie']); // Nettoie le champ

    if (!empty($nom)) {
        // Vérifie si une autre catégorie utilise déjà ce nom
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE nom_categorie = ? AND id != ?");
        $stmt->execute([$nom, $categorie_id]);

        if ($stmt->fetch()) {
            $message = "❌ Ce nom est déjà utilisé."; // Nom déjà existant
        } else {
            // Met à jour le nom de la catégorie
            $stmt = $pdo->prepare("UPDATE categories SET nom_categorie = ? WHERE id = ?");
            if ($stmt->execute([$nom, $categorie_id])) {
                $message = "✅ Catégorie modifiée avec succès.";
                $categorie['nom_categorie'] = $nom; // Mise à jour locale du tableau PHP
            } else {
                $message = "❌ Erreur lors de la mise à jour."; // Échec SQL
            }
        }
    } else {
        $message = "❌ Le champ est vide."; // Si champ vide
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une Catégorie</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Framework CSS pour le style rapide -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-blue-100 to-blue-200 min-h-screen font-sans">
<!-- En-tête -->
<header class="bg-blue-600 text-white py-4 px-6 shadow">
    <h1 class="text-xl font-semibold">✏️ Modifier une Catégorie</h1>
</header>

<main class="max-w-xl mx-auto mt-10 bg-white p-8 rounded-lg shadow-md">
    
    <!-- Message de confirmation ou d'erreur -->
    <?php if (!empty($message)): ?>
        <div class="mb-4 p-3 rounded 
            <?= str_starts_with($message, '✅') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire de modification -->
    <form method="post" class="space-y-6">
        <div>
            <label for="nom_categorie" class="block text-gray-700 font-medium">Nom de la catégorie :</label>
            <!-- Champ texte avec valeur pré-remplie -->
            <input type="text" 
                   name="nom_categorie" 
                   id="nom_categorie" 
                   required
                   value="<?= htmlspecialchars($categorie['nom_categorie']) ?>"  <?php // Valeur actuelle affichée ?>
                   class="mt-2 w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <!-- Bouton d'enregistrement -->
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded transition">
            💾 Enregistrer
        </button>
    </form>

    <!-- Lien de retour -->
    <div class="mt-6">
        <a href="../gerer_categories.php" class="text-blue-600 hover:underline">⬅️ Retour à la gestion des catégories</a>
    </div>
</main>

</body>
</html>
