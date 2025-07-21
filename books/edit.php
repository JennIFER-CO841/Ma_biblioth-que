<?php
session_start(); // Démarre la session pour gérer les utilisateurs
require_once '../includes/config.php'; // Inclut la configuration PDO et la connexion à la BDD

// 🔒 Vérification que l'utilisateur est connecté ET qu'il est admin
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php"); // Redirige vers la page d'accueil si pas admin
    exit;
}

// Vérifie que l'ID du livre est fourni dans l'URL et qu'il est numérique
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php"); // Redirige sinon
    exit;
}

$livre_id = intval($_GET['id']); // Sécurise l'ID en entier
$message = ''; // Variable pour stocker les messages à afficher

// Charge toutes les catégories depuis la base, triées par nom
$categories = $pdo->query("SELECT id, nom_categorie FROM categories ORDER BY nom_categorie")->fetchAll();

// Charge tous les auteurs, triés par nom puis prénom
$auteurs = $pdo->query("SELECT id, prenom, nom FROM auteurs ORDER BY nom, prenom")->fetchAll();

// Charge les données du livre à modifier
$stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
$stmt->execute([$livre_id]);
$livre = $stmt->fetch();

if (!$livre) {
    // Si livre introuvable, prépare un message d'erreur
    $message = "❌ Livre introuvable.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si formulaire soumis, récupère et nettoie les données postées
    $titre = trim($_POST['titre']);
    $isbn = trim($_POST['isbn']);
    $annee = (int)$_POST['annee'];
    $resume = trim($_POST['resume']);
    $categorie_id = (int)$_POST['categorie_id'];
    $auteur_id = (int)$_POST['auteur_id'];
    $exemplaires_total = (int)$_POST['exemplaires'];

    // Vérifie que tous les champs obligatoires sont remplis et valides
    if ($titre && $isbn && $annee && $resume && $categorie_id && $auteur_id && $exemplaires_total > 0) {
        // Vérifie que l'ISBN est unique, excepté pour ce livre (évite doublons)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM livres WHERE isbn = ? AND id != ?");
        $stmt->execute([$isbn, $livre_id]);
        if ($stmt->fetchColumn() > 0) {
            // Si ISBN déjà utilisé ailleurs, message d'erreur
            $message = "⚠️ ISBN déjà utilisé par un autre livre.";
        } else {
            // Calcul la différence entre le nouveau stock total et l'ancien
            $difference = $exemplaires_total - $livre['nombre_exemplaires_total'];
            // Met à jour le stock disponible en ajoutant la différence,
            // en s'assurant qu'il ne soit pas négatif (min 0)
            $exemplaires_dispo = max(0, $livre['nombre_exemplaires_disponibles'] + $difference);

            // Prépare et exécute la mise à jour en base de toutes les données modifiées
            $stmt = $pdo->prepare("
                UPDATE livres SET 
                    titre = :titre,
                    isbn = :isbn,
                    annee_publication = :annee,
                    resume = :resume,
                    categorie_id = :categorie_id,
                    auteur_id = :auteur_id,
                    nombre_exemplaires_total = :total,
                    nombre_exemplaires_disponibles = :dispo
                WHERE id = :id
            ");
            $stmt->execute([
                ':titre' => $titre,
                ':isbn' => $isbn,
                ':annee' => $annee,
                ':resume' => $resume,
                ':categorie_id' => $categorie_id,
                ':auteur_id' => $auteur_id,
                ':total' => $exemplaires_total,
                ':dispo' => $exemplaires_dispo,
                ':id' => $livre_id
            ]);

            // Message de succès
            $message = "✅ Livre mis à jour avec succès.";

            // Recharge les données mises à jour du livre
            $stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
            $stmt->execute([$livre_id]);
            $livre = $stmt->fetch();
        }
    } else {
        // Message si champs invalides ou manquants
        $message = "⚠️ Tous les champs sont obligatoires et valides.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>✏️ Modifier le Livre</title>
    <script src="https://cdn.tailwindcss.com"></script> <!-- Framework CSS Tailwind -->
</head>
<body class="bg-gradient-to-br from-blue-100 to-blue-200 min-h-screen">

<header class="bg-blue-700 text-white py-4 px-6">
    <h1 class="text-xl font-bold">✏️ Modifier le livre</h1>
</header>

<main class="max-w-3xl mx-auto mt-8 bg-white p-8 rounded-lg shadow-lg">
    <?php if ($message): ?>
        <!-- Affiche un message d'information ou d'erreur -->
        <div class="mb-4 p-4 bg-blue-50 border-l-4 border-blue-600 text-blue-800">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($livre): ?>
        <!-- Formulaire de modification du livre pré-rempli avec les données actuelles -->
        <form method="POST" class="space-y-4">
            <div>
                <label class="font-semibold">Titre</label>
                <input type="text" name="titre" required class="w-full p-2 border rounded" value="<?= htmlspecialchars($livre['titre']) ?>">
            </div>

            <div>
                <label class="font-semibold">ISBN</label>
                <input type="text" name="isbn" required class="w-full p-2 border rounded" value="<?= htmlspecialchars($livre['isbn']) ?>">
            </div>

            <div>
                <label class="font-semibold">Année de publication</label>
                <input type="number" name="annee" min="1000" max="<?= date('Y') ?>" required class="w-full p-2 border rounded" value="<?= $livre['annee_publication'] ?>">
            </div>

            <div>
                <label class="font-semibold">Catégorie</label>
                <select name="categorie_id" required class="w-full p-2 border rounded">
                    <option value="">-- Sélectionnez une catégorie --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $livre['categorie_id'] == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nom_categorie']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="font-semibold">Auteur</label>
                <select name="auteur_id" required class="w-full p-2 border rounded">
                    <option value="">-- Sélectionnez un auteur --</option>
                    <?php foreach ($auteurs as $auteur): ?>
                        <option value="<?= $auteur['id'] ?>" <?= $livre['auteur_id'] == $auteur['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($auteur['prenom'] . ' ' . $auteur['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="font-semibold">Résumé</label>
                <textarea name="resume" rows="4" required class="w-full p-2 border rounded"><?= htmlspecialchars($livre['resume']) ?></textarea>
            </div>

            <div>
                <label class="font-semibold">Nombre total d'exemplaires</label>
                <input type="number" name="exemplaires" min="1" required class="w-full p-2 border rounded" value="<?= $livre['nombre_exemplaires_total'] ?>">
            </div>

            <div class="flex justify-between">
                <a href="view.php?id=<?= $livre_id ?>" class="text-blue-600 hover:underline">⬅️ Retour</a>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">💾 Enregistrer</button>
            </div>
        </form>
    <?php endif; ?>
</main>

</body>
</html>
