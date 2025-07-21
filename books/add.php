<?php
session_start(); // Démarre la session utilisateur
require_once '../includes/config.php'; // Inclusion de la configuration (connexion DB)

// Vérifie que l'utilisateur est connecté, sinon redirige vers la page d'accueil
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: ../index.php");
    exit;
}

$message = ''; // Message d'information à afficher à l'utilisateur (succès ou erreur)

// Traitement du formulaire lors d'une requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données envoyées par l'utilisateur
    $titre = trim($_POST['titre']);
    $isbn = trim($_POST['isbn']);
    $annee = $_POST['annee'];
    $categorie_id = $_POST['categorie_id'];
    $resume = trim($_POST['resume']);
    $exemplaires = (int)$_POST['exemplaires'];
    $nom_auteur = trim($_POST['nom_auteur']);

    // Vérifie que tous les champs sont remplis
    if ($titre && $isbn && $annee && $categorie_id && $resume && $exemplaires && $nom_auteur) {
        try {
            // Séparation du prénom et nom à partir de l'entrée "Victor Hugo"
            $auteur_parts = explode(' ', $nom_auteur, 2);
            $prenom = $auteur_parts[0] ?? '';
            $nom = $auteur_parts[1] ?? '';

            // Vérifie si l'auteur existe déjà dans la base
            $stmt = $pdo->prepare("SELECT id FROM auteurs WHERE nom = :nom AND prenom = :prenom");
            $stmt->execute(['nom' => $nom, 'prenom' => $prenom]);
            $auteur = $stmt->fetch();

            if ($auteur) {
                // Si l'auteur existe, on récupère son ID
                $auteur_id = $auteur['id'];
            } else {
                // Sinon, on l'insère dans la table auteurs
                $stmt = $pdo->prepare("INSERT INTO auteurs (nom, prenom) VALUES (:nom, :prenom)");
                $stmt->execute(['nom' => $nom, 'prenom' => $prenom]);
                $auteur_id = $pdo->lastInsertId(); // Récupère l'ID nouvellement créé
            }

            // Insertion du livre dans la base de données
            $stmt = $pdo->prepare("
                INSERT INTO livres (
                    titre, isbn, annee_publication, resume, 
                    nombre_exemplaires_total, nombre_exemplaires_disponibles, 
                    auteur_id, categorie_id, utilisateur_id
                ) VALUES (
                    :titre, :isbn, :annee, :resume, 
                    :nb_total, :nb_dispo, :auteur_id, :categorie_id, :utilisateur_id
                )
            ");
            $stmt->execute([
                ':titre' => $titre,
                ':isbn' => $isbn,
                ':annee' => $annee,
                ':resume' => $resume,
                ':nb_total' => $exemplaires,
                ':nb_dispo' => $exemplaires, // Livre disponible en totalité
                ':auteur_id' => $auteur_id,
                ':categorie_id' => $categorie_id,
                ':utilisateur_id' => $_SESSION['utilisateur_id']
            ]);

            // Message de succès
            $message = "Livre ajouté avec succès.";
        } catch (PDOException $e) {
            // Gestion des erreurs de base de données
            $message = "Erreur : " . $e->getMessage();
        }
    } else {
        // Message d'erreur si un champ est vide
        $message = "Tous les champs sont obligatoires.";
    }
}

// Récupération des catégories pour le menu déroulant
$stmt = $pdo->query("SELECT id, nom_categorie FROM categories ORDER BY nom_categorie ASC");
$categories = $stmt->fetchAll(); // Liste des catégories disponibles
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Livre</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Chargement de Tailwind CSS pour le style -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-blue-200 min-h-screen">

<!-- Conteneur principal centré -->
<div class="max-w-2xl mx-auto mt-12 bg-white p-8 rounded-xl shadow-md">
    
    <!-- Titre de la page -->
    <h1 class="text-2xl font-bold mb-6 text-gray-800">Ajouter un nouveau livre</h1>

    <!-- Affichage du message de succès ou d’erreur s’il existe -->
    <?php if ($message): ?>
        <div class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-500 text-blue-700 rounded">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire d'ajout de livre -->
    <form method="POST" class="space-y-4">

        <!-- Champ : Titre du livre -->
        <div>
            <label for="titre" class="block font-medium text-gray-700">Titre</label>
            <input type="text" name="titre" required class="w-full mt-1 px-4 py-2 border rounded shadow-sm">
        </div>

        <!-- Champ : ISBN du livre -->
        <div>
            <label for="isbn" class="block font-medium text-gray-700">ISBN</label>
            <input type="text" name="isbn" required class="w-full mt-1 px-4 py-2 border rounded shadow-sm">
        </div>

        <!-- Champ : Année de publication -->
        <div>
            <label for="annee" class="block font-medium text-gray-700">Année de publication</label>
            <input type="number" name="annee" min="1000" max="<?= date('Y') ?>" required class="w-full mt-1 px-4 py-2 border rounded shadow-sm">
        </div>

        <!-- Champ : Sélection de la catégorie -->
        <div>
            <label for="categorie_id" class="block font-medium text-gray-700">Catégorie</label>
            <select name="categorie_id" required class="w-full mt-1 px-4 py-2 border rounded shadow-sm">
                <option value="">-- Choisissez une catégorie --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nom_categorie']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Champ : Résumé du livre -->
        <div>
            <label for="resume" class="block font-medium text-gray-700">Résumé</label>
            <textarea name="resume" rows="4" required class="w-full mt-1 px-4 py-2 border rounded shadow-sm"></textarea>
        </div>

        <!-- Champ : Nombre d'exemplaires disponibles -->
        <div>
            <label for="exemplaires" class="block font-medium text-gray-700">Nombre d'exemplaires</label>
            <input type="number" name="exemplaires" min="1" required class="w-full mt-1 px-4 py-2 border rounded shadow-sm">
        </div>

        <!-- Champ : Nom complet de l'auteur -->
        <div>
            <label for="nom_auteur" class="block font-medium text-gray-700">Auteur (prénom nom)</label>
            <input type="text" name="nom_auteur" placeholder="ex: Victor Hugo" required class="w-full mt-1 px-4 py-2 border rounded shadow-sm">
        </div>

        <!-- Bouton de soumission du formulaire -->
        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition">
            Ajouter le livre
        </button>
    </form>

    <!-- Lien de retour vers le tableau de bord -->
    <div class="mt-6">
        <a href="../dashboard.php" class="text-blue-600 hover:underline">← Retour au tableau de bord</a>
    </div>
</div>

</body>
</html>
