<?php
session_start();
require_once '../includes/config.php'; // Connexion à la base de données

// Vérification que seul un administrateur peut accéder à la page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Vérification que l'ID de l'auteur est bien passé et est valide
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: gerer_auteurs.php');
    exit;
}

$auteur_id = intval($_GET['id']); // Conversion en entier pour sécurité

// Récupération des informations de l’auteur
$stmt = $pdo->prepare("SELECT * FROM auteurs WHERE id = ?");
$stmt->execute([$auteur_id]);
$auteur = $stmt->fetch();

// Si aucun auteur trouvé avec cet ID, on arrête l'exécution
if (!$auteur) {
    echo "<p class='text-red-500 text-center mt-10'>Auteur non trouvé.</p>";
    exit;
}

// Tableau pour stocker les erreurs
$errors = [];

// Variable pour stocker le message de succès
$success = "";

// Vérification si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Nettoyage des entrées utilisateur
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $date_naissance = $_POST['date_naissance'] ?: null; // Peut rester null
    $biographie = trim($_POST['biographie']);

    // Validation des champs obligatoires
    if (empty($nom) || empty($prenom)) {
        $errors[] = "Le nom et le prénom sont obligatoires.";
    }

    // Si pas d'erreurs, mise à jour dans la base
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE auteurs SET nom = ?, prenom = ?, date_naissance = ?, biographie = ? WHERE id = ?");
        $stmt->execute([$nom, $prenom, $date_naissance, $biographie, $auteur_id]);

        // Mettre à jour les données affichées
        $auteur['nom'] = $nom;
        $auteur['prenom'] = $prenom;
        $auteur['date_naissance'] = $date_naissance;
        $auteur['biographie'] = $biographie;

        // Message de succès
        $success = "Auteur modifié avec succès !";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier l’Auteur</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-100 to-blue-200 min-h-screen font-sans">

<!-- En-tête -->
<header class="bg-blue-600 text-white p-4 flex justify-between items-center shadow">
    <h1 class="text-xl font-semibold">✏️ Modifier l’Auteur</h1>
    <nav class="space-x-4">
        <a href="../index.php" class="hover:underline">🏠 Accueil</a>
        <a href="gerer_auteurs.php" class="hover:underline">📚 Gérer les auteurs</a>
    </nav>
</header>

<!-- Contenu principal -->
<main class="max-w-xl mx-auto bg-white mt-10 p-6 rounded shadow">
    <h2 class="text-2xl font-semibold mb-4 text-blue-700">Modifier l’auteur</h2>

    <!-- Affichage des erreurs -->
    <?php if ($errors): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php foreach ($errors as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Message de succès -->
    <?php if ($success): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire de modification -->
    <form method="post" class="space-y-4">
        <div>
            <label for="nom" class="block font-medium">Nom :</label>
            <input type="text" name="nom" id="nom" required
                   value="<?= htmlspecialchars($auteur['nom'] ?? '') ?>"
                   class="mt-1 w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label for="prenom" class="block font-medium">Prénom :</label>
            <input type="text" name="prenom" id="prenom" required
                   value="<?= htmlspecialchars($auteur['prenom'] ?? '') ?>"
                   class="mt-1 w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label for="date_naissance" class="block font-medium">Date de naissance :</label>
            <input type="date" name="date_naissance" id="date_naissance"
                   value="<?= htmlspecialchars($auteur['date_naissance'] ?? '') ?>"
                   class="mt-1 w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label for="biographie" class="block font-medium">Biographie :</label>
            <textarea name="biographie" id="biographie" rows="5"
                      class="mt-1 w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"><?= htmlspecialchars($auteur['biographie'] ?? '') ?></textarea>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded font-semibold transition">
                💾 Enregistrer
            </button>
        </div>
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
