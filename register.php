<?php

// Inclusion du fichier de configuration (connexion à la base de données)
require_once 'includes/config.php';

// Initialisation d'un tableau pour stocker les messages d'erreurs
$erreurs = [];

// Vérifie si le formulaire a été soumis via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

     // Récupération et nettoyage des champs du formulaire
    $nom_utilisateur = trim($_POST['nom_utilisateur'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';
    $role = $_POST['role'] ?? 'lecteur'; // Par défaut, le rôle est "lecteur"

        // Sécurisation du champ rôle : évite qu'un utilisateur injecte un autre rôle non autorisé
    if (!in_array($role, ['lecteur', 'admin'])) {
        $role = 'lecteur';
    }

    if (empty($nom_utilisateur)) $erreurs[] = "Le nom d'utilisateur est requis.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide.";
    if (empty($mot_de_passe)) $erreurs[] = "Le mot de passe est requis.";
    elseif (strlen($mot_de_passe) < 6) $erreurs[] = "Minimum 6 caractères.";
    if ($mot_de_passe !== $confirmation) $erreurs[] = "Les mots de passe ne correspondent pas.";

    if (empty($erreurs)) {

        try {
             // Vérifie si le nom d'utilisateur ou l'email est déjà utilisé
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE nom_utilisateur = :nom_utilisateur OR email = :email");
            $stmt->execute(['nom_utilisateur' => $nom_utilisateur, 'email' => $email]);

             // Si un utilisateur existe déjà, on affiche une erreur
            if ($stmt->fetch()) {
                $erreurs[] = "Nom ou email déjà utilisé.";
            } else {

                 // Hachage sécurisé du mot de passe
                $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

                // Insertion du nouvel utilisateur dans la base
                $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom_utilisateur, email, mot_de_passe, role) 
                                       VALUES (:nom_utilisateur, :email, :mot_de_passe, :role)");
                $stmt->execute([
                    'nom_utilisateur' => $nom_utilisateur,
                    'email' => $email,
                    'mot_de_passe' => $hash,
                    'role' => $role
                ]);

                // Indique que l'inscription s'est bien déroulée
                $success = true;
            }
        } catch (PDOException $e) {

            // Capture et affiche les erreurs liées à la base de données
            $erreurs[] = "Erreur serveur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-100 to-purple-100 min-h-screen flex items-center justify-center px-4">

 <!-- Conteneur principal du formulaire -->
    <div class="bg-white shadow-md rounded-lg w-full max-w-md p-8">
        <h2 class="text-2xl font-bold text-center text-blue-700 mb-6">Créer un compte</h2>

        <!-- Message de succès -->
        <?php if (!empty($success)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                ✅ Inscription réussie ! Vous pouvez <a href="login.php" class="underline font-semibold">vous connecter</a>.
            </div>
        <?php endif; ?>

        <!--  Affichage des erreurs -->
        <?php foreach ($erreurs as $erreur): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-2">
                ❌ <?= htmlspecialchars($erreur) ?>
            </div>
        <?php endforeach; ?>

        <!-- Formulaire d'inscription -->
        <form method="POST" action="">
            <label class="block mb-2 font-medium text-gray-700">Nom d'utilisateur</label>
            <input type="text" name="nom_utilisateur" required
                value="<?= htmlspecialchars($_POST['nom_utilisateur'] ?? '') ?>"
                class="w-full px-4 py-2 border rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400">

            <label class="block mb-2 font-medium text-gray-700">Adresse Email</label>
            <input type="email" name="email" required
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                class="w-full px-4 py-2 border rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400">

            <label class="block mb-2 font-medium text-gray-700">Mot de passe</label>
            <input type="password" name="mot_de_passe" required
                class="w-full px-4 py-2 border rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400">

            <label class="block mb-2 font-medium text-gray-700">Confirmer le mot de passe</label>
            <input type="password" name="confirmation" required
                class="w-full px-4 py-2 border rounded-lg mb-4 focus:outline-none focus:ring-2 focus:ring-blue-400">

            <label class="block mb-2 font-medium text-gray-700">Rôle</label>
            <select name="role" required
                class="w-full px-4 py-2 border rounded-lg mb-6 focus:outline-none focus:ring-2 focus:ring-blue-400">
                <option value="lecteur" <?= (($_POST['role'] ?? '') === 'lecteur' ? 'selected' : '') ?>>Lecteur</option>
                <option value="admin" <?= (($_POST['role'] ?? '') === 'admin' ? 'selected' : '') ?>>Administrateur</option>
            </select>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                S'inscrire
            </button>
        </form>

        <!-- Lien pour ceux qui ont déjà un compte -->
        <p class="text-center text-sm mt-6">Déjà inscrit ? <a href="login.php" class="text-blue-600 font-medium hover:underline">Connexion</a></p>
    </div>
</body>
</html>
