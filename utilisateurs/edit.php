<?php
session_start(); // Démarre la session pour utiliser les variables de session
require_once '../includes/config.php'; // Inclusion de la connexion à la base de données

// Vérifie que l'utilisateur est connecté et qu'il a un rôle d'admin
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] !== 'admin') {
    exit("⛔ Accès refusé."); // Bloque l'accès si ce n’est pas un admin
}

// Vérifie que l’ID de l'utilisateur à modifier est passé dans l'URL et qu'il est bien numérique
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit("❌ ID invalide.");
}

$utilisateur_id = intval($_GET['id']); // Convertit l’ID reçu en entier

// Récupère les informations de l'utilisateur à modifier depuis la base de données
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$utilisateur_id]);
$utilisateur = $stmt->fetch();

// Si aucun utilisateur n’est trouvé avec cet ID, on arrête tout
if (!$utilisateur) {
    exit("❌ Utilisateur introuvable.");
}

// Prépare un message vide pour les retours (succès ou erreur)
$message = '';

// Si le formulaire a été soumis (méthode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère et nettoie les valeurs du formulaire
    $nom_utilisateur = trim($_POST['nom_utilisateur']);
    $email = trim($_POST['email']);
    $role = $_POST['role']; // Pas besoin de trim ici car c'est une valeur fixe du <select>

    // Vérifie que tous les champs sont remplis et que le rôle est valide
    if ($nom_utilisateur && $email && in_array($role, ['admin', 'lecteur'])) {
        // Met à jour l'utilisateur dans la base de données
        $stmt = $pdo->prepare("UPDATE utilisateurs SET nom_utilisateur = ?, email = ?, role = ? WHERE id = ?");
        if ($stmt->execute([$nom_utilisateur, $email, $role, $utilisateur_id])) {
            $message = "✅ Utilisateur mis à jour avec succès."; // Succès
        } else {
            $message = "❌ Erreur lors de la mise à jour."; // Erreur SQL
        }
    } else {
        $message = "❌ Tous les champs sont obligatoires."; // Validation échouée
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un utilisateur</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Intègre Tailwind CSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen font-sans flex items-center justify-center px-4">
<!-- Conteneur principal -->
<div class="w-full max-w-xl bg-white p-8 rounded-xl shadow-md">

    <!-- Titre de la page -->
    <h1 class="text-2xl font-bold text-blue-700 mb-6">✏️ Modifier un utilisateur</h1>

    <!-- Affichage du message (succès ou erreur) s’il y en a -->
    <?php if (!empty($message)): ?>
        <div class="mb-4 p-3 rounded text-white <?= str_starts_with($message, '✅') ? 'bg-green-500' : 'bg-red-500' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire de modification -->
    <form method="post" class="space-y-4">
        <!-- Nom d'utilisateur -->
        <div>
            <label for="nom_utilisateur" class="block font-medium text-gray-700">Nom d'utilisateur</label>
            <input type="text" id="nom_utilisateur" name="nom_utilisateur"
                   value="<?= htmlspecialchars($utilisateur['nom_utilisateur']) ?>"
                   required
                   class="mt-1 block w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring focus:border-blue-400">
        </div>

        <!-- Email -->
        <div>
            <label for="email" class="block font-medium text-gray-700">Email</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($utilisateur['email']) ?>"
                   required
                   class="mt-1 block w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring focus:border-blue-400">
        </div>

        <!-- Rôle -->
        <div>
            <label for="role" class="block font-medium text-gray-700">Rôle</label>
            <select id="role" name="role" required
                    class="mt-1 block w-full border border-gray-300 rounded px-4 py-2 bg-white focus:outline-none focus:ring focus:border-blue-400">
                <!-- Sélection du rôle actuel -->
                <option value="lecteur" <?= $utilisateur['role'] === 'lecteur' ? 'selected' : '' ?>>Lecteur</option>
                <option value="admin" <?= $utilisateur['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>

        <!-- Boutons -->
        <div class="flex items-center justify-between">
            <!-- Bouton pour soumettre -->
            <button type="submit"
                    class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition">
                💾 Enregistrer
            </button>
            <!-- Lien retour -->
            <a href="../gerer_utilisateurs.php"
               class="text-sm text-gray-600 hover:underline ml-4">⬅️ Retour</a>
        </div>
    </form>
</div>

</body>
</html>
