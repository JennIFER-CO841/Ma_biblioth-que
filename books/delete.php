<?php
session_start();
require_once '../includes/config.php';

// Vérifie que l'utilisateur est connecté et qu'il a le rôle "admin"
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] !== 'admin') {
    exit("Accès refusé.");
}

// Vérifie que l'ID du livre est présent dans l'URL et que c'est un nombre valide
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    exit("ID invalide.");
}

$livre_id = intval($_GET['id']); // Conversion de l'ID en entier

// Récupère les informations du livre à partir de son ID
$stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
$stmt->execute([$livre_id]);
$livre = $stmt->fetch();

// Si aucun livre correspondant n'est trouvé
if (!$livre) {
    exit("Livre introuvable.");
}

// Vérifie si tous les exemplaires sont disponibles (aucun emprunt en cours)
if ($livre['nombre_exemplaires_disponibles'] < $livre['nombre_exemplaires_total']) {
    exit("Impossible de supprimer ce livre : certains exemplaires sont encore empruntés.");
}

// Supprime le livre de la base de données
$stmt = $pdo->prepare("DELETE FROM livres WHERE id = ?");
$deleted = $stmt->execute([$livre_id]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suppression d’un Livre</title>
    <!-- Import de Tailwind CSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-red-100 to-white min-h-screen flex items-center justify-center">

<!-- Boîte centrale affichant le résultat -->
<div class="bg-white p-6 rounded-lg shadow-lg max-w-md text-center">

    <!-- Affiche un message différent selon que la suppression a réussi ou échoué -->
    <?php if ($deleted): ?>
        <h2 class="text-2xl font-bold text-green-600 mb-4">Livre supprimé avec succès.</h2>
    <?php else: ?>
        <h2 class="text-2xl font-bold text-red-600 mb-4">Une erreur est survenue lors de la suppression.</h2>
    <?php endif; ?>
    
    <!-- Lien de retour vers l'accueil ou la liste des livres -->
    <a href="../index.php" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
        Retour à la liste des livres
    </a>
</div>

</body>
</html>
